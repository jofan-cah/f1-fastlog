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
                <p class="text-gray-600 mt-1" x-text="receiptType === 'po_based' ? 'Catat penerimaan barang berdasarkan Purchase Order' : 'Catat penerimaan barang langsung dari supplier'"></p>
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

    <!-- Receipt Type Selection -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-clipboard-list mr-2 text-blue-600"></i>
                Jenis Penerimaan
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- PO Based Receipt -->
                <div class="relative">
                    <input type="radio" id="receipt_po_based" x-model="receiptType" value="po_based"
                           class="peer absolute opacity-0">
                    <label for="receipt_po_based"
                           class="flex flex-col p-4 border-2 border-gray-200 rounded-xl cursor-pointer peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300 transition-all">
                        <div class="flex items-center space-x-3 mb-2">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-invoice text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Berdasarkan PO</h4>
                                <p class="text-sm text-gray-600">Terima barang sesuai Purchase Order</p>
                            </div>
                        </div>
                        <ul class="text-xs text-gray-500 space-y-1">
                            <li>• Items otomatis dari PO yang dipilih</li>
                            <li>• Tracking progress PO completion</li>
                            <li>• Validasi terhadap quantity ordered</li>
                        </ul>
                    </label>
                </div>

                <!-- Direct Receipt -->
                <div class="relative">
                    <input type="radio" id="receipt_direct" x-model="receiptType" value="direct"
                           class="peer absolute opacity-0">
                    <label for="receipt_direct"
                           class="flex flex-col p-4 border-2 border-gray-200 rounded-xl cursor-pointer peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300 transition-all">
                        <div class="flex items-center space-x-3 mb-2">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-truck-loading text-purple-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Penerimaan Langsung</h4>
                                <p class="text-sm text-gray-600">Terima barang tanpa PO</p>
                            </div>
                        </div>
                        <ul class="text-xs text-gray-500 space-y-1">
                            <li>• Pilih supplier dan items manual</li>
                            <li>• Untuk barang urgent atau donasi</li>
                            <li>• Input harga dan quantity bebas</li>
                        </ul>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- GR Form -->
    <form method="POST" action="{{ route('goods-received.store') }}" x-ref="grForm" @submit.prevent="submitForm()">
        @csrf
        <input type="hidden" name="receipt_type" x-model="receiptType">

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            <!-- Left Column - Form (3/4 width) -->
            <div class="xl:col-span-3 space-y-6">

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
                                    <span class="text-sm font-mono text-gray-900" x-text="receiveNumber"></span>
                                </div>
                                <input type="hidden" name="receive_number" x-model="receiveNumber">
                            </div>

                            <!-- Receive Date -->
                            <div>
                                <label for="receive_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Penerimaan <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="receive_date" name="receive_date"
                                    value="{{ old('receive_date', now()->format('Y-m-d')) }}"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                            </div>

                            <!-- PO Selection (Only for PO-based) -->
                            <div x-show="receiptType === 'po_based'" class="md:col-span-2">
                                <label for="po_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Purchase Order <span class="text-red-500">*</span>
                                </label>
                                <select id="po_id" name="po_id" x-model="selectedPOId"
                                    @change="onPOChange()"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
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
                            </div>

                            <!-- Supplier Selection (Only for Direct Receipt) -->
                            <div x-show="receiptType === 'direct'" class="md:col-span-2">
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Supplier <span class="text-red-500">*</span>
                                </label>
                                <select id="supplier_id" name="supplier_id" x-model="selectedSupplierId"
                                    @change="onSupplierChange()"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                                    <option value="">Pilih Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->supplier_id }}">
                                            {{ $supplier->supplier_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- External References (Only for Direct Receipt) -->
                            <template x-if="receiptType === 'direct'">
                                <div class="md:col-span-2">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label for="delivery_note_number" class="block text-sm font-medium text-gray-700 mb-2">
                                                No. Surat Jalan
                                            </label>
                                            <input type="text" id="delivery_note_number" name="delivery_note_number"
                                                placeholder="Nomor surat jalan..."
                                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                                        </div>

                                        <div>
                                            <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-2">
                                                No. Invoice
                                            </label>
                                            <input type="text" id="invoice_number" name="invoice_number"
                                                placeholder="Nomor invoice..."
                                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                                        </div>

                                        <div>
                                            <label for="external_reference" class="block text-sm font-medium text-gray-700 mb-2">
                                                Referensi Lain
                                            </label>
                                            <input type="text" id="external_reference" name="external_reference"
                                                placeholder="Referensi eksternal..."
                                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Notes -->
                        <div class="mt-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan
                            </label>
                            <textarea id="notes" name="notes" rows="3" placeholder="Catatan penerimaan barang..."
                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">{{ old('notes') }}</textarea>
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
                            <div x-show="receiptType === 'po_based' && selectedPO" class="text-sm text-gray-600">
                                <span x-text="selectedPO ? selectedPO.po_number : ''"></span>
                            </div>
                            <div x-show="receiptType === 'direct'" class="flex items-center space-x-2">
                                <button type="button" @click="addDirectItem()"
                                    class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                    <i class="fas fa-plus mr-1"></i>
                                    Tambah Item
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div x-show="loadingPO" class="p-8 text-center">
                        <div class="inline-flex items-center">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-600 mr-3"></div>
                            <span class="text-gray-600">Memuat data...</span>
                        </div>
                    </div>

                    <!-- No Selection State -->
                    <div x-show="!hasSelectedSource() && !loadingPO" class="p-8 text-center">
                        <i class="fas fa-file-invoice text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2" x-text="receiptType === 'po_based' ? 'Pilih Purchase Order' : 'Pilih Supplier'"></h3>
                        <p class="text-gray-500" x-text="receiptType === 'po_based' ? 'Pilih PO terlebih dahulu untuk menampilkan items yang dapat diterima' : 'Pilih supplier dan mulai menambahkan items'"></p>
                    </div>

                    <!-- Items Table -->
                    <div x-show="availableItems.length > 0 && !loadingPO" class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th x-show="receiptType === 'po_based'" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sisa Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Terima</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serial Numbers</th>
                                    <th x-show="receiptType === 'direct'" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <template x-for="(item, index) in availableItems" :key="getItemKey(item, index)">
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

                                        <!-- Remaining Quantity (PO-based only) -->
                                        <td x-show="receiptType === 'po_based'" class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900" x-text="formatNumber(item.remaining_quantity)"></div>
                                            <div class="text-xs text-gray-500" x-text="item.unit"></div>
                                            <div class="text-xs text-gray-400">
                                                Ordered: <span x-text="formatNumber(item.quantity_ordered)"></span>
                                            </div>
                                        </td>

                                        <!-- Quantity Received -->
                                        <td class="px-6 py-4">
                                            <div class="space-y-2">
                                                <input type="number"
                                                    :name="`items[${index}][quantity_received]`"
                                                    x-model="item.quantity_received"
                                                    @input="updateItemCalculations(index)"
                                                    :max="receiptType === 'po_based' ? item.remaining_quantity : null"
                                                    step="1"
                                                    min="0"
                                                    placeholder="0"
                                                    class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">

                                                <!-- Hidden inputs -->
                                                <input type="hidden" :name="`items[${index}][item_id]`" x-model="item.item_id">
                                                <input type="hidden" :name="`items[${index}][unit_price]`" x-model="item.unit_price">
                                                <input type="hidden" x-show="receiptType === 'po_based'" :name="`items[${index}][po_detail_id]`" x-model="item.po_detail_id">

                                                <!-- Validation -->
                                                <div x-show="receiptType === 'po_based' && item.quantity_received > item.remaining_quantity"
                                                     class="text-xs text-red-600">
                                                    Melebihi sisa order
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Unit Price -->
                                        <td class="px-6 py-4">
                                            <div x-show="receiptType === 'po_based'">
                                                <div class="text-sm font-medium text-gray-900" x-text="formatCurrency(item.unit_price)"></div>
                                                <div class="text-xs text-gray-500" x-text="`per ${item.unit}`"></div>
                                            </div>
                                            <div x-show="receiptType === 'direct'">
                                                <input type="number"
                                                    :name="`items[${index}][unit_price]`"
                                                    x-model="item.unit_price"
                                                    @input="updateItemCalculations(index)"
                                                    step="0.01"
                                                    min="0"
                                                    placeholder="0.00"
                                                    class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                                                <div class="text-xs text-gray-500 mt-1" x-text="`per ${item.unit}`"></div>
                                            </div>
                                        </td>

                                        <!-- Total -->
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900" x-text="formatCurrency(item.total)"></div>
                                        </td>

                                        <!-- Serial Numbers -->
                                        <td class="px-6 py-4">
                                            <div class="space-y-2">
                                                <button type="button"
                                                    @click="openSerialNumberModal(index)"
                                                    :disabled="!item.quantity_received || item.quantity_received <= 0"
                                                    :class="item.quantity_received > 0 ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                                    class="px-3 py-2 rounded-lg text-sm transition-all">
                                                    <i class="fas fa-barcode mr-1"></i>
                                                    <span x-show="item.quantity_received > 0" x-text="`Input SN (${item.quantity_received})`"></span>
                                                    <span x-show="!item.quantity_received || item.quantity_received <= 0">Input SN</span>
                                                </button>

                                                <!-- Serial number status -->
                                                <div x-show="item.quantity_received > 0" class="text-xs">
                                                    <div x-show="getSerialNumberInputCount(index) === parseInt(item.quantity_received)" class="text-green-600">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        <span x-text="`${getSerialNumberInputCount(index)} SN siap`"></span>
                                                    </div>
                                                    <div x-show="getSerialNumberInputCount(index) !== parseInt(item.quantity_received)" class="text-orange-600">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                        <span x-text="`${getSerialNumberInputCount(index)}/${item.quantity_received} SN`"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Actions (Direct Receipt only) -->
                                        <td x-show="receiptType === 'direct'" class="px-6 py-4">
                                            <button type="button" @click="removeDirectItem(index)"
                                                class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Additional Item Info -->
                    <div x-show="availableItems.length > 0 && !loadingPO" class="px-6 py-4 border-t border-gray-200">
                        <div class="space-y-4">
                            <h4 class="text-sm font-medium text-gray-900">Informasi Tambahan (Opsional)</h4>

                            <template x-for="(item, index) in availableItems" :key="'info-' + getItemKey(item, index)">
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
                                <div class="text-lg font-bold text-blue-600" x-text="totalItemDetails"></div>
                                <div class="text-xs text-gray-600">Item Details</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-bold text-green-600" x-text="totalSerialNumbers"></div>
                                <div class="text-xs text-gray-600">Serial Numbers</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-bold text-purple-600" x-text="formatCurrency(totalValue)"></div>
                                <div class="text-xs text-gray-600">Total Nilai</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Summary & Actions (1/4 width) -->
            <div class="space-y-6">
                <!-- Source Info Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-info-circle mr-2" :class="receiptType === 'po_based' ? 'text-blue-600' : 'text-purple-600'"></i>
                            <span x-text="receiptType === 'po_based' ? 'Purchase Order' : 'Supplier Info'"></span>
                        </h3>
                    </div>
                    <div class="p-6">
                        <!-- PO Info -->
                        <div x-show="receiptType === 'po_based' && selectedPO" class="space-y-4">
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

                        <!-- Supplier Info (Direct Receipt) -->
                        <div x-show="receiptType === 'direct' && selectedSupplier" class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Supplier</label>
                                <p class="text-sm text-gray-900 mt-1" x-text="selectedSupplier?.supplier_name"></p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Jenis Penerimaan</label>
                                <p class="text-sm text-gray-900 mt-1">Penerimaan Langsung</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Status</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Direct Receipt
                                </span>
                            </div>
                        </div>

                        <!-- No Selection -->
                        <div x-show="!hasSelectedSource()" class="text-center py-4">
                            <i class="text-3xl text-gray-300 mb-2" :class="receiptType === 'po_based' ? 'fas fa-file-invoice' : 'fas fa-building'"></i>
                            <p class="text-gray-500 text-sm" x-text="receiptType === 'po_based' ? 'Pilih PO untuk melihat informasi' : 'Pilih supplier untuk melanjutkan'"></p>
                        </div>
                    </div>
                </div>

                <!-- Receive Summary -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-calculator mr-2 text-green-600"></i>
                            Ringkasan
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
                            <span class="text-sm text-gray-600">Item Details</span>
                            <span class="text-sm font-medium text-blue-600" x-text="totalItemDetails"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Serial Numbers</span>
                            <span class="text-sm font-medium text-green-600" x-text="totalSerialNumbers"></span>
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
                                :disabled="!canSubmit || submitting"
                                class="w-full px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-save" x-show="!submitting"></i>
                            <i class="fas fa-spinner fa-spin" x-show="submitting"></i>
                            <span x-show="!submitting">Simpan Penerimaan</span>
                            <span x-show="submitting">Menyimpan...</span>
                        </button>

                        <a href="{{ route('goods-received.index') }}"
                           class="w-full px-4 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </a>

                        <button type="button"
                                @click="generateAllSerialNumbers()"
                                :disabled="!hasSelectedSource() || totalReceived === 0"
                                class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-magic"></i>
                            <span>Auto Generate SN</span>
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
                            <span x-text="receiptType === 'po_based' ? 'Barang diterima akan dicocokkan dengan PO yang dipilih' : 'Semua barang yang diterima akan masuk sebagai stock tersedia'"></span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Serial number bisa diinput manual atau auto-generate</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Setiap unit akan dibuatkan ItemDetail dengan QR code</span>
                        </li>
                        <li x-show="receiptType === 'direct'" class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Penerimaan langsung tidak terkait dengan PO</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </form>

    <!-- Add Item Modal (For Direct Receipt) -->
    <div x-show="showAddItemModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="fixed inset-0 z-50 overflow-y-auto">

        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75" @click="closeAddItemModal()"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Tambah Item</h3>
                        <button @click="closeAddItemModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Item Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Item <span class="text-red-500">*</span>
                            </label>
                            <select x-model="newItemId" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Item</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->item_id }}"
                                            data-item-code="{{ $item->item_code }}"
                                            data-item-name="{{ $item->item_name }}"
                                            data-category-name="{{ $item->category->category_name ?? 'N/A' }}"
                                            data-unit="{{ $item->unit }}">
                                        {{ $item->item_code }} - {{ $item->item_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" x-model="newItemQuantity" min="1" step="1" placeholder="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Unit Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Harga Satuan <span class="text-red-500">*</span>
                            </label>
                            <input type="number" x-model="newItemPrice" min="0" step="0.01" placeholder="0.00"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <button type="button"
                            @click="confirmAddItem()"
                            :disabled="!newItemId || !newItemQuantity || newItemQuantity <= 0 || !newItemPrice || newItemPrice < 0"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Item
                        </button>

                        <button type="button"
                            @click="closeAddItemModal()"
                            class="flex-1 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Serial Number Modal -->
    <div x-show="showSerialModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="fixed inset-0 z-50 overflow-y-auto">

        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75" @click="closeSerialNumberModal()"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900" x-text="serialModalTitle"></h3>
                        <button @click="closeSerialNumberModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div x-show="currentItemForSerial">
                        <!-- Item Info -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900" x-text="currentItemForSerial?.item_name"></h4>
                                    <p class="text-sm text-gray-500" x-text="currentItemForSerial?.item_code"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-blue-600" x-text="currentItemForSerial?.quantity_received + ' units'"></p>
                                    <p class="text-sm text-gray-500">Quantity diterima</p>
                                </div>
                            </div>
                        </div>

                        <!-- Serial Number Input Grid -->
                        <div class="max-h-96 overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <template x-for="(serial, serialIndex) in currentSerialNumbers" :key="serialIndex">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700" x-text="`Unit ${serialIndex + 1}`"></label>
                                        <div class="relative">
                                            <input type="text"
                                                x-model="serial.value"
                                                @input="validateSerialNumber(serialIndex)"
                                                :placeholder="`SN untuk unit ${serialIndex + 1}`"
                                                :class="{
                                                    'border-red-500': serial.error,
                                                    'border-green-500': serial.valid && serial.value,
                                                    'border-gray-300': !serial.error && !serial.valid
                                                }"
                                                class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                                            <!-- Validation Icons -->
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                                <i x-show="serial.validating" class="fas fa-spinner fa-spin text-gray-400"></i>
                                                <i x-show="!serial.validating && serial.valid && serial.value" class="fas fa-check text-green-500"></i>
                                                <i x-show="!serial.validating && serial.error" class="fas fa-times text-red-500"></i>
                                            </div>
                                        </div>

                                        <!-- Error Message -->
                                        <div x-show="serial.error" class="text-xs text-red-600" x-text="serial.errorMessage"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Modal Actions -->
                        <div class="mt-6 flex flex-col sm:flex-row gap-3">
                            <button type="button"
                                @click="generateSerialNumbersForItem()"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-magic mr-2"></i>
                                Auto Generate Semua
                            </button>

                            <button type="button"
                                @click="clearSerialNumbersForItem()"
                                class="flex-1 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                <i class="fas fa-eraser mr-2"></i>
                                Clear Semua
                            </button>

                            <button type="button"
                                @click="saveSerialNumbers()"
                                :disabled="!canSaveSerialNumbers"
                                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-save mr-2"></i>
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div x-show="notifications.length > 0" class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="(notification, index) in notifications" :key="index">
            <div x-show="notification.show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-full"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform translate-x-full"
                 :class="{
                     'bg-green-100 border-green-400 text-green-700': notification.type === 'success',
                     'bg-red-100 border-red-400 text-red-700': notification.type === 'error',
                     'bg-blue-100 border-blue-400 text-blue-700': notification.type === 'info',
                     'bg-yellow-100 border-yellow-400 text-yellow-700': notification.type === 'warning'
                 }"
                 class="border px-4 py-3 rounded-xl shadow-lg max-w-md">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i :class="{
                            'fas fa-check-circle': notification.type === 'success',
                            'fas fa-exclamation-circle': notification.type === 'error',
                            'fas fa-info-circle': notification.type === 'info',
                            'fas fa-exclamation-triangle': notification.type === 'warning'
                        }" class="mr-2"></i>
                        <span x-text="notification.message"></span>
                    </div>
                    <button @click="removeNotification(index)" class="ml-4 hover:opacity-75">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
