<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePembelianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isOwner();
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'tanggal' => ['required', 'date'],
            'metode_pembayaran' => ['nullable', 'string', 'in:Cash,Transfer Bank,Tempo'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produk_id' => ['required', 'exists:produks,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.harga_beli' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier tidak valid.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'items.required' => 'Minimal harus ada satu produk.',
            'items.min' => 'Minimal harus ada satu produk.',
            'items.*.produk_id.required' => 'Produk wajib dipilih.',
            'items.*.produk_id.exists' => 'Produk tidak valid.',
            'items.*.qty.required' => 'Qty wajib diisi.',
            'items.*.qty.integer' => 'Qty harus berupa angka bulat.',
            'items.*.qty.min' => 'Qty minimal 1.',
            'items.*.harga_beli.required' => 'Harga beli wajib diisi.',
            'items.*.harga_beli.numeric' => 'Harga beli harus berupa angka.',
            'items.*.harga_beli.min' => 'Harga beli minimal 0.',
        ];
    }
}
