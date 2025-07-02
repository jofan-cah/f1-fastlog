@extends('layouts.app')

@section('title', 'Barang - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="itemManager()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Barang</h1>
            <p class="text-gray-600 mt-1">Kelola master data barang dan inventori</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <button onclick="window.location.href='{{ route('items.create') }}'"
                    class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-plus"></i>
                <span>Tambah Barang</span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-boxes text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Barang</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-green-600 to-green-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Barang Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Stok Rendah</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['low_stock'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-times-circle text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tanpa Stok</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['without_stock'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <form method="GET" action="{{ route('items.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari kode, nama barang, atau deskripsi..."
                               class="pl-10 pr-4 py-3 w-full bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select name="category"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->category_id }}" {{ request('category') == $category->category_id ? 'selected' : '' }}>
                                {{ $category->category_name }} ({{ $category->items_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                <!-- Stock Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Stok</label>
                    <select name="stock_filter"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        <option value="">Semua Stok</option>
                        <option value="available" {{ request('stock_filter') == 'available' ? 'selected' : '' }}>Ada Stok</option>
                        <option value="low" {{ request('stock_filter') == 'low' ? 'selected' : '' }}>Stok Rendah</option>
                        <option value="empty" {{ request('stock_filter') == 'empty' ? 'selected' : '' }}>Stok Habis</option>
                    </select>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-filter"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('items.index') }}"
                   class="px-6 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-boxes mr-2 text-blue-600"></i>
                    Daftar Barang
                </h3>
                <span class="text-sm text-gray-600">Total: {{ $items->total() }} barang</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('items.index', array_merge(request()->query(), ['sort' => 'item_code', 'direction' => $sortField == 'item_code' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}"
                               class="flex items-center space-x-1 hover:text-gray-700">
                                <span>Barang</span>
                                @if($sortField == 'item_code')
                                    <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }} text-red-500"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('items.index', array_merge(request()->query(), ['sort' => 'category_id', 'direction' => $sortField == 'category_id' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}"
                               class="flex items-center space-x-1 hover:text-gray-700">
                                <span>Kategori</span>
                                @if($sortField == 'category_id')
                                    <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }} text-red-500"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QR Code</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($items as $item)
                        @php
                            $stockInfo = $item->getStockInfo();
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <!-- Item Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-box text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $item->item_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->item_code }}</div>
                                        <div class="text-xs text-gray-400">{{ $item->unit }} • ID: {{ $item->item_id }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($item->category)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $item->category->category_name }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">Tidak ada kategori</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Stock Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium">{{ $stockInfo['available'] }}/{{ $stockInfo['total'] }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            @if($stockInfo['status'] == 'sufficient') bg-green-100 text-green-800
                                            @elseif($stockInfo['status'] == 'low') bg-yellow-100 text-yellow-800
                                            @elseif($stockInfo['status'] == 'empty') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $stockInfo['status_text'] }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">Min: {{ $item->min_stock }} {{ $item->unit }}</div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $item->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                    {{ $item->getStatusText() }}
                                </span>
                            </td>

                            <!-- QR Code -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($item->hasQRCode())
                                    <div class="flex items-center justify-center space-x-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-qrcode mr-1"></i>
                                            Ada
                                        </span>
                                        <a href="{{ route('items.download-qr', $item->item_id) }}"
                                           class="text-blue-600 hover:text-blue-800 text-xs"
                                           title="Download QR">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-qrcode mr-1"></i>
                                        Belum Ada
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('items.show', $item->item_id) }}"
                                       class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                       title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('items.edit', $item->item_id) }}"
                                       class="text-yellow-600 hover:text-yellow-900 p-2 hover:bg-yellow-50 rounded-lg transition-all duration-200"
                                       title="Edit Barang">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$item->hasQRCode())
                                        <button @click="showGenerateQRModal('{{ $item->item_id }}', '{{ addslashes($item->item_name) }}')"
                                                class="text-purple-600 hover:text-purple-900 p-2 hover:bg-purple-50 rounded-lg transition-all duration-200"
                                                title="Generate QR Code">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                    @endif
                                    <button @click="showToggleModal('{{ $item->item_id }}', '{{ addslashes($item->item_name) }}', {{ $item->is_active ? 'true' : 'false' }})"
                                            class="p-2 rounded-lg transition-all duration-200 {{ $item->is_active ? 'text-orange-600 hover:text-orange-900 hover:bg-orange-50' : 'text-green-600 hover:text-green-900 hover:bg-green-50' }}"
                                            title="{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Barang">
                                        <i class="fas fa-{{ $item->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                    </button>
                                    <button @click="showDeleteModal('{{ $item->item_id }}', '{{ addslashes($item->item_name) }}', {{ $item->hasStock() ? 'true' : 'false' }}, {{ $item->transactions()->exists() || $item->poDetails()->exists() ? 'true' : 'false' }})"
                                            class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded-lg transition-all duration-200"
                                            title="Hapus Barang">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-boxes text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada barang</h3>
                                    <p class="text-gray-500 mb-4">Belum ada barang yang terdaftar dalam sistem.</p>
                                    <a href="{{ route('items.create') }}"
                                       class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200">
                                        Tambah Barang Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($items->hasPages())
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Menampilkan {{ $items->firstItem() }} sampai {{ $items->lastItem() }}
                    dari {{ $items->total() }} hasil
                </div>
                <div class="flex items-center space-x-2">
                    {{ $items->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hideDeleteModal()"
         @keydown.escape.window="hideDeleteModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="deleteModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash text-2xl text-red-600"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Hapus Barang</h3>

                <div class="text-gray-600 text-center mb-6">
                    <p class="mb-2">
                        Apakah Anda yakin ingin menghapus barang <span x-text="deleteModal.itemName" class="font-semibold text-gray-900"></span>?
                    </p>

                    <div x-show="deleteModal.hasStock || deleteModal.hasTransactions" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-start space-x-2 text-yellow-800">
                            <i class="fas fa-exclamation-triangle mt-0.5"></i>
                            <div class="text-sm">
                                <div x-show="deleteModal.hasStock" class="font-medium">Barang masih memiliki stok</div>
                                <div x-show="deleteModal.hasTransactions" class="font-medium">Barang memiliki riwayat transaksi</div>
                                <p class="text-xs mt-1">Barang tidak dapat dihapus jika masih memiliki stok atau transaksi</p>
                            </div>
                        </div>
                    </div>

                    <div x-show="!deleteModal.hasStock && !deleteModal.hasTransactions" class="text-sm text-red-600 mt-2">
                        Tindakan ini tidak dapat dibatalkan.
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            @click="hideDeleteModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="button"
                            @click="confirmDelete()"
                            :disabled="deleteModal.hasStock || deleteModal.hasTransactions"
                            :class="(deleteModal.hasStock || deleteModal.hasTransactions) ? 'flex-1 px-4 py-3 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed flex items-center justify-center space-x-2' : 'flex-1 px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl'">
                        <i class="fas fa-trash"></i>
                        <span>Hapus</span>
                    </button>
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
                <div :class="toggleModal.isActive ? 'w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4' : 'w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4'">
                    <i :class="toggleModal.isActive ? 'fas fa-toggle-off text-2xl text-orange-600' : 'fas fa-toggle-on text-2xl text-green-600'"></i>
                </div>

                <h3 x-text="toggleModal.isActive ? 'Nonaktifkan Barang' : 'Aktifkan Barang'" class="text-xl font-bold text-gray-900 text-center mb-2"></h3>

                <p class="text-gray-600 text-center mb-6">
                    Apakah Anda yakin ingin <span x-text="toggleModal.isActive ? 'menonaktifkan' : 'mengaktifkan'" class="font-semibold"></span> barang <span x-text="toggleModal.itemName" class="font-semibold text-gray-900"></span>?
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            @click="hideToggleModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="button"
                            @click="confirmToggle()"
                            :class="toggleModal.isActive ? 'flex-1 px-4 py-3 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-xl hover:from-orange-700 hover:to-orange-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl' : 'flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl'">
                        <i :class="toggleModal.isActive ? 'fas fa-toggle-off' : 'fas fa-toggle-on'"></i>
                        <span x-text="toggleModal.isActive ? 'Nonaktifkan' : 'Aktifkan'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate QR Modal -->
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
                    Generate QR Code untuk barang <span x-text="qrModal.itemName" class="font-semibold text-gray-900"></span>?
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
    function itemManager() {
        return {
            deleteModal: {
                show: false,
                itemId: '',
                itemName: '',
                hasStock: false,
                hasTransactions: false
            },
            toggleModal: {
                show: false,
                itemId: '',
                itemName: '',
                isActive: false
            },
            qrModal: {
                show: false,
                itemId: '',
                itemName: ''
            },

            // Delete Modal Functions
            showDeleteModal(itemId, itemName, hasStock, hasTransactions) {
                this.deleteModal = {
                    show: true,
                    itemId: itemId,
                    itemName: itemName,
                    hasStock: hasStock === 'true' || hasStock === true,
                    hasTransactions: hasTransactions === 'true' || hasTransactions === true
                };
            },

            hideDeleteModal() {
                this.deleteModal.show = false;
                setTimeout(() => {
                    this.deleteModal = {
                        show: false,
                        itemId: '',
                        itemName: '',
                        hasStock: false,
                        hasTransactions: false
                    };
                }, 300);
            },

            confirmDelete() {
                if (this.deleteModal.itemId && !this.deleteModal.hasStock && !this.deleteModal.hasTransactions) {
                    // Create and submit delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ route('items.index') }}/${this.deleteModal.itemId}`;
                    form.style.display = 'none';

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);

                    this.hideDeleteModal();
                    form.submit();
                }
            },

            // Toggle Status Modal Functions
            showToggleModal(itemId, itemName, isActive) {
                this.toggleModal = {
                    show: true,
                    itemId: itemId,
                    itemName: itemName,
                    isActive: isActive === true || isActive === 'true'
                };
            },

            hideToggleModal() {
                this.toggleModal.show = false;
                setTimeout(() => {
                    this.toggleModal = {
                        show: false,
                        itemId: '',
                        itemName: '',
                        isActive: false
                    };
                }, 300);
            },

            confirmToggle() {
                if (this.toggleModal.itemId) {
                    // Create and submit toggle form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ route('items.index') }}/${this.toggleModal.itemId}/toggle-status`;
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
                }
            },

            // QR Modal Functions
            showGenerateQRModal(itemId, itemName) {
                this.qrModal = {
                    show: true,
                    itemId: itemId,
                    itemName: itemName
                };
            },

            hideQRModal() {
                this.qrModal.show = false;
                setTimeout(() => {
                    this.qrModal = {
                        show: false,
                        itemId: '',
                        itemName: ''
                    };
                }, 300);
            },

            confirmGenerateQR() {
                if (this.qrModal.itemId) {
                    // Create and submit QR generation form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ route('items.index') }}/${this.qrModal.itemId}/generate-qr`;
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
            }
        }
    }
</script>
@endpush
