<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Product;
use App\Models\DetailTransaksi;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KasirController extends Controller
{
    private const CART_KEY = 'pos_cart';

    private function cartLineId(int $produkId, string $jenisHarga): string
    {
        return $produkId.'|'.$jenisHarga;
    }

    /**
     * Normalisasi session keranjang: format lama { id => qty } → format baru array of lines.
     *
     * @return list<array{produk_id: int, jenis_harga: string, qty: int}> qty = qty yang diinput (pcs untuk ecer/grosir, bal untuk bal)
     */
    private function normalizeCart(array $raw): array
    {
        if ($raw === []) {
            return [];
        }

        $first = reset($raw);
        if (is_array($first) && isset($first['produk_id'], $first['jenis_harga'], $first['qty'])) {
            return array_values($raw);
        }

        $out = [];
        foreach ($raw as $pid => $qty) {
            $out[] = [
                'produk_id' => (int) $pid,
                'jenis_harga' => Product::JENIS_ECERAN,
                'qty' => (int) $qty,
            ];
        }

        return $out;
    }

    /**
     * @param  list<array{produk_id: int, jenis_harga: string, qty: int}>  $lines  qty = input kasir (bukan pcs jika Bal)
     */
    private function saveCart(array $lines): void
    {
        session([self::CART_KEY => array_values($lines)]);
    }

    private function jenisHargaRule(): array
    {
        return [
            'required',
            'string',
            Rule::in([Product::JENIS_ECERAN, Product::JENIS_GROSIR, Product::JENIS_BAL]),
        ];
    }

    /**
     * Pencarian produk untuk autocomplete kasir (AJAX).
     */
    public function searchProducts(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $pattern = '%'.addcslashes($q, '%_\\').'%';

        $rows = Product::query()
            ->where('is_active', true)
            ->where('stok', '>', 0)
            ->where('nama', 'LIKE', $pattern)
            ->orderBy('nama')
            ->limit(15)
            ->get(['id', 'nama', 'barcode', 'kode', 'stok', 'harga_jual']);

        return response()->json($rows->map(fn (Product $p) => [
            'id' => $p->id,
            'nama' => $p->nama,
            'barcode' => $p->barcode,
            'kode' => $p->kode,
            'stok' => $p->stok,
            'harga_jual' => (float) $p->harga_jual,
        ]));
    }

    /**
     * Data keranjang untuk respons AJAX (tanpa reload halaman).
     *
     * @return array<string, mixed>
     */
    private function cartFragmentPayload(): array
    {
        $cart = $this->normalizeCart(session(self::CART_KEY, []));
        $lines = $this->buildCartLines($cart);
        $total = $this->sumLines($lines);
        $cartBlocked = $lines->contains(fn ($l) => ! empty($l['stok_kurang']));

        return [
            'total' => $total,
            'total_formatted' => 'Rp '.number_format($total, 0, ',', '.'),
            'cart_html' => view('kasir.partials.cart-body', ['lines' => $lines])->render(),
            'cart_empty' => $lines->isEmpty(),
            'cart_blocked' => $cartBlocked,
            'item_count' => $lines->sum('qty'),
        ];
    }

    private function addToCartResponse(Request $request, bool $success, string $message): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if ($request->expectsJson()) {
            $payload = [
                'ok' => $success,
                'message' => $message,
            ];
            if ($success) {
                $payload = array_merge($payload, $this->cartFragmentPayload());
            }

            return response()->json($payload, $success ? 200 : 422);
        }

        return $success
            ? back()->with('success', $message)
            : back()->with('error', $message);
    }

    public function index()
    {
        app(\App\Services\HoldTransactionService::class)->autoDeleteOldHolds();

        $products = Product::query()
            ->with('satuanModel')
            ->where('is_active', true)
            ->where('stok', '>', 0)
            ->orderBy('nama')
            ->get();

        $cart = $this->normalizeCart(session(self::CART_KEY, []));
        $this->saveCart($cart);

        $lines = $this->buildCartLines($cart);
        $pelanggans = Pelanggan::query()->orderBy('nama')->get();
        $heldCount = \App\Models\HoldTransaction::count();

        return view('kasir.index', [
            'products' => $products,
            'lines' => $lines,
            'total' => $this->sumLines($lines),
            'pelanggans' => $pelanggans,
            'jenisHargaList' => Product::jenisHargaList(),
            'heldCount' => $heldCount,
        ]);
    }

    public function addToCart(Request $request)
    {
        $data = $request->validate([
            'produk_id' => ['required', 'integer', 'exists:produks,id'],
            'qty' => ['nullable', 'integer', 'min:1'],
            'jenis_harga' => $this->jenisHargaRule(),
        ]);

        $qty = $data['qty'] ?? 1;
        $jenis = $data['jenis_harga'];

        $product = Product::query()->find($data['produk_id']);
        if (! $product || ! $product->is_active || $product->stok < 1) {
            return $this->addToCartResponse($request, false, 'Produk tidak tersedia atau stok habis.');
        }

        $cart = $this->normalizeCart(session(self::CART_KEY, []));
        $found = false;
        foreach ($cart as $i => $line) {
            if ($line['produk_id'] === $product->id && $line['jenis_harga'] === $jenis) {
                $cart[$i]['qty'] = $line['qty'] + $qty;
                $found = true;
                break;
            }
        }

        if (! $found) {
            $cart[] = [
                'produk_id' => $product->id,
                'jenis_harga' => $jenis,
                'qty' => $qty,
            ];
        }

        $newQtyPcs = 0;
        foreach ($cart as $line) {
            if ($line['produk_id'] === $product->id) {
                $newQtyPcs += $product->hitungQtyPcs($line['jenis_harga'], (int) $line['qty']);
            }
        }

        if ($newQtyPcs > (int) $product->stok) {
            return $this->addToCartResponse($request, false, 'Stok tidak mencukupi untuk produk ini.');
        }

        $this->saveCart($cart);

        return $this->addToCartResponse($request, true, 'Produk ditambahkan ke keranjang.');
    }

    /**
     * Tambah ke keranjang (AJAX): kirim salah satu — id produk atau code/barcode/nama (satu jalur dengan scan/ketik).
     */
    public function addToCartUnified(Request $request)
    {
        $request->validate([
            'jenis_harga' => $this->jenisHargaRule(),
            'qty' => ['nullable', 'integer', 'min:1'],
            'id' => ['nullable', 'integer', 'exists:produks,id'],
            'code' => ['nullable', 'string', 'max:255'],
        ]);

        $hasId = $request->filled('id');
        $hasCode = $request->filled('code');

        if ($hasId && $hasCode) {
            return $this->addToCartResponse($request, false, 'Kirim salah satu: id atau code.');
        }

        if (! $hasId && ! $hasCode) {
            return $this->addToCartResponse($request, false, 'Kirim id produk atau code/barcode.');
        }

        if ($hasId) {
            $request->merge(['produk_id' => (int) $request->input('id')]);

            return $this->addToCart($request);
        }

        $request->merge(['barcode' => trim((string) $request->input('code'))]);

        return $this->addToCartByBarcode($request);
    }

    /**
     * @return array{product: ?Product, error: ?string}
     */
    protected function resolveProductFromSearch(string $term): array
    {
        $term = trim($term);
        if ($term === '') {
            return ['product' => null, 'error' => 'Masukkan barcode atau nama produk.'];
        }

        $byBarcode = Product::query()->where('barcode', $term)->first();
        if ($byBarcode) {
            return ['product' => $byBarcode, 'error' => null];
        }

        $lower = Str::lower($term);
        $exactNama = Product::query()
            ->whereRaw('LOWER(nama) = ?', [$lower])
            ->get();

        if ($exactNama->count() === 1) {
            return ['product' => $exactNama->first(), 'error' => null];
        }

        if ($exactNama->count() > 1) {
            return [
                'product' => null,
                'error' => 'Lebih dari satu produk dengan nama yang sama. Gunakan barcode.',
            ];
        }

        $pattern = '%'.addcslashes($term, '%_\\').'%';
        $partial = Product::query()->where('nama', 'LIKE', $pattern)->get();

        if ($partial->count() === 1) {
            return ['product' => $partial->first(), 'error' => null];
        }

        if ($partial->count() > 1) {
            return [
                'product' => null,
                'error' => 'Beberapa produk cocok dengan "'.$term.'". Perjelas nama atau gunakan barcode.',
            ];
        }

        return ['product' => null, 'error' => 'Produk tidak ditemukan (cek barcode atau nama).'];
    }

    public function addToCartByBarcode(Request $request)
    {
        $data = $request->validate([
            'barcode' => ['required', 'string', 'max:255'],
            'jenis_harga' => $this->jenisHargaRule(),
        ]);

        $term = trim($data['barcode']);
        $resolved = $this->resolveProductFromSearch($term);
        $product = $resolved['product'];

        if ($resolved['error'] !== null || ! $product) {
            return $this->addToCartResponse($request, false, $resolved['error'] ?? 'Produk tidak ditemukan.');
        }

        if (! $product->is_active || $product->stok < 1) {
            return $this->addToCartResponse($request, false, 'Produk tidak tersedia atau stok habis.');
        }

        $request->merge([
            'produk_id' => $product->id,
            'qty' => 1,
            'jenis_harga' => $data['jenis_harga'],
        ]);

        return $this->addToCart($request);
    }

    public function updateCart(Request $request)
    {
        $data = $request->validate([
            'line_id' => ['required', 'string', 'max:64'],
            'qty' => ['required', 'integer', 'min:0'],
        ]);

        $parts = explode('|', $data['line_id'], 2);
        if (count($parts) !== 2) {
            return back()->with('error', 'Baris keranjang tidak valid.');
        }

        [$produkId, $jenis] = [(int) $parts[0], $parts[1]];
        if (! in_array($jenis, [Product::JENIS_ECERAN, Product::JENIS_GROSIR, Product::JENIS_BAL], true)) {
            return back()->with('error', 'Jenis harga tidak valid.');
        }

        $cart = $this->normalizeCart(session(self::CART_KEY, []));

        if ($data['qty'] === 0) {
            $cart = array_values(array_filter($cart, fn ($line) => ! ($line['produk_id'] === $produkId && $line['jenis_harga'] === $jenis)));
            $this->saveCart($cart);

            return back()->with('success', 'Item dihapus dari keranjang.');
        }

        $product = Product::query()->find($produkId);
        if (! $product || ! $product->is_active) {
            $cart = array_values(array_filter($cart, fn ($line) => ! ($line['produk_id'] === $produkId && $line['jenis_harga'] === $jenis)));
            $this->saveCart($cart);

            return back()->with('error', 'Produk tidak valid; dihapus dari keranjang.');
        }

        $sumOtherPcs = 0;
        foreach ($cart as $line) {
            if ($line['produk_id'] === $produkId && $line['jenis_harga'] !== $jenis) {
                $sumOtherPcs += $product->hitungQtyPcs($line['jenis_harga'], (int) $line['qty']);
            }
        }

        $qtyPcsBaris = $product->hitungQtyPcs($jenis, (int) $data['qty']);
        if ($sumOtherPcs + $qtyPcsBaris > (int) $product->stok) {
            return back()->with('error', 'Stok tidak mencukupi (tersedia: ' . $product->stok . ' pcs).');
        }

        foreach ($cart as $i => $line) {
            if ($line['produk_id'] === $produkId && $line['jenis_harga'] === $jenis) {
                $cart[$i]['qty'] = $data['qty'];
                break;
            }
        }

        $this->saveCart($cart);

$lines = $this->buildCartLines($cart);

return response()->json([
    'ok' => true,
    'cart_html' => view(
        'kasir.partials.cart-body',
        compact('lines')
    )->render(),

    'total' => $this->sumLines($lines),

    'total_formatted' => 'Rp ' . number_format(
        $this->sumLines($lines),
        0,
        ',',
        '.'
    ),

    'cart_empty' => $lines->isEmpty(),

    'cart_blocked' => $lines->contains(
        fn($l) => !empty($l['stok_kurang'])
    ),
    'item_count' => $lines->sum('qty'),
]);

        return back()->with('success', 'Keranjang diperbarui.');
    }
    
    public function updatePriceType(Request $request)
{
    $request->validate([
        'line_id' => 'required',
        'jenis_harga' => 'required|in:eceran,grosir,bal',
    ]);

    $cart = session(self::CART_KEY, []);

    foreach ($cart as &$item) {
        $currentLineId = $this->cartLineId(
            $item['produk_id'],
            $item['jenis_harga']
        );

        if ($currentLineId === $request->line_id) {
            $item['jenis_harga'] = $request->jenis_harga;
            break;
        }
    }

    session([self::CART_KEY => $cart]);

    $lines = $this->buildCartLines($cart);

    return response()->json([
        'ok' => true,
        'cart_html' => view(
            'kasir.partials.cart-body',
            compact('lines')
        )->render(),
        'total' => $this->sumLines($lines),
        'total_formatted' => 'Rp ' . number_format(
            $this->sumLines($lines),
            0,
            ',',
            '.'
        ),
        'cart_empty' => $lines->isEmpty(),
        'cart_blocked' => $lines->contains(
            fn ($l) => !empty($l['stok_kurang'])
        ),
        'item_count' => $lines->sum('qty'),
    ]);
}

    public function removeFromCart(Request $request)
{
    $data = $request->validate([
        'line_id' => ['required', 'string', 'max:64'],
    ]);

    $parts = explode('|', $data['line_id'], 2);

    if (count($parts) !== 2) {
        return response()->json([
            'ok' => false,
            'message' => 'Baris keranjang tidak valid.'
        ], 422);
    }

    [$produkId, $jenis] = [(int)$parts[0], $parts[1]];

    $cart = array_values(array_filter(
        $this->normalizeCart(session(self::CART_KEY, [])),
        fn($line) => !(
            $line['produk_id'] === $produkId
            && $line['jenis_harga'] === $jenis
        )
    ));

    $this->saveCart($cart);

    // rebuild cart
    $lines = $this->buildCartLines($cart);

    $total = $lines->sum('subtotal');

    return response()->json([
        'ok' => true,
        'cart_html' => view(
            'kasir.partials.cart-body',
            compact('lines')
        )->render(),
        'total' => $total,
        'total_formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
        'cart_empty' => $lines->isEmpty(),
        'cart_blocked' => $lines->contains(fn($l) => !empty($l['stok_kurang'])),
        'item_count' => $lines->sum('qty'),
    ]);
}

    public function storeTransaksi(Request $request)
    {
        $cart = $this->normalizeCart(session(self::CART_KEY, []));
        if ($cart === []) {
            return back()->with('error', 'Keranjang kosong.');
        }

        $lines = $this->buildCartLines($cart);
        if ($lines->isEmpty()) {
            session()->forget(self::CART_KEY);

            return back()->with('error', 'Tidak ada baris keranjang yang valid.');
        }

        foreach ($lines as $line) {
            if (($line['stok_kurang'] ?? false) === true) {
                return back()->with('error', 'Perbaiki jumlah di keranjang: stok tidak mencukupi untuk "' . $line['nama'] . '".');
            }
        }

        $total = $this->sumLines($lines);

        $metodePembayaran = $request->input('metode_pembayaran', 'Cash');
        if (!in_array($metodePembayaran, ['Cash', 'Transfer Bank', 'QRIS'], true)) {
            $metodePembayaran = 'Cash';
        }

        $rules = [
            'pelanggan_id' => ['nullable', 'integer', 'exists:pelanggans,id'],
            'metode_pembayaran' => ['required', 'string', 'in:Cash,Transfer Bank,QRIS'],
        ];

        if ($metodePembayaran === 'Cash') {
            $rules['bayar'] = ['required', 'numeric', 'gte:' . $total];
        } elseif ($metodePembayaran === 'Transfer Bank') {
            $rules['nama_bank'] = ['required', 'string', 'in:BCA,BRI,BNI,Mandiri,Bank Kalsel,Bank Lainnya'];
            $rules['nomor_referensi'] = ['nullable', 'string', 'max:255'];
        } elseif ($metodePembayaran === 'QRIS') {
            $rules['nomor_referensi'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        if ($metodePembayaran === 'Cash') {
            $bayar = (float) $validated['bayar'];
            $kembalian = round($bayar - $total, 2);
            $namaBank = null;
            $nomorReferensi = null;
        } else {
            $bayar = $total;
            $kembalian = 0.0;
            $namaBank = $validated['nama_bank'] ?? null;
            $nomorReferensi = $validated['nomor_referensi'] ?? null;
        }

        $pelangganId = $validated['pelanggan_id'] ?? null;
        $namaPelanggan = null;
        if ($pelangganId) {
            $namaPelanggan = Pelanggan::query()->whereKey($pelangganId)->value('nama');
        }

        try {
            $transaksi = DB::transaction(function () use ($lines, $total, $bayar, $kembalian, $pelangganId, $namaPelanggan, $metodePembayaran, $namaBank, $nomorReferensi) {
                $transaksi = Transaksi::create([
                    'pelanggan_id' => $pelangganId,
                    'nama_pelanggan' => $namaPelanggan,
                    'tanggal' => now(),
                    'total' => $total,
                    'bayar' => $bayar,
                    'kembalian' => $kembalian,
                    'metode_pembayaran' => $metodePembayaran,
                    'nama_bank' => $namaBank,
                    'nomor_referensi' => $nomorReferensi,
                ]);

                foreach ($lines as $line) {
                    $product = Product::query()
                        ->whereKey($line['produk_id'])
                        ->lockForUpdate()
                        ->first();

                    $qtyPcs = (int) $line['qty_pcs'];
                    if (! $product || $qtyPcs < 1 || $qtyPcs > (int) $product->stok) {
                        throw new \RuntimeException('Stok berubah saat transaksi; coba lagi.');
                    }

                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $product->id,
                        'jenis_harga' => $line['jenis_harga'],
                        'qty_input' => (int) $line['qty_input'],
                        'qty_pcs' => $qtyPcs,
                        'qty' => $qtyPcs,
                        'harga' => $line['harga'],
                        'subtotal' => $line['subtotal'],
                    ]);

                    $product->decrement('stok', $qtyPcs);
                    \App\Models\StokLog::logChange($product->id, 'Penjualan', 0, $qtyPcs, 'TRX-' . $transaksi->id, auth()->id() ?? null, 'Penjualan Kasir');
                }

                return $transaksi;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        session()->forget(self::CART_KEY);

        if (session()->has('hold_transaction_id')) {
            $holdId = session('hold_transaction_id');
            app(\App\Services\HoldTransactionService::class)->delete($holdId);
            session()->forget('hold_transaction_id');
        }

session([
    'last_transaction_id' => $transaksi->id
]);

return redirect()
    ->route('kasir.struk', $transaksi)
    ->with([
        'success' => 'Transaksi #' . $transaksi->id . ' berhasil.',
        'struk_autoprint' => true,
    ]);
    }

    public function struk(Transaksi $transaksi)
    {
        $transaksi->load(['detailTransaksis.product', 'pelanggan']);

        return view('kasir.struk', compact('transaksi'));
    }

    public function rawbt(Transaksi $transaksi)
{
    $transaksi->load(['detailTransaksis.product', 'pelanggan']);

    return view('kasir.rawbt', compact('transaksi'));
}
    /**
     * @param  list<array{produk_id: int, jenis_harga: string, qty: int}>  $cart
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function buildCartLines(array $cart)
    {
        if ($cart === []) {
            return collect();
        }

        $ids = array_unique(array_column($cart, 'produk_id'));
        $products = Product::query()->whereIn('id', $ids)->get()->keyBy('id');

        $pcsByProduct = [];
        foreach ($cart as $l) {
            $pid = (int) $l['produk_id'];
            $p = $products->get($pid);
            if (! $p) {
                continue;
            }
            $pcsByProduct[$pid] = ($pcsByProduct[$pid] ?? 0) + $p->hitungQtyPcs($l['jenis_harga'], (int) $l['qty']);
        }

        return collect($cart)
            ->map(function ($line) use ($products, $pcsByProduct) {
                $qtyInput = (int) $line['qty'];
                $jenis = $line['jenis_harga'];
                $id = (int) $line['produk_id'];

                if ($qtyInput < 1) {
                    return null;
                }

                $product = $products->get($id);
                if (! $product || ! $product->is_active) {
                    return null;
                }

                if (! in_array($jenis, [Product::JENIS_ECERAN, Product::JENIS_GROSIR, Product::JENIS_BAL], true)) {
                    return null;
                }

                $harga = $product->hargaUntukJenis($jenis);
                $qtyPcs = $product->hitungQtyPcs($jenis, $qtyInput);
                $stokKurang = ($pcsByProduct[$id] ?? 0) > (int) $product->stok;

                $isiBal = $product->pcsPerSatuanBal();
                $previewBal = $jenis === Product::JENIS_BAL
                    ? '1 bal = '.$isiBal.' pcs'
                    : null;
                $previewStok = 'Stok akan berkurang '.number_format($qtyPcs, 0, ',', '.').' pcs';

                return [
                    'line_id' => $this->cartLineId($product->id, $jenis),
                    'produk_id' => $product->id,
                    'nama' => $product->nama,
                    'kode' => $product->kode,
                    'jenis_harga' => $jenis,
                    'jenis_label' => Product::labelJenisHarga($jenis),
                    'qty' => $qtyInput,
                    'qty_input' => $qtyInput,
                    'qty_pcs' => $qtyPcs,
                    'harga' => $harga,
                    'subtotal' => round($harga * $qtyInput, 2),
                    'stok_tersedia' => (int) $product->stok,
                    'stok_kurang' => $stokKurang,
                    'preview_bal' => $previewBal,
                    'preview_stok_kurang' => $previewStok,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $lines
     */
    private function sumLines($lines): float
    {
        return round((float) $lines->sum('subtotal'), 2);
    }

    public function holdCart(Request $request)
    {
        $cart = $this->normalizeCart(session(self::CART_KEY, []));
        if ($cart === []) {
            return response()->json(['ok' => false, 'message' => 'Keranjang kosong.'], 422);
        }

        $lines = $this->buildCartLines($cart);
        $total = $this->sumLines($lines);

        $pelanggan = $request->input('pelanggan');
        $catatan = $request->input('catatan');
        $kasirId = auth()->id();

        $service = app(\App\Services\HoldTransactionService::class);
        $hold = $service->hold($cart, $kasirId, $pelanggan, $catatan, $total);

        return response()->json([
            'ok' => true,
            'message' => 'Transaksi berhasil disimpan sementara (Code: ' . $hold->code . ').',
            'heldCount' => \App\Models\HoldTransaction::count(),
        ]);
    }

    public function listHeld(Request $request)
    {
        $held = \App\Models\HoldTransaction::with('kasir')->orderBy('id', 'desc')->get();

        return response()->json($held->map(fn($h) => [
            'id' => $h->id,
            'code' => $h->code,
            'pelanggan' => $h->pelanggan,
            'catatan' => $h->catatan,
            'total' => $h->total,
            'total_formatted' => 'Rp ' . number_format($h->total, 0, ',', '.'),
            'item_count' => collect($h->cart_data)->sum('qty'),
            'time' => $h->created_at->timezone('Asia/Makassar')->format('H:i'),
            'date_formatted' => $h->created_at->format('d M Y'),
        ]));
    }

    public function resumeHeld(Request $request, $id)
    {
        $service = app(\App\Services\HoldTransactionService::class);
        $hold = $service->resume((int) $id);

        return response()->json([
            'ok' => true,
            'message' => 'Transaksi ' . $hold->code . ' berhasil dilanjutkan.',
        ]);
    }

    public function deleteHeld(Request $request, $id)
    {
        $service = app(\App\Services\HoldTransactionService::class);
        $deleted = $service->delete((int) $id);

        return response()->json([
            'ok' => $deleted,
            'message' => $deleted ? 'Transaksi ditunda berhasil dihapus.' : 'Transaksi ditunda tidak ditemukan.',
            'heldCount' => \App\Models\HoldTransaction::count(),
        ]);
    }

    public function clearCart(Request $request)
    {
        session()->forget(self::CART_KEY);
        session()->forget('hold_transaction_id');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Keranjang berhasil dikosongkan.',
                'total' => 0,
                'total_formatted' => 'Rp 0',
                'cart_html' => view('kasir.partials.cart-body', ['lines' => collect()])->render(),
                'cart_empty' => true,
                'cart_blocked' => false,
                'item_count' => 0,
            ]);
        }

        return back()->with('success', 'Keranjang berhasil dikosongkan.');
    }
}
