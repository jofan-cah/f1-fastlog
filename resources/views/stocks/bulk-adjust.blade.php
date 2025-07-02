@extends('layouts.app')

@section('title', 'Bulk Stock Adjustment - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="bulkAdjustment()">
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
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">Bulk Adjustment</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-purple-700 rounded-2xl flex items-center justify-center">
                <i class="fas fa-list-check text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Bulk Stock Adjustment</h1>
                <p class="text-gray-600 mt-1">Adjust stok multiple items sekaligus untuk efisiensi</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('stocks.adjust') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-edit"></i>
                <span>Single Adjust</span>
            </a>
            <a href="{{ route('stocks.index') }}"
               class="px-4 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-boxes text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Items</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="totalItems">{{ $items->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Selected</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="selectedCount">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Low Stock</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="lowStockCount">{{ $items->filter(function($item) { return $item->stock && $item->stock->quantity_available <= $item->min_stock; })->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-times-circle text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Out of Stock</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="outOfStockCount">{{ $items->filter(function($item) { return $item->stock && $item->stock->total_quantity == 0; })->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions Card -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-2xl p-6">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-info-circle text-white"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-purple-900 mb-2">Cara Menggunakan Bulk Adjustment</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-purple-800">
                    <div>
                        <h4 class="font-medium mb-2">Langkah-langkah:</h4>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Filter items yang ingin di-adjust</li>
                            <li>Pilih items dengan checkbox</li>
                            <li>Atur nilai Available dan Used baru</li>
                            <li>Isi alasan adjustment</li>
                            <li>Klik "Apply Bulk Adjustment"</li>
                        </ol>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Tips:</h4>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Gunakan filter untuk fokus pada items tertentu</li>
                            <li>Quick actions untuk set nilai yang sama</li>
                            <li>Preview changes sebelum submit</li>
                            <li>Pastikan alasan adjustment jelas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <form action="{{ route('stocks.bulk-adjustment') }}" method="POST" @submit="validateForm($event)">
        @csrf

        <!-- Filters & Controls -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-600"></i>
                    Filter & Selection
                </h3>
            </div>
            <div class="p-6">
                <!-- Filter Row -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Items</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text"
                                   x-model="searchTerm"
                                   @input="filterItems()"
                                   placeholder="Cari nama atau kode..."
                                   class="pl-10 pr-4 py-3 w-full bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select x-model="selectedCategory" @change="filterItems()" class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">All Categories</option>
                            @foreach($items->pluck('category')->filter()->unique('category_id') as $category)
                                <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stock Status</label>
                        <select x-model="stockFilter" @change="filterItems()" class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">All Status</option>
                            <option value="low">Low Stock</option>
                            <option value="out">Out of Stock</option>
                            <option value="sufficient">Sufficient</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quick Filter</label>
                        <select x-model="quickFilter" @change="applyQuickFilter()" class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">No Filter</option>
                            <option value="select_low">Select Low Stock</option>
                            <option value="select_out">Select Out of Stock</option>
                            <option value="select_all_visible">Select All Visible</option>
                        </select>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div class="flex flex-wrap gap-3 mb-6">
                    <button type="button" @click="selectAllVisible()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-check-double"></i>
                        <span>Select All Visible</span>
                    </button>
                    <button type="button" @click="clearAllSelections()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Clear All</span>
                    </button>
                    <button type="button" @click="showQuickSetModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-magic"></i>
                        <span>Quick Set Values</span>
                    </button>
                    <button type="button" @click="showPreviewModal()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-eye"></i>
                        <span>Preview Changes</span>
                    </button>
                </div>

                <!-- Reason -->
                <div class="mb-6">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Bulk Adjustment <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <select id="reason" name="reason" required x-model="adjustmentReason" class="py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 @error('reason') border-red-500 bg-red-50 @enderror">
                            <option value="">Pilih Alasan</option>
                            <option value="stock_opname">Stock Opname</option>
                            <option value="bulk_correction">Bulk Correction</option>
                            <option value="system_migration">System Migration</option>
                            <option value="inventory_audit">Inventory Audit</option>
                            <option value="seasonal_adjustment">Seasonal Adjustment</option>
                            <option value="damage_loss">Damage/Loss Report</option>
                        </select>
                        <textarea name="notes" placeholder="Catatan tambahan (opsional)..." x-model="adjustmentNotes" class="py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none" rows="1"></textarea>
                    </div>
                    @error('reason')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-table mr-2 text-purple-600"></i>
                        Items to Adjust
                    </h3>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600" x-text="`${selectedCount} of ${visibleCount} selected`"></span>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm text-gray-600">Rows per page:</label>
                            <select x-model="itemsPerPage" @change="updatePagination()" class="text-sm border border-gray-300 rounded px-2 py-1">
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <div class="max-h-[600px] overflow-y-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <input type="checkbox" @change="toggleAllVisible($event)" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500" id="select-all-checkbox">
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Available</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Used</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($items as $index => $item)
                                @php
                                    $stock = $item->stock;
                                    $isLowStock = $stock && $stock->quantity_available <= $item->min_stock;
                                    $isOutOfStock = $stock && $stock->total_quantity == 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors"
                                    x-show="isItemVisible({{ $index }})"
                                    x-data="{
                                        newAvailable: {{ $stock->quantity_available ?? 0 }},
                                        newUsed: {{ $stock->quantity_used ?? 0 }},
                                        originalAvailable: {{ $stock->quantity_available ?? 0 }},
                                        originalUsed: {{ $stock->quantity_used ?? 0 }}
                                    }">
                                    <td class="px-4 py-3">
                                        <input type="checkbox"
                                               name="selected_items[]"
                                               value="{{ $stock->stock_id ?? '' }}"
                                               @change="updateSelection({{ $index }}, $event.target.checked)"
                                               class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 item-checkbox"
                                               data-index="{{ $index }}">
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-red-600 to-red-700 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-box text-white text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $item->item_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $item->item_code }}</div>
                                                <div class="text-xs text-gray-400">Min: {{ $item->min_stock }} {{ $item->unit }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($item->category)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $item->category->category_name }}
                                            </span>
                                        @else
                                            <span class="text-gray-500 text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-blue-600 font-medium">{{ $stock->quantity_available ?? 0 }}</span>
                                                <span class="text-gray-400">/</span>
                                                <span class="text-yellow-600 font-medium">{{ $stock->quantity_used ?? 0 }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500">Total: {{ $stock->total_quantity ?? 0 }} {{ $item->unit }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number"
                                               :name="`adjustments[{{ $stock->stock_id ?? '' }}][new_available]`"
                                               x-model="newAvailable"
                                               min="0"
                                               class="w-24 py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
                                               :class="newAvailable !== originalAvailable ? 'border-purple-500 bg-purple-50' : ''"
                                               @input="markAsModified({{ $index }})">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number"
                                               :name="`adjustments[{{ $stock->stock_id ?? '' }}][new_used]`"
                                               x-model="newUsed"
                                               min="0"
                                               class="w-24 py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
                                               :class="newUsed !== originalUsed ? 'border-purple-500 bg-purple-50' : ''"
                                               @input="markAsModified({{ $index }})">
                                        <input type="hidden" :name="`adjustments[{{ $stock->stock_id ?? '' }}][stock_id]`" value="{{ $stock->stock_id ?? '' }}">
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col space-y-1">
                                            @if($isOutOfStock)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-times-circle mr-1"></i>
                                                    Out of Stock
                                                </span>
                                            @elseif($isLowStock)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    Low Stock
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Sufficient
                                                </span>
                                            @endif

                                            <span x-show="newAvailable !== originalAvailable || newUsed !== originalUsed"
                                                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-edit mr-1"></i>
                                                Modified
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination (Simple) -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <span x-text="visibleCount"></span> of <span x-text="totalItems"></span> items
                    </div>
                    <div class="text-sm text-gray-500">
                        Use filters to narrow down the list
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-save"></i>
                    <span>Apply Bulk Adjustment</span>
                    <span x-show="selectedCount > 0" x-text="`(${selectedCount} items)`" class="ml-2 opacity-75"></span>
                </button>
                <button type="button" @click="resetAllChanges()"
                        class="px-6 py-3 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-undo"></i>
                    <span>Reset All</span>
                </button>
                <a href="{{ route('stocks.index') }}"
                   class="px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>Cancel</span>
                </a>
            </div>
        </div>
    </form>

    <!-- Quick Set Modal -->
    <div x-show="quickSetModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hideQuickSetModal()"
         @keydown.escape.window="hideQuickSetModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="quickSetModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-magic text-2xl text-blue-600"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-6">Quick Set Values</h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Available Value</label>
                            <input type="number" x-model="quickSetModal.available" min="0" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Used Value</label>
                            <input type="number" x-model="quickSetModal.used" min="0" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Apply to</label>
                        <select x-model="quickSetModal.applyTo" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="selected">Selected Items Only</option>
                            <option value="visible">All Visible Items</option>
                            <option value="low_stock">Low Stock Items</option>
                            <option value="out_of_stock">Out of Stock Items</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" @click="hideQuickSetModal()" class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200">
                        Cancel
                    </button>
                    <button type="button" @click="applyQuickSet()" class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200">
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div x-show="previewModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hidePreviewModal()"
         @keydown.escape.window="hidePreviewModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="previewModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Preview Changes</h3>
                    <button @click="hidePreviewModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="text-sm text-gray-600 mb-4" x-text="`${getModifiedItemsCount()} items will be modified`"></div>

                <div class="overflow-y-auto max-h-96">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Item</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Current</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">New</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Change</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="change in getPreviewChanges()" :key="change.itemCode">
                                <tr>
                                    <td class="px-4 py-2">
                                        <div>
                                            <div class="font-medium" x-text="change.itemName"></div>
                                            <div class="text-gray-500" x-text="change.itemCode"></div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div x-text="`${change.currentAvailable}/${change.currentUsed}`"></div>
                                        <div class="text-xs text-gray-500" x-text="`Total: ${change.currentTotal}`"></div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div x-text="`${change.newAvailable}/${change.newUsed}`"></div>
                                        <div class="text-xs text-gray-500" x-text="`Total: ${change.newTotal}`"></div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="space-y-1">
                                            <div x-show="change.availableChange !== 0"
                                                 :class="change.availableChange > 0 ? 'text-green-600' : 'text-red-600'"
                                                 x-text="`Available: ${change.availableChange > 0 ? '+' : ''}${change.availableChange}`"></div>
                                            <div x-show="change.usedChange !== 0"
                                                 :class="change.usedChange > 0 ? 'text-yellow-600' : 'text-blue-600'"
                                                 x-text="`Used: ${change.usedChange > 0 ? '+' : ''}${change.usedChange}`"></div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="text-sm text-gray-600">
                        <strong>Reason:</strong> <span x-text="adjustmentReason || 'Not specified'"></span>
                    </div>
                    <div x-show="adjustmentNotes" class="text-sm text-gray-600 mt-1">
                        <strong>Notes:</strong> <span x-text="adjustmentNotes"></span>
                    </div>
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
    function bulkAdjustment() {
        return {
            // Filter states
            searchTerm: '',
            selectedCategory: '',
            stockFilter: '',
            quickFilter: '',
            itemsPerPage: 25,

            // Form states
            adjustmentReason: '',
            adjustmentNotes: '',

            // Selection states
            selectedItems: [],
            modifiedItems: [],

            // Modal states
            quickSetModal: {
                show: false,
                available: 0,
                used: 0,
                applyTo: 'selected'
            },
            previewModal: {
                show: false
            },

            // Data
            items: @json($items->values()),

            // Computed properties
            get totalItems() {
                return this.items.length;
            },

            get selectedCount() {
                return this.selectedItems.length;
            },

            get visibleCount() {
                return this.items.filter((item, index) => this.isItemVisible(index)).length;
            },

            get lowStockCount() {
                return this.items.filter(item =>
                    item.stock && item.stock.quantity_available <= item.min_stock
                ).length;
            },

            get outOfStockCount() {
                return this.items.filter(item =>
                    item.stock && item.stock.total_quantity === 0
                ).length;
            },

            // Filter methods
            filterItems() {
                this.updateVisibleCount();
            },

            isItemVisible(index) {
                const item = this.items[index];
                if (!item) return false;

                // Search filter
                if (this.searchTerm) {
                    const searchLower = this.searchTerm.toLowerCase();
                    if (!item.item_name.toLowerCase().includes(searchLower) &&
                        !item.item_code.toLowerCase().includes(searchLower)) {
                        return false;
                    }
                }

                // Category filter
                if (this.selectedCategory && item.category?.category_id !== this.selectedCategory) {
                    return false;
                }

                // Stock status filter
                if (this.stockFilter && item.stock) {
                    switch (this.stockFilter) {
                        case 'low':
                            if (item.stock.quantity_available > item.min_stock) return false;
                            break;
                        case 'out':
                            if (item.stock.total_quantity > 0) return false;
                            break;
                        case 'sufficient':
                            if (item.stock.quantity_available <= item.min_stock) return false;
                            break;
                    }
                }

                return true;
            },

            // Selection methods
            updateSelection(index, isSelected) {
                const item = this.items[index];
                if (!item?.stock) return;

                const stockId = item.stock.stock_id;

                if (isSelected) {
                    if (!this.selectedItems.includes(stockId)) {
                        this.selectedItems.push(stockId);
                    }
                } else {
                    this.selectedItems = this.selectedItems.filter(id => id !== stockId);
                }
            },

            selectAllVisible() {
                this.items.forEach((item, index) => {
                    if (this.isItemVisible(index) && item.stock) {
                        const checkbox = document.querySelector(`input[data-index="${index}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                            this.updateSelection(index, true);
                        }
                    }
                });
                this.updateSelectAllCheckbox();
            },

            clearAllSelections() {
                this.selectedItems = [];
                document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                this.updateSelectAllCheckbox();
            },

            toggleAllVisible(event) {
                if (event.target.checked) {
                    this.selectAllVisible();
                } else {
                    this.clearAllSelections();
                }
            },

            updateSelectAllCheckbox() {
                const selectAllCheckbox = document.getElementById('select-all-checkbox');
                const visibleCheckboxes = document.querySelectorAll('.item-checkbox');
                const visibleCheckedCount = Array.from(visibleCheckboxes).filter(cb =>
                    cb.style.display !== 'none' && cb.checked
                ).length;
                const visibleTotalCount = Array.from(visibleCheckboxes).filter(cb =>
                    cb.style.display !== 'none'
                ).length;

                if (visibleCheckedCount === 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                } else if (visibleCheckedCount === visibleTotalCount) {
                    selectAllCheckbox.checked = true;
                    selectAllCheckbox.indeterminate = false;
                } else {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = true;
                }
            },

            // Quick filter methods
            applyQuickFilter() {
                switch (this.quickFilter) {
                    case 'select_low':
                        this.clearAllSelections();
                        this.items.forEach((item, index) => {
                            if (item.stock && item.stock.quantity_available <= item.min_stock) {
                                const checkbox = document.querySelector(`input[data-index="${index}"]`);
                                if (checkbox) {
                                    checkbox.checked = true;
                                    this.updateSelection(index, true);
                                }
                            }
                        });
                        break;
                    case 'select_out':
                        this.clearAllSelections();
                        this.items.forEach((item, index) => {
                            if (item.stock && item.stock.total_quantity === 0) {
                                const checkbox = document.querySelector(`input[data-index="${index}"]`);
                                if (checkbox) {
                                    checkbox.checked = true;
                                    this.updateSelection(index, true);
                                }
                            }
                        });
                        break;
                    case 'select_all_visible':
                        this.selectAllVisible();
                        break;
                }
                this.quickFilter = '';
                this.updateSelectAllCheckbox();
            },

            // Modification tracking
            markAsModified(index) {
                if (!this.modifiedItems.includes(index)) {
                    this.modifiedItems.push(index);
                }
            },

            // Quick set methods
            showQuickSetModal() {
                this.quickSetModal.show = true;
            },

            hideQuickSetModal() {
                this.quickSetModal.show = false;
            },

            applyQuickSet() {
                const { available, used, applyTo } = this.quickSetModal;

                this.items.forEach((item, index) => {
                    if (!item.stock) return;

                    let shouldApply = false;
                    switch (applyTo) {
                        case 'selected':
                            shouldApply = this.selectedItems.includes(item.stock.stock_id);
                            break;
                        case 'visible':
                            shouldApply = this.isItemVisible(index);
                            break;
                        case 'low_stock':
                            shouldApply = item.stock.quantity_available <= item.min_stock;
                            break;
                        case 'out_of_stock':
                            shouldApply = item.stock.total_quantity === 0;
                            break;
                    }

                    if (shouldApply) {
                        const availableInput = document.querySelector(`input[name="adjustments[${item.stock.stock_id}][new_available]"]`);
                        const usedInput = document.querySelector(`input[name="adjustments[${item.stock.stock_id}][new_used]"]`);

                        if (availableInput) {
                            availableInput.value = available;
                            availableInput.dispatchEvent(new Event('input'));
                        }
                        if (usedInput) {
                            usedInput.value = used;
                            usedInput.dispatchEvent(new Event('input'));
                        }

                        this.markAsModified(index);
                    }
                });

                this.hideQuickSetModal();
            },

            // Preview methods
            showPreviewModal() {
                this.previewModal.show = true;
            },

            hidePreviewModal() {
                this.previewModal.show = false;
            },

            getModifiedItemsCount() {
                return this.getPreviewChanges().length;
            },

            getPreviewChanges() {
                const changes = [];

                this.items.forEach((item, index) => {
                    if (!item.stock || !this.selectedItems.includes(item.stock.stock_id)) return;

                    const availableInput = document.querySelector(`input[name="adjustments[${item.stock.stock_id}][new_available]"]`);
                    const usedInput = document.querySelector(`input[name="adjustments[${item.stock.stock_id}][new_used]"]`);

                    if (!availableInput || !usedInput) return;

                    const newAvailable = parseInt(availableInput.value) || 0;
                    const newUsed = parseInt(usedInput.value) || 0;
                    const currentAvailable = item.stock.quantity_available;
                    const currentUsed = item.stock.quantity_used;

                    if (newAvailable !== currentAvailable || newUsed !== currentUsed) {
                        changes.push({
                            itemName: item.item_name,
                            itemCode: item.item_code,
                            currentAvailable,
                            currentUsed,
                            currentTotal: currentAvailable + currentUsed,
                            newAvailable,
                            newUsed,
                            newTotal: newAvailable + newUsed,
                            availableChange: newAvailable - currentAvailable,
                            usedChange: newUsed - currentUsed
                        });
                    }
                });

                return changes;
            },

            // Utility methods
            resetAllChanges() {
                if (confirm('Are you sure you want to reset all changes?')) {
                    this.items.forEach((item, index) => {
                        if (!item.stock) return;

                        const availableInput = document.querySelector(`input[name="adjustments[${item.stock.stock_id}][new_available]"]`);
                        const usedInput = document.querySelector(`input[name="adjustments[${item.stock.stock_id}][new_used]"]`);

                        if (availableInput) availableInput.value = item.stock.quantity_available;
                        if (usedInput) usedInput.value = item.stock.quantity_used;
                    });

                    this.modifiedItems = [];
                }
            },

            updatePagination() {
                // This could be implemented for actual pagination
                this.filterItems();
            },

            updateVisibleCount() {
                // Update visible count when filters change
                this.$nextTick(() => {
                    this.updateSelectAllCheckbox();
                });
            },

            validateForm(event) {
                if (this.selectedItems.length === 0) {
                    event.preventDefault();
                    alert('Please select at least one item to adjust.');
                    return false;
                }

                if (!this.adjustmentReason) {
                    event.preventDefault();
                    alert('Please select a reason for the adjustment.');
                    return false;
                }

                return true;
            }
        }
    }
</script>
@endpush
