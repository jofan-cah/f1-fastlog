@extends('layouts.app')

@section('title', 'Adjust Stok - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="stockAdjust()">
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
                    <a href="{{ route('stocks.index') }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                        Manajemen Stok
                    </a>
                </div>
            </li>
            @if(isset($stock))
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <a href="{{ route('stocks.show', $stock->stock_id) }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                            {{ $item->item_name }}
                        </a>
                    </div>
                </li>
            @endif
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">Adjust Stok</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-purple-700 rounded-2xl flex items-center justify-center">
                <i class="fas fa-edit text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    @if(isset($stock))
                        Adjust Stok: {{ $item->item_name }}
                    @else
                        Bulk Stock Adjustment
                    @endif
                </h1>
                <p class="text-gray-600 mt-1">
                    @if(isset($stock))
                        {{ $item->item_code }} • {{ $stock->quantity_available }}/{{ $stock->total_quantity }} {{ $item->unit }}
                    @else
                        Adjust stok untuk multiple items sekaligus
                    @endif
                </p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            @if(isset($stock))
                <a href="{{ route('stocks.show', $stock->stock_id) }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-eye"></i>
                    <span>Lihat Detail</span>
                </a>
            @endif
            <a href="{{ route('stocks.index') }}"
               class="px-4 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    @if(isset($stock))
        <!-- Single Item Adjustment -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Form -->
            <div class="lg:col-span-2">
                <!-- Current Stock Status -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                            <div>
                                <h4 class="font-medium text-yellow-900">Stok Rendah</h4>
                                <p class="text-sm text-yellow-800">
                                    Stok saat ini ({{ $stock->quantity_available }}) di bawah minimum ({{ $item->min_stock }})
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($stock->isOutOfStock())
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-times-circle text-red-600"></i>
                            <div>
                                <h4 class="font-medium text-red-900">Stok Habis</h4>
                                <p class="text-sm text-red-800">
                                    Barang ini tidak memiliki stok tersedia
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
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
    function stockAdjust() {
        return {
            // Single adjustment logic can be added here if needed
        }
    }

    function bulkAdjustment() {
        return {
            searchTerm: '',
            selectedCategory: '',
            stockFilter: '',
            selectedItems: [],
            items: @json($items ?? []),

            get selectedCount() {
                return this.selectedItems.length;
            },

            filterItems() {
                // This would filter the visible items based on search criteria
                // For now, we'll show all items
            },

            isItemVisible(index) {
                const item = this.items[index];
                if (!item) return false;

                // Apply search filter
                if (this.searchTerm) {
                    const searchLower = this.searchTerm.toLowerCase();
                    if (!item.item_name.toLowerCase().includes(searchLower) &&
                        !item.item_code.toLowerCase().includes(searchLower)) {
                        return false;
                    }
                }

                // Apply category filter
                if (this.selectedCategory && item.category_id !== this.selectedCategory) {
                    return false;
                }

                // Apply stock status filter
                if (this.stockFilter) {
                    const stock = item.stock;
                    if (!stock) return false;

                    switch (this.stockFilter) {
                        case 'low':
                            if (stock.quantity_available > item.min_stock) return false;
                            break;
                        case 'out':
                            if (stock.total_quantity > 0) return false;
                            break;
                        case 'sufficient':
                            if (stock.quantity_available <= item.min_stock) return false;
                            break;
                    }
                }

                return true;
            },

            updateSelection(index, isSelected) {
                const item = this.items[index];
                if (!item || !item.stock) return;

                const stockId = item.stock.stock_id;

                if (isSelected) {
                    if (!this.selectedItems.includes(stockId)) {
                        this.selectedItems.push(stockId);
                    }
                } else {
                    this.selectedItems = this.selectedItems.filter(id => id !== stockId);
                }
            },

            selectAll() {
                // Select all visible items
                this.items.forEach((item, index) => {
                    if (this.isItemVisible(index) && item.stock) {
                        const checkbox = document.querySelector(`input[value="${item.stock.stock_id}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                            this.updateSelection(index, true);
                        }
                    }
                });
            },

            clearAll() {
                // Clear all selections
                this.selectedItems = [];
                document.querySelectorAll('input[name="selected_items[]"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
            },

            toggleAll(event) {
                if (event.target.checked) {
                    this.selectAll();
                } else {
                    this.clearAll();
                }
            }
        }
    }
</script>
@endpush
