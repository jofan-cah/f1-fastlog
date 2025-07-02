@extends('layouts.app')

@section('title', 'Detail Barang - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="itemDetail()">
    <!-- Breadcrumb -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-red-600">
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="{{ route('items.index') }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                        Barang
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">{{ $item->item_name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-br from-red-600 to-red-700 rounded-2xl flex items-center justify-center">
                <i class="fas fa-box text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $item->item_name }}</h1>
                <p class="text-gray-600 mt-1">{{ $item->item_code }} • {{ $item->getStatusText() }}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('items.edit', $item->item_id) }}"
               class="px-4 py-2 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-edit"></i>
                <span>Edit Barang</span>
            </a>
            <button @click="showToggleModal()"
                    class="px-4 py-2 rounded-xl transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl {{ $item->is_active ? 'bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800' : 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' }} text-white">
                <i class="fas fa-{{ $item->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                <span>{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
            </button>
        </div>
    </div>

    <!-- Status & Stock Badges -->
    <div class="flex items-center space-x-3 flex-wrap">
        <!-- Status Badge -->
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
            <span class="w-2 h-2 rounded-full mr-2 {{ $item->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
            {{ $item->getStatusText() }}
        </span>

        <!-- Stock Status Badge -->
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            @if($stockInfo['status'] == 'sufficient') bg-green-100 text-green-800
            @elseif($stockInfo['status'] == 'low') bg-yellow-100 text-yellow-800
            @elseif($stockInfo['status'] == 'empty') bg-red-100 text-red-800
            @else bg-gray-100 text-gray-800 @endif">
            <i class="fas fa-boxes mr-2"></i>
            {{ $stockInfo['status_text'] }}
        </span>

        <!-- Category Badge -->
        @if($item->category)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                <i class="fas fa-tags mr-2"></i>
                {{ $item->category->category_name }}
            </span>
        @endif

        <!-- QR Code Badge -->
        @if($item->hasQRCode())
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                <i class="fas fa-qrcode mr-2"></i>
                QR Code Available
            </span>
        @endif

        <!-- ID Badge -->
        <span class="text-sm text-gray-500">ID: {{ $item->item_id }}</span>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Informasi Barang
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kode Barang</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm font-mono text-gray-900">{{ $item->item_code }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $item->item_name }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $item->getCategoryPath() }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Satuan</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $item->unit }}</span>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Stok</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-900">{{ $item->min_stock }} {{ $item->unit }}</span>
                                    @if($stockInfo['available'] <= $item->min_stock && $stockInfo['available'] > 0)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Stok di bawah minimum
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($item->description)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <p class="text-sm text-gray-900">{{ $item->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Stock Information Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-warehouse mr-2 text-green-600"></i>
                        Informasi Stok
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Available Stock -->
                        <div class="text-center p-4 bg-blue-50 rounded-xl">
                            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-boxes text-white"></i>
                            </div>
                            <div class="text-2xl font-bold text-blue-600">{{ $stockInfo['available'] }}</div>
                            <div class="text-sm text-gray-600">Stok Tersedia</div>
                        </div>

                        <!-- Used Stock -->
                        <div class="text-center p-4 bg-yellow-50 rounded-xl">
                            <div class="w-12 h-12 bg-yellow-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-shipping-fast text-white"></i>
                            </div>
                            <div class="text-2xl font-bold text-yellow-600">{{ $stockInfo['used'] }}</div>
                            <div class="text-sm text-gray-600">Stok Terpakai</div>
                        </div>

                        <!-- Total Stock -->
                        <div class="text-center p-4 bg-green-50 rounded-xl">
                            <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-warehouse text-white"></i>
                            </div>
                            <div class="text-2xl font-bold text-green-600">{{ $stockInfo['total'] }}</div>
                            <div class="text-sm text-gray-600">Total Stok</div>
                        </div>
                    </div>

                    <!-- Stock Progress Bar -->
                    @if($stockInfo['total'] > 0)
                        <div class="mt-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Utilisasi Stok</span>
                                <span class="text-sm text-gray-500">{{ round(($stockInfo['used'] / $stockInfo['total']) * 100, 1) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-blue-600 to-green-600 h-2 rounded-full" style="width: {{ ($stockInfo['used'] / $stockInfo['total']) * 100 }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-exchange-alt mr-2 text-purple-600"></i>
                            Transaksi Terbaru
                        </h3>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800">
                            Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    @if($recentTransactions->count() > 0)
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($recentTransactions as $transaction)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($transaction->transaction_type == 'in') bg-green-100 text-green-800
                                                @elseif($transaction->transaction_type == 'out') bg-red-100 text-red-800
                                                @else bg-blue-100 text-blue-800 @endif">
                                                {{ ucfirst($transaction->transaction_type ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $transaction->quantity ?? 0 }} {{ $item->unit }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $transaction->createdBy->name ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-8 text-center">
                            <i class="fas fa-exchange-alt text-4xl text-gray-300 mb-4"></i>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Transaksi</h4>
                            <p class="text-gray-500">Belum ada riwayat transaksi untuk barang ini</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Item Details -->
            @if($item->itemDetails->count() > 0)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-list-ul mr-2 text-indigo-600"></i>
                            Detail Barang Individual
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serial/Batch</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($item->itemDetails->take(5) as $detail)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $detail->serial_number ?? $detail->batch_number ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $detail->status ?? 'Available' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $detail->location ?? 'Warehouse' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $detail->created_at->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column - Actions & QR -->
        <div class="space-y-6">
            <!-- QR Code Card -->
            @if($item->hasQRCode())
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-qrcode mr-2 text-purple-600"></i>
                            QR Code
                        </h3>
                    </div>
                    <div class="p-6 text-center">
                        <div class="w-48 h-48 mx-auto mb-4 bg-white border border-gray-200 rounded-lg flex items-center justify-center">
                            <img src="{{ $item->getQRCodePath() }}" alt="QR Code {{ $item->item_code }}" class="max-w-full max-h-full">
                        </div>
                        <div class="space-y-3">
                            <a href="{{ route('items.download-qr', $item->item_id) }}"
                               class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-download"></i>
                                <span>Download QR</span>
                            </a>
                            <p class="text-xs text-gray-500">Scan untuk akses cepat informasi barang</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-qrcode mr-2 text-purple-600"></i>
                            QR Code
                        </h3>
                    </div>
                    <div class="p-6 text-center">
                        <div class="w-48 h-48 mx-auto mb-4 bg-gray-100 border border-gray-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-qrcode text-4xl text-gray-400"></i>
                        </div>
                        <div class="space-y-3">
                            <button @click="showGenerateQRModal()"
                                    class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-plus"></i>
                                <span>Generate QR</span>
                            </button>
                            <p class="text-xs text-gray-500">Generate QR Code untuk tracking barang</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bolt mr-2 text-yellow-600"></i>
                        Aksi Cepat
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <button class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>Tambah Stok</span>
                        </button>

                        <button class="w-full px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-minus"></i>
                            <span>Kurangi Stok</span>
                        </button>

                        <button class="w-full px-4 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Transfer Stok</span>
                        </button>

                        <button class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-history"></i>
                            <span>Lihat Riwayat</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Category Tree Card -->
            @if($item->category)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-sitemap mr-2 text-green-600"></i>
                            Hirarki Kategori
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-gray-600">
                            {{ $item->getCategoryPath() }}
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('items.index', ['category' => $item->category_id]) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                Lihat barang lain dalam kategori ini
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Metadata Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info mr-2 text-gray-600"></i>
                        Metadata
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Dibuat:</span>
                            <span class="text-gray-900">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Diupdate:</span>
                            <span class="text-gray-900">{{ $item->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID Barang:</span>
                            <span class="text-gray-900 font-mono">{{ $item->item_id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Transaksi:</span>
                            <span class="text-gray-900">{{ $recentTransactions->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Status Modal -->
    <div x-show="toggleModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hideToggleModal()"
         @keydown.escape.window="hideToggleModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="toggleModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="w-16 h-16 {{ $item->is_active ? 'bg-orange-100' : 'bg-green-100' }} rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-{{ $item->is_active ? 'toggle-off' : 'toggle-on' }} text-2xl {{ $item->is_active ? 'text-orange-600' : 'text-green-600' }}"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">
                    {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Barang
                </h3>

                <p class="text-gray-600 text-center mb-6">
                    Apakah Anda yakin ingin {{ $item->is_active ? 'menonaktifkan' : 'mengaktifkan' }} barang
                    <span class="font-semibold text-gray-900">{{ $item->item_name }}</span>?
                </p>

                @if($item->hasStock() && $item->is_active)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                        <div class="flex items-start space-x-2 text-yellow-800">
                            <i class="fas fa-exclamation-triangle mt-0.5"></i>
                            <div class="text-sm">
                                <p class="font-medium">Barang memiliki stok ({{ $stockInfo['total'] }} {{ $item->unit }})</p>
                                <p class="text-xs mt-1">Menonaktifkan akan mencegah transaksi baru</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            @click="hideToggleModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="button"
                            @click="confirmToggle()"
                            class="flex-1 px-4 py-3 bg-gradient-to-r {{ $item->is_active ? 'from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800' : 'from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' }} text-white rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-{{ $item->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                        <span>{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate QR Modal -->
    @if(!$item->hasQRCode())
        <div x-show="qrModal.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="hideQRModal()"
             @keydown.escape.window="hideQRModal()"
             class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
             style="display: none;">
            <div x-show="qrModal.show"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
                <div class="p-6">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-qrcode text-2xl text-purple-600"></i>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Generate QR Code</h3>

                    <p class="text-gray-600 text-center mb-6">
                        Generate QR Code untuk barang <span class="font-semibold text-gray-900">{{ $item->item_name }}</span>?
                        QR Code ini dapat digunakan untuk tracking dan identifikasi barang.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button"
                                @click="hideQRModal()"
                                class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </button>
                        <button type="button"
                                @click="confirmGenerateQR()"
                                class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                            <i class="fas fa-qrcode"></i>
                            <span>Generate</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-full"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-full"
             class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="ml-4 text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-full"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-full"
             class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="ml-4 text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function itemDetail() {
        return {
            toggleModal: {
                show: false
            },
            qrModal: {
                show: false
            },

            // Toggle Status Modal Functions
            showToggleModal() {
                this.toggleModal.show = true;
            },

            hideToggleModal() {
                this.toggleModal.show = false;
            },

            confirmToggle() {
                // Create and submit toggle form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('items.toggle-status', $item->item_id) }}`;
                form.style.display = 'none';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'PATCH';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);

                this.hideToggleModal();
                form.submit();
            },

            // QR Modal Functions (only if QR doesn't exist)
            @if(!$item->hasQRCode())
            showGenerateQRModal() {
                this.qrModal.show = true;
            },

            hideQRModal() {
                this.qrModal.show = false;
            },

            confirmGenerateQR() {
                // Create and submit QR generation form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('items.generate-qr', $item->item_id) }}`;
                form.style.display = 'none';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                form.appendChild(csrfToken);
                document.body.appendChild(form);

                this.hideQRModal();
                form.submit();
            }
            @endif
        }
    }
</script>
@endpush