function goodsReceivedCreate() {
    return {
        // Basic properties
        receiptType: '{{ old('receipt_type', $receiptType ?? 'po_based') }}',
        receiveNumber: '{{ $receiveNumber }}',

        // PO-based properties
        selectedPOId: '{{ old('po_id', request('po_id')) }}',
        selectedPO: null,

        // Direct receipt properties
        selectedSupplierId: '{{ old('supplier_id') }}',
        selectedSupplier: null,

        // Common properties
        availableItems: [],
        loadingPO: false,
        submitting: false,

        // Direct receipt - Add item modal
        showAddItemModal: false,
        newItemId: '',
        newItemQuantity: 1,
        newItemPrice: 0,

        // Serial number management
        showSerialModal: false,
        currentItemIndex: null,
        currentItemForSerial: null,
        currentSerialNumbers: [],
        serialModalTitle: '',

        // Notifications
        notifications: [],

        // Computed properties
        get totalReceived() {
            return this.availableItems.reduce((total, item) => total + (parseInt(item.quantity_received) || 0), 0);
        },

        get totalItemDetails() {
            return this.totalReceived; // 1 item detail per quantity received
        },

        get totalSerialNumbers() {
            return this.availableItems.reduce((total, item) => {
                return total + this.getSerialNumberInputCount(this.availableItems.indexOf(item));
            }, 0);
        },

        get totalValue() {
            return this.availableItems.reduce((total, item) => total + (item.total || 0), 0);
        },

        get itemsWithReceiving() {
            return this.availableItems.filter(item => (parseInt(item.quantity_received) || 0) > 0).length;
        },

        get canSubmit() {
            // Must have selected source
            if (!this.hasSelectedSource()) return false;

            // Must have items with receiving
            if (this.itemsWithReceiving === 0) return false;

            // Check if all items with quantity_received have complete serial numbers
            return this.availableItems.every(item => {
                const qtyReceived = parseInt(item.quantity_received) || 0;
                if (qtyReceived === 0) return true;

                const serialCount = this.getSerialNumberInputCount(this.availableItems.indexOf(item));
                return serialCount === qtyReceived;
            });
        },

        get canSaveSerialNumbers() {
            if (!this.currentSerialNumbers) return false;

            const requiredCount = parseInt(this.currentItemForSerial?.quantity_received) || 0;
            const validSerials = this.currentSerialNumbers.filter(serial =>
                serial.value && serial.value.trim() && !serial.error
            ).length;

            return validSerials === requiredCount;
        },

        init() {
            console.log('Initializing goods received create form');

            // Watch receipt type changes
            this.$watch('receiptType', () => {
                this.onReceiptTypeChange();
            });

            // Load initial data based on receipt type
            if (this.receiptType === 'po_based' && this.selectedPOId) {
                this.onPOChange();
            } else if (this.receiptType === 'direct' && this.selectedSupplierId) {
                this.onSupplierChange();
            }

            // Update receive number when receipt type changes
            this.updateReceiveNumber();

            // Initialize serial numbers storage for each item
            this.initializeSerialNumbers();
        },

        // NEW: Handle receipt type change
        onReceiptTypeChange() {
            // Clear existing data when switching types
            this.availableItems = [];
            this.selectedPO = null;
            this.selectedSupplier = null;
            this.selectedPOId = '';
            this.selectedSupplierId = '';

            // Update receive number based on new type
            this.updateReceiveNumber();

            console.log('Receipt type changed to:', this.receiptType);
        },

        // NEW: Update receive number based on receipt type
        async updateReceiveNumber() {
            try {
                const response = await fetch('/api/goods-received/generate-receive-number', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        receipt_type: this.receiptType
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.receiveNumber = data.data.receive_number;
                    console.log('Updated receive number:', this.receiveNumber);
                }
            } catch (error) {
                console.error('Error updating receive number:', error);
            }
        },

        // NEW: Check if has selected source (PO or Supplier)
        hasSelectedSource() {
            if (this.receiptType === 'po_based') {
                return !!this.selectedPOId;
            } else if (this.receiptType === 'direct') {
                return !!this.selectedSupplierId;
            }
            return false;
        },

        // NEW: Get unique item key for template loops
        getItemKey(item, index) {
            if (this.receiptType === 'po_based') {
                return `po-${item.po_detail_id || item.item_id}-${index}`;
            } else {
                return `direct-${item.item_id}-${index}`;
            }
        },

        initializeSerialNumbers() {
            // Initialize empty serial numbers array for items
            this.availableItems.forEach((item, index) => {
                if (!item.serialNumbers) {
                    item.serialNumbers = [];
                }
            });
        },

        // EXISTING: PO Change handler (for PO-based receipts)
        async onPOChange() {
            if (!this.selectedPOId || this.receiptType !== 'po_based') {
                this.selectedPO = null;
                this.availableItems = [];
                return;
            }

            console.log('Loading PO details for ID:', this.selectedPOId);
            this.loadingPO = true;

            try {
                const url = `/api/goods-received/po-details/${this.selectedPOId}`;
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.po && data.details) {
                    this.selectedPO = {
                        po_id: data.po.po_id,
                        po_number: data.po.po_number,
                        supplier_name: data.po.supplier_name,
                        po_date: data.po.po_date
                    };

                    this.availableItems = data.details.map(item => ({
                        // Data dari controller response
                        po_detail_id: item.po_detail_id,
                        item_id: item.item_id,
                        item_code: item.item_code,
                        item_name: item.item_name,
                        category_name: item.category_name,
                        unit: item.unit,
                        quantity_ordered: item.quantity_ordered,
                        quantity_received_existing: item.quantity_received,
                        remaining_quantity: item.remaining_quantity,
                        unit_price: item.unit_price,
                        can_receive: item.can_receive,

                        // Data untuk form input
                        quantity_received: 0,
                        total: 0,
                        serialNumbers: [] // Array untuk menyimpan serial numbers
                    }));

                    this.showNotification(`Berhasil memuat ${data.details.length} items dari PO ${data.po.po_number}`, 'success');
                } else {
                    throw new Error(data.error || 'Data PO tidak lengkap');
                }
            } catch (error) {
                console.error('Error loading PO details:', error);
                this.showNotification('Gagal memuat detail PO: ' + error.message, 'error');
                this.selectedPO = null;
                this.availableItems = [];
            } finally {
                this.loadingPO = false;
            }
        },

        // NEW: Supplier Change handler (for direct receipts)
        async onSupplierChange() {
            if (!this.selectedSupplierId || this.receiptType !== 'direct') {
                this.selectedSupplier = null;
                return;
            }

            console.log('Loading supplier details for ID:', this.selectedSupplierId);

            try {
                // Find supplier from available suppliers
                const supplierSelect = document.getElementById('supplier_id');
                const selectedOption = supplierSelect.options[supplierSelect.selectedIndex];

                if (selectedOption && selectedOption.value) {
                    this.selectedSupplier = {
                        supplier_id: selectedOption.value,
                        supplier_name: selectedOption.text
                    };

                    this.showNotification(`Supplier ${this.selectedSupplier.supplier_name} dipilih`, 'success');
                }
            } catch (error) {
                console.error('Error setting supplier:', error);
                this.showNotification('Gagal memilih supplier: ' + error.message, 'error');
                this.selectedSupplier = null;
            }
        },

        // NEW: Add Direct Item
        addDirectItem() {
            this.showAddItemModal = true;
            this.newItemId = '';
            this.newItemQuantity = 1;
            this.newItemPrice = 0;
        },

        // NEW: Close Add Item Modal
        closeAddItemModal() {
            this.showAddItemModal = false;
            this.newItemId = '';
            this.newItemQuantity = 1;
            this.newItemPrice = 0;
        },

        // NEW: Confirm Add Item
        confirmAddItem() {
            if (!this.newItemId || !this.newItemQuantity || this.newItemQuantity <= 0 || !this.newItemPrice || this.newItemPrice < 0) {
                this.showNotification('Semua field harus diisi dengan benar', 'error');
                return;
            }

            // Check if item already exists in the list
            const existingItemIndex = this.availableItems.findIndex(item => item.item_id === this.newItemId);

            if (existingItemIndex !== -1) {
                this.showNotification('Item sudah ada dalam daftar', 'error');
                return;
            }

            // Get item data from select option
            const itemSelect = document.getElementById('new_item_select') || document.querySelector(`option[value="${this.newItemId}"]`);
            const selectedOption = document.querySelector(`option[value="${this.newItemId}"]`);

            if (!selectedOption) {
                this.showNotification('Item tidak ditemukan', 'error');
                return;
            }

            // Create new item object
            const newItem = {
                item_id: this.newItemId,
                item_code: selectedOption.dataset.itemCode || 'N/A',
                item_name: selectedOption.dataset.itemName || 'Unknown Item',
                category_name: selectedOption.dataset.categoryName || 'N/A',
                unit: selectedOption.dataset.unit || 'pcs',

                // Direct receipt specific
                quantity_received: parseInt(this.newItemQuantity),
                unit_price: parseFloat(this.newItemPrice),
                total: parseInt(this.newItemQuantity) * parseFloat(this.newItemPrice),
                serialNumbers: []
            };

            // Add to available items
            this.availableItems.push(newItem);

            this.showNotification(`Item ${newItem.item_name} berhasil ditambahkan`, 'success');
            this.closeAddItemModal();
        },

        // NEW: Remove Direct Item
        removeDirectItem(index) {
            if (index >= 0 && index < this.availableItems.length) {
                const removedItem = this.availableItems[index];
                this.availableItems.splice(index, 1);
                this.showNotification(`Item ${removedItem.item_name} dihapus`, 'info');
            }
        },

        updateItemCalculations(index) {
            const item = this.availableItems[index];
            const qtyReceived = parseInt(item.quantity_received) || 0;
            const unitPrice = parseFloat(item.unit_price) || 0;

            // Calculate total value
            item.total = qtyReceived * unitPrice;

            // Initialize serial numbers array based on quantity
            this.updateSerialNumbersArray(index, qtyReceived);
        },

        updateSerialNumbersArray(index, quantity) {
            const item = this.availableItems[index];

            // Adjust serial numbers array to match quantity
            if (quantity > item.serialNumbers.length) {
                // Add more serial number slots
                for (let i = item.serialNumbers.length; i < quantity; i++) {
                    item.serialNumbers.push('');
                }
            } else if (quantity < item.serialNumbers.length) {
                // Remove excess serial number slots
                item.serialNumbers = item.serialNumbers.slice(0, quantity);
            }
        },

        getSerialNumberInputCount(index) {
            if (index < 0 || index >= this.availableItems.length) return 0;

            const item = this.availableItems[index];
            if (!item.serialNumbers) return 0;

            return item.serialNumbers.filter(sn => sn && sn.trim()).length;
        },

        openSerialNumberModal(index) {
            const item = this.availableItems[index];
            const quantity = parseInt(item.quantity_received) || 0;

            if (quantity <= 0) return;

            this.currentItemIndex = index;
            this.currentItemForSerial = item;
            this.serialModalTitle = `Input Serial Numbers - ${item.item_name} (${quantity} units)`;

            // Initialize current serial numbers for modal
            this.currentSerialNumbers = [];
            for (let i = 0; i < quantity; i++) {
                this.currentSerialNumbers.push({
                    value: item.serialNumbers[i] || '',
                    valid: false,
                    error: false,
                    errorMessage: '',
                    validating: false
                });
            }

            this.showSerialModal = true;
        },

        closeSerialNumberModal() {
            this.showSerialModal = false;
            this.currentItemIndex = null;
            this.currentItemForSerial = null;
            this.currentSerialNumbers = [];
        },

        async validateSerialNumber(serialIndex) {
            const serialObj = this.currentSerialNumbers[serialIndex];
            const serialNumber = serialObj.value.trim();

            if (!serialNumber) {
                serialObj.valid = false;
                serialObj.error = false;
                serialObj.errorMessage = '';
                return;
            }

            serialObj.validating = true;
            serialObj.error = false;

            try {
                const response = await fetch('/api/goods-received/validate-serial-number', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        serial_number: serialNumber
                    })
                });

                const data = await response.json();

                if (data.valid) {
                    // Check for duplicates in current list
                    const duplicateIndex = this.currentSerialNumbers.findIndex((s, i) =>
                        i !== serialIndex && s.value.trim() === serialNumber
                    );

                    if (duplicateIndex !== -1) {
                        serialObj.valid = false;
                        serialObj.error = true;
                        serialObj.errorMessage = 'Duplikat dalam list ini';
                    } else {
                        serialObj.valid = true;
                        serialObj.error = false;
                        serialObj.errorMessage = '';
                    }
                } else {
                    serialObj.valid = false;
                    serialObj.error = true;
                    serialObj.errorMessage = data.message || 'Serial number sudah digunakan';
                }
            } catch (error) {
                serialObj.valid = false;
                serialObj.error = true;
                serialObj.errorMessage = 'Error validating serial number';
            } finally {
                serialObj.validating = false;
            }
        },

        async generateSerialNumbersForItem() {
            if (!this.currentItemForSerial) return;

            try {
                const response = await fetch('/api/goods-received/serial-number-template', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        item_id: this.currentItemForSerial.item_id,
                        quantity: this.currentItemForSerial.quantity_received,
                        format: 'f1' // Use F1 format by default
                    })
                });

                const data = await response.json();

                if (data.success) {
                    data.data.serial_numbers.forEach((sn, index) => {
                        if (this.currentSerialNumbers[index]) {
                            this.currentSerialNumbers[index].value = sn;
                            this.currentSerialNumbers[index].valid = true;
                            this.currentSerialNumbers[index].error = false;
                            this.currentSerialNumbers[index].errorMessage = '';
                        }
                    });

                    this.showNotification('Serial numbers berhasil di-generate', 'success');
                } else {
                    this.showNotification('Gagal generate serial numbers: ' + data.message, 'error');
                }
            } catch (error) {
                this.showNotification('Error generating serial numbers: ' + error.message, 'error');
            }
        },

        clearSerialNumbersForItem() {
            this.currentSerialNumbers.forEach(serial => {
                serial.value = '';
                serial.valid = false;
                serial.error = false;
                serial.errorMessage = '';
            });
        },

        saveSerialNumbers() {
            if (!this.canSaveSerialNumbers) return;

            // Save current serial numbers to item
            const serialValues = this.currentSerialNumbers.map(s => s.value.trim());
            this.availableItems[this.currentItemIndex].serialNumbers = serialValues;

            this.showNotification(`${serialValues.length} serial numbers disimpan untuk ${this.currentItemForSerial.item_name}`, 'success');
            this.closeSerialNumberModal();
        },

        async generateAllSerialNumbers() {
            for (let index = 0; index < this.availableItems.length; index++) {
                const item = this.availableItems[index];
                const quantity = parseInt(item.quantity_received) || 0;

                if (quantity > 0) {
                    try {
                        const response = await fetch('/api/goods-received/serial-number-template', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                item_id: item.item_id,
                                quantity: quantity,
                                format: 'f1'
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            item.serialNumbers = data.data.serial_numbers;
                        }
                    } catch (error) {
                        console.error('Error generating serial numbers for item:', error);
                    }
                }
            }

            this.showNotification('Semua serial numbers berhasil di-generate', 'success');
        },

        async submitForm() {
            if (!this.canSubmit || this.submitting) return;

            this.submitting = true;

            try {
                // Prepare form data
                const formData = new FormData();

                // Basic form fields
                formData.append('receive_number', this.receiveNumber);
                formData.append('receipt_type', this.receiptType);
                formData.append('receive_date', document.querySelector('input[name="receive_date"]').value);
                formData.append('notes', document.querySelector('textarea[name="notes"]').value);

                // Receipt type specific fields
                if (this.receiptType === 'po_based') {
                    formData.append('po_id', this.selectedPOId);
                } else if (this.receiptType === 'direct') {
                    formData.append('supplier_id', this.selectedSupplierId);

                    // Optional external references
                    const deliveryNote = document.querySelector('input[name="delivery_note_number"]')?.value;
                    const invoiceNumber = document.querySelector('input[name="invoice_number"]')?.value;
                    const externalRef = document.querySelector('input[name="external_reference"]')?.value;

                    if (deliveryNote) formData.append('delivery_note_number', deliveryNote);
                    if (invoiceNumber) formData.append('invoice_number', invoiceNumber);
                    if (externalRef) formData.append('external_reference', externalRef);
                }

                // Items data
                this.availableItems.forEach((item, index) => {
                    const qtyReceived = parseInt(item.quantity_received) || 0;
                    if (qtyReceived > 0) {
                        formData.append(`items[${index}][item_id]`, item.item_id);
                        formData.append(`items[${index}][quantity_received]`, qtyReceived);
                        formData.append(`items[${index}][unit_price]`, item.unit_price);

                        // PO-specific fields
                        if (this.receiptType === 'po_based' && item.po_detail_id) {
                            formData.append(`items[${index}][po_detail_id]`, item.po_detail_id);
                        }

                        // Optional fields
                        const batchNumber = document.querySelector(`input[name="items[${index}][batch_number]"]`)?.value;
                        const expiryDate = document.querySelector(`input[name="items[${index}][expiry_date]"]`)?.value;
                        const itemNotes = document.querySelector(`input[name="items[${index}][notes]"]`)?.value;

                        if (batchNumber) formData.append(`items[${index}][batch_number]`, batchNumber);
                        if (expiryDate) formData.append(`items[${index}][expiry_date]`, expiryDate);
                        if (itemNotes) formData.append(`items[${index}][notes]`, itemNotes);

                        // Serial numbers
                        item.serialNumbers.forEach((serialNumber, serialIndex) => {
                            if (serialNumber && serialNumber.trim()) {
                                formData.append(`items[${index}][serial_numbers][${serialIndex}]`, serialNumber.trim());
                            }
                        });
                    }
                });

                const response = await fetch('{{ route("goods-received.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showNotification(data.message, 'success');

                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = data.data.redirect_url;
                    }, 2000);
                } else {
                    if (data.errors) {
                        // Show validation errors
                        Object.keys(data.errors).forEach(field => {
                            data.errors[field].forEach(error => {
                                this.showNotification(error, 'error');
                            });
                        });
                    } else {
                        this.showNotification(data.message || 'Terjadi kesalahan', 'error');
                    }
                }
            } catch (error) {
                console.error('Submit error:', error);
                this.showNotification('Error: ' + error.message, 'error');
            } finally {
                this.submitting = false;
            }
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

        showNotification(message, type = 'info') {
            const notification = {
                message,
                type,
                show: true
            };

            this.notifications.push(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                const index = this.notifications.indexOf(notification);
                if (index > -1) {
                    this.removeNotification(index);
                }
            }, 5000);
        },

        removeNotification(index) {
            if (index >= 0 && index < this.notifications.length) {
                this.notifications[index].show = false;
                setTimeout(() => {
                    this.notifications.splice(index, 1);
                }, 300);
            }
        }
    }
}
</script>
@endpush
