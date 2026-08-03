@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Dashboard Owner</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-2">Total Produk</h2>
            <p class="text-3xl font-bold text-blue-600">{{ $totalProducts }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Produk Stok Rendah</h2>
            @if($lowStockProducts->isEmpty())
                <p class="text-gray-500">Semua produk memiliki stok yang cukup.</p>
            @else
                <ul class="space-y-2">
                    @foreach($lowStockProducts as $product)
                        <li class="flex justify-between items-center p-2 bg-red-50 rounded">
                            <span>{{ $product->nama }}</span>
                            <span class="font-semibold text-red-600">Stok: {{ $product->stok }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Stok Masuk Terbaru</h2>
            @if($recentStockIns->isEmpty())
                <p class="text-gray-500">Belum ada data stok masuk.</p>
            @else
                <ul class="space-y-2">
                    @foreach($recentStockIns as $stock)
                        <li class="p-2 bg-green-50 rounded">
                            <div class="font-semibold">{{ $stock->product->nama }}</div>
                            <div class="text-sm text-gray-600">
                                Jumlah: {{ $stock->quantity }} | {{ $stock->created_at->format('d/m/Y H:i') }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="mt-8">
        <a href="{{ route('laporan.penjualan') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Lihat Laporan Penjualan
        </a>
        <a href="{{ route('reports.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ml-4">
            Lihat Laporan Kasir
        </a>
    </div>
</div>
@endsection
