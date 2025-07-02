@extends('layouts.app')

@section('title', 'Edit Purchase Order - LogistiK Admin')

@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="space-y-6" x-data="purchaseOrderEdit()">
        <!-- Breadcrumb -->
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-red-600">
                        <i class="fas fa-home mr-2"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <a href="{{ route('purchase-orders.index') }}"
                            class="text-sm font-medium text-gray-700 hover:text-red-600">
                            Purchase Orders
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <a href="{{ route('purchase-orders.show', $purchaseOrder->po_id) }}"
                            class="text-sm font-medium text-gray-700 hover:text-red-600">
                            {{ $purchaseOrder->po_number }}
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-sm font-medium text-gray-500">Edit</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div
                    class="w-16 h-16 bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-edit text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Purchase Order</h1>
                    <p class="text-gray-600 mt-1">{{ $purchaseOrder->po_number }} •
                        {{ $purchaseOrder->supplier->supplier_name }}</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('purchase-orders.show', $purchaseOrder->po_id) }}"
                    class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <!-- Status Warning -->
        @if ($purchaseOrder->status !== 'draft')
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6">
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-yellow-900 mb-2">Perhatian!</h3>
                        <p class="text-yellow-700 text-sm">
                            PO ini memiliki status <strong>{{ $purchaseOrder->getStatusInfo()['text'] }}</strong>.
                            Hanya PO dengan status Draft yang dapat diedit secara penuh.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- PO Form -->
        <form method="POST" action="{{ route('purchase-orders.update', $purchaseOrder->po_id) }}" x-ref="poForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- PO Header Card -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-file-invoice mr-2 text-blue-600"></i>
                                Informasi Purchase Order
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- PO Number -->
                                <div>
                                    <label for="po_number" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nomor PO
                                    </label>
                                    <div class="p-3 bg-gray-50 rounded-lg border">
                                        <span class="text-sm font-mono text-gray-900">{{ $purchaseOrder->po_number }}</span>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Status Saat Ini
                                    </label>
                                    <div class="p-3 bg-gray-50 rounded-lg border">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $purchaseOrder->getStatusInfo()['class'] }}">
                                            {{ $purchaseOrder->getStatusInfo()['text'] }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Supplier -->
                                <div>
                                    <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Supplier <span class="text-red-500">*</span>
                                    </label>
                                    <select id="supplier_id" name="supplier_id" x-model="selectedSupplierId"
                                        @change="onSupplierChange()"
                                        class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all @error('supplier_id') border-red-500 @enderror">
                                        <option value="">Pilih Supplier</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->supplier_id }}"
                                                {{ old('supplier_id', $purchaseOrder->supplier_id) == $supplier->supplier_id ? 'selected' : '' }}>
                                                {{ $supplier->supplier_name }} ({{ $supplier->supplier_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- PO Date -->
                                <div>
                                    <label for="po_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tanggal PO <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" id="po_date" name="po_date"
                                        value="{{ old('po_date', $purchaseOrder->po_date->format('Y-m-d')) }}"
                                        class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all @error('po_date') border-red-500 @enderror">
                                    @error('po_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Expected Date -->
                                <div>
                                    <label for="expected_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tanggal Diharapkan
                                    </label>
                                    <input type="date" id="expected_date" name="expected_date"
                                        value="{{ old('expected_date', $purchaseOrder->expected_date?->format('Y-m-d')) }}"
                                        class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all @error('expected_date') border-red-500 @enderror">
                                    @error('expected_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Created Info -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Dibuat Oleh
                                    </label>
                                    <div class="p-3 bg-gray-50 rounded-lg border">
                                        <span
                                            class="text-sm text-gray-900">{{ $purchaseOrder->createdBy->full_name }}</span>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $purchaseOrder->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mt-6">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Catatan
                                </label>
                                <textarea id="notes" name="notes" rows="3" placeholder="Catatan tambahan untuk purchase order..."
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all @error('notes') border-red-500 @enderror">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                                @error('notes')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Items Card -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-boxes mr-2 text-purple-600"></i>
                                    Items Purchase Order
                                </h3>
                                <button type="button" @click="showAddItemModal()"
                                    class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-plus mr-1"></i>
                                    Tambah Item
                                </button>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <template x-for="(item, index) in selectedItems" :key="index">
                                        <tr class="hover:bg-gray-50" :class="item.quantity_received > 0 ? 'bg-blue-50' : ''">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg flex items-center justify-center">
                                                        <i class="fas fa-box text-white text-sm"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900" x-text="item.item_name"></div>
                                                        <div class="text-sm text-gray-500" x-text="item.item_code"></div>
                                                        <div class="text-xs text-gray-400" x-text="item.category_name"></div>
                                                    </div>
                                                </div>
                                                <div class="mt-3 flex items-center justify-between">
                                                    <span class="text-xs text-gray-500" x-text="`Unit: ${item.unit}`"></span>
                                                    <div class="text-xs" :class="item.stock_status === 'low' ? 'text-red-600' : 'text-green-600'">
                                                        <span x-text="`Stok: ${item.available_stock || 0}`"></span>
                                                    </div>
                                                </div>
                                                <!-- Progress indicator untuk received items -->
                                                <div x-show="item.quantity_received > 0" class="mt-2">
                                                    <div class="flex items-center space-x-2">
                                                        <i class="fas fa-check-circle text-green-600 text-xs"></i>
                                                        <span class="text-xs text-green-600" x-text="`Diterima: ${item.quantity_received}/${item.quantity}`"></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <input type="number"
                                                    :name="`items[${index}][quantity]`"
                                                    x-model="item.quantity"
                                                    @input="updateItemTotal(index)"
                                                    :min="item.quantity_received || 0"
                                                    :class="item.quantity_received > 0 ? 'cursor-not-allowed bg-gray-100' : ''"
                                                    :readonly="item.quantity_received > 0"
                                                    class="w-20 px-2 py-1 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <input type="hidden" :name="`items[${index}][item_id]`" x-model="item.item_id">
                                            </td>
                                            <td class="px-6 py-4">
                                                <input type="number"
                                                    :name="`items[${index}][unit_price]`"
                                                    x-model="item.unit_price"
                                                    @input="updateItemTotal(index)"
                                                    step="0.01"
                                                    class="w-28 px-2 py-1 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm font-medium text-gray-900" x-text="formatCurrency(item.total)"></span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <button type="button"
                                                    @click="removeItem(index)"
                                                    :disabled="item.quantity_received > 0"
                                                    :class="item.quantity_received > 0 ? 'cursor-not-allowed opacity-50' : 'hover:bg-red-100'"
                                                    class="p-2 text-red-600 rounded-lg transition-colors">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>

                                    <!-- Empty state -->
                                    <tr x-show="selectedItems.length === 0">
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fas fa-box-open text-4xl text-gray-300 mb-2"></i>
                                            <p>Belum ada item yang dipilih</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Items Summary -->
                        <div x-show="selectedItems.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-600">
                                    Total Items: <span class="font-medium" x-text="selectedItems.length"></span>
                                    | Total Qty: <span class="font-medium" x-text="totalQuantity"></span>
                                </div>
                                <div class="text-lg font-bold text-gray-900">
                                    Total: <span x-text="formatCurrency(totalAmount)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Summary & Actions -->
                <div class="space-y-6">
                    <!-- Supplier Info Card -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-truck mr-2 text-green-600"></i>
                                Supplier Info
                            </h3>
                        </div>
                        <div class="p-6">
                            <div x-show="selectedSupplier" class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Nama Supplier</label>
                                    <p class="text-sm text-gray-900 mt-1" x-text="selectedSupplier?.supplier_name"></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Kode Supplier</label>
                                    <p class="text-sm text-gray-900 mt-1" x-text="selectedSupplier?.supplier_code"></p>
                                </div>
                                <div x-show="selectedSupplier?.contact_person">
                                    <label class="text-sm font-medium text-gray-700">Contact Person</label>
                                    <p class="text-sm text-gray-900 mt-1" x-text="selectedSupplier?.contact_person"></p>
                                </div>
                                <div x-show="selectedSupplier?.phone">
                                    <label class="text-sm font-medium text-gray-700">Telepon</label>
                                    <p class="text-sm text-gray-900 mt-1" x-text="selectedSupplier?.phone"></p>
                                </div>
                            </div>
                            <div x-show="!selectedSupplier" class="text-center py-4">
                                <i class="fas fa-truck text-3xl text-gray-300 mb-2"></i>
                                <p class="text-gray-500 text-sm">Pilih supplier terlebih dahulu</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-calculator mr-2 text-blue-600"></i>
                                Order Summary
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Items</span>
                                <span class="text-sm font-medium" x-text="selectedItems.length"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Quantity</span>
                                <span class="text-sm font-medium" x-text="totalQuantity"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Received</span>
                                <span class="text-sm font-medium text-green-600" x-text="totalReceived"></span>
                            </div>
                            <div class="border-t pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold text-gray-900">Grand Total</span>
                                    <span class="text-lg font-bold text-blue-600" x-text="formatCurrency(totalAmount)"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="p-6 space-y-4">
                            <button type="submit"
                                class="w-full px-4 py-3 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-save"></i>
                                <span>Update Purchase Order</span>
                            </button>

                            <a href="{{ route('purchase-orders.show', $purchaseOrder->po_id) }}"
                                class="w-full px-4 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-times"></i>
                                <span>Batal</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Add Item Modal -->
        <div x-show="addItemModal.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 transition-opacity" @click="hideAddItemModal()">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Pilih Item</h3>
                            <button @click="hideAddItemModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Search -->
                        <div class="mb-6">
                            <input type="text"
                                x-model="itemSearch"
                                @input="filterItems()"
                                placeholder="Cari item berdasarkan nama, kode, atau kategori..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Items Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                            <template x-for="item in filteredItems" :key="item.item_id">
                                <div @click="selectItemForAdd(item)"
                                     class="item-card border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-blue-500 hover:shadow-md transition-all">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-white"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-medium text-gray-900 truncate" x-text="item.item_name"></h4>
                                            <p class="text-xs text-gray-500" x-text="item.item_code"></p>
                                            <p class="text-xs text-gray-400" x-text="item.category_name"></p>
                                            <div class="mt-2 flex items-center justify-between">
                                                <span class="text-xs text-gray-600" x-text="`Unit: ${item.unit}`"></span>
                                                <div class="text-xs" :class="item.stock_status === 'low' ? 'text-red-600' : 'text-green-600'">
                                                    <span x-text="`Stok: ${item.available_stock || 0}`"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- No items found -->
                        <div x-show="filteredItems.length === 0" class="text-center py-8">
                            <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">Tidak ada item yang ditemukan</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex justify-end">
                            <button type="button" @click="hideAddItemModal()"
                                class="px-4 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-colors">
                                Tutup
                            </button>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function purchaseOrderEdit() {
            return {
                selectedSupplierId: '{{ old('supplier_id', $purchaseOrder->supplier_id) }}',
                selectedSupplier: null,
                selectedItems: [],
                addItemModal: {
                    show: false
                },
                itemSearch: '',
                filteredItems: [],
                availableItems: [],
                suppliers: [],

                get totalAmount() {
                    return this.selectedItems.reduce((total, item) => total + (item.total || 0), 0);
                },

                get totalQuantity() {
                    return this.selectedItems.reduce((total, item) => total + (parseInt(item.quantity) || 0), 0);
                },

                get totalReceived() {
                    return this.selectedItems.reduce((total, item) => total + (parseInt(item.quantity_received) || 0), 0);
                },

                init() {
                    this.loadData();
                    this.loadExistingItems();
                    this.updateSelectedSupplier();
                },

                loadData() {
                    // Load available items
                    try {
                        this.availableItems = {!! json_encode(
                            $items->map(function ($item) {
                                $stockInfo = $item->getStockInfo();
                                return [
                                    'item_id' => $item->item_id,
                                    'item_name' => $item->item_name,
                                    'item_code' => $item->item_code,
                                    'category_name' => optional($item->category)->category_name ?? 'No Category',
                                    'unit' => $item->unit,
                                    'available_stock' => $stockInfo['available'] ?? 0,
                                    'stock_status' => $stockInfo['status'] ?? 'unknown',
                                ];
                            }),
                        ) !!};
                    } catch (e) {
                        console.log('Error loading items:', e);
                        this.availableItems = [];
                    }

                    // Load suppliers
                    try {
                        this.suppliers = {!! json_encode(
                            $suppliers->map(function ($supplier) {
                                return [
                                    'supplier_id' => $supplier->supplier_id,
                                    'supplier_name' => $supplier->supplier_name,
                                    'supplier_code' => $supplier->supplier_code,
                                    'contact_person' => $supplier->contact_person ?? '',
                                    'phone' => $supplier->phone ?? '',
                                    'address' => $supplier->address ?? '',
                                ];
                            }),
                        ) !!};
                    } catch (e) {
                        console.log('Error loading suppliers:', e);
                        this.suppliers = [];
                    }

                    this.filteredItems = [...this.availableItems];
                },

                loadExistingItems() {
                    // Load existing PO items
                    try {
                        this.selectedItems = {!! json_encode(
                            $purchaseOrder->poDetails->map(function ($detail) {
                                return [
                                    'item_id' => $detail->item_id,
                                    'item_name' => $detail->item->item_name,
                                    'item_code' => $detail->item->item_code,
                                    'category_name' => optional($detail->item->category)->category_name ?? 'No Category',
                                    'unit' => $detail->item->unit,
                                    'quantity' => $detail->quantity_ordered,
                                    'quantity_received' => $detail->quantity_received,
                                    'unit_price' => floatval($detail->unit_price),
                                    'total' => floatval($detail->total_price),
                                ];
                            }),
                        ) !!};
                    } catch (e) {
                        console.log('Error loading existing items:', e);
                        this.selectedItems = [];
                    }
                },

                updateSelectedSupplier() {
                    if (this.selectedSupplierId) {
                        this.selectedSupplier = this.suppliers.find(s => s.supplier_id === this.selectedSupplierId);
                    } else {
                        this.selectedSupplier = null;
                    }
                },

                onSupplierChange() {
                    this.updateSelectedSupplier();
                },

                showAddItemModal() {
                    this.addItemModal.show = true;
                    this.itemSearch = '';
                    this.filterItems();
                },

                hideAddItemModal() {
                    this.addItemModal.show = false;
                },

                filterItems() {
                    if (!this.itemSearch.trim()) {
                        this.filteredItems = [...this.availableItems];
                    } else {
                        const search = this.itemSearch.toLowerCase();
                        this.filteredItems = this.availableItems.filter(item =>
                            item.item_name.toLowerCase().includes(search) ||
                            item.item_code.toLowerCase().includes(search) ||
                            item.category_name.toLowerCase().includes(search)
                        );
                    }

                    // Remove already selected items
                    const selectedIds = this.selectedItems.map(item => item.item_id);
                    this.filteredItems = this.filteredItems.filter(item => !selectedIds.includes(item.item_id));
                },

                selectItemForAdd(item) {
                    this.addItem(item.item_id, item.item_name, item.item_code, item.category_name, item.unit);
                    this.hideAddItemModal();
                },

                addItem(itemId, itemName, itemCode, categoryName, unit) {
                    // Check if item already exists
                    const existingIndex = this.selectedItems.findIndex(item => item.item_id === itemId);
                    if (existingIndex !== -1) {
                        // Increase quantity if already exists
                        this.selectedItems[existingIndex].quantity = parseInt(this.selectedItems[existingIndex].quantity) + 1;
                        this.updateItemTotal(existingIndex);
                        this.showToast('Quantity item ditambah', 'info');
                    } else {
                        // Add new item
                        this.selectedItems.push({
                            item_id: itemId,
                            item_name: itemName,
                            item_code: itemCode,
                            category_name: categoryName,
                            unit: unit,
                            quantity: 1,
                            quantity_received: 0,
                            unit_price: 0,
                            total: 0
                        });
                        this.showToast('Item berhasil ditambahkan', 'success');
                    }
                },

                removeItem(index) {
                    const item = this.selectedItems[index];

                    // Check if item has been received
                    if (item.quantity_received > 0) {
                        this.showToast('Item yang sudah diterima tidak dapat dihapus', 'error');
                        return;
                    }

                    this.selectedItems.splice(index, 1);
                    this.showToast(`${item.item_name} dihapus dari PO`, 'info');
                },

                updateItemTotal(index) {
                    const item = this.selectedItems[index];
                    const quantity = parseFloat(item.quantity) || 0;
                    const unitPrice = parseFloat(item.unit_price) || 0;
                    item.total = quantity * unitPrice;
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(amount || 0);
                },

                showToast(message, type = 'info') {
                    const toast = document.createElement('div');
                    toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-xl shadow-lg transition-all duration-300 ${
                        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
                        type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' :
                        'bg-blue-100 border border-blue-400 text-blue-700'
                    }`;

                    toast.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
                            <span>${message}</span>
                        </div>
                    `;

                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                },

                // Validation before submit
                validateForm() {
                    if (!this.selectedSupplierId) {
                        this.showToast('Supplier harus dipilih', 'error');
                        return false;
                    }

                    if (this.selectedItems.length === 0) {
                        this.showToast('Minimal harus ada 1 item', 'error');
                        return false;
                    }

                    // Validate each item
                    for (let i = 0; i < this.selectedItems.length; i++) {
                        const item = this.selectedItems[i];
                        if (!item.quantity || item.quantity <= 0) {
                            this.showToast(`Quantity ${item.item_name} harus lebih dari 0`, 'error');
                            return false;
                        }
                        if (item.quantity < item.quantity_received) {
                            this.showToast(
                                `Quantity ${item.item_name} tidak boleh kurang dari yang sudah diterima (${item.quantity_received})`,
                                'error');
                            return false;
                        }
                        if (!item.unit_price || item.unit_price < 0) {
                            this.showToast(`Harga ${item.item_name} harus diisi`, 'error');
                            return false;
                        }
                    }

                    return true;
                }
            }
        }

        // Form submission with validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const alpineComponent = document.querySelector('[x-data]');
                    if (alpineComponent && alpineComponent._x_dataStack) {
                        const alpineData = alpineComponent._x_dataStack[0];
                        if (alpineData && alpineData.validateForm && !alpineData.validateForm()) {
                            e.preventDefault();
                            return false;
                        }
                    }
                });
            }

            // Initialize Select2 for better UX
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#supplier_id').select2({
                    placeholder: 'Pilih Supplier',
                    allowClear: true,
                    width: '100%'
                }).on('change', function() {
                    // Trigger Alpine.js update
                    const event = new Event('change');
                    this.dispatchEvent(event);
                });
            }
        });
    </script>

    <style>
        /* Custom animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slideIn {
            animation: slideIn 0.3s ease-out;
        }

        /* Item selection hover effects */
        .item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Number input styling */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        /* Select2 custom styling */
        .select2-container--default .select2-selection--single {
            height: 48px !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 12px !important;
            background-color: #f9fafb !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 46px !important;
            padding-left: 16px !important;
            color: #374151 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
            right: 16px !important;
        }

        /* Disabled state for received items */
        .cursor-not-allowed {
            cursor: not-allowed !important;
        }

        /* Lock icon styling */
        .fa-lock {
            opacity: 0.7;
        }

        /* Progress indicator improvements */
        .progress-indicator {
            transition: all 0.3s ease;
        }

        /* Responsive table improvements */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 14px;
            }

            .table-responsive td {
                padding: 8px 4px;
            }

            .table-responsive input {
                min-width: 60px;
            }
        }

        /* Highlight changes */
        .item-changed {
            background-color: rgba(59, 130, 246, 0.05);
            border-left: 3px solid #3b82f6;
        }

        /* Warning states */
        .item-warning {
            background-color: rgba(245, 158, 11, 0.05);
            border-left: 3px solid #f59e0b;
        }

        /* Modal improvements */
        .modal-backdrop {
            backdrop-filter: blur(4px);
        }

        /* Toast improvements */
        .toast-enter {
            animation: toast-slide-in 0.3s ease-out;
        }

        @keyframes toast-slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Form field focus improvements */
        .form-input:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Button hover improvements */
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* Table row hover improvements */
        .table-row:hover {
            background-color: rgba(59, 130, 246, 0.02);
        }

        /* Received items styling */
        .item-received {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(34, 197, 94, 0.05) 100%);
            border-left: 4px solid #22c55e;
        }

        /* Price input focus */
        input[type="number"]:focus {
            background-color: #fff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Status indicators */
        .status-badge {
            position: relative;
            overflow: hidden;
        }

        .status-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s;
        }

        .status-badge:hover::before {
            left: 100%;
        }
    </style>
@endpush
