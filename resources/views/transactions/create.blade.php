@extends('layouts.app')

@section('title', 'Buat Transaksi - LogistiK Admin')

@section('content')
    <div x-data="flexibleTransactionCreate()" class="space-y-6">

        <!-- Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Buat Transaksi</h1>
                        <p class="text-sm text-gray-600" x-text="isMultiMode ? 'Mode: Multi Item' : 'Mode: Single Item'"></p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Toggle Single/Multi Mode -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Single</span>
                        <button @click="toggleMode()" :class="isMultiMode ? 'bg-purple-600' : 'bg-gray-300'"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                            <span :class="isMultiMode ? 'translate-x-6' : 'translate-x-1'"
                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                        </button>
                        <span class="text-sm text-gray-600">Multi</span>
                    </div>

                    <a href="{{ route('transactions.index') }}"
                        class="text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Mode Info -->
        <div :class="isMultiMode ? 'bg-purple-50 border-purple-200' : 'bg-blue-50 border-blue-200'"
            class="rounded-lg border p-4">
            <div class="flex items-center space-x-2">
                <i :class="isMultiMode ? 'fas fa-layer-group text-purple-600' : 'fas fa-cube text-blue-600'"></i>
                <span class="font-medium text-gray-900"
                    x-text="isMultiMode ? 'Multi Item Mode' : 'Single Item Mode'"></span>
            </div>
            <p class="text-sm mt-1" :class="isMultiMode ? 'text-purple-700' : 'text-blue-700'">
                <span x-show="!isMultiMode">Pilih 1 barang untuk transaksi</span>
                <span x-show="isMultiMode">Pilih beberapa barang untuk 1 transaksi bersamaan</span>
            </p>
        </div>

        <!-- Add Items Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <span x-show="!isMultiMode">Pilih Barang</span>
                <span x-show="isMultiMode">Tambah Barang</span>
            </h2>

            <!-- Enhanced Method Selection with Hardware Scanner -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <!-- QR Camera -->
                <button @click="inputMethod = 'qr'"
                    :class="inputMethod === 'qr' ? 'border-red-500 bg-red-50' : 'border-gray-200'"
                    class="border-2 rounded-lg p-4 text-left transition-all hover:border-gray-300">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-qrcode text-red-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Scan QR Camera</h3>
                            <p class="text-sm text-gray-600">Kamera web browser</p>
                        </div>
                    </div>
                </button>

                <!-- Hardware Scanner -->
                <button @click="inputMethod = 'hardware'"
                    :class="inputMethod === 'hardware' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'"
                    class="border-2 rounded-lg p-4 text-left transition-all hover:border-gray-300">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-barcode text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Hardware Scanner</h3>
                            <p class="text-sm text-gray-600">Barcode scanner fisik</p>
                            <div class="flex items-center mt-1">
                                <div :class="getScannerStatusClass()" class="w-2 h-2 rounded-full mr-2"></div>
                                <span class="text-xs" :class="getScannerStatusClass()"
                                    x-text="getScannerStatusText()"></span>
                            </div>
                        </div>
                    </div>
                </button>

                <!-- Manual Search -->
                <button @click="inputMethod = 'manual'"
                    :class="inputMethod === 'manual' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                    class="border-2 rounded-lg p-4 text-left transition-all hover:border-gray-300">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-search text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Cari Manual</h3>
                            <p class="text-sm text-gray-600">Cari dari daftar</p>
                        </div>
                    </div>
                </button>
            </div>

            <!-- QR Camera Scanner -->
            <div x-show="inputMethod === 'qr'" class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-gray-900">QR Camera Scanner</h3>
                    <button @click="toggleQRScanner()"
                        :class="qrScannerActive ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                        class="px-4 py-2 text-white rounded-lg">
                        <i :class="qrScannerActive ? 'fas fa-stop' : 'fas fa-play'" class="mr-2"></i>
                        <span x-text="qrScannerActive ? 'Stop Camera' : 'Start Camera'"></span>
                    </button>
                </div>

                <div x-show="qrScannerActive" class="flex justify-center mb-4">
                    <video id="flexible-qr-scanner" width="400" height="300"
                        style="border: 2px solid #ccc; border-radius: 8px; background: #000;">
                    </video>
                </div>
            </div>

            <!-- Hardware Scanner Section -->
            <div x-show="inputMethod === 'hardware'" class="mb-6">
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">

                    <!-- Scanner Status Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-barcode text-purple-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Hardware Barcode Scanner</h3>
                                <div class="flex items-center space-x-2 text-sm">
                                    <span class="text-gray-600">Status:</span>
                                    <span :class="getScannerStatusClass()" class="font-medium"
                                        x-text="getScannerStatusText()"></span>
                                    <span class="text-gray-400">•</span>
                                    <span class="text-gray-600" x-text="getScannerTypeText()"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Scanner Controls -->
                        <div class="flex items-center space-x-2">
                            <!-- Connect/Disconnect Button -->
                            <button x-show="scannerStatus === 'disconnected'" @click="connectHardwareScanner()"
                                :disabled="loading"
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white rounded-lg text-sm">
                                <i class="fas fa-plug mr-2"></i>
                                Connect Scanner
                            </button>

                            <!-- Start/Stop Scanning Button -->
                            <button x-show="scannerStatus === 'connected' || scannerStatus === 'scanning'"
                                @click="toggleHardwareScanner()"
                                :class="hardwareScannerActive ? 'bg-red-600 hover:bg-red-700' :
                                    'bg-green-600 hover:bg-green-700'"
                                class="px-4 py-2 text-white rounded-lg text-sm">
                                <i :class="hardwareScannerActive ? 'fas fa-stop' : 'fas fa-play'" class="mr-2"></i>
                                <span x-text="hardwareScannerActive ? 'Stop Scanning' : 'Start Scanning'"></span>
                            </button>

                            <!-- Disconnect Button -->
                            <button x-show="scannerStatus !== 'disconnected'" @click="disconnectHardwareScanner()"
                                class="px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Error Display -->
                    <div x-show="scannerError" class="mb-4 p-3 bg-red-100 border border-red-300 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                                <span class="text-red-800 text-sm" x-text="scannerError"></span>
                            </div>
                            <button @click="connectHardwareScanner()"
                                class="text-red-600 hover:text-red-800 text-sm underline">
                                Retry
                            </button>
                        </div>
                    </div>

                    <!-- Scanner Type Selection -->
                    <div x-show="scannerStatus === 'disconnected'" class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Scanner Type</label>
                        <select x-model="scannerType"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="auto">Auto Detect</option>
                            <option value="hid">USB HID (Keyboard Mode)</option>
                            <option value="usb">USB Direct Connection</option>
                            <option value="serial">Serial Port</option>
                        </select>
                        <p class="text-xs text-gray-600 mt-1">
                            Kebanyakan scanner USB menggunakan mode HID (Keyboard)
                        </p>
                    </div>

                    <!-- Scanning Status -->
                    <div x-show="hardwareScannerActive" class="text-center py-8">
                        <div class="inline-flex items-center space-x-3">
                            <div class="w-8 h-8 border-4 border-purple-600 border-t-transparent rounded-full animate-spin">
                            </div>
                            <span class="text-purple-700 font-medium">Ready to scan... Arahkan scanner ke barcode/QR</span>
                        </div>

                        <!-- Last Scanned Display -->
                        <div x-show="lastScannedCode" class="mt-4 p-3 bg-white rounded-lg border">
                            <div class="text-sm text-gray-600">Last scanned:</div>
                            <div class="font-mono text-sm text-gray-900 break-all" x-text="lastScannedCode"></div>
                            <div class="text-xs text-gray-500 mt-1" x-text="new Date().toLocaleTimeString()"></div>
                        </div>
                    </div>

                    <!-- Scanner Instructions -->
                    <div x-show="!hardwareScannerActive" class="bg-white rounded-lg p-4 border border-purple-200">
                        <h4 class="font-medium text-gray-900 mb-2">
                            <i class="fas fa-info-circle text-purple-600 mr-2"></i>
                            Petunjuk Penggunaan
                        </h4>

                        <div class="text-sm text-gray-600 space-y-2">
                            <div class="flex items-start space-x-2">
                                <span class="font-medium text-purple-600">1.</span>
                                <span>Hubungkan barcode scanner ke komputer via USB</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <span class="font-medium text-purple-600">2.</span>
                                <span>Klik "Connect Scanner" untuk menghubungkan</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <span class="font-medium text-purple-600">3.</span>
                                <span>Klik "Start Scanning" untuk mengaktifkan</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <span class="font-medium text-purple-600">4.</span>
                                <span>Arahkan scanner ke QR code atau barcode pada barang</span>
                            </div>
                        </div>

                        <!-- Supported Scanners -->
                        <details class="mt-4">
                            <summary class="cursor-pointer text-sm font-medium text-purple-600 hover:text-purple-800">
                                <i class="fas fa-chevron-right mr-1"></i>
                                Scanner yang didukung
                            </summary>
                            <div class="mt-2 text-xs text-gray-600 space-y-1">
                                <template x-for="scanner in supportedScanners" :key="scanner.name">
                                    <div class="flex items-center justify-between py-1">
                                        <span x-text="scanner.name"></span>
                                        <span x-text="scanner.type.toUpperCase()"
                                            class="text-purple-600 font-medium"></span>
                                    </div>
                                </template>
                            </div>
                        </details>
                    </div>
                </div>
            </div>

            <!-- Manual Search -->
            <div x-show="inputMethod === 'manual'">
                <div class="mb-4">
                    <input type="text" x-model="searchQuery" @input="searchItems()"
                        placeholder="Cari barang berdasarkan nama, kode, atau serial number..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div x-show="searchResults.length > 0" class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg">
                    <template x-for="item in searchResults" :key="item.item_detail_id">
                        <div class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0"
                            @click="addItemToSelection(item)">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-cube text-gray-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900" x-text="item.item_name"></h4>
                                        <p class="text-sm text-gray-600"
                                            x-text="item.item_code + ' • ' + item.serial_number"></p>
                                        <p class="text-xs text-gray-500"
                                            x-text="'Status: ' + item.current_status + ' • Lokasi: ' + (item.location || 'N/A')">
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span x-show="isItemAlreadySelected(item)" class="text-green-600">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <span x-show="!isItemAlreadySelected(item)" class="text-gray-400">
                                        <i class="fas fa-plus-circle"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="searchQuery.length > 0 && searchResults.length === 0" class="text-center py-8 text-gray-500">
                    <i class="fas fa-search text-3xl mb-2"></i>
                    <p>Tidak ada barang ditemukan untuk pencarian "<span x-text="searchQuery"></span>"</p>
                </div>
            </div>
        </div>

        <!-- Single Mode: Selected Item -->
        <div x-show="!isMultiMode && selectedItem" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-medium text-gray-900 mb-3">Barang Terpilih</h3>
            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cube text-blue-600"></i>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900" x-text="selectedItem?.item_name"></h4>
                        <p class="text-sm text-gray-600"
                            x-text="selectedItem?.item_code + ' • ' + selectedItem?.serial_number"></p>
                        <p class="text-xs text-gray-500"
                            x-text="'Status: ' + selectedItem?.current_status + ' • Lokasi: ' + (selectedItem?.location || 'N/A')">
                        </p>
                    </div>
                </div>
                <button @click="clearSelection()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Multi Mode: Selected Items -->
        <div x-show="isMultiMode && selectedItems.length > 0"
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-medium text-gray-900">
                    Barang Terpilih (<span x-text="selectedItems.length"></span> item)
                </h3>
                <button @click="clearAllItems()" class="text-red-600 hover:text-red-800 text-sm">
                    <i class="fas fa-trash mr-1"></i>
                    Hapus Semua
                </button>
            </div>

            <div class="space-y-2 max-h-48 overflow-y-auto">
                <template x-for="(item, index) in selectedItems" :key="index">
                    <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cube text-purple-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900" x-text="item.item_name"></h4>
                                <p class="text-sm text-gray-600" x-text="item.item_code + ' • ' + item.serial_number"></p>
                            </div>
                        </div>
                        <button @click="removeItem(index)" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Transaction Form -->
        <form x-show="hasSelectedItems()" @submit.prevent="submitFlexibleTransaction()"
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

            <h2 class="text-lg font-semibold text-gray-900 mb-6">Detail Transaksi</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- hidden input supaya dikirim ke server -->
                <input type="hidden" name="transaction_type" :value="form.transaction_type">

                <select disabled x-model="form.transaction_type" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                    <template x-if="form.transaction_type === 'IN'">
                        <option value="IN">Barang Masuk</option>
                    </template>
                    <template x-if="form.transaction_type === 'OUT'">
                        <option value="OUT">Barang Keluar</option>
                    </template>
                    <template x-if="form.transaction_type === 'REPAIR'">
                        <option value="REPAIR">Barang Repair</option>
                    </template>
                    <template x-if="form.transaction_type === 'LOST'">
                        <option value="LOST">Barang Hilang</option>
                    </template>
                    <template x-if="form.transaction_type === 'RETURN'">
                        <option value="RETURN">Barang Kembali</option>
                    </template>
                </select>

                <!-- Reference Type & ID Section -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reference Type</label>
                    <select x-model="form.reference_type" @change="handleReferenceTypeChange()"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        <option value="">Pilih Reference Type</option>
                        <option value="ticket">Ticket ID (External API)</option>

                    </select>
                </div>

                <!-- Reference ID - Dynamic Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Reference ID
                        <span x-show="form.reference_type === 'ticket'" class="text-blue-600 text-xs">(dari API)</span>
                    </label>

                    <!-- Dropdown untuk Ticket API -->
                    <div x-show="form.reference_type === 'ticket'">
                        <div class="relative">
                            <select x-model="form.reference_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 bg-white">
                                <option value="">
                                    <span x-show="!ticketIds.length && !loadingTickets">Pilih Ticket ID</span>
                                    <span x-show="loadingTickets">Loading tickets...</span>
                                    <span x-show="ticketIds.length && !loadingTickets">Pilih dari daftar ticket</span>
                                </option>
                                <template x-for="ticketId in ticketIds" :key="ticketId">
                                    <option :value="ticketId" x-text="ticketId"></option>
                                </template>
                            </select>

                            <!-- Loading indicator -->
                            <div x-show="loadingTickets" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <i class="fas fa-spinner fa-spin text-gray-400"></i>
                            </div>

                            <!-- Refresh button -->
                            <button type="button" @click="fetchTicketIds()" :disabled="loadingTickets"
                                class="absolute right-10 top-1/2 transform -translate-y-1/2 text-blue-600 hover:text-blue-800 disabled:opacity-50"
                                title="Refresh ticket list">
                                <i class="fas fa-sync-alt text-sm"></i>
                            </button>
                        </div>

                        <!-- API Status -->
                        <div class="mt-2 text-xs">
                            <span x-show="apiError" class="text-red-600 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <span x-text="apiError"></span>
                                <button @click="fetchTicketIds()" class="ml-2 text-blue-600 underline">Retry</button>
                            </span>
                            <span x-show="ticketIds.length && !apiError" class="text-green-600 flex items-center">
                                <i class="fas fa-check-circle mr-1"></i>
                                <span x-text="`${ticketIds.length} ticket(s) tersedia`"></span>
                                <span class="text-gray-500 ml-2">•</span>
                                <span class="text-gray-500 ml-1" x-text="lastFetched"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Manual Input untuk tipe lain -->
                    <input x-show="form.reference_type !== 'ticket'" type="text" x-model="form.reference_id"
                        :placeholder="getReferenceIdPlaceholder()"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                </div>

                <!-- From Location -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Asal</label>
                    <input type="text" x-model="form.from_location" placeholder="Lokasi asal barang"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                </div>

                <!-- To Location -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Tujuan</label>
                    <input type="text" x-model="form.to_location" placeholder="Lokasi tujuan barang"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                </div>
            </div>

            <!-- Notes -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea x-model="form.notes" rows="3" placeholder="Catatan untuk transaksi ini..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"></textarea>
            </div>

            <!-- Submit -->
            <div class="mt-8 flex items-center justify-end space-x-3">
                <button type="button" @click="resetForm()"
                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Reset
                </button>
                <button type="submit" :disabled="loading"
                    class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-50">
                    <span x-show="!loading" class="flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        <span
                            x-text="isMultiMode ? `Buat Transaksi (${selectedItems.length} items)` : 'Buat Transaksi'"></span>
                    </span>
                    <span x-show="loading" class="flex items-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Processing...
                    </span>
                </button>
            </div>
        </form>

    </div>
