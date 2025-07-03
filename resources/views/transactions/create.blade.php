@extends('layouts.app')

@section('title', 'Buat Transaksi - LogistiK Admin')

@section('content')
<div x-data="flexibleTransactionCreate()" class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-lg flex items-center justify-center">
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
                    <button @click="toggleMode()"
                            :class="isMultiMode ? 'bg-purple-600' : 'bg-gray-300'"
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
            <span class="font-medium text-gray-900" x-text="isMultiMode ? 'Multi Item Mode' : 'Single Item Mode'"></span>
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

        <!-- Method Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <button @click="inputMethod = 'qr'"
                    :class="inputMethod === 'qr' ? 'border-red-500 bg-red-50' : 'border-gray-200'"
                    class="border-2 rounded-lg p-4 text-left transition-all hover:border-gray-300">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-qrcode text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Scan QR Code</h3>
                        <p class="text-sm text-gray-600">Scan QR pada barang</p>
                    </div>
                </div>
            </button>

            <button @click="inputMethod = 'manual'"
                    :class="inputMethod === 'manual' ? 'border-red-500 bg-red-50' : 'border-gray-200'"
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

        <!-- QR Scanner -->
        <div x-show="inputMethod === 'qr'" class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-medium text-gray-900">QR Scanner</h3>
                <button @click="toggleQRScanner()"
                        :class="qrScannerActive ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                        class="px-4 py-2 text-white rounded-lg">
                    <span x-text="qrScannerActive ? 'Stop Scanner' : 'Start Scanner'"></span>
                </button>
            </div>

            <div x-show="qrScannerActive" class="flex justify-center mb-4">
                <video id="flexible-qr-scanner"
                       width="400"
                       height="300"
                       style="border: 2px solid #ccc; border-radius: 8px; background: #000;">
                </video>
            </div>
        </div>

        <!-- Manual Search -->
        <div x-show="inputMethod === 'manual'">
            <div class="mb-4">
                <input type="text"
                       x-model="searchQuery"
                       @input="searchItems()"
                       placeholder="Cari barang..."
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
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
                                    <p class="text-sm text-gray-600" x-text="item.item_code + ' • ' + item.serial_number"></p>
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
                    <p class="text-sm text-gray-600" x-text="selectedItem?.item_code + ' • ' + selectedItem?.serial_number"></p>
                </div>
            </div>
            <button @click="clearSelection()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Multi Mode: Selected Items -->
    <div x-show="isMultiMode && selectedItems.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
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
    <form x-show="hasSelectedItems()"
          @submit.prevent="submitFlexibleTransaction()"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

        <h2 class="text-lg font-semibold text-gray-900 mb-6">Detail Transaksi</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Transaction Type -->
            <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Transaksi *</label>

    <!-- hidden input supaya dikirim ke server -->
    <input type="hidden" name="transaction_type" :value="form.transaction_type">

    <select disabled
            x-model="form.transaction_type"
            required
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
</div>


           <!-- Reference Type & ID Section - UPDATED -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Reference Type</label>
                <select x-model="form.reference_type"
                        @change="handleReferenceTypeChange()"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                    <option value="">Pilih Reference Type</option>
                    <option value="ticket">Ticket ID (External API)</option>
                    <option value="manual">Manual Input</option>
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
                        <button type="button"
                                @click="fetchTicketIds()"
                                :disabled="loadingTickets"
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
                <input x-show="form.reference_type !== 'ticket'"
                       type="text"
                       x-model="form.reference_id"
                       :placeholder="getReferenceIdPlaceholder()"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
            </div>
            <!-- From Location -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Asal</label>
                <input type="text"
                       x-model="form.from_location"
                       placeholder="Lokasi asal barang"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
            </div>

            <!-- To Location -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Tujuan</label>
                <input type="text"
                       x-model="form.to_location"
                       placeholder="Lokasi tujuan barang"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
            </div>
        </div>

        <!-- Notes -->
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
            <textarea x-model="form.notes"
                      rows="3"
                      placeholder="Catatan untuk transaksi ini..."
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"></textarea>
        </div>

        <!-- Submit -->
        <div class="mt-8 flex items-center justify-end space-x-3">
            <button type="button"
                    @click="resetForm()"
                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Reset
            </button>
            <button type="submit"
                    :disabled="loading"
                    class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-50">
                <span x-show="!loading" class="flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    <span x-text="isMultiMode ? `Buat Transaksi (${selectedItems.length} items)` : 'Buat Transaksi'"></span>
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
        // Mode control
        isMultiMode: false,

        // Input method
        inputMethod: 'manual',

        // QR Scanner
        qrScannerActive: false,
        codeReader: null,

        // Single mode
        selectedItem: null,

        // Multi mode
        selectedItems: [],

        // Search
        searchQuery: '',
        searchResults: [],

        // API Ticket Integration - NEW
        ticketIds: [],
        loadingTickets: false,
        apiError: null,
        lastFetched: null,

        // Form - UPDATED dengan reference_type
        form: {
            transaction_type: '{{ request()->get("type") ?? "" }}',
            reference_type: '',
            reference_id: '',
            from_location: '',
            to_location: '',
            notes: ''
        },

        loading: false,

        // Initialize
        init() {
            console.log('=== FLEXIBLE TRANSACTION CREATE INIT ===');
            this.codeReader = new ZXing.BrowserQRCodeReader();
        },

        // ================================================================
        // API TICKET METHODS - NEW
        // ================================================================

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
            // Optional: Show toast atau notification
            console.warn('API Ticket Error:', errorMessage);

            // Auto-switch ke manual input jika API fail
            setTimeout(() => {
                if (this.ticketIds.length === 0) {
                    this.form.reference_type = 'manual';
                    alert('❌ Gagal memuat ticket dari API. Silakan input manual atau pilih tipe reference lain.');
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

            return placeholders[this.form.reference_type] || 'Reference ID';
        },

        // ================================================================
        // EXISTING METHODS (unchanged)
        // ================================================================

        // Mode control
        toggleMode() {
            this.isMultiMode = !this.isMultiMode;
            this.clearSelection();
            this.stopQRScanner();
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
                    alert('Item sudah ada dalam daftar!');
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

        // QR Scanner
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
                console.log('Flexible QR Scanner started');

            } catch (error) {
                console.error('QR Scanner error:', error);
                alert('Camera error: ' + error.message);
            }
        },

        stopQRScanner() {
            if (this.codeReader && this.qrScannerActive) {
                this.codeReader.reset();
                this.qrScannerActive = false;
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

            } catch (error) {
                console.error('QR processing error:', error);
                alert('QR Code Error: ' + error.message);
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

        selectItem(item) {
            this.selectedItem = item;
            this.form.from_location = item.location || '';
        },
        // Form submission - UPDATED dengan reference_type
        async submitFlexibleTransaction() {
            if (!this.hasSelectedItems() || !this.form.transaction_type) {
                alert('Pilih barang dan tipe transaksi terlebih dahulu');
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

                const response = await fetch('{{ route("transactions.store") }}', {
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
                    window.location.href = '/transactions/';
                } else {
                    alert('❌ Error: ' + (data.message || 'Failed to create transaction'));
                }

            } catch (error) {
                console.error('Submit error:', error);
                alert('❌ Error: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        resetForm() {
            this.clearSelection();
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
        }
    }
}
</script>
@endpush
