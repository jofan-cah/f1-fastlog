@extends('layouts.app')

@section('title', 'Manajemen Stok - LogistiK Admin')

@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
    <div class="space-y-6" x-data="stockManager()">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Stok</h1>
                <p class="text-gray-600 mt-1">Monitor dan kelola stok barang inventori</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                {{-- <a href="{{ route('stocks.adjust') }}"
               class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-plus-minus"></i>
                <span>Bulk Adjustment</span>
            </a> --}}
                {{-- <a href="{{ route('stocks.adjust') }}"
               class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-edit"></i>
                <span>Adjust Stok</span>
            </a> --}}
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-boxes text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Item</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $summary['total_items'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-green-600 to-green-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Stok Tersedia</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $summary['available_items'] }}</p>
                    </div>
                </div>
            </div>
            {{-- <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-thumbs-up text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Stok Cukup</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $summary['sufficient_items'] }}</p>
                </div>
            </div>
        </div> --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Stok Rendah</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $summary['low_stock_items'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-times-circle text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Stok Habis</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $summary['out_of_stock_items'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        @if ($lowStockAlerts->count() > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6">
                <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 bg-yellow-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-yellow-900 mb-2">Peringatan Stok Rendah</h3>
                        <p class="text-sm text-yellow-800 mb-4">{{ $lowStockAlerts->count() }} item memiliki stok di bawah
                            minimum</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach ($lowStockAlerts as $alert)
                                <div class="bg-white p-3 rounded-lg border border-yellow-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ $alert->item->item_name }}</p>
                                            <p class="text-xs text-gray-500">{{ $alert->item->item_code }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-red-600">{{ $alert->quantity_available }}</p>
                                            <p class="text-xs text-gray-500">/ {{ $alert->item->min_stock }} min</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filters Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <form method="GET" action="{{ route('stocks.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari nama atau kode barang..."
                                class="pl-10 pr-4 py-3 w-full bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                        <select name="category"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->category_id }}"
                                    {{ request('category') == $category->category_id ? 'selected' : '' }}>
                                    {{ $category->category_name }} ({{ $category->items_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Stok</label>
                        <select name="status"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                            <option value="">Semua Status</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Ada Stok
                            </option>
                            <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>Stok Rendah</option>
                            <option value="out" {{ request('status') == 'out' ? 'selected' : '' }}>Stok Habis</option>
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
                    <a href="{{ route('stocks.index') }}"
                        class="px-6 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Reset</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Stocks Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-warehouse mr-2 text-blue-600"></i>
                        Daftar Stok Barang
                    </h3>
                    <span class="text-sm text-gray-600">Total: {{ $stocks->total() }} item</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Barang
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kategori
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('stocks.index', array_merge(request()->query(), ['sort' => 'quantity_available', 'direction' => $sortField == 'quantity_available' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Stok Gudang</span>
                                    @if ($sortField == 'quantity_available')
                                        <i
                                            class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }} text-red-500"></i>
                                    @else
                                        <i class="fas fa-sort text-gray-400"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('stocks.index', array_merge(request()->query(), ['sort' => 'quantity_used', 'direction' => $sortField == 'quantity_used' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Stok Siap Pakai</span>
                                    @if ($sortField == 'quantity_used')
                                        <i
                                            class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }} text-red-500"></i>
                                    @else
                                        <i class="fas fa-sort text-gray-400"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('stocks.index', array_merge(request()->query(), ['sort' => 'total_quantity', 'direction' => $sortField == 'total_quantity' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Total Stok</span>
                                    @if ($sortField == 'total_quantity')
                                        <i
                                            class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }} text-red-500"></i>
                                    @else
                                        <i class="fas fa-sort text-gray-400"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('stocks.index', array_merge(request()->query(), ['sort' => 'last_updated', 'direction' => $sortField == 'last_updated' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Update Terakhir</span>
                                    @if ($sortField == 'last_updated')
                                        <i
                                            class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }} text-red-500"></i>
                                    @else
                                        <i class="fas fa-sort text-gray-400"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($stocks as $stock)
                            @php
                                $statusInfo = $stock->getStockStatus();
                                $percentage = $stock->getStockPercentage();
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <!-- Item Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-box text-white text-lg"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $stock->item->item_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">{{ $stock->item->item_code }}</div>
                                            <div class="text-xs text-gray-400">{{ $stock->item->unit }} • Min:
                                                {{ $stock->item->min_stock }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($stock->item->category)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $stock->item->category->category_name }}
                                        </span>
                                    @else
                                        <span class="text-gray-500 text-sm">Tidak ada kategori</span>
                                    @endif
                                </td>

                                <!-- Available Stock -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <div class="font-medium text-lg">{{ number_format($stock->quantity_available) }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $stock->item->unit }}</div>
                                    </div>
                                </td>

                                <!-- Used Stock -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <div class="font-medium">{{ number_format($stock->quantity_used) }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $stock->item->unit }}
                                            @if ($stock->item->unit == 'pack')
                                                (5 pcs)
                                            @endif
                                        </div>

                                    </div>
                                </td>

                                <!-- Total Stock -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <div class="font-medium text-lg">{{ number_format($stock->total_quantity) }}</div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                            <div class="bg-gradient-to-r from-blue-600 to-green-600 h-2 rounded-full"
                                                style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $percentage }}% tersedia</div>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusInfo['class'] }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full mr-1.5
                                        @if ($statusInfo['status'] == 'sufficient') bg-green-400
                                        @elseif($statusInfo['status'] == 'low') bg-yellow-400
                                        @else bg-red-400 @endif"></span>
                                        {{ $statusInfo['text'] }}
                                    </span>
                                </td>

                                <!-- Last Updated -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if ($stock->last_updated)
                                        <div>{{ $stock->last_updated->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $stock->last_updated->format('H:i') }}</div>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('stocks.show', $stock->stock_id) }}"
                                            class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                            title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('stocks.edit', $stock->stock_id) }}"
                                            class="text-green-600 hover:text-green-900 p-2 hover:bg-green-50 rounded-lg transition-all duration-200"
                                            title="Adjust Stok">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        {{-- <button @click="showQuickAdjustModal('{{ $stock->stock_id }}', '{{ addslashes($stock->item->item_name) }}', {{ $stock->quantity_available }}, {{ $stock->quantity_used }}, '{{ $stock->item->unit }}')"
                                            class="text-purple-600 hover:text-purple-900 p-2 hover:bg-purple-50 rounded-lg transition-all duration-200"
                                            title="Quick Adjust">
                                        <i class="fas fa-plus-minus"></i>
                                    </button> --}}
                                        {{-- <button @click="showStockHistoryModal('{{ $stock->stock_id }}', '{{ addslashes($stock->item->item_name) }}')"
                                            class="text-indigo-600 hover:text-indigo-900 p-2 hover:bg-indigo-50 rounded-lg transition-all duration-200"
                                            title="Lihat History">
                                        <i class="fas fa-history"></i>
                                    </button> --}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-warehouse text-4xl text-gray-300 mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data stok</h3>
                                        <p class="text-gray-500 mb-4">Belum ada data stok yang tersedia.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if ($stocks->hasPages())
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ $stocks->firstItem() }} sampai {{ $stocks->lastItem() }}
                        dari {{ $stocks->total() }} hasil
                    </div>
                    <div class="flex items-center space-x-2">
                        {{ $stocks->appends(request()->query())->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Adjust Modal -->
        <div x-show="quickAdjustModal.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.self="hideQuickAdjustModal()"
            @keydown.escape.window="hideQuickAdjustModal()"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="quickAdjustModal.show" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
                <div class="p-6">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-plus-minus text-2xl text-purple-600"></i>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Quick Adjust Stok</h3>
                    <p class="text-gray-600 text-center mb-6">
                        <span x-text="quickAdjustModal.itemName" class="font-semibold"></span>
                    </p>

                    <form @submit.prevent="submitQuickAdjust()">
                        <div class="space-y-4">
                            <!-- Current Stock Info -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">Tersedia:</span>
                                        <span class="ml-2 font-medium" x-text="quickAdjustModal.currentAvailable"></span>
                                        <span x-text="quickAdjustModal.unit"></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Terpakai:</span>
                                        <span class="ml-2 font-medium" x-text="quickAdjustModal.currentUsed"></span>
                                        <span x-text="quickAdjustModal.unit"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Adjustment Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Adjustment</label>
                                <select x-model="quickAdjustModal.adjustmentType"
                                    class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="add">Tambah Stok</option>
                                    <option value="reduce">Kurangi Stok</option>
                                    <option value="return">Return Stok</option>
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                                <input type="number" x-model="quickAdjustModal.quantity" min="1"
                                    class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    required>
                            </div>

                            <!-- Reason -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan</label>
                                <select x-model="quickAdjustModal.reason"
                                    class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="manual_adjustment">Manual Adjustment</option>
                                    <option value="stock_opname">Stock Opname</option>
                                    <option value="correction">Koreksi Data</option>
                                    <option value="damage">Barang Rusak</option>
                                    <option value="lost">Barang Hilang</option>
                                    <option value="return">Return dari User</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 mt-6">
                            <button type="button" @click="hideQuickAdjustModal()"
                                class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-times"></i>
                                <span>Batal</span>
                            </button>
                            <button type="submit"
                                class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                                <i class="fas fa-save"></i>
                                <span>Simpan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stock History Modal -->
        <div x-show="historyModal.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.self="hideHistoryModal()"
            @keydown.escape.window="hideHistoryModal()"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="historyModal.show" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-900">History Stok</h3>
                        <button @click="hideHistoryModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <p class="text-gray-600 mb-6">
                        <span x-text="historyModal.itemName" class="font-semibold"></span>
                    </p>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center space-x-2 text-blue-800">
                            <i class="fas fa-info-circle"></i>
                            <div class="text-sm">
                                <p class="font-medium">Fitur History Stok</p>
                                <p>History detail pergerakan stok akan tersedia setelah implementasi StockMovement model</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
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

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
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
        function stockManager() {
            return {
                quickAdjustModal: {
                    show: false,
                    stockId: '',
                    itemName: '',
                    currentAvailable: 0,
                    currentUsed: 0,
                    unit: '',
                    adjustmentType: 'add',
                    quantity: 1,
                    reason: 'manual_adjustment'
                },
                historyModal: {
                    show: false,
                    stockId: '',
                    itemName: ''
                },

                // Quick Adjust Modal Functions
                showQuickAdjustModal(stockId, itemName, currentAvailable, currentUsed, unit) {
                    this.quickAdjustModal = {
                        show: true,
                        stockId: stockId,
                        itemName: itemName,
                        currentAvailable: parseInt(currentAvailable),
                        currentUsed: parseInt(currentUsed),
                        unit: unit,
                        adjustmentType: 'add',
                        quantity: 1,
                        reason: 'manual_adjustment'
                    };
                },

                hideQuickAdjustModal() {
                    this.quickAdjustModal.show = false;
                    setTimeout(() => {
                        this.quickAdjustModal = {
                            show: false,
                            stockId: '',
                            itemName: '',
                            currentAvailable: 0,
                            currentUsed: 0,
                            unit: '',
                            adjustmentType: 'add',
                            quantity: 1,
                            reason: 'manual_adjustment'
                        };
                    }, 300);
                },

                submitQuickAdjust() {
                    // Validate quantity
                    if (!this.quickAdjustModal.quantity || this.quickAdjustModal.quantity < 1) {
                        alert('Jumlah harus diisi dan minimal 1');
                        return;
                    }

                    // Check if reduce/return has enough stock
                    if (this.quickAdjustModal.adjustmentType === 'reduce' &&
                        this.quickAdjustModal.quantity > this.quickAdjustModal.currentAvailable) {
                        alert('Jumlah tidak boleh melebihi stok tersedia');
                        return;
                    }

                    if (this.quickAdjustModal.adjustmentType === 'return' &&
                        this.quickAdjustModal.quantity > this.quickAdjustModal.currentUsed) {
                        alert('Jumlah return tidak boleh melebihi stok terpakai');
                        return;
                    }

                    // Create and submit form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('stocks.adjustment') }}';
                    form.style.display = 'none';

                    // CSRF Token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    form.appendChild(csrfToken);

                    // Stock ID
                    const stockIdInput = document.createElement('input');
                    stockIdInput.type = 'hidden';
                    stockIdInput.name = 'stock_id';
                    stockIdInput.value = this.quickAdjustModal.stockId;
                    form.appendChild(stockIdInput);

                    // Adjustment Type
                    const typeInput = document.createElement('input');
                    typeInput.type = 'hidden';
                    typeInput.name = 'adjustment_type';
                    typeInput.value = this.quickAdjustModal.adjustmentType;
                    form.appendChild(typeInput);

                    // Quantity
                    const quantityInput = document.createElement('input');
                    quantityInput.type = 'hidden';
                    quantityInput.name = 'quantity';
                    quantityInput.value = this.quickAdjustModal.quantity;
                    form.appendChild(quantityInput);

                    // Reason
                    const reasonInput = document.createElement('input');
                    reasonInput.type = 'hidden';
                    reasonInput.name = 'reason';
                    reasonInput.value = this.quickAdjustModal.reason;
                    form.appendChild(reasonInput);

                    document.body.appendChild(form);
                    this.hideQuickAdjustModal();
                    form.submit();
                },

                // Stock History Modal Functions
                showStockHistoryModal(stockId, itemName) {
                    this.historyModal = {
                        show: true,
                        stockId: stockId,
                        itemName: itemName
                    };
                },

                hideHistoryModal() {
                    this.historyModal.show = false;
                    setTimeout(() => {
                        this.historyModal = {
                            show: false,
                            stockId: '',
                            itemName: ''
                        };
                    }, 300);
                }
            }
        }
    </script>
@endpush