@endsection

@push('scripts')
    <!-- ZXing Browser Library -->
    <script src="https://unpkg.com/@zxing/library@latest"></script>
    <script>
        function flexibleTransactionCreate() {
            return {
                // ================================================================
                // BASIC PROPERTIES
                // ================================================================

                // Mode control
                isMultiMode: false,

                // Input method
                inputMethod: 'manual', // 'qr', 'hardware', 'manual'

                // QR Camera Scanner
                qrScannerActive: false,
                codeReader: null,

                // Single mode
                selectedItem: null,

                // Multi mode
                selectedItems: [],

                // Search
                searchQuery: '',
                searchResults: [],

                // API Ticket Integration
                ticketIds: [],
                loadingTickets: false,
                apiError: null,
                lastFetched: null,

                // Form
                form: {
                    transaction_type: '{{ request()->get('type') ?? '' }}',
                    reference_type: '',
                    reference_id: '',
                    from_location: '',
                    to_location: '',
                    notes: ''
                },

                loading: false,

                // ================================================================
                // HARDWARE SCANNER PROPERTIES
                // ================================================================

                // Hardware scanner properties
                hardwareScannerActive: false,
                serialPort: null,
                usbDevice: null,
                hidDevice: null,
                scannerType: 'auto', // 'serial', 'usb', 'hid', 'auto'

                // Scanner settings
                scannerSettings: {
                    baudRate: 9600,
                    dataBits: 8,
                    stopBits: 1,
                    parity: 'none',
                    flowControl: 'none'
                },

                // Scanner status
                scannerStatus: 'disconnected', // 'disconnected', 'connecting', 'connected', 'scanning', 'error'
                scannerError: null,
                lastScannedCode: null,
                scanBuffer: '',
                scanTimeout: null,

                // Supported scanner models
                supportedScanners: [{
                        name: 'Generic USB HID Scanner',
                        type: 'hid',
                        vendorId: null
                    },
                    {
                        name: 'Honeywell Voyager 1400g',
                        type: 'hid',
                        vendorId: 0x0c2e
                    },
                    {
                        name: 'Symbol/Zebra LS2208',
                        type: 'hid',
                        vendorId: 0x05e0
                    },
                    {
                        name: 'Datalogic QuickScan',
                        type: 'hid',
                        vendorId: 0x05f9
                    },
                    {
                        name: 'Code CR1000',
                        type: 'hid',
                        vendorId: 0x1659
                    },
                    {
                        name: 'Generic Serial Scanner',
                        type: 'serial',
                        vendorId: null
                    },
                    {
                        name: 'Generic USB Serial Scanner',
                        type: 'usb',
                        vendorId: null
                    }
                ],

                // ================================================================
                // INITIALIZATION
                // ================================================================

                init() {
                    console.log('=== FLEXIBLE TRANSACTION CREATE INIT ===');

                    // Initialize QR Camera Scanner
                    this.codeReader = new ZXing.BrowserQRCodeReader();

                    // Initialize hardware scanner detection
                    this.initHardwareScanner();

                    // Listen for keyboard input (for USB HID scanners)
                    this.initKeyboardListener();

                    // Listen for page unload to cleanup
                    window.addEventListener('beforeunload', () => {
                        this.cleanup();
                    });
                },

                cleanup() {
                    this.stopQRScanner();
                    this.disconnectHardwareScanner();
                },

                // ================================================================
                // HARDWARE SCANNER INITIALIZATION
                // ================================================================

                async initHardwareScanner() {
                    console.log('🔍 Initializing hardware scanner detection...');

                    // Check browser support
                    this.checkBrowserSupport();

                    // Auto-detect connected scanners
                    await this.autoDetectScanners();
                },

                checkBrowserSupport() {
                    const support = {
                        webSerial: 'serial' in navigator,
                        webUSB: 'usb' in navigator,
                        webHID: 'hid' in navigator
                    };

                    console.log('🌐 Browser support:', support);

                    if (!support.webSerial && !support.webUSB && !support.webHID) {
                        console.warn('⚠️ No hardware scanner APIs supported in this browser');
                        this.scannerError = 'Browser tidak mendukung koneksi hardware scanner';
                        return false;
                    }

                    return true;
                },

                async autoDetectScanners() {
                    try {
                        // Try to detect HID scanners first (most common)
                        if ('hid' in navigator) {
                            await this.detectHIDScanners();
                        }

                        // Try to detect USB scanners
                        if ('usb' in navigator) {
                            await this.detectUSBScanners();
                        }

                        console.log('📡 Scanner auto-detection completed');

                    } catch (error) {
                        console.error('❌ Scanner detection error:', error);
                        this.scannerError = 'Gagal mendeteksi scanner: ' + error.message;
                    }
                },

                async detectHIDScanners() {
                    try {
                        const devices = await navigator.hid.getDevices();
                        const scanners = devices.filter(device => this.isBarcodeScannerDevice(device));

                        if (scanners.length > 0) {
                            console.log('✅ Found HID scanners:', scanners);
                            this.hidDevice = scanners[0];
                            this.scannerType = 'hid';
                            this.scannerStatus = 'connected';
                        } else {
                            console.log('ℹ️ No HID scanners found');
                        }

                    } catch (error) {
                        console.error('❌ HID detection error:', error);
                    }
                },

                async detectUSBScanners() {
                    try {
                        const devices = await navigator.usb.getDevices();
                        const scanners = devices.filter(device => this.isBarcodeScannerDevice(device));

                        if (scanners.length > 0) {
                            console.log('✅ Found USB scanners:', scanners);
                            this.usbDevice = scanners[0];
                            this.scannerType = 'usb';
                            this.scannerStatus = 'connected';
                        } else {
                            console.log('ℹ️ No USB scanners found');
                        }

                    } catch (error) {
                        console.error('❌ USB detection error:', error);
                    }
                },

                isBarcodeScannerDevice(device) {
                    // Check if device is likely a barcode scanner
                    const scannerVendorIds = [0x0c2e, 0x05e0, 0x05f9, 0x1659, 0x1a86, 0x04b4];
                    const scannerKeywords = ['scanner', 'barcode', 'qr', 'code', 'reader', 'honeywell', 'symbol', 'zebra',
                        'datalogic'
                    ];

                    // Check vendor ID
                    if (device.vendorId && scannerVendorIds.includes(device.vendorId)) {
                        return true;
                    }

                    // Check product name
                    if (device.productName) {
                        const productName = device.productName.toLowerCase();
                        return scannerKeywords.some(keyword => productName.includes(keyword));
                    }

                    // Check by usage (HID specific)
                    if (device.collections) {
                        return device.collections.some(collection =>
                            collection.usage === 0x05 || // Generic Desktop
                            collection.usage === 0x06 || // Keyboard
                            collection.usagePage === 0x08 // LED
                        );
                    }

                    return false;
                },

                // ================================================================
                // KEYBOARD INPUT LISTENER (USB HID Scanners)
                // ================================================================

                initKeyboardListener() {
                    // Most USB barcode scanners work as HID keyboard devices
                    document.addEventListener('keydown', (event) => {
                        if (this.hardwareScannerActive && this.scannerType === 'hid') {
                            this.handleKeyboardInput(event);
                        }
                    });

                    // Also listen for paste events (some scanners might paste data)
                    document.addEventListener('paste', (event) => {
                        if (this.hardwareScannerActive && this.scannerType === 'hid') {
                            event.preventDefault();
                            const pasteData = event.clipboardData.getData('text');
                            if (pasteData.trim()) {
                                this.handleHardwareScan(pasteData.trim());
                            }
                        }
                    });
                },

                handleKeyboardInput(event) {
                    // Prevent default behavior for scanner input
                    if (this.hardwareScannerActive) {
                        // Check if this looks like scanner input (rapid typing)
                        const currentTime = Date.now();
                        if (!this.lastKeyTime) {
                            this.lastKeyTime = currentTime;
                        } else {
                            const timeDiff = currentTime - this.lastKeyTime;
                            if (timeDiff < 50) { // Very fast typing, likely scanner
                                this.isScannerInput = true;
                            }
                            this.lastKeyTime = currentTime;
                        }
                    }

                    // Barcode scanners typically send data very quickly followed by Enter
                    if (event.key === 'Enter') {
                        if (this.scanBuffer.trim() && this.hardwareScannerActive) {
                            console.log('📱 Hardware scanner input:', this.scanBuffer);
                            this.handleHardwareScan(this.scanBuffer.trim());
                            this.scanBuffer = '';
                            this.isScannerInput = false;
                            event.preventDefault();
                        }
                        return;
                    }

                    // Accumulate characters (ignore special keys)
                    if (event.key.length === 1 && this.hardwareScannerActive) {
                        this.scanBuffer += event.key;

                        // Clear buffer after timeout (in case scan was interrupted)
                        if (this.scanTimeout) {
                            clearTimeout(this.scanTimeout);
                        }

                        this.scanTimeout = setTimeout(() => {
                            this.scanBuffer = '';
                            this.isScannerInput = false;
                        }, 1000);
                    }
                },

                // ================================================================
                // MANUAL HARDWARE SCANNER CONNECTION
                // ================================================================

                async connectHardwareScanner() {
                    this.scannerStatus = 'connecting';
                    this.scannerError = null;
                    this.loading = true;

                    try {
                        switch (this.scannerType) {
                            case 'serial':
                                await this.connectSerialScanner();
                                break;
                            case 'usb':
                                await this.connectUSBScanner();
                                break;
                            case 'hid':
                                await this.connectHIDScanner();
                                break;
                            default:
                                await this.autoConnectScanner();
                        }

                    } catch (error) {
                        console.error('❌ Scanner connection error:', error);
                        this.scannerError = 'Gagal terhubung ke scanner: ' + error.message;
                        this.scannerStatus = 'error';
                    } finally {
                        this.loading = false;
                    }
                },

                async connectSerialScanner() {
                    if (!('serial' in navigator)) {
                        throw new Error('Web Serial API tidak didukung browser ini');
                    }

                    this.serialPort = await navigator.serial.requestPort();
                    await this.serialPort.open(this.scannerSettings);

                    // Start listening for data
                    this.startSerialListener();

                    this.scannerStatus = 'connected';
                    console.log('✅ Serial scanner connected');
                },

                async connectUSBScanner() {
                    if (!('usb' in navigator)) {
                        throw new Error('Web USB API tidak didukung browser ini');
                    }

                    this.usbDevice = await navigator.usb.requestDevice({
                        filters: this.supportedScanners
                            .filter(s => s.type === 'usb' && s.vendorId)
                            .map(s => ({
                                vendorId: s.vendorId
                            }))
                    });

                    await this.usbDevice.open();
                    await this.usbDevice.selectConfiguration(1);

                    // Start listening for data
                    this.startUSBListener();

                    this.scannerStatus = 'connected';
                    console.log('✅ USB scanner connected');
                },

                async connectHIDScanner() {
                    if (!('hid' in navigator)) {
                        throw new Error('Web HID API tidak didukung browser ini');
                    }

                    // Request HID device if not already detected
                    if (!this.hidDevice) {
                        const devices = await navigator.hid.requestDevice({
                            filters: this.supportedScanners
                                .filter(s => s.type === 'hid' && s.vendorId)
                                .map(s => ({
                                    vendorId: s.vendorId
                                }))
                        });

                        if (devices.length > 0) {
                            this.hidDevice = devices[0];
                        }
                    }

                    if (this.hidDevice) {
                        if (!this.hidDevice.opened) {
                            await this.hidDevice.open();
                        }

                        // Start listening for HID input reports
                        this.startHIDListener();
                    }

                    // HID scanners usually work automatically as keyboard input
                    this.scannerStatus = 'connected';
                    console.log('✅ HID scanner ready');
                },

                async autoConnectScanner() {
                    // Try different connection methods automatically
                    const methods = ['hid', 'usb', 'serial'];

                    for (const method of methods) {
                        try {
                            this.scannerType = method;

                            switch (method) {
                                case 'hid':
                                    await this.connectHIDScanner();
                                    return;
                                case 'usb':
                                    await this.connectUSBScanner();
                                    return;
                                case 'serial':
                                    await this.connectSerialScanner();
                                    return;
                            }
                        } catch (error) {
                            console.log(`❌ ${method} connection failed:`, error.message);
                            continue;
                        }
                    }

                    throw new Error('Tidak dapat terhubung dengan metode apapun');
                },

                // ================================================================
                // DATA LISTENERS
                // ================================================================

                async startSerialListener() {
                    if (!this.serialPort || !this.serialPort.readable) return;

                    const reader = this.serialPort.readable.getReader();

                    try {
                        while (this.serialPort.readable) {
                            const {
                                value,
                                done
                            } = await reader.read();
                            if (done) break;

                            const text = new TextDecoder().decode(value);
                            this.handleHardwareScan(text.trim());
                        }
                    } catch (error) {
                        console.error('❌ Serial read error:', error);
                    } finally {
                        reader.releaseLock();
                    }
                },

                async startUSBListener() {
                    if (!this.usbDevice) return;

                    try {
                        while (this.usbDevice.opened) {
                            const result = await this.usbDevice.transferIn(1, 64);

                            if (result.data && result.data.byteLength > 0) {
                                const text = new TextDecoder().decode(result.data);
                                this.handleHardwareScan(text.trim());
                            }
                        }
                    } catch (error) {
                        console.error('❌ USB read error:', error);
                    }
                },

                async startHIDListener() {
                    if (!this.hidDevice) return;

                    this.hidDevice.addEventListener('inputreport', (event) => {
                        const {
                            data,
                            device,
                            reportId
                        } = event;

                        // Convert data to text
                        const text = new TextDecoder().decode(data);
                        if (text.trim()) {
                            this.handleHardwareScan(text.trim());
                        }
                    });
                },

                // ================================================================
                // HARDWARE SCANNER CONTROL
                // ================================================================

                toggleHardwareScanner() {
                    if (this.hardwareScannerActive) {
                        this.stopHardwareScanner();
                    } else {
                        this.startHardwareScanner();
                    }
                },

                async startHardwareScanner() {
                    try {
                        if (this.scannerStatus === 'disconnected') {
                            await this.connectHardwareScanner();
                        }

                        this.hardwareScannerActive = true;
                        this.scannerStatus = 'scanning';
                        this.scanBuffer = '';
                        this.lastScannedCode = null;

                        console.log('🚀 Hardware scanner activated');
                        this.showNotification('Scanner aktif - siap menerima input', 'success');

                    } catch (error) {
                        console.error('❌ Failed to start hardware scanner:', error);
                        this.showNotification('Gagal mengaktifkan scanner: ' + error.message, 'error');
                    }
                },

                stopHardwareScanner() {
                    this.hardwareScannerActive = false;
                    this.scannerStatus = this.scannerStatus === 'scanning' ? 'connected' : this.scannerStatus;
                    this.scanBuffer = '';

                    if (this.scanTimeout) {
                        clearTimeout(this.scanTimeout);
                        this.scanTimeout = null;
                    }

                    console.log('⏹️ Hardware scanner stopped');
                },

                disconnectHardwareScanner() {
                    this.stopHardwareScanner();

                    // Close connections
                    if (this.serialPort && this.serialPort.readable) {
                        this.serialPort.close();
                        this.serialPort = null;
                    }

                    if (this.usbDevice && this.usbDevice.opened) {
                        this.usbDevice.close();
                        this.usbDevice = null;
                    }

                    if (this.hidDevice && this.hidDevice.opened) {
                        this.hidDevice.close();
                        this.hidDevice = null;
                    }

                    this.scannerStatus = 'disconnected';
                    this.scannerError = null;

                    console.log('🔌 Hardware scanner disconnected');
                },

                // ================================================================
                // HARDWARE SCAN PROCESSING
                // ================================================================

                async handleHardwareScan(scanData) {
                    console.log('📡 Hardware scan received:', scanData);

                    this.lastScannedCode = scanData;

                    try {
                        // Try to parse as JSON first (for QR codes)
                        let qrData;
                        try {
                            qrData = JSON.parse(scanData);

                            if (qrData.type === 'item_detail' && qrData.item_detail_id) {
                                // Process QR code
                                await this.processQRData(qrData);
                                return;
                            }
                        } catch (parseError) {
                            // Not JSON, treat as barcode
                        }

                        // Process as barcode/text
                        await this.processBarcodeData(scanData);

                    } catch (error) {
                        console.error('❌ Hardware scan processing error:', error);
                        this.showNotification('Error processing scan: ' + error.message, 'error');
                    }
                },

                async processQRData(qrData) {
                    console.log('📱 Processing QR data:', qrData);

                    // Create item object from QR
                    const item = {
                        item_detail_id: qrData.item_detail_id,
                        item_name: qrData.item_name,
                        item_code: qrData.item_code,
                        serial_number: qrData.serial_number,
                        item_id: qrData.item_id,
                        current_status: 'available',
                        location: 'Warehouse'
                    };

                    // Add to selection
                    this.addItemToSelection(item);

                    // Show success feedback
                    this.showScanSuccess(`QR Code: ${item.item_name}`);
                },

                async processBarcodeData(barcodeData) {
                    console.log('📊 Processing barcode data:', barcodeData);

                    try {
                        // Search for item by barcode/serial number
                        const response = await fetch('/api/items/search-by-barcode?' + new URLSearchParams({
                            barcode: barcodeData
                        }));

                        const data = await response.json();

                        if (data.success && data.item) {
                            this.addItemToSelection(data.item);
                            this.showScanSuccess(`Barcode: ${data.item.item_name}`);
                        } else {
                            throw new Error('Item tidak ditemukan untuk barcode: ' + barcodeData);
                        }

                    } catch (error) {
                        console.error('❌ Barcode lookup error:', error);

                        // Show barcode input dialog as fallback
                        this.showBarcodeInputDialog(barcodeData);
                    }
                },

                showScanSuccess(message) {
                    this.showNotification('✅ ' + message, 'success');
                    this.playBeepSound();
                },

                showBarcodeInputDialog(barcodeData) {
                    const action = confirm(
                        `Barcode terdeteksi: ${barcodeData}\n\n` +
                        'Item tidak ditemukan di database.\n\n' +
                        'Apakah Anda ingin mencari manual?'
                    );

                    if (action) {
                        this.inputMethod = 'manual';
                        this.searchQuery = barcodeData;
                        this.searchItems();
                    }
                },

                playBeepSound() {
                    try {
                        // Simple beep sound for scan feedback
                        const audioContext = new(window.AudioContext || window.webkitAudioContext)();
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();

                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);

                        oscillator.frequency.value = 800;
                        oscillator.type = 'square';

                        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

                        oscillator.start(audioContext.currentTime);
                        oscillator.stop(audioContext.currentTime + 0.1);
                    } catch (error) {
                        // Ignore audio errors
                    }
                },

                showNotification(message, type = 'info') {
                    // Create notification element
                    const notification = document.createElement('div');
                    const colors = {
                        success: 'bg-green-500',
                        error: 'bg-red-500',
                        warning: 'bg-yellow-500',
                        info: 'bg-blue-500'
                    };

                    notification.className =
                        `fixed top-4 right-4 ${colors[type]} text-white px-4 py-2 rounded-lg shadow-lg z-50 transform transition-transform duration-300`;
                    notification.textContent = message;
                    notification.style.transform = 'translateX(100%)';

                    document.body.appendChild(notification);

                    // Animate in
                    setTimeout(() => {
                        notification.style.transform = 'translateX(0)';
                    }, 10);

                    // Remove after delay
                    setTimeout(() => {
                        notification.style.transform = 'translateX(100%)';
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.parentNode.removeChild(notification);
                            }
                        }, 300);
                    }, 3000);
                },

                // ================================================================
                // SCANNER STATUS HELPERS
                // ================================================================

                getScannerStatusClass() {
                    const classes = {
                        'disconnected': 'text-gray-500',
                        'connecting': 'text-yellow-500',
                        'connected': 'text-blue-500',
                        'scanning': 'text-green-500',
                        'error': 'text-red-500'
                    };

                    return classes[this.scannerStatus] || 'text-gray-500';
                },

                getScannerStatusText() {
                    const texts = {
                        'disconnected': 'Tidak Terhubung',
                        'connecting': 'Menghubungkan...',
                        'connected': 'Terhubung',
                        'scanning': 'Aktif Scanning',
                        'error': 'Error'
                    };

                    return texts[this.scannerStatus] || 'Unknown';
                },

                getScannerTypeText() {
                    const types = {
                        'hid': 'USB HID (Keyboard)',
                        'usb': 'USB Direct',
                        'serial': 'Serial Port',
                        'auto': 'Auto Detect'
                    };

                    return types[this.scannerType] || 'Unknown';
                },

                // ================================================================
                // EXISTING METHODS (Mode control, QR Scanner, etc.)
                // ================================================================

                // Mode control
                toggleMode() {
                    this.isMultiMode = !this.isMultiMode;
                    this.clearSelection();
                    this.stopQRScanner();
                    this.stopHardwareScanner();
                    console.log('Mode switched to:', this.isMultiMode ? 'Multi' : 'Single');
                },

                // Selection management
                hasSelectedItems() {
                    return this.isMultiMode ? this.selectedItems.length > 0 : this.selectedItem !== null;
                },

                clearSelection() {
                    this.selectedItem = null;
                    this.selectedItems = [];
                    this.searchQuery = '';
                    this.searchResults = [];
                },

                clearAllItems() {
                    if (confirm('Hapus semua barang dari daftar?')) {
                        this.selectedItems = [];
                    }
                },

                removeItem(index) {
                    this.selectedItems.splice(index, 1);
                },

                isItemAlreadySelected(item) {
                    if (this.isMultiMode) {
                        return this.selectedItems.some(selected => selected.item_detail_id === item.item_detail_id);
                    } else {
                        return this.selectedItem && this.selectedItem.item_detail_id === item.item_detail_id;
                    }
                },

                addItemToSelection(item) {
                    if (this.isMultiMode) {
                        // Multi mode: add to array if not already selected
                        if (!this.isItemAlreadySelected(item)) {
                            this.selectedItems.push(item);
                            console.log('Added item to multi selection:', item.item_name);
                        } else {
                            this.showNotification('Item sudah ada dalam daftar!', 'warning');
                        }
                    } else {
                        // Single mode: replace current selection
                        this.selectedItem = item;
                        this.form.from_location = item.location || '';
                        console.log('Selected single item:', item.item_name);
                    }

                    // Clear search
                    this.searchQuery = '';
                    this.searchResults = [];
                },

                // QR Camera Scanner
                toggleQRScanner() {
                    if (this.qrScannerActive) {
                        this.stopQRScanner();
                    } else {
                        this.startQRScanner();
                    }
                },

                async startQRScanner() {
                    try {
                        const videoElement = document.getElementById('flexible-qr-scanner');

                        await this.codeReader.decodeFromVideoDevice(
                            undefined,
                            videoElement,
                            (result, error) => {
                                if (result) {
                                    console.log('QR detected:', result.text);
                                    this.handleQRScan(result.text);
                                }
                            }
                        );

                        this.qrScannerActive = true;
                        console.log('QR Camera Scanner started');

                    } catch (error) {
                        console.error('QR Scanner error:', error);
                        this.showNotification('Camera error: ' + error.message, 'error');
                    }
                },

                stopQRScanner() {
                    if (this.codeReader && this.qrScannerActive) {
                        this.codeReader.reset();
                        this.qrScannerActive = false;
                        console.log('QR Camera Scanner stopped');
                    }
                },

                async handleQRScan(qrText) {
                    try {
                        // Parse QR data
                        let qrData;
                        try {
                            qrData = JSON.parse(qrText);
                        } catch (parseError) {
                            throw new Error('QR Code format tidak valid');
                        }

                        if (qrData.type !== 'item_detail' || !qrData.item_detail_id) {
                            throw new Error('QR Code bukan untuk item detail');
                        }

                        // Create item object from QR
                        const item = {
                            item_detail_id: qrData.item_detail_id,
                            item_name: qrData.item_name,
                            item_code: qrData.item_code,
                            serial_number: qrData.serial_number,
                            item_id: qrData.item_id,
                            current_status: 'available',
                            location: 'Warehouse'
                        };

                        // Add to selection based on mode
                        this.addItemToSelection(item);

                        // In single mode, stop scanner after successful scan
                        if (!this.isMultiMode) {
                            this.stopQRScanner();
                        }

                        this.showScanSuccess(`QR Camera: ${item.item_name}`);

                    } catch (error) {
                        console.error('QR processing error:', error);
                        this.showNotification('QR Code Error: ' + error.message, 'error');
                    }
                },

                // Manual search methods
                async searchItems() {
                    if (this.searchQuery.length < 2) {
                        this.searchResults = [];
                        return;
                    }

                    try {
                        const response = await fetch('/api/requests/search-items?' + new URLSearchParams({
                            query: this.searchQuery
                        }));

                        const data = await response.json();

                        if (data.success) {
                            this.searchResults = data.items;
                        } else {
                            this.searchResults = [];
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                        this.searchResults = [];
                    }
                },

                // API Ticket Integration
                async fetchTicketIds() {
                    this.loadingTickets = true;
                    this.apiError = null;

                    try {
                        console.log('Fetching ticket IDs from API...');

                        const response = await fetch('https://befast.fiberone.net.id/api/tickets/active-ids', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            // Add timeout
                            signal: AbortSignal.timeout(10000) // 10 seconds timeout
                        });

                        if (!response.ok) {
                            throw new Error(`API Error: ${response.status} ${response.statusText}`);
                        }

                        const data = await response.json();

                        // Validate response is array
                        if (!Array.isArray(data)) {
                            throw new Error('Invalid API response format');
                        }

                        this.ticketIds = data;
                        this.lastFetched = new Date().toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        console.log(`✅ Loaded ${this.ticketIds.length} ticket IDs:`, this.ticketIds);

                    } catch (error) {
                        console.error('❌ Failed to fetch ticket IDs:', error);
                        this.apiError = error.message;
                        this.ticketIds = [];

                        // Fallback ke manual input jika API gagal
                        this.showTicketApiError(error.message);
                    } finally {
                        this.loadingTickets = false;
                    }
                },

                showTicketApiError(errorMessage) {
                    console.warn('API Ticket Error:', errorMessage);

                    // Auto-switch ke manual input jika API fail
                    setTimeout(() => {
                        if (this.ticketIds.length === 0) {
                            this.form.reference_type = 'manual';
                            this.showNotification(
                                '❌ Gagal memuat ticket dari API. Silakan input manual atau pilih tipe reference lain.',
                                'warning');
                        }
                    }, 2000);
                },

                handleReferenceTypeChange() {
                    // Reset reference_id when type changes
                    this.form.reference_id = '';
                    this.apiError = null;

                    // Auto-fetch tickets jika pilih type ticket
                    if (this.form.reference_type === 'ticket') {
                        // Only fetch if not already loaded or if last fetch was > 5 minutes ago
                        const shouldFetch = this.ticketIds.length === 0 ||
                            !this.lastFetched ||
                            this.isDataStale();

                        if (shouldFetch) {
                            this.fetchTicketIds();
                        }
                    }
                },

                isDataStale() {
                    if (!this.lastFetched) return true;

                    const now = new Date();
                    const lastFetch = new Date();
                    const [hours, minutes] = this.lastFetched.split(':');
                    lastFetch.setHours(parseInt(hours), parseInt(minutes), 0, 0);

                    // Consider data stale after 5 minutes
                    return (now - lastFetch) > 5 * 60 * 1000;
                },

                getReferenceIdPlaceholder() {
                    const placeholders = {
                        'po': 'Nomor Purchase Order',
                        'gr': 'Nomor Goods Received',
                        'maintenance': 'ID Maintenance Request',
                        'project': 'Kode Project',
                        'manual': 'Input manual reference ID',
                        '': 'Pilih reference type terlebih dahulu'
                    };

                    return placeholders[this.form.reference_type] ;
                },

                // Form submission
                async submitFlexibleTransaction() {
                    if (!this.hasSelectedItems() || !this.form.transaction_type) {
                        this.showNotification('Pilih barang dan tipe transaksi terlebih dahulu', 'warning');
                        return;
                    }

                    this.loading = true;

                    try {
                        let payload;

                        if (this.isMultiMode) {
                            // Multi-item payload
                            payload = {
                                transaction_type: this.form.transaction_type,
                                reference_type: this.form.reference_type || null,
                                reference_id: this.form.reference_id || null,
                                from_location: this.form.from_location || null,
                                to_location: this.form.to_location || null,
                                notes: this.form.notes || null,
                                items: this.selectedItems.map(item => ({
                                    item_detail_id: item.item_detail_id,
                                    notes: null
                                }))
                            };
                        } else {
                            // Single-item payload
                            payload = {
                                transaction_type: this.form.transaction_type,
                                reference_type: this.form.reference_type || null,
                                reference_id: this.form.reference_id || null,
                                from_location: this.form.from_location || null,
                                to_location: this.form.to_location || null,
                                notes: this.form.notes || null,
                                item_detail_id: this.selectedItem.item_detail_id
                            };
                        }

                        console.log('Submitting flexible transaction:', payload);

                        const response = await fetch('{{ route('transactions.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();
                        console.log('Response:', data);

                        if (response.ok && data.success) {
                            const itemsCount = data.transaction?.items_count || 1;
                            const transactionType = data.transaction?.type || 'single';

                            let successMessage = '✅ Transaksi berhasil dibuat!\n\n';
                            successMessage += `Transaction ID: ${data.transaction.transaction_id}\n`;
                            successMessage += `Type: ${transactionType.toUpperCase()}\n`;
                            successMessage += `Items: ${itemsCount} barang\n`;

                            // Show reference info if available
                            if (this.form.reference_id) {
                                successMessage += `Reference: ${this.form.reference_id}\n`;
                            }

                            successMessage += '\n' + data.message;

                            alert(successMessage);

                            // Cleanup before redirect
                            this.cleanup();

                            // Redirect to transactions list
                            window.location.href = '{{ route('transactions.index') }}';
                        } else {
                            this.showNotification('❌ Error: ' + (data.message || 'Failed to create transaction'),
                                'error');
                        }

                    } catch (error) {
                        console.error('Submit error:', error);
                        this.showNotification('❌ Error: ' + error.message, 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                resetForm() {
                    this.clearSelection();
                    this.stopQRScanner();
                    this.stopHardwareScanner();

                    this.form = {
                        transaction_type: '',
                        reference_type: '',
                        reference_id: '',
                        from_location: '',
                        to_location: '',
                        notes: ''
                    };

                    // Clear API data
                    this.apiError = null;
                    this.lastScannedCode = null;

                    this.showNotification('Form telah direset', 'info');
                }
            }
        }
    </script>
