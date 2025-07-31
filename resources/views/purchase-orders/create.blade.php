@extends('layouts.app')

@section('title', 'Buat Purchase Order - LogistiK Admin')

@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="space-y-6" x-data="purchaseOrderCreate()">
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
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-sm font-medium text-gray-500">Buat PO Baru</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-red-600 to-red-700 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-plus text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Buat Purchase Order</h1>
                    <p class="text-gray-600 mt-1">
                        @if ($lowStockItems)
                            Buat PO untuk barang dengan stok rendah
                        @elseif($selectedSupplier)
                            Buat PO untuk {{ $selectedSupplier->supplier_name }}
                        @else
                            Buat purchase order baru untuk pemesanan barang
                        @endif
                    </p>
                    <div class="flex items-center mt-2 text-sm text-blue-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span>Status awal: Draft Logistic</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('purchase-orders.index') }}"
                    class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <!-- Workflow Info Alert -->
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
            <div class="flex items-start">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-route text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Workflow Purchase Order</h3>
                    <div class="text-blue-800 text-sm space-y-1">
                        <p>• <strong>Draft Logistic:</strong> PO dibuat dan dapat diedit</p>
                        <p>• <strong>Submit ke Finance F1:</strong> Review supplier dan opsi pembayaran</p>
                        <p>• <strong>FINANCE RBP:</strong> Approval final dan setup pembayaran</p>
                        <p>• <strong>Approved:</strong> Siap dikirim ke supplier</p>
                    </div>
                    <div class="mt-3 text-xs text-blue-700 bg-blue-100 rounded-lg p-2">
                        <i class="fas fa-lightbulb mr-1"></i>
                        <strong>Tips:</strong> Supplier dapat dipilih nanti di tahap Finance F1 jika belum yakin sekarang
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert for Low Stock Items -->
        @if ($lowStockItems)
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6">
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-yellow-900 mb-2">Mode Stok Rendah</h3>
                        <p class="text-yellow-700 text-sm">
                            Anda sedang membuat PO untuk barang dengan stok rendah.
                            Sistem akan menampilkan barang yang stoknya di bawah minimum stock.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- PO Form -->
        <form method="POST" action="{{ route('purchase-orders.store') }}" x-ref="poForm">
            @csrf

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
                                        Nomor PO <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="po_number" name="po_number"
                                        value="{{ old('po_number', $poNumber) }}"
                                        class="w-full py-3 px-4 bg-gray-100 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('po_number') border-red-500 @enderror"
                                        readonly>
                                    <p class="text-xs text-gray-500 mt-1">Nomor PO akan otomatis di-generate sistem</p>
                                    @error('po_number')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Supplier - NOW OPTIONAL -->
                                <div>
                                    <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Supplier <span class="text-gray-400">(Opsional)</span>
                                    </label>
                                    <select id="supplier_id" name="supplier_id" x-model="selectedSupplierId"
                                        @change="onSupplierChange()"
                                        class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('supplier_id') border-red-500 @enderror">
                                        <option value="">Pilih supplier atau kosongkan untuk dipilih di Finance F1</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->supplier_id }}"
                                                {{ old('supplier_id', $selectedSupplier?->supplier_id) == $supplier->supplier_id ? 'selected' : '' }}>
                                                {{ $supplier->supplier_name }} ({{ $supplier->supplier_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Jika tidak dipilih sekarang, supplier akan dipilih pada tahap Finance F1
                                    </p>
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
                                        value="{{ old('po_date', now()->format('Y-m-d')) }}"
                                        class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('po_date') border-red-500 @enderror">
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
                                        value="{{ old('expected_date') }}"
                                        min="{{ now()->addDay()->format('Y-m-d') }}"
                                        class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('expected_date') border-red-500 @enderror">
                                    @error('expected_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mt-6">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Catatan
                                </label>
                                <textarea id="notes" name="notes" rows="3"
                                    placeholder="Catatan tambahan untuk purchase order. Contoh: prioritas, instruksi khusus, dll..."
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
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
                                                <input type="hidden" :name="`items[${index}][item_id]`" :value="item.item_id">
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center space-x-2">
                                                    <input type="number" :name="`items[${index}][quantity]`"
                                                        x-model="item.quantity" @input="updateItemTotal(index)"
                                                        min="1" required
                                                        class="w-20 py-2 px-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    <span class="text-sm text-gray-500" x-text="item.unit"></span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="relative">
                                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                                    <input type="number" :name="`items[${index}][unit_price]`"
                                                        x-model="item.unit_price" @input="updateItemTotal(index)"
                                                        step="1" min="0" placeholder="0" required
                                                        class="w-32 py-2 pl-8 pr-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900" x-text="formatCurrency(item.total)"></div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <button type="button" @click="removeItem(index)"
                                                    class="text-red-600 hover:text-red-800 p-1 rounded">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>

                                    <!-- Empty State -->
                                    <tr x-show="selectedItems.length === 0">
                                        <td colspan="5" class="px-6 py-8 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <i class="fas fa-boxes text-4xl text-gray-300 mb-4"></i>
                                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada item</h3>
                                                <p class="text-gray-500 mb-4">Tambahkan item untuk purchase order ini</p>
                                                <button type="button" @click="showAddItemModal()"
                                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                                    Tambah Item Pertama
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>

                                <!-- Total Footer -->
                                <tfoot class="bg-gray-50 border-t" x-show="selectedItems.length > 0">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                            Total Amount:
                                        </td>
                                        <td class="px-6 py-4 text-sm font-bold text-gray-900" x-text="formatCurrency(totalAmount)"></td>
                                        <td class="px-6 py-4"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('purchase-orders.index') }}"
                                class="flex-1 px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-times"></i>
                                <span>Batal</span>
                            </a>
                            <button type="submit" :disabled="selectedItems.length === 0"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-save"></i>
                                <span>Simpan sebagai Draft</span>
                            </button>
                        </div>
                        <div class="mt-3 text-xs text-center text-gray-500">
                            PO akan disimpan dengan status "Draft Logistic" dan dapat diedit sebelum disubmit ke Finance F1
                        </div>
                    </div>
                </div>

                <!-- Right Column - Summary & Info -->
                <div class="space-y-6">
                    <!-- Summary Card -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-calculator mr-2 text-green-600"></i>
                                Ringkasan
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Items:</span>
                                    <span class="font-semibold" x-text="selectedItems.length"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Quantity:</span>
                                    <span class="font-semibold" x-text="totalQuantity"></span>
                                </div>
                                <hr>
                                <div class="flex justify-between text-lg">
                                    <span class="text-gray-900 font-semibold">Total Amount:</span>
                                    <span class="font-bold text-green-600" x-text="formatCurrency(totalAmount)"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Supplier Info -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden" x-show="selectedSupplier">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-building mr-2 text-blue-600"></i>
                                Info Supplier
                            </h3>
                        </div>
                        <div class="p-6" x-show="selectedSupplier">
                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Nama:</span>
                                    <div class="text-gray-900" x-text="selectedSupplier?.supplier_name"></div>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Kode:</span>
                                    <div class="text-gray-900 font-mono" x-text="selectedSupplier?.supplier_code"></div>
                                </div>
                                <div x-show="selectedSupplier?.contact_person">
                                    <span class="font-medium text-gray-700">Contact:</span>
                                    <div class="text-gray-900" x-text="selectedSupplier?.contact_person"></div>
                                </div>
                                <div x-show="selectedSupplier?.phone">
                                    <span class="font-medium text-gray-700">Telepon:</span>
                                    <div class="text-gray-900" x-text="selectedSupplier?.phone"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No Supplier Selected Info -->
                    <div class="bg-yellow-50 rounded-2xl border border-yellow-200 p-6" x-show="!selectedSupplier">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-info-circle text-yellow-600 mt-1"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-yellow-900 mb-2">Supplier Belum Dipilih</h4>
                                <p class="text-sm text-yellow-800">
                                    Tidak masalah! Supplier dapat dipilih nanti pada tahap Finance F1 berdasarkan analisis kebutuhan dan kebijakan perusahaan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Add Items -->
                    @if ($lowStockItems && $items->count() > 0)
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-2 text-yellow-600"></i>
                                    Barang Stok Rendah
                                </h3>
                            </div>
                            <div class="divide-y divide-gray-200 max-h-64 overflow-y-auto">
                                @foreach ($items->take(10) as $item)
                                    @php
                                        $stockInfo = $item->getStockInfo();
                                    @endphp
                                    <div class="p-3 hover:bg-gray-50 cursor-pointer"
                                        @click="quickAddItem('{{ $item->item_id }}', '{{ addslashes($item->item_name) }}', '{{ $item->item_code }}', '{{ $item->category->category_name ?? 'No Category' }}', '{{ $item->unit }}')">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900">{{ $item->item_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $item->item_code }}</div>
                                                <div class="text-xs text-red-600">Stok: {{ $stockInfo['available'] }}/{{ $item->min_stock }}</div>
                                            </div>
                                            <button type="button" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Next Steps Info -->
                    <div class="bg-blue-50 rounded-2xl border border-blue-200 p-6">
                        <h4 class="text-sm font-semibold text-blue-900 mb-3">📋 Langkah Selanjutnya</h4>
                        <div class="text-sm text-blue-800 space-y-2">
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-arrow-right text-blue-600 mt-1 text-xs"></i>
                                <span>Setelah disimpan, PO dapat diedit selama masih draft</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-arrow-right text-blue-600 mt-1 text-xs"></i>
                                <span>Submit ke Finance F1 untuk review supplier & pembayaran</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-arrow-right text-blue-600 mt-1 text-xs"></i>
                                <span>FINANCE RBP akan melakukan approval final</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-arrow-right text-blue-600 mt-1 text-xs"></i>
                                <span>PO siap dikirim ke supplier</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tips -->
                    <div class="bg-green-50 rounded-2xl border border-green-200 p-6">
                        <h4 class="text-sm font-semibold text-green-900 mb-3">💡 Tips</h4>
                        <ul class="text-sm text-green-800 space-y-2">
                            <li>• Item dan quantity wajib diisi dengan benar</li>
                            <li>• Harga dapat diisi perkiraan atau kosong dulu</li>
                            <li>• Gunakan tanggal diharapkan untuk tracking</li>
                            <li>• Tambahkan catatan jika ada instruksi khusus</li>
                            <li>• Supplier dapat dipilih nanti jika belum yakin</li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>

        <!-- Add Item Modal -->
        <div x-show="addItemModal.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.self="hideAddItemModal()"
            @keydown.escape.window="hideAddItemModal()"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="addItemModal.show" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">

                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-plus mr-2 text-blue-600"></i>
                        Tambah Item ke Purchase Order
                    </h3>
                </div>

                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <!-- Search -->
                    <div class="mb-4">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" x-model="itemSearch" @input="filterItems()"
                                placeholder="Cari item berdasarkan nama atau kode..."
                                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Items Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="item in filteredItems" :key="item.item_id">
                            <div class="border border-gray-200 rounded-xl p-4 hover:border-blue-300 transition-colors cursor-pointer"
                                @click="selectItemForAdd(item)">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-box text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900" x-text="item.item_name"></div>
                                        <div class="text-sm text-gray-500" x-text="item.item_code"></div>
                                        <div class="text-xs text-gray-400" x-text="item.category_name"></div>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-xs text-gray-500" x-text="`Unit: ${item.unit}`"></span>
                                    <div class="text-xs" :class="item.stock_status === 'low' ? 'text-red-600' : 'text-green-600'">
                                        <span x-text="`Stok: ${item.available_stock}`"></span>
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
        const availableItems = {!! json_encode($availableItems ?? []) !!};
        const suppliers = {!! json_encode($suppliersJson ?? []) !!};

        function purchaseOrderCreate() {
            return {
                selectedSupplierId: '{{ old("supplier_id", $selectedSupplier?->supplier_id) }}',
                selectedSupplier: null,
                selectedItems: [],
                addItemModal: {
                    show: false
                },
                itemSearch: '',
                filteredItems: [],
                availableItems: availableItems,
                suppliers: suppliers,

                get totalAmount() {
                    return this.selectedItems.reduce((total, item) => total + (item.total || 0), 0);
                },

                get totalQuantity() {
                    return this.selectedItems.reduce((total, item) => total + (parseInt(item.quantity) || 0), 0);
                },

                init() {
                    this.filteredItems = [...this.availableItems];
                    this.updateSelectedSupplier();

                    // Auto-add low stock items if in low stock mode
                    @if ($lowStockItems)
                        this.autoAddLowStockItems();
                    @endif
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

                autoAddLowStockItems() {
                    // Auto-add first 5 low stock items for quick setup
                    const lowStockItems = this.availableItems.filter(item => item.stock_status === 'low').slice(0, 5);
                    lowStockItems.forEach(item => {
                        this.quickAddItem(item.item_id, item.item_name, item.item_code, item.category_name, item.unit);
                    });
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

                quickAddItem(itemId, itemName, itemCode, categoryName, unit) {
                    this.addItem(itemId, itemName, itemCode, categoryName, unit);
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
                            unit_price: 0,
                            total: 0
                        });
                        this.showToast('Item berhasil ditambahkan', 'success');
                    }
                },

                removeItem(index) {
                    const item = this.selectedItems[index];
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
                        if (item.unit_price < 0) {
                            this.showToast(`Harga ${item.item_name} tidak boleh negatif`, 'error');
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
            form.addEventListener('submit', function(e) {
                const alpineData = Alpine.$data(document.querySelector('[x-data]'));
                if (!alpineData.validateForm()) {
                    e.preventDefault();
                    return false;
                }
            });

            // Initialize Select2 for better UX
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#supplier_id').select2({
                    placeholder: 'Pilih supplier atau kosongkan untuk dipilih di Finance F1',
                    allowClear: true,
                    width: '100%'
                }).on('change', function() {
                    // Trigger Alpine.js update
                    const event = new Event('change');
                    this.dispatchEvent(event);
                });
            }
        });

        // Auto-save to localStorage as draft (optional)
        function saveDraft() {
            const alpineData = Alpine.$data(document.querySelector('[x-data]'));
            const draft = {
                supplier_id: alpineData.selectedSupplierId,
                items: alpineData.selectedItems,
                po_date: document.getElementById('po_date').value,
                expected_date: document.getElementById('expected_date').value,
                notes: document.getElementById('notes').value,
                timestamp: new Date().toISOString()
            };

            try {
                localStorage.setItem('po_draft', JSON.stringify(draft));
            } catch (e) {
                console.log('Could not save draft to localStorage');
            }
        }

        // Clear draft on successful submission
        window.addEventListener('beforeunload', function() {
            saveDraft();
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

        /* Progress indicator for form completion */
        .form-progress {
            transition: width 0.3s ease;
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
    </style>
@endpush
