<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Supplier::class);

        // Auto-seed default suppliers if total suppliers count < 10
        if (Supplier::query()->count() < 10) {
            (new \Database\Seeders\SupplierSeeder())->run();
        }

        $suppliers = Supplier::query()
            ->withCount('stokMasuks')
            ->orderBy('nama_supplier')
            ->paginate(25);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $this->authorize('create', Supplier::class);

        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Supplier::class);

        $validated = $request->validate([
            'nama_supplier' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'no_hp' => ['nullable', 'string', 'max:30'],
        ]);

        Supplier::create($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        $this->authorize('update', $supplier);

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->authorize('update', $supplier);

        $validated = $request->validate([
            'nama_supplier' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'no_hp' => ['nullable', 'string', 'max:30'],
        ]);

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorize('delete', $supplier);

        if ($supplier->stokMasuks()->exists()) {
            return redirect()
                ->route('suppliers.index')
                ->with('error', 'Supplier tidak dapat dihapus karena memiliki riwayat stok masuk.');
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }
}
