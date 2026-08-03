<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PelangganController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Pelanggan::class);

        $pelanggans = Pelanggan::query()
            ->orderBy('nama')
            ->paginate(15);

        return view('pelanggan.index', compact('pelanggans'));
    }

    public function create()
    {
        $this->authorize('create', Pelanggan::class);

        return view('pelanggan.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Pelanggan::class);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:pelanggans,nama'],
        ]);

        Pelanggan::create($validated);

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Pelanggan $pelanggan)
    {
        $this->authorize('update', $pelanggan);

        return view('pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $this->authorize('update', $pelanggan);

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pelanggans', 'nama')->ignore($pelanggan->id),
            ],
        ]);

        $pelanggan->update($validated);

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $this->authorize('delete', $pelanggan);

        $pelanggan->delete();

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}
