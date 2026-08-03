<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\OwnerAnalyticsController;
use App\Http\Controllers\OwnerStockController;
use App\Http\Controllers\OwnerRestokController;
use App\Http\Controllers\OwnerReportController;
use App\Http\Controllers\StockPredictionController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportLaporanController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\StokMasukController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PengeluaranController;
use Illuminate\Support\Facades\Route;

Route::get('/jalankan-migrasi', function() {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return "Migrasi database berhasil dijalankan! Output: <pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->defaultDashboardRoute());
    }

    return redirect()->route('login');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    /* Pelanggan: hanya katalog baca + profil (lihat also grup profile di bawah) */
    Route::middleware('role:pelanggan')->group(function () {
        Route::get('/katalog', KatalogController::class)->name('katalog.index');
    });

    Route::middleware('role:admin,kasir,owner,pelanggan')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    /* Owner + admin toko: dashboard, pengguna, modul sistem (supplier, pelanggan, inventory, kasir) */
    /* Owner hanya READ — lapisan keamanan tambahan via middleware owner.readonly */
    Route::middleware(['role:admin,owner', 'owner.readonly'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        /* ---- Pengguna: hanya admin yang boleh write ---- */
        Route::middleware('role:admin')->group(function () {
            Route::resource('pengguna', UserController::class)
                ->only(['create', 'store', 'edit', 'update', 'destroy'])
                ->parameters(['pengguna' => 'user'])
                ->names([
                    'create'  => 'users.create',
                    'store'   => 'users.store',
                    'edit'    => 'users.edit',
                    'update'  => 'users.update',
                    'destroy' => 'users.destroy',
                ]);
        });
        Route::get('/pengguna', [UserController::class, 'index'])
            ->name('users.index');

        /* ---- Supplier: hanya admin yang boleh write ---- */
        Route::middleware('role:admin')->group(function () {
            Route::resource('supplier', SupplierController::class)
                ->only(['create', 'store', 'edit', 'update', 'destroy'])
                ->names([
                    'create'  => 'suppliers.create',
                    'store'   => 'suppliers.store',
                    'edit'    => 'suppliers.edit',
                    'update'  => 'suppliers.update',
                    'destroy' => 'suppliers.destroy',
                ]);
        });
        Route::get('/supplier', [SupplierController::class, 'index'])
            ->name('suppliers.index');

        /* ---- Pembelian: hanya admin yang boleh write ---- */
        Route::get('/pembelian/export/pdf', [PembelianController::class, 'exportPdf'])->name('pembelian.export.pdf');
        Route::get('/pembelian/export/excel', [PembelianController::class, 'exportExcel'])->name('pembelian.export.excel');
        Route::get('/pembelian/{pembelian}/cetak', [PembelianController::class, 'cetak'])->name('pembelian.cetak');
        Route::middleware('role:admin')->group(function () {
            Route::resource('pembelian', PembelianController::class)
                ->only(['create', 'store', 'edit', 'update', 'destroy']);
        });
        Route::resource('pembelian', PembelianController::class)
            ->only(['index', 'show']);

        /* ---- Pengeluaran: hanya admin yang boleh write ---- */
        Route::get('/pengeluaran/export/pdf', [PengeluaranController::class, 'exportPdf'])->name('pengeluaran.export.pdf');
        Route::get('/pengeluaran/export/excel', [PengeluaranController::class, 'exportExcel'])->name('pengeluaran.export.excel');
        Route::middleware('role:admin')->group(function () {
            Route::resource('pengeluaran', PengeluaranController::class)
                ->only(['create', 'store', 'edit', 'update', 'destroy']);
        });
        Route::resource('pengeluaran', PengeluaranController::class)
            ->only(['index', 'show']);

        /* ---- Pelanggan: hanya admin yang boleh write ---- */
        Route::middleware('role:admin')->group(function () {
            Route::resource('pelanggan', PelangganController::class)
                ->only(['create', 'store', 'edit', 'update', 'destroy'])
                ->names([
                    'create'  => 'pelanggan.create',
                    'store'   => 'pelanggan.store',
                    'edit'    => 'pelanggan.edit',
                    'update'  => 'pelanggan.update',
                    'destroy' => 'pelanggan.destroy',
                ]);
        });
        Route::get('/pelanggan', [PelangganController::class, 'index'])
            ->name('pelanggan.index');

        /* ---- Inventory: GET untuk semua, POST hanya admin ---- */
        Route::get('/stok-masuk', [InventoryController::class, 'index'])->name('stok-masuk.index');
        Route::get('/inventory/export/pdf', [InventoryController::class, 'exportPdf'])->name('inventory.export.pdf');
        Route::get('/inventory/export/excel', [InventoryController::class, 'exportExcel'])->name('inventory.export.excel');
        Route::get('/inventory/export-riwayat/pdf', [InventoryController::class, 'exportRiwayatPdf'])->name('inventory.export-riwayat.pdf');
        Route::get('/inventory/export-riwayat/excel', [InventoryController::class, 'exportRiwayatExcel'])->name('inventory.export-riwayat.excel');
        Route::get('/stok-masuk/search-product', [StokMasukController::class, 'searchProducts'])->name('stok-masuk.search-product');
        Route::middleware('role:admin')->group(function () {
            Route::post('/inventory/stock-opname', [InventoryController::class, 'storeStockOpname'])->name('inventory.stock-opname.store');
            Route::post('/inventory/penyesuaian', [InventoryController::class, 'storePenyesuaian'])->name('inventory.penyesuaian.store');
        });

        /* ---- Owner reports & stock monitoring ---- */
        Route::get('/owner/stok', OwnerStockController::class)->name('owner.stok');
        Route::get('/owner/stok/restok', OwnerRestokController::class)->name('owner.stok.restok');
        Route::get('/owner/reports', OwnerReportController::class)->name('owner.reports');
        Route::get('/owner/reports/pdf', [OwnerReportController::class, 'exportPdf'])->name('owner.reports.pdf');
        Route::get('/owner/reports/export/excel', [OwnerReportController::class, 'exportExcel'])->name('owner.reports.export.excel');
        Route::get('/owner/stok/kritis/pdf', [LaporanController::class, 'exportStokKritisPdf'])->name('owner.stok.kritis.pdf');
        Route::get('/owner/stok/kritis/excel', [LaporanController::class, 'exportStokKritisExcel'])->name('owner.stok.kritis.excel');
        Route::get('/laporan/produk-terlaris/pdf', [LaporanController::class, 'exportProdukTerlarisPdf'])->name('laporan.produk-terlaris.pdf');

        Route::get('/owner/produk/{produk}/prediksi-stok', [StockPredictionController::class, 'show'])
            ->name('owner.produk.prediksi-stok');
    });

    /* Admin + Owner + Kasir: Laporan Retur (melihat) */
    Route::middleware('role:admin,owner,kasir')->group(function () {
        Route::get('/laporan/retur', fn () => view('laporan.retur'))->name('laporan.retur');
        Route::get('/api/laporan/retur', [ReportLaporanController::class, 'returPenjualan'])->name('laporan.retur.index');
        Route::get('/api/laporan/retur/products', [ReportLaporanController::class, 'searchProducts'])->name('laporan.retur.search-products');
        Route::get('/api/laporan/retur/stats', [ReportLaporanController::class, 'returStats'])->name('laporan.retur.stats');
        Route::get('/laporan/retur/export/pdf', [ReportLaporanController::class, 'exportPdf'])->name('laporan.retur.export.pdf');
        Route::get('/laporan/retur/export/excel', [ReportLaporanController::class, 'exportExcel'])->name('laporan.retur.export.excel');
        Route::get('/laporan/retur/{id}', [ReportLaporanController::class, 'showDetailPage'])->name('laporan.retur.show');
    });

    /* Admin + Kasir: Input Retur Baru — Owner tidak boleh input retur */
    Route::middleware('role:admin,kasir')->group(function () {
        Route::post('/api/laporan/retur', [ReportLaporanController::class, 'storeRetur'])->name('laporan.retur.store');
    });


    /* Owner & Admin: Edit & Hapus Retur — Owner tetap tidak bisa karena owner.readonly */
    Route::middleware('role:admin')->group(function () {
        Route::put('/api/laporan/retur/{id}', [ReportLaporanController::class, 'updateRetur'])->name('laporan.retur.update');
        Route::delete('/api/laporan/retur/{id}', [ReportLaporanController::class, 'destroyRetur'])->name('laporan.retur.destroy');
    });

    /* Owner Only: Approval Retur */
    Route::middleware('role:owner')->group(function () {
        Route::post('/api/laporan/retur/{id}/approve', [ReportLaporanController::class, 'approveRetur'])->name('laporan.retur.approve');
        Route::post('/api/laporan/retur/{id}/reject', [ReportLaporanController::class, 'rejectRetur'])->name('laporan.retur.reject');
    });


    /* Owner + admin toko: master katalog (produk, kategori, satuan) */
    /* Owner hanya bisa GET (index, show). Write routes dibatasi ke admin. */
    Route::middleware(['role:admin,owner', 'owner.readonly'])->group(function () {
        Route::get('/produk/export/pdf', [ProductController::class, 'exportProdukPdf'])->name('produk.export.pdf');
        Route::get('/produk/export/excel', [ProductController::class, 'exportProdukExcel'])->name('produk.export.excel');
        Route::get('/produk/search-suggestions', [ProductController::class, 'searchSuggestions'])->name('products.search-suggestions');

        /* Index/show bisa diakses owner */
        Route::get('/produk', [ProductController::class, 'index'])
            ->name('products.index');
        Route::get('/kategori', [CategoryController::class, 'index'])
            ->name('categories.index');
        Route::get('/satuan', [SatuanController::class, 'index'])
            ->name('satuans.index');
        Route::redirect('/products', '/produk');

        /* Write routes: hanya admin */
        Route::middleware('role:admin')->group(function () {
            Route::post('kategori/quick-store', [CategoryController::class, 'storeJson'])->name('categories.store-json');
            Route::post('satuan/quick-store', [SatuanController::class, 'storeJson'])->name('satuans.store-json');

            Route::resource('kategori', CategoryController::class)
                ->except(['show', 'index'])
                ->parameters(['kategori' => 'category'])
                ->names([
                    'create'  => 'categories.create',
                    'store'   => 'categories.store',
                    'edit'    => 'categories.edit',
                    'update'  => 'categories.update',
                    'destroy' => 'categories.destroy',
                ]);

            Route::resource('satuan', SatuanController::class)
                ->except(['show', 'index'])
                ->parameters(['satuan' => 'satuan'])
                ->names([
                    'create'  => 'satuans.create',
                    'store'   => 'satuans.store',
                    'edit'    => 'satuans.edit',
                    'update'  => 'satuans.update',
                    'destroy' => 'satuans.destroy',
                ]);

            Route::resource('produk', ProductController::class)
                ->except(['show', 'index'])
                ->parameters(['produk' => 'product'])
                ->names([
                    'create'  => 'products.create',
                    'store'   => 'products.store',
                    'edit'    => 'products.edit',
                    'update'  => 'products.update',
                    'destroy' => 'products.destroy',
                ]);
        });
    });

    /* Kasir + admin: transaksi penjualan. Owner TIDAK termasuk — owner tidak melakukan penjualan. */
    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('/api/products/search', [KasirController::class, 'searchProducts'])->name('api.products.search');
        Route::get('/search-product', [KasirController::class, 'searchProducts'])->name('kasir.search-product');
        Route::post('/add-to-cart', [KasirController::class, 'addToCartUnified'])->name('kasir.add-to-cart');
        Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
        Route::get('/kasir/struk/{transaksi}', [KasirController::class, 'struk'])->name('kasir.struk');
        Route::get('/kasir/rawbt/{transaksi}', [KasirController::class, 'rawbt'])->name('kasir.rawbt');
        Route::post('/kasir/cart/add', [KasirController::class, 'addToCart'])->name('kasir.cart.add');
        Route::post('/kasir/cart/barcode', [KasirController::class, 'addToCartByBarcode'])->name('kasir.cart.barcode');
        Route::post('/kasir/cart/update', [KasirController::class, 'updateCart'])->name('kasir.cart.update');
        Route::post('/kasir/cart/update-price-type', [KasirController::class, 'updatePriceType'])
            ->name('kasir.cart.update-price-type');
        Route::post('/kasir/cart/remove', [KasirController::class, 'removeFromCart'])->name('kasir.cart.remove');
        Route::post('/kasir/cart/clear', [KasirController::class, 'clearCart'])->name('kasir.cart.clear');
        Route::post('/kasir/transaksi', [KasirController::class, 'storeTransaksi'])->name('kasir.transaksi.store');
        Route::post('/kasir/hold', [KasirController::class, 'holdCart'])->name('kasir.hold');
        Route::get('/kasir/hold/list', [KasirController::class, 'listHeld'])->name('kasir.hold.list');
        Route::post('/kasir/hold/{id}/resume', [KasirController::class, 'resumeHeld'])->name('kasir.hold.resume');
        Route::delete('/kasir/hold/{id}', [KasirController::class, 'deleteHeld'])->name('kasir.hold.delete');
    });


    /* Laporan kasir → owner: buat hanya kasir; lihat admin toko, owner, kasir */
    Route::middleware('role:kasir')->group(function () {
        Route::get('/laporan-kasir/buat', [ReportController::class, 'create'])->name('reports.create');
        Route::post('/laporan-kasir', [ReportController::class, 'store'])->name('reports.store');
    });

    Route::middleware('role:admin,owner,kasir')->group(function () {
        Route::get('/laporan-kasir', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan-kasir/{report}', [ReportController::class, 'show'])->name('reports.show');
    });

    /* Laporan finansial: gate (owner selalu, admin toko bila diizinkan config) */
    Route::middleware(['can:view-laporan-finansial'])->group(function () {
        Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');
        Route::get('/laporan/penjualan/detail', [LaporanController::class, 'penjualanDetail'])->name('laporan.penjualan.detail');
        Route::get('/laporan/laba', [LaporanController::class, 'laba'])->name('laporan.laba');
        Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPenjualanPdf'])->name('laporan.export.pdf');
        Route::get('/laporan/export/excel', [LaporanController::class, 'exportPenjualanExcel'])->name('laporan.export.excel');
        Route::match(['get', 'post'], '/laporan/laba/export/pdf', [LaporanController::class, 'exportLabaPdf'])->name('laporan.laba.export.pdf');
    });

    if (config('app.debug')) {
        Route::get('/_ui/pola-tampilan', function () {
            return view('examples.responsive-patterns');
        })->name('dev.ui.patterns');
    }
});
