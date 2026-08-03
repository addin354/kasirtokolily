<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SatuanController extends Controller
{
    public function index(): RedirectResponse
    {
        $this->authorize('viewAny', Satuan::class);

        return redirect()->route('products.index', ['tab' => 'satuan']);
    }

    public function create()
    {
        $this->authorize('create', Satuan::class);

        return view('satuans.create');
    }

    public function storeJson(Request $request): JsonResponse
    {
        $this->authorize('create', Satuan::class);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:50', 'unique:satuans,nama'],
        ]);

        $satuan = Satuan::create($validated);

        return response()->json([
            'id' => $satuan->id,
            'nama' => $satuan->nama,
            'message' => 'Satuan berhasil ditambahkan.',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Satuan::class);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:50', 'unique:satuans,nama'],
        ]);

        Satuan::create($validated);

        return redirect()
            ->route('products.index', ['tab' => 'satuan'])
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Satuan $satuan)
    {
        $this->authorize('update', $satuan);

        return view('satuans.edit', compact('satuan'));
    }

    public function update(Request $request, Satuan $satuan): RedirectResponse
    {
        $this->authorize('update', $satuan);

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:50',
                Rule::unique('satuans', 'nama')->ignore($satuan->id),
            ],
        ]);

        $satuan->update($validated);

        return redirect()
            ->route('products.index', ['tab' => 'satuan'])
            ->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Satuan $satuan): RedirectResponse
    {
        $this->authorize('delete', $satuan);

        if ($satuan->products()->exists()) {
            return redirect()
                ->route('products.index', ['tab' => 'satuan'])
                ->with('error', 'Satuan tidak bisa dihapus karena masih dipakai oleh produk.');
        }

        $satuan->delete();

        return redirect()
            ->route('products.index', ['tab' => 'satuan'])
            ->with('success', 'Satuan berhasil dihapus.');
    }
}
