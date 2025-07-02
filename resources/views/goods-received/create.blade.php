@extends('layouts.app')

@section('title', 'Terima Barang - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="space-y-6" x-data="goodsReceivedCreate()">
    <!-- Breadcrumb -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-green-600">
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="{{ route('goods-received.index') }}"
                        class="text-sm font-medium text-gray-700 hover:text-green-600">
                        Penerimaan Barang
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">Terima Barang</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-br from-green-600 to-green-700 rounded-2xl flex items-center justify-center">
                <i class="fas fa-truck text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Terima Barang</h1>
                <p class="text-gray-600 mt-1">Catat penerimaan barang dari purchase order</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('goods-received.index') }}"
               class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- GR Form -->
    <form method="POST" action="{{ route('goods-received.store') }}" x-ref="grForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- GR Header Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-truck mr-2 text-green-600"></i>
                            Informasi Penerimaan
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- GR Number -->
                            <div>
                                <label for="receive_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor GR
                                </label>
                                <div class="p-3 bg-gray-50 rounded-lg border">
                                    <span class="text-sm font-mono text-gray-900">{{ $receiveNumber }}</span>
                                </div>
                                <input type="hidden" name="receive_number" value="{{ $receiveNumber }}">
                            </div>

                            <!-- Receive Date -->
                            <div>
                                <label for="receive_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Penerimaan <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="receive_date" name="receive_date"
                                    value="{{ old('receive_date', now()->format('Y-m-d')) }}"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('receive_date') border-red-500 @enderror">
                                @error('receive_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Purchase Order -->
                            <div class="md:col-span-2">
                                <label for="po_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Purchase Order <span class="text-red-500">*</span>
                                </label>
                                <select id="po_id" name="po_id" x-model="selectedPOId"
                                    @change="onPOChange()"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('po_id') border-red-500 @enderror">
                                    <option value="">Pilih Purchase Order</option>
                                    @foreach ($availablePOs as $po)
                                        <option value="{{ $po->po_id }}"
                                                {{ old('po_id', request('po_id')) == $po->po_id ? 'selected' : '' }}
                                                data-supplier-name="{{ $po->supplier->supplier_name }}"
                                                data-po-date="{{ $po->po_date->format('d/m/Y') }}">
                                            {{ $po->po_number }} - {{ $po->supplier->supplier_name }} ({{ $po->po_date->format('d/m/Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('po_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mt-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan
                            </label>
                            <textarea id="notes" name="notes" rows="3" placeholder="Catatan penerimaan barang..."
                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
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
                                Items yang Diterima
                            </h3>
                            <div x-show="selectedPO" class="text-sm text-gray-600">
                                <span x-text="selectedPO ? selectedPO.po_number : ''"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div x-show="loadingPO" class="p-8 text-center">
                        <div class="inline-flex items-center">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-600 mr-3"></div>
                            <span class="text-gray-600">Memuat data PO...</span>
                        </div>
                    </div>

                    <!-- No PO Selected -->
                    <div x-show="!selectedPOId && !loadingPO" class="p-8 text-center">
                        <i class="fas fa-file-invoice text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Pilih Purchase Order</h3>
                        <p class="text-gray-500">Pilih PO terlebih dahulu untuk menampilkan items yang dapat diterima</p>
                    </div>

                    <!-- Error State -->
                    <div x-show="selectedPOId && !loadingPO && availableItems.length === 0 && !selectedPO" class="p-8 text-center">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Gagal Memuat Data PO</h3>
                        <p class="text-gray-500 mb-4">Terjadi kesalahan saat memuat detail purchase order</p>
                        <button @click="onPOChange()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-refresh mr-2"></i>
                            Coba Lagi
                        </button>
                    </div>

                    <!-- No Items Available -->
                    <div x-show="selectedPO && !loadingPO && availableItems.length === 0" class="p-8 text-center">
                        <i class="fas fa-check-circle text-4xl text-green-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Semua Item Sudah Diterima</h3>
                        <p class="text-gray-500">Tidak ada item yang dapat diterima dari PO ini</p>
                    </div>

             <!-- Items Table - Updated sesuai dengan response controller -->
<div x-show="availableItems.length > 0 && !loadingPO" class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sisa Order</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Terima</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alokasi Stok</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siap Pakai</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <template x-for="(item, index) in availableItems" :key="item.item_id">
                <tr class="hover:bg-gray-50">
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
                    </td>

                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900" x-text="formatNumber(item.remaining_quantity)"></div>
                        <div class="text-xs text-gray-500" x-text="item.unit"></div>
                        <div class="text-xs text-gray-400">
                            Ordered: <span x-text="formatNumber(item.quantity_ordered)"></span> |
                            Received: <span x-text="formatNumber(item.quantity_received_existing)"></span>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <div class="space-y-2">
                            <input type="number"
                                :name="`items[${index}][quantity_received]`"
                                x-model="item.quantity_received"
                                @input="updateItemCalculations(index)"
                                :max="item.remaining_quantity"
                                step="0.01"
                                min="0"
                                placeholder="0"
                                class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">

                            <!-- Hidden inputs untuk data yang diperlukan -->
                            <input type="hidden" :name="`items[${index}][po_detail_id]`" x-model="item.po_detail_id">
                            <input type="hidden" :name="`items[${index}][item_id]`" x-model="item.item_id">
                            <input type="hidden" :name="`items[${index}][unit_price]`" x-model="item.unit_price">

                            <!-- Validation -->
                            <div x-show="item.quantity_received > item.remaining_quantity"
                                 class="text-xs text-red-600">
                                Melebihi sisa order
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <div class="space-y-2">
                            <input type="number"
                                :name="`items[${index}][quantity_to_stock]`"
                                x-model="item.quantity_to_stock"
                                @input="updateItemCalculations(index)"
                                :max="item.quantity_received || 0"
                                step="0.01"
                                min="0"
                                placeholder="0"
                                class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                            <div class="text-xs text-blue-600">
                                <span x-text="item.quantity_to_stock ? ((item.quantity_to_stock / (item.quantity_received || 1)) * 100).toFixed(1) + '%' : '0%'"></span>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <div class="space-y-2">
                            <input type="number"
                                :name="`items[${index}][quantity_to_ready]`"
                                x-model="item.quantity_to_ready"
                                @input="updateItemCalculations(index)"
                                :max="item.quantity_received || 0"
                                step="0.01"
                                min="0"
                                placeholder="0"
                                class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">

                            <div class="text-xs text-green-600">
                                <span x-text="item.quantity_to_ready ? ((item.quantity_to_ready / (item.quantity_received || 1)) * 100).toFixed(1) + '%' : '0%'"></span>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900" x-text="formatCurrency(item.unit_price)"></div>
                        <div class="text-xs text-gray-500" x-text="`per ${item.unit}`"></div>
                    </td>

                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900" x-text="formatCurrency(item.total)"></div>

                        <!-- Validation indicator -->
                        <div x-show="!isItemValid(item)" class="text-xs text-red-600 mt-1">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Alokasi tidak sesuai
                        </div>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>

<!-- Additional Item Info - Updated sesuai dengan response controller -->
<div x-show="availableItems.length > 0 && !loadingPO" class="px-6 py-4 border-t border-gray-200">
    <div class="space-y-4">
        <h4 class="text-sm font-medium text-gray-900">Informasi Tambahan (Opsional)</h4>

        <template x-for="(item, index) in availableItems" :key="'info-' + item.item_id">
            <div x-show="item.quantity_received > 0" class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm font-medium text-gray-900 mb-3" x-text="item.item_name"></div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Batch Number</label>
                        <input type="text"
                            :name="`items[${index}][batch_number]`"
                            placeholder="Nomor batch..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Expiry Date</label>
                        <input type="date"
                            :name="`items[${index}][expiry_date]`"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Catatan Item</label>
                        <input type="text"
                            :name="`items[${index}][notes]`"
                            placeholder="Catatan untuk item ini..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

                    <!-- Items Summary -->
                    <div x-show="availableItems.length > 0 && !loadingPO" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center">
                                <div class="text-lg font-bold text-gray-900" x-text="totalReceived"></div>
                                <div class="text-xs text-gray-600">Total Diterima</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-bold text-blue-600" x-text="totalToStock"></div>
                                <div class="text-xs text-gray-600">Ke Stok</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-bold text-green-600" x-text="totalToReady"></div>
                                <div class="text-xs text-gray-600">Siap Pakai</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-bold text-purple-600" x-text="formatCurrency(totalValue)"></div>
                                <div class="text-xs text-gray-600">Total Nilai</div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>

            <!-- Right Column - Summary & Actions -->
            <div class="space-y-6">
                <!-- PO Info Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-file-invoice mr-2 text-blue-600"></i>
                            Purchase Order Info
                        </h3>
                    </div>
                    <div class="p-6">
                        <div x-show="selectedPO" class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Nomor PO</label>
                                <p class="text-sm text-gray-900 mt-1" x-text="selectedPO?.po_number"></p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Supplier</label>
                                <p class="text-sm text-gray-900 mt-1" x-text="selectedPO?.supplier_name"></p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Tanggal PO</label>
                                <p class="text-sm text-gray-900 mt-1" x-text="selectedPO?.po_date"></p>
                            </div>
                        </div>
                        <div x-show="!selectedPO" class="text-center py-4">
                            <i class="fas fa-file-invoice text-3xl text-gray-300 mb-2"></i>
                            <p class="text-gray-500 text-sm">Pilih PO untuk melihat informasi</p>
                        </div>
                    </div>
                </div>

                <!-- Receive Summary -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-calculator mr-2 text-green-600"></i>
                            Ringkasan Penerimaan
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Items dengan Penerimaan</span>
                            <span class="text-sm font-medium" x-text="itemsWithReceiving"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total Quantity</span>
                            <span class="text-sm font-medium" x-text="totalReceived"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Ke Stok</span>
                            <span class="text-sm font-medium text-blue-600" x-text="totalToStock"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Siap Pakai</span>
                            <span class="text-sm font-medium text-green-600" x-text="totalToReady"></span>
                        </div>
                        <div class="border-t pt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">Grand Total</span>
                                <span class="text-lg font-bold text-green-600" x-text="formatCurrency(totalValue)"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 space-y-4">
                        <button type="submit"
                                :disabled="!canSubmit"
                                class="w-full px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-save"></i>
                            <span>Simpan Penerimaan</span>
                        </button>

                        <a href="{{ route('goods-received.index') }}"
                           class="w-full px-4 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </a>

                        <button type="button"
                                @click="autoAllocate()"
                                :disabled="!selectedPOId"
                                class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-magic"></i>
                            <span>Auto Alokasi</span>
                        </button>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="bg-blue-50 rounded-2xl border border-blue-200 p-6">
                    <h4 class="text-lg font-semibold text-blue-900 mb-3 flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Tips
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Alokasi Stok: untuk barang yang disimpan di gudang</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Siap Pakai: untuk barang yang langsung digunakan</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Total alokasi harus sama dengan quantity yang diterima</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </form>

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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
function goodsReceivedCreate() {
    return {
        selectedPOId: '{{ old('po_id', request('po_id')) }}',
        selectedPO: null,
        availableItems: [],
        loadingPO: false,

        get totalReceived() {
            return this.availableItems.reduce((total, item) => total + (parseFloat(item.quantity_received) || 0), 0);
        },

        get totalToStock() {
            return this.availableItems.reduce((total, item) => total + (parseFloat(item.quantity_to_stock) || 0), 0);
        },

        get totalToReady() {
            return this.availableItems.reduce((total, item) => total + (parseFloat(item.quantity_to_ready) || 0), 0);
        },

        get totalValue() {
            return this.availableItems.reduce((total, item) => total + (item.total || 0), 0);
        },

        get itemsWithReceiving() {
            return this.availableItems.filter(item => (parseFloat(item.quantity_received) || 0) > 0).length;
        },

        get canSubmit() {
            if (!this.selectedPOId || this.itemsWithReceiving === 0) return false;

            // Check if all items with quantity_received have valid allocations
            return this.availableItems.every(item => {
                if ((parseFloat(item.quantity_received) || 0) === 0) return true;
                return this.isItemValid(item);
            });
        },

        init() {
            console.log('Initializing goods received create form');
            console.log('Selected PO ID:', this.selectedPOId);

            // Load PO if pre-selected
            if (this.selectedPOId) {
                this.onPOChange();
            }

            // Add CSRF token to all requests
            const token = document.querySelector('meta[name="csrf-token"]');
            if (token) {
                window.csrfToken = token.getAttribute('content');
            }
        },

        async onPOChange() {
            if (!this.selectedPOId) {
                this.selectedPO = null;
                this.availableItems = [];
                return;
            }

            console.log('Loading PO details for ID:', this.selectedPOId);
            this.loadingPO = true;

            try {
                // Sesuaikan dengan route yang sudah ada
                const url = `/api/goods-received/po-details/${this.selectedPOId}`;

                console.log('Fetching from URL:', url);

                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                console.log('Response status:', response.status);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Response error:', errorText);
                    throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
                }

                const data = await response.json();
                console.log('Response data:', data);

                // Sesuaikan dengan struktur response dari controller
                if (data.po && data.details) {
                    this.selectedPO = {
                        po_id: data.po.po_id,
                        po_number: data.po.po_number,
                        supplier_name: data.po.supplier_name,
                        po_date: data.po.po_date
                    };

                    // Map details sesuai dengan response controller
                    this.availableItems = data.details.map(item => ({
                        // Data dari controller response
                        po_detail_id: item.po_detail_id,
                        item_id: item.item_id,
                        item_code: item.item_code,
                        item_name: item.item_name,
                        category_name: item.category_name,
                        unit: item.unit,
                        quantity_ordered: item.quantity_ordered,
                        quantity_received_existing: item.quantity_received, // Qty yang sudah diterima sebelumnya
                        remaining_quantity: item.remaining_quantity,
                        unit_price: item.unit_price,
                        can_receive: item.can_receive,

                        // Data untuk form input (initialize dengan 0)
                        quantity_received: 0,        // Qty yang akan diterima sekarang
                        quantity_to_stock: 0,        // Alokasi ke stok
                        quantity_to_ready: 0,        // Alokasi ke ready/siap pakai
                        total: 0                     // Total nilai (akan dihitung)
                    }));

                    console.log('Available items:', this.availableItems);
                    this.showToast(`Berhasil memuat ${data.details.length} items dari PO ${data.po.po_number}`, 'success');
                } else {
                    console.error('Invalid data structure:', data);
                    throw new Error(data.error || 'Data PO tidak lengkap');
                }
            } catch (error) {
                console.error('Error loading PO details:', error);
                this.showToast('Gagal memuat detail PO: ' + error.message, 'error');
                this.selectedPO = null;
                this.availableItems = [];
            } finally {
                this.loadingPO = false;
            }
        },

        updateItemCalculations(index) {
            const item = this.availableItems[index];
            const qtyReceived = parseFloat(item.quantity_received) || 0;
            const qtyToStock = parseFloat(item.quantity_to_stock) || 0;
            const qtyToReady = parseFloat(item.quantity_to_ready) || 0;
            const unitPrice = parseFloat(item.unit_price) || 0;

            // Calculate total value
            item.total = qtyReceived * unitPrice;

            // Auto-adjust allocation if sum exceeds received
            const totalAllocation = qtyToStock + qtyToReady;
            if (totalAllocation > qtyReceived && qtyReceived > 0) {
                // Redistribute proportionally
                const stockRatio = qtyToStock / totalAllocation;
                const readyRatio = qtyToReady / totalAllocation;

                item.quantity_to_stock = Math.round(qtyReceived * stockRatio * 100) / 100;
                item.quantity_to_ready = Math.round(qtyReceived * readyRatio * 100) / 100;

                // Adjust for rounding errors
                const newTotal = item.quantity_to_stock + item.quantity_to_ready;
                if (newTotal !== qtyReceived) {
                    item.quantity_to_stock = qtyReceived - item.quantity_to_ready;
                }
            }
        },

        isItemValid(item) {
            const qtyReceived = parseFloat(item.quantity_received) || 0;
            const qtyToStock = parseFloat(item.quantity_to_stock) || 0;
            const qtyToReady = parseFloat(item.quantity_to_ready) || 0;

            if (qtyReceived === 0) return true;

            // Validasi: total alokasi harus sama dengan qty yang diterima
            return Math.abs((qtyToStock + qtyToReady) - qtyReceived) < 0.01;
        },

        autoAllocate() {
            if (!this.selectedPOId) return;

            // Auto-allocate 70% to stock, 30% to ready for items with quantity_received
            this.availableItems.forEach((item, index) => {
                const qtyReceived = parseFloat(item.quantity_received) || 0;
                if (qtyReceived > 0) {
                    item.quantity_to_stock = Math.round(qtyReceived * 0.7 * 100) / 100;
                    item.quantity_to_ready = Math.round(qtyReceived * 0.3 * 100) / 100;

                    // Adjust for rounding
                    const total = item.quantity_to_stock + item.quantity_to_ready;
                    if (total !== qtyReceived) {
                        item.quantity_to_stock = qtyReceived - item.quantity_to_ready;
                    }

                    this.updateItemCalculations(index);
                }
            });

            this.showToast('Auto alokasi berhasil diterapkan (70% stok, 30% siap pakai)', 'success');
        },

        formatCurrency(amount) {
            if (!amount || isNaN(amount)) return 'Rp 0';

            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        },

        formatNumber(number) {
            if (!number || isNaN(number)) return '0';
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(number);
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
            }, 5000);
        }
    }
}
    // Form submission validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const alpineComponent = document.querySelector('[x-data]');
                if (alpineComponent && alpineComponent._x_dataStack) {
                    const alpineData = alpineComponent._x_dataStack[0];

                    if (alpineData && !alpineData.canSubmit) {
                        e.preventDefault();
                        alpineData.showToast('Pastikan semua data sudah terisi dengan benar', 'error');
                        return false;
                    }

                    // Additional validation
                    const hasReceiving = alpineData.itemsWithReceiving > 0;
                    if (!hasReceiving) {
                        e.preventDefault();
                        alpineData.showToast('Minimal harus ada 1 item yang diterima', 'error');
                        return false;
                    }
                }
            });
        }

        // Initialize Select2 for better UX
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#po_id').select2({
                placeholder: 'Pilih Purchase Order',
                allowClear: true,
                width: '100%'
            }).on('change', function() {
                // Trigger Alpine.js update
                const event = new Event('change');
                this.dispatchEvent(event);
            });
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + S for save
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                form.submit();
            }

            // Ctrl + A for auto allocate
            if (e.ctrlKey && e.key === 'a') {
                e.preventDefault();
                const alpineComponent = document.querySelector('[x-data]');
                if (alpineComponent && alpineComponent._x_dataStack) {
                    const alpineData = alpineComponent._x_dataStack[0];
                    if (alpineData && alpineData.autoAllocate) {
                        alpineData.autoAllocate();
                    }
                }
            }
        });
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

    /* Input focus improvements */
    input:focus, select:focus, textarea:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Button hover improvements */
    .btn-hover {
        transition: all 0.3s ease;
    }

    .btn-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    /* Table improvements */
    .table-hover tr:hover {
        background-color: rgba(16, 185, 129, 0.02);
    }

    /* Loading spinner */
    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
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

    /* Validation states */
    .validation-error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }

    .validation-success {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
    }

    /* Progress indicators */
    .progress-indicator {
        transition: all 0.3s ease;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .grid-cols-1.md\\:grid-cols-3 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        .grid-cols-2.md\\:grid-cols-4 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .space-x-3 {
            gap: 0.5rem;
        }
    }

    /* Print styles */
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
@endpush
