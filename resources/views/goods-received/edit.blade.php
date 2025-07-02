@extends('layouts.app')

@section('title', 'Edit Penerimaan Barang - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="space-y-6" x-data="goodsReceivedEdit()">
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
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="{{ route('goods-received.show', $goodsReceived) }}"
                        class="text-sm font-medium text-gray-700 hover:text-green-600">
                        {!! $goodsReceived->receive_number !!}
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
            <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl flex items-center justify-center">
                <i class="fas fa-edit text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Penerimaan Barang</h1>
                <p class="text-gray-600 mt-1">Edit GR {!! $goodsReceived->receive_number !!}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('goods-received.show', $goodsReceived) }}"
               class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Alert if cannot edit -->
    @if($goodsReceived->status === 'complete')
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        Penerimaan ini tidak dapat diedit karena statusnya sudah complete. Status saat ini:
                        <span class="font-semibold">{!! $goodsReceived->getStatusInfo()['text'] !!}</span>
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- GR Edit Form -->
    <form method="POST" action="{{ route('goods-received.update', $goodsReceived) }}" x-ref="grForm" @submit="validateForm">
        @csrf
        @method('PUT')

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
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- GR Number (Read Only) -->
                            <div>
                                <label for="receive_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor GR
                                </label>
                                <div class="p-3 bg-gray-50 rounded-lg border">
                                    <span class="text-sm font-mono text-gray-900">{!! $goodsReceived->receive_number !!}</span>
                                </div>
                            </div>

                            <!-- PO Number (Read Only) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor PO
                                </label>
                                <div class="p-3 bg-gray-50 rounded-lg border">
                                    <span class="text-sm font-mono text-gray-900">{!! $goodsReceived->purchaseOrder->po_number !!}</span>
                                </div>
                            </div>

                            <!-- Supplier (Read Only) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Supplier
                                </label>
                                <div class="p-3 bg-gray-50 rounded-lg border">
                                    <span class="text-sm text-gray-900">{!! $goodsReceived->supplier->supplier_name !!}</span>
                                </div>
                            </div>

                            <!-- Receive Date -->
                            <div>
                                <label for="receive_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Penerimaan <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="receive_date" name="receive_date"
                                    value="{{ old('receive_date', $goodsReceived->receive_date->format('Y-m-d')) }}"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('receive_date') border-red-500 @enderror">
                                @error('receive_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status (Read Only) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <div class="p-3 bg-gray-50 rounded-lg border">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {!! $goodsReceived->getStatusInfo()['class'] !!}">
                                        {!! $goodsReceived->getStatusInfo()['text'] !!}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mt-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan
                            </label>
                            <textarea id="notes" name="notes" rows="3" placeholder="Catatan penerimaan barang..."
                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('notes') border-red-500 @enderror">{{ old('notes', $goodsReceived->notes) }}</textarea>
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
                            <div class="text-sm text-gray-600">
                                <span>PO: {!! $goodsReceived->purchaseOrder->po_number !!}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Terima</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alokasi Stok</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siap Pakai</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Info Tambahan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <template x-for="(item, index) in grItems" :key="item.gr_detail_id">
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
                                            <div class="space-y-2">
                                                <input type="number"
                                                    :name="`items[${item.gr_detail_id}][quantity_received]`"
                                                    x-model="item.quantity_received"
                                                    @input="updateItemCalculations(index)"
                                                    step="1"
                                                    min="0"
                                                    class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                                                <div class="text-xs text-gray-500" x-text="item.unit"></div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="space-y-2">
                                                <input type="number"
                                                    :name="`items[${item.gr_detail_id}][quantity_to_stock]`"
                                                    x-model="item.quantity_to_stock"
                                                    @input="updateItemCalculations(index)"
                                                    :max="item.quantity_received || 0"
                                                    step="1"
                                                    min="0"
                                                    class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                                                <div class="text-xs text-blue-600">
                                                    <span x-text="item.quantity_to_stock ? ((item.quantity_to_stock / (item.quantity_received || 1)) * 100).toFixed(1) + '%' : '0%'"></span>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="space-y-2">
                                                <input type="number"
                                                    :name="`items[${item.gr_detail_id}][quantity_to_ready]`"
                                                    x-model="item.quantity_to_ready"
                                                    @input="updateItemCalculations(index)"
                                                    :max="item.quantity_received || 0"
                                                    step="1"
                                                    min="0"
                                                    class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">

                                                <div class="text-xs text-green-600">
                                                    <span x-text="item.quantity_to_ready ? ((item.quantity_to_ready / (item.quantity_received || 1)) * 100).toFixed(1) + '%' : '0%'"></span>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            <input type="number"
                                                :name="`items[${item.gr_detail_id}][unit_price]`"
                                                x-model="item.unit_price"
                                                @input="updateItemCalculations(index)"
                                                step="0.01"
                                                min="0"
                                                class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900" x-text="formatCurrency(item.total)"></div>

                                            <!-- Validation indicator -->
                                            <div x-show="!isItemValid(item)" class="text-xs text-red-600 mt-1">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Alokasi tidak sesuai
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="space-y-2">
                                                <input type="text"
                                                    :name="`items[${item.gr_detail_id}][batch_number]`"
                                                    x-model="item.batch_number"
                                                    placeholder="Batch number..."
                                                    class="w-24 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-green-500">

                                                <input type="date"
                                                    :name="`items[${item.gr_detail_id}][expiry_date]`"
                                                    x-model="item.expiry_date"
                                                    class="w-32 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-green-500">

                                                <input type="text"
                                                    :name="`items[${item.gr_detail_id}][notes]`"
                                                    x-model="item.notes"
                                                    placeholder="Catatan..."
                                                    class="w-32 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-green-500">
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Items Summary -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
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
                <!-- GR Info Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-file-invoice mr-2 text-blue-600"></i>
                            Informasi GR
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Nomor GR</span>
                            <span class="text-sm font-mono font-medium">{!! $goodsReceived->receive_number !!}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Nomor PO</span>
                            <span class="text-sm font-mono font-medium">{!! $goodsReceived->purchaseOrder->po_number !!}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Supplier</span>
                            <span class="text-sm font-medium">{!! $goodsReceived->supplier->supplier_name !!}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Status</span>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {!! $goodsReceived->getStatusInfo()['class'] !!}">
                                {!! $goodsReceived->getStatusInfo()['text'] !!}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Diterima Oleh</span>
                            <span class="text-sm font-medium">{!! $goodsReceived->receivedBy->username !!}</span>
                        </div>
                    </div>
                </div>

                <!-- Edit Summary -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-calculator mr-2 text-green-600"></i>
                            Ringkasan Edit
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total Items</span>
                            <span class="text-sm font-medium" x-text="grItems.length"></span>
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
                                class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-save"></i>
                            <span>Update Penerimaan</span>
                        </button>

                        <a href="{{ route('goods-received.show', $goodsReceived) }}"
                           class="w-full px-4 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </a>

                        <button type="button"
                                @click="autoAllocate()"
                                class="w-full px-4 py-3 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-magic"></i>
                            <span>Auto Alokasi</span>
                        </button>

                        <button type="button"
                                @click="resetForm()"
                                class="w-full px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-undo"></i>
                            <span>Reset Form</span>
                        </button>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="bg-blue-50 rounded-2xl border border-blue-200 p-6">
                    <h4 class="text-lg font-semibold text-blue-900 mb-3 flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Tips Edit
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Hanya GR dengan status partial yang bisa diedit</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Total alokasi harus sama dengan quantity received</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Batch number dan expiry date opsional</span>
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
    function goodsReceivedEdit() {
        return {
            grItems: [],
            originalData: {},

            get totalReceived() {
                return this.grItems.reduce((total, item) => total + (parseInt(item.quantity_received) || 0), 0);
            },

            get totalToStock() {
                return this.grItems.reduce((total, item) => total + (parseInt(item.quantity_to_stock) || 0), 0);
            },

            get totalToReady() {
                return this.grItems.reduce((total, item) => total + (parseInt(item.quantity_to_ready) || 0), 0);
            },

            get totalValue() {
                return this.grItems.reduce((total, item) => total + (item.total || 0), 0);
            },

            get canSubmit() {
                if (this.grItems.length === 0) return false;

                // Check if all items have valid allocations
                return this.grItems.every(item => {
                    return this.isItemValid(item) &&
                           item.quantity_received > 0 &&
                           item.unit_price >= 0;
                });
            },

            init() {
                console.log('Initializing goods received edit form');

                // Load existing GR details from backend
                this.loadExistingItems();

                // Store original data for reset
                this.originalData = JSON.parse(JSON.stringify(this.grItems));

                // Add CSRF token to all requests
                const token = document.querySelector('meta[name="csrf-token"]');
                if (token) {
                    window.csrfToken = token.getAttribute('content');
                }
            },

            loadExistingItems() {
                this.grItems = {!! json_encode($goodsReceived->grDetails->map(function($detail) {
                    return [
                        'gr_detail_id' => $detail->gr_detail_id,
                        'item_id' => $detail->item_id,
                        'item_code' => $detail->item->item_code,
                        'item_name' => $detail->item->item_name,
                        'category_name' => $detail->item->category->category_name ?? '',
                        'unit' => $detail->item->unit,
                        'quantity_received' => $detail->quantity_received,
                        'quantity_to_stock' => $detail->quantity_to_stock,
                        'quantity_to_ready' => $detail->quantity_to_ready,
                        'unit_price' => $detail->unit_price,
                        'batch_number' => $detail->batch_number ?? '',
                        'expiry_date' => $detail->expiry_date ? $detail->expiry_date->format('Y-m-d') : '',
                        'notes' => $detail->notes ?? '',
                        'total' => $detail->getTotalValue()
                    ];
                })) !!};

                console.log('Loaded existing items:', this.grItems);
            },

            updateItemCalculations(index) {
                const item = this.grItems[index];
                const qtyReceived = parseInt(item.quantity_received) || 0;
                const qtyToStock = parseInt(item.quantity_to_stock) || 0;
                const qtyToReady = parseInt(item.quantity_to_ready) || 0;
                const unitPrice = parseFloat(item.unit_price) || 0;

                // Calculate total value
                item.total = qtyReceived * unitPrice;

                // Auto-adjust allocation if sum exceeds received
                const totalAllocation = qtyToStock + qtyToReady;
                if (totalAllocation > qtyReceived && qtyReceived > 0) {
                    // Redistribute proportionally
                    const stockRatio = qtyToStock / totalAllocation;
                    const readyRatio = qtyToReady / totalAllocation;

                    item.quantity_to_stock = Math.floor(qtyReceived * stockRatio);
                    item.quantity_to_ready = Math.floor(qtyReceived * readyRatio);

                    // Adjust for rounding errors
                    const newTotal = item.quantity_to_stock + item.quantity_to_ready;
                    if (newTotal !== qtyReceived) {
                        item.quantity_to_stock = qtyReceived - item.quantity_to_ready;
                    }
                }
            },

            isItemValid(item) {
                const qtyReceived = parseInt(item.quantity_received) || 0;
                const qtyToStock = parseInt(item.quantity_to_stock) || 0;
                const qtyToReady = parseInt(item.quantity_to_ready) || 0;

                if (qtyReceived === 0) return false;

                // Validasi: total alokasi harus sama dengan qty yang diterima
                return (qtyToStock + qtyToReady) === qtyReceived;
            },

            autoAllocate() {
                // Auto-allocate 70% to stock, 30% to ready for all items
                this.grItems.forEach((item, index) => {
                    const qtyReceived = parseInt(item.quantity_received) || 0;
                    if (qtyReceived > 0) {
                        item.quantity_to_stock = Math.floor(qtyReceived * 0.7);
                        item.quantity_to_ready = Math.floor(qtyReceived * 0.3);

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

            resetForm() {
                if (confirm('Yakin ingin mereset form? Semua perubahan akan hilang.')) {
                    // Reset to original data
                    this.grItems = JSON.parse(JSON.stringify(this.originalData));

                    // Reset form fields
                    document.getElementById('receive_date').value = '{!! $goodsReceived->receive_date->format('Y-m-d') !!}';
                    document.getElementById('notes').value = '{!! addslashes($goodsReceived->notes ?? '') !!}';

                    this.showToast('Form berhasil direset!', 'info');
                }
            },

            validateForm(event) {
                if (!this.canSubmit) {
                    event.preventDefault();
                    this.showToast('Pastikan semua data sudah terisi dengan benar!', 'error');
                    return false;
                }

                // Additional validation
                if (this.grItems.length === 0) {
                    event.preventDefault();
                    this.showToast('Tidak ada item untuk diupdate!', 'error');
                    return false;
                }

                // Check for invalid allocations
                const invalidItems = this.grItems.filter(item => !this.isItemValid(item));
                if (invalidItems.length > 0) {
                    event.preventDefault();
                    this.showToast('Ada item dengan alokasi yang tidak valid!', 'error');
                    return false;
                }

                // Check if receive date is valid
                const receiveDate = new Date(document.getElementById('receive_date').value);
                const today = new Date();

                if (receiveDate > today) {
                    event.preventDefault();
                    this.showToast('Tanggal penerimaan tidak boleh di masa depan!', 'error');
                    return false;
                }

                return true;
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

    // Form submission enhancements
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + S for save
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                if (form) {
                    form.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            }

            // Ctrl + R for reset
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                const alpineComponent = document.querySelector('[x-data]');
                if (alpineComponent && alpineComponent._x_dataStack) {
                    const alpineData = alpineComponent._x_dataStack[0];
                    if (alpineData && alpineData.resetForm) {
                        alpineData.resetForm();
                    }
                }
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

        // Warn before leaving if form has changes
        let formChanged = false;
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                formChanged = true;
            });
        });

        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });

        // Remove warning when form is submitted
        form.addEventListener('submit', function() {
            formChanged = false;
        });

        // Auto-save functionality (optional)
        let autoSaveTimeout;
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(() => {
                    // Auto-save draft to localStorage
                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries());
                    localStorage.setItem('gr_edit_draft_{!! $goodsReceived->gr_id !!}', JSON.stringify(data));
                    console.log('Draft auto-saved');
                }, 2000);
            });
        });

        // Load draft on page load
        const savedDraft = localStorage.getItem('gr_edit_draft_{!! $goodsReceived->gr_id !!}');
        if (savedDraft) {
            console.log('Draft found, but not auto-loading to prevent data loss');
            // Could show a notification to user about saved draft
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

    /* Number input styling */
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
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

    /* Form enhancements */
    .form-group {
        position: relative;
    }

    .form-group.has-error input,
    .form-group.has-error select,
    .form-group.has-error textarea {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .form-group.has-success input,
    .form-group.has-success select,
    .form-group.has-success textarea {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    /* Progress indicators */
    .progress-indicator {
        transition: all 0.3s ease;
    }

    /* Loading spinner */
    .spinner {
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Status indicators */
    .status-partial {
        background-color: #fef3c7;
        color: #92400e;
    }

    .status-complete {
        background-color: #d1fae5;
        color: #065f46;
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

        .overflow-x-auto table {
            min-width: 900px;
        }

        .px-6 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }

    /* Print styles */
    @media print {
        .no-print {
            display: none !important;
        }

        .bg-gradient-to-r,
        .bg-gradient-to-br {
            background: #374151 !important;
            -webkit-print-color-adjust: exact;
        }
    }

    /* Dark mode support (if needed) */
    @media (prefers-color-scheme: dark) {
        .dark-mode-support {
            /* Add dark mode styles here */
        }
    }

    /* Custom scrollbar */
    .overflow-x-auto::-webkit-scrollbar {
        height: 8px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush
