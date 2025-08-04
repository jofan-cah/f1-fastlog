@extends('layouts.app')

@section('title', 'Buat Transaksi - LogistiK Admin')

@section('content')
    <div x-data="simplifiedTransactionCreate()" class="space-y-6">

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
                        <p class="text-sm text-gray-600">Scan atau pilih barang untuk transaksi</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('transactions.index') }}"
                        class="text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Add Items Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Tambah Barang</h2>

            <!-- Method Selection -->
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
                    <video id="qr-scanner" width="400" height="300"
                        style="border: 2px solid #ccc; border-radius: 8px; background: #000;">
                    </video>
                </div>
            </div>

            <!-- Hardware Scanner Section -->
            <div x-show="inputMethod === 'hardware'" class="mb-6">
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                    <!-- Scanner Status -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-barcode text-purple-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Hardware Scanner</h3>
                                <div class="flex items-center space-x-2 text-sm">
                                    <span class="text-gray-600">Status:</span>
                                    <span :class="getScannerStatusClass()" class="font-medium"
                                        x-text="getScannerStatusText()"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Scanner Controls -->
                        <div class="flex items-center space-x-2">
                            <button x-show="scannerStatus === 'disconnected'" @click="connectHardwareScanner()"
                                :disabled="loading"
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white rounded-lg text-sm">
                                <i class="fas fa-plug mr-2"></i>
                                Connect
                            </button>

                            <button x-show="scannerStatus === 'connected' || scannerStatus === 'scanning'"
                                @click="toggleHardwareScanner()"
                                :class="hardwareScannerActive ? 'bg-red-600 hover:bg-red-700' :
                                    'bg-green-600 hover:bg-green-700'"
                                class="px-4 py-2 text-white rounded-lg text-sm">
                                <i :class="hardwareScannerActive ? 'fas fa-stop' : 'fas fa-play'" class="mr-2"></i>
                                <span x-text="hardwareScannerActive ? 'Stop' : 'Start'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Scanning Status -->
                    <div x-show="hardwareScannerActive" class="text-center py-8">
                        <div class="inline-flex items-center space-x-3">
                            <div class="w-8 h-8 border-4 border-purple-600 border-t-transparent rounded-full animate-spin">
                            </div>
                            <span class="text-purple-700 font-medium">Ready to scan...</span>
                        </div>

                        <div x-show="lastScannedCode" class="mt-4 p-3 bg-white rounded-lg border">
                            <div class="text-sm text-gray-600">Last scanned:</div>
                            <div class="font-mono text-sm text-gray-900 break-all" x-text="lastScannedCode"></div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div x-show="!hardwareScannerActive" class="bg-white rounded-lg p-4 border border-purple-200">
                        <h4 class="font-medium text-gray-900 mb-2">
                            <i class="fas fa-info-circle text-purple-600 mr-2"></i>
                            Petunjuk
                        </h4>
                        <div class="text-sm text-gray-600 space-y-1">
                            <div>1. Hubungkan scanner ke komputer</div>
                            <div>2. Klik "Connect" untuk menghubungkan</div>
                            <div>3. Klik "Start" untuk mulai scanning</div>
                            <div>4. Arahkan scanner ke barcode/QR code</div>
                        </div>
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

        <!-- Selected Items -->
        <div x-show="selectedItems.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-medium text-gray-900">
                    Barang Dipilih (<span x-text="selectedItems.length"></span>)
                </h3>
                <button @click="clearAllItems()" class="text-red-600 hover:text-red-800 text-sm">
                    <i class="fas fa-trash mr-1"></i>
                    Hapus Semua
                </button>
            </div>

            <div class="space-y-2 max-h-64 overflow-y-auto">
                <template x-for="(item, index) in selectedItems" :key="index">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cube text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900" x-text="item.item_name"></h4>
                                <p class="text-sm text-gray-600" x-text="item.item_code + ' • ' + item.serial_number"></p>
                                <p class="text-xs text-gray-500" x-text="'Status: ' + item.current_status"></p>
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
        <form x-show="selectedItems.length > 0" @submit.prevent="submitTransaction()"
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Detail Transaksi</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Transaction Type - Fixed or Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Transaksi</label>

                    <!-- Fixed Transaction Type (when comes from URL) -->
                    <div x-show="isTransactionTypeFixed()">
                        <!-- Hidden input for form submission -->
                        <input type="hidden" name="transaction_type" :value="form.transaction_type">

                        <div class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-medium text-gray-900"
                                        x-text="getTransactionTypeInfo(form.transaction_type).label"></span>
                                    <p class="text-sm text-gray-600 mt-1"
                                        x-text="getTransactionTypeInfo(form.transaction_type).description"></p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-medium"
                                        x-text="form.transaction_type"></span>
                                    <i class="fas fa-lock text-gray-400" title="Fixed from URL parameter"></i>
                                </div>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Tipe transaksi sudah ditentukan dari URL parameter
                        </div>
                    </div>

                    <!-- Dropdown Transaction Type (when not fixed) -->
                    <div x-show="!isTransactionTypeFixed()">
                        <select x-model="form.transaction_type" @change="handleTransactionTypeChange()"
                            :required="!isTransactionTypeFixed()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                            <option value="">Pilih Tipe Transaksi</option>
                            <template x-for="type in availableTransactionTypes" :key="type.value">
                                <option :value="type.value" x-text="type.label + ' - ' + type.description"></option>
                            </template>
                        </select>

                        <!-- Transaction Type Info -->
                        <div x-show="form.transaction_type" class="mt-2 p-2 bg-gray-50 rounded text-sm">
                            <template x-for="type in availableTransactionTypes" :key="type.value">
                                <div x-show="form.transaction_type === type.value" class="flex items-center space-x-2">
                                    <i class="fas fa-info-circle text-blue-500"></i>
                                    <span x-text="type.description" class="text-gray-700"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Reference Type -->
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
                                <option value="" disabled selected>
                                    <template x-if="!ticketIds.length && !loadingTickets">
                                        <span>Pilih Ticket ID</span>
                                    </template>
                                    <template x-if="loadingTickets">
                                        <span>Loading tickets...</span>
                                    </template>
                                    <template x-if="ticketIds.length && !loadingTickets">
                                        <span>Pilih dari daftar ticket</span>
                                    </template>
                                </option>

                                <template x-for="ticket in ticketIds" :key="ticket.ticket_id">
                                    <option :value="ticket.ticket_id"
                                        x-text="ticket.subs_name ? `${ticket.ticket_id} - ${ticket.subs_name} - ${ticket.subscription_id}` : ticket.ticket_id">
                                    </option>
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

                <!-- DAMAGED Fields (show only for DAMAGED type) -->
                <div x-show="form.transaction_type === 'DAMAGED'" class="md:col-span-2">
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                        <div class="md:col-span-2">
                            <h4 class="font-medium text-orange-900 mb-3 flex items-center">
                                <i class="fas fa-exclamation-triangle text-orange-600 mr-2"></i>
                                Detail Kerusakan
                            </h4>
                        </div>

                        <!-- Damage Level -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Level Kerusakan <span class="text-red-500">*</span>
                            </label>
                            <select x-model="form.damage_level" :required="form.transaction_type === 'DAMAGED'"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                                <option value="">Pilih level kerusakan...</option>
                                <option value="light">Ringan - Kerusakan kecil, mudah diperbaiki</option>
                                <option value="medium">Sedang - Perlu repair khusus</option>
                                <option value="heavy">Berat - Repair mahal/sulit</option>
                                <option value="total">Total - Tidak bisa diperbaiki</option>
                            </select>
                        </div>

                        <!-- Damage Reason -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Alasan Kerusakan <span class="text-red-500">*</span>
                            </label>
                            <select x-model="form.damage_reason" :required="form.transaction_type === 'DAMAGED'"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                                <option value="">Pilih alasan...</option>
                                <option value="accident">Kecelakaan/Terjatuh</option>
                                <option value="wear">Keausan Normal</option>
                                <option value="misuse">Pemakaian Salah</option>
                                <option value="environment">Faktor Lingkungan</option>
                                <option value="manufacturing">Cacat Produksi</option>
                                <option value="electrical">Kerusakan Elektrik</option>
                                <option value="mechanical">Kerusakan Mekanik</option>
                                <option value="water_damage">Kerusakan Air</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>

                        <!-- Repair Estimate (show only for heavy damage) -->
                        <div x-show="form.damage_level === 'heavy'" class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Estimasi Biaya Repair <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                <input type="number" x-model="form.repair_estimate"
                                    :required="form.transaction_type === 'DAMAGED' && form.damage_level === 'heavy'"
                                    placeholder="0"
                                    class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                            </div>
                            <p class="text-xs text-gray-600 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Wajib diisi untuk kerusakan berat
                            </p>
                        </div>
                    </div>
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

                <!-- Kondisi (show only for certain transaction types) -->
                <div x-show="['IN', 'OUT', 'RETURN'].includes(form.transaction_type)">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kondisi Barang</label>
                    <select x-model="form.kondisi"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        <option value="good">Normal</option>
                        <option value="no_good">Observasi </option>
                    </select>
                </div>

                <!-- Lost Reason (show only for LOST type) -->
                <div x-show="form.transaction_type === 'LOST'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Hilang <span
                            class="text-red-500">*</span></label>
                    <select x-model="form.lost_reason" :required="form.transaction_type === 'LOST'"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        <option value="">Pilih alasan...</option>
                        <option value="stolen">Dicuri/Hilang</option>
                        <option value="damaged">Rusak Total</option>
                        <option value="misplaced">Salah Tempat/Tidak Ditemukan</option>
                        <option value="accident">Kecelakaan/Bencana</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
            </div>

            <!-- Notes -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan
                    <span x-show="form.transaction_type === 'LOST'" class="text-red-500">*</span>
                </label>
                <textarea x-model="form.notes" rows="3"
                    :placeholder="form.transaction_type === 'LOST' ? 'Jelaskan detail kronologi barang hilang...' :
                        'Catatan untuk transaksi ini...'"
                    :required="form.transaction_type === 'LOST'"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"></textarea>

                <!-- Notes Helper Text -->
                <div x-show="form.transaction_type === 'LOST'" class="mt-1 text-xs text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Catatan detail wajib untuk transaksi barang hilang
                </div>
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
                            x-text="selectedItems.length === 1 ? 'Buat Transaksi (1 item)' : `Buat Transaksi (${selectedItems.length} items)`"></span>
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
        function simplifiedTransactionCreate() {
            return {
                // Basic properties
                inputMethod: 'manual', // 'qr', 'hardware', 'manual'
                selectedItems: [],

                // QR Scanner
                qrScannerActive: false,
                codeReader: null,

                // Hardware Scanner
                hardwareScannerActive: false,
                scannerStatus: 'disconnected', // 'disconnected', 'connected', 'scanning'
                lastScannedCode: null,
                scanBuffer: '',
                scanTimeout: null,
                isProcessingScan: false,

                // Search
                searchQuery: '',
                searchResults: [],

                // API Ticket Integration
                ticketIds: [],
                loadingTickets: false,
                apiError: null,
                lastFetched: null,

                // Available Transaction Types
                availableTransactionTypes: [{
                        value: 'IN',
                        label: 'Barang Masuk',
                        description: 'Penerimaan barang baru dari supplier/vendor'
                    },
                    {
                        value: 'OUT',
                        label: 'Barang Keluar',
                        description: 'Pengiriman/distribusi barang ke customer/lokasi'
                    },
                    {
                        value: 'REPAIR',
                        label: 'Barang Repair',
                        description: 'Barang yang perlu diperbaiki atau maintenance'
                    },
                    {
                        value: 'LOST',
                        label: 'Barang Hilang',
                        description: 'Barang yang hilang atau rusak total'
                    },
                    {
                        value: 'RETURN',
                        label: 'Barang Kembali',
                        description: 'Pengembalian barang dari repair/customer'
                    },
                    {
                        value: 'MOVE',
                        label: 'Pindah Lokasi',
                        description: 'Perpindahan barang antar lokasi/gudang'
                    },
                    {
                        value: 'MAINTENANCE',
                        label: 'Maintenance',
                        description: 'Perawatan rutin dan preventive maintenance'
                    },
                    {
                        value: 'DAMAGED', // 🆕 BARU - TAMBAH INI
                        label: 'Barang Rusak',
                        description: 'Barang yang mengalami kerusakan dan perlu evaluasi'
                    },
                ],

                // Form
                form: {
                    transaction_type: '{{ request()->get('type') ?? '' }}',
                    reference_type: '',
                    reference_id: '',
                    from_location: '',
                    to_location: '',
                    notes: '',
                    kondisi: 'good',
                    lost_reason: '',
                    damage_level: '', // light, medium, heavy, total
                    damage_reason: '', // accident, wear, misuse, etc
                    repair_estimate: '' // estimasi biaya repair
                },

                loading: false,

                // ================================================================
                // INITIALIZATION
                // ================================================================

                async init() {
                    console.log('=== SIMPLIFIED TRANSACTION INIT ===');

                    try {
                        // Set initial transaction type from URL
                        this.setInitialTransactionType();

                        // Initialize QR Scanner
                        this.initQRScanner();

                        // Initialize Hardware Scanner
                        await this.initHardwareScanner();

                        // Setup event listeners
                        this.setupEventListeners();

                        console.log('✅ Initialization completed');
                    } catch (error) {
                        console.error('❌ Init error:', error);
                    }
                },

                setInitialTransactionType() {
                    // Get dari URL parameter
                    const urlParams = new URLSearchParams(window.location.search);
                    const typeFromUrl = urlParams.get('type');

                    // Get dari template variable jika ada
                    const typeFromTemplate = '{{ request()->get('type') ?? '' }}';

                    // Priority: URL > Template > Current form value
                    const initialType = typeFromUrl || typeFromTemplate || this.form.transaction_type || '';

                    console.log('🎯 Initial transaction type:', initialType);

                    // Validate transaction type
                    if (initialType && this.isValidTransactionType(initialType)) {
                        this.form.transaction_type = initialType;

                        // Auto-set locations dan trigger change handler
                        this.handleTransactionTypeChange();

                        // Show info untuk fixed type
                        if (typeFromUrl) {
                            setTimeout(() => {
                                const typeInfo = this.getTransactionTypeInfo(initialType);
                                this.showNotification(
                                    `🔒 Transaction type fixed: ${typeInfo.label}`,
                                    'info'
                                );
                            }, 1000);
                        }
                    } else if (initialType) {
                        console.warn('⚠️ Invalid transaction type from URL:', initialType);
                        this.showNotification('Invalid transaction type from URL', 'warning');
                    }
                },

                isTransactionTypeFixed() {
                    // Check if transaction type came from URL parameter
                    const urlParams = new URLSearchParams(window.location.search);
                    const typeFromUrl = urlParams.get('type');

                    return typeFromUrl && this.isValidTransactionType(typeFromUrl);
                },

                initQRScanner() {
                    try {
                        this.codeReader = new ZXing.BrowserQRCodeReader();
                        console.log('✅ QR Scanner initialized');
                    } catch (error) {
                        console.error('❌ QR Scanner init failed:', error);
                        this.showNotification('QR Scanner tidak tersedia', 'warning');
                    }
                },

                async initHardwareScanner() {
                    console.log('🔍 Initializing hardware scanner...');
                    this.setupKeyboardListener();
                },

                setupEventListeners() {
                    // Cleanup on page unload
                    window.addEventListener('beforeunload', () => {
                        this.cleanup();
                    });
                },

                setupKeyboardListener() {
                    document.addEventListener('keydown', (event) => {
                        if (this.hardwareScannerActive) {
                            this.handleKeyboardInput(event);
                        }
                    });
                },

                cleanup() {
                    this.stopQRScanner();
                    this.stopHardwareScanner();
                    if (this.scanTimeout) {
                        clearTimeout(this.scanTimeout);
                    }
                },

                // ================================================================
                // ITEM SELECTION
                // ================================================================

                addItemToSelection(item) {
                    if (!this.isItemAlreadySelected(item)) {
                        this.selectedItems.push(item);
                        console.log('Added item:', item.item_name);

                        // Set from_location dari item pertama
                        if (this.selectedItems.length === 1) {
                            this.form.from_location = item.location || '';
                        }

                        this.showNotification(`✅ ${item.item_name} ditambahkan`, 'success');
                    } else {
                        this.showNotification('Item sudah ada dalam daftar!', 'warning');
                    }

                    // Clear search
                    this.searchQuery = '';
                    this.searchResults = [];
                },

                isItemAlreadySelected(item) {
                    return this.selectedItems.some(selected => selected.item_detail_id === item.item_detail_id);
                },

                removeItem(index) {
                    const item = this.selectedItems[index];
                    this.selectedItems.splice(index, 1);
                    this.showNotification(`${item.item_name} dihapus dari daftar`, 'info');
                },

                clearAllItems() {
                    if (confirm('Hapus semua barang dari daftar?')) {
                        this.selectedItems = [];
                        this.showNotification('Semua item dihapus', 'info');
                    }
                },

                // ================================================================
                // QR SCANNER
                // ================================================================

                toggleQRScanner() {
                    if (this.qrScannerActive) {
                        this.stopQRScanner();
                    } else {
                        this.startQRScanner();
                    }
                },

                async startQRScanner() {
                    try {
                        console.log('📹 Starting QR scanner...');

                        const videoElement = document.getElementById('qr-scanner');
                        if (!videoElement) {
                            throw new Error('Video element not found');
                        }

                        await this.codeReader.decodeFromVideoDevice(
                            undefined,
                            videoElement,
                            (result, error) => {
                                if (result && !this.isProcessingScan) {
                                    console.log('📹 QR detected:', result.text);
                                    this.handleQRScan(result.text);
                                }
                            }
                        );

                        this.qrScannerActive = true;
                        this.showNotification('📹 QR Scanner aktif', 'success');
                    } catch (error) {
                        console.error('❌ QR Scanner error:', error);
                        this.showNotification('Camera error: ' + error.message, 'error');
                    }
                },

                stopQRScanner() {
                    if (this.codeReader && this.qrScannerActive) {
                        this.codeReader.reset();
                        this.qrScannerActive = false;
                        console.log('QR Scanner stopped');
                    }
                },

                async handleQRScan(qrText) {
                    if (this.isProcessingScan) return;
                    this.isProcessingScan = true;

                    try {
                        let qrData = JSON.parse(qrText);

                        if (qrData.type !== 'item_detail' || !qrData.item_detail_id) {
                            throw new Error('QR Code format tidak valid');
                        }

                        const item = {
                            item_detail_id: qrData.item_detail_id,
                            item_name: qrData.item_name || 'Unknown Item',
                            item_code: qrData.item_code || 'N/A',
                            serial_number: qrData.serial_number || 'N/A',
                            item_id: qrData.item_id || null,
                            current_status: qrData.current_status || 'available',
                            location: qrData.location || 'Warehouse'
                        };

                        this.addItemToSelection(item);
                        this.playBeepSound();

                    } catch (error) {
                        console.error('QR processing error:', error);
                        this.showNotification('QR Error: ' + error.message, 'error');
                    } finally {
                        setTimeout(() => {
                            this.isProcessingScan = false;
                        }, 1000);
                    }
                },

                // ================================================================
                // HARDWARE SCANNER
                // ================================================================

                async connectHardwareScanner() {
                    this.scannerStatus = 'connected';
                    this.showNotification('📱 Hardware scanner connected', 'success');
                },

                toggleHardwareScanner() {
                    if (this.hardwareScannerActive) {
                        this.stopHardwareScanner();
                    } else {
                        this.startHardwareScanner();
                    }
                },

                startHardwareScanner() {
                    this.hardwareScannerActive = true;
                    this.scannerStatus = 'scanning';
                    this.resetScanBuffer();
                    this.showNotification('📱 Hardware scanner aktif', 'success');
                },

                stopHardwareScanner() {
                    this.hardwareScannerActive = false;
                    this.scannerStatus = 'connected';
                    this.resetScanBuffer();
                },

                handleKeyboardInput(event) {
                    if (this.isProcessingScan) {
                        event.preventDefault();
                        return;
                    }

                    if (event.key === 'Enter') {
                        if (this.scanBuffer.trim()) {
                            this.isProcessingScan = true;
                            const scanData = this.scanBuffer.trim();
                            this.lastScannedCode = scanData;

                            setTimeout(() => {
                                this.processScanData(scanData);
                                this.resetScanBuffer();
                            }, 100);

                            event.preventDefault();
                        }
                        return;
                    }

                    if (event.key.length === 1) {
                        event.preventDefault();
                        this.scanBuffer += event.key;

                        if (this.scanTimeout) {
                            clearTimeout(this.scanTimeout);
                        }

                        this.scanTimeout = setTimeout(() => {
                            this.resetScanBuffer();
                        }, 2000);
                    }
                },

                resetScanBuffer() {
                    this.scanBuffer = '';
                    this.isProcessingScan = false;
                    if (this.scanTimeout) {
                        clearTimeout(this.scanTimeout);
                        this.scanTimeout = null;
                    }
                },

                async processScanData(scanData) {
                    try {
                        // Try QR format first
                        try {
                            const qrData = JSON.parse(scanData);
                            if (qrData.type === 'item_detail' && qrData.item_detail_id) {
                                await this.processQRData(qrData);
                                return;
                            }
                        } catch (e) {
                            // Not JSON, try barcode
                        }

                        // Try barcode lookup
                        await this.processBarcodeData(scanData);

                    } catch (error) {
                        console.error('Scan processing error:', error);
                        this.showNotification('Scan Error: ' + error.message, 'error');
                    } finally {
                        setTimeout(() => {
                            this.isProcessingScan = false;
                        }, 1000);
                    }
                },

                async processQRData(qrData) {
                    const item = {
                        item_detail_id: qrData.item_detail_id,
                        item_name: qrData.item_name || 'Unknown Item',
                        item_code: qrData.item_code || 'N/A',
                        serial_number: qrData.serial_number || 'N/A',
                        item_id: qrData.item_id || null,
                        current_status: qrData.current_status || 'available',
                        location: qrData.location || 'Warehouse'
                    };

                    this.addItemToSelection(item);
                    this.playBeepSound();
                },

                async processBarcodeData(barcodeData) {
                    this.loading = true;

                    try {
                        const response = await fetch('/api/items/search-by-barcode?' + new URLSearchParams({
                            barcode: barcodeData,
                            include_details: 'true'
                        }));

                        if (!response.ok) {
                            throw new Error(`API Error: ${response.status}`);
                        }

                        const data = await response.json();

                        if (data.success && data.item) {
                            this.addItemToSelection(data.item);
                            this.playBeepSound();
                        } else {
                            throw new Error('Item tidak ditemukan untuk barcode: ' + barcodeData);
                        }
                    } catch (error) {
                        console.error('Barcode lookup error:', error);
                        this.showNotification('Barcode Error: ' + error.message, 'error');

                        // Fallback ke manual search
                        this.inputMethod = 'manual';
                        this.searchQuery = barcodeData;
                        this.searchItems();
                    } finally {
                        this.loading = false;
                    }
                },

                // ================================================================
                // MANUAL SEARCH
                // ================================================================

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

                // ================================================================
                // FORM SUBMISSION
                // ================================================================

                async submitTransaction() {
                    // Enhanced validation
                    const errors = this.validateForm();
                    if (errors.length > 0) {
                        this.showNotification('Error: ' + errors.join(', '), 'error');
                        return;
                    }

                    this.loading = true;

                    try {
                        const payload = {
                            transaction_type: this.form.transaction_type,
                            reference_type: this.form.reference_type || null,
                            reference_id: this.form.reference_id || null,
                            from_location: this.form.from_location || null,
                            to_location: this.form.to_location || null,
                            notes: this.form.notes || null,
                            kondisi: this.form.kondisi || 'good',
                            lost_reason: this.form.lost_reason || null,
                            // 🆕 TAMBAH DAMAGED FIELDS KE PAYLOAD
                            damage_level: this.form.damage_level || null,
                            damage_reason: this.form.damage_reason || null,
                            repair_estimate: this.form.repair_estimate || null,
                            items: this.selectedItems.map(item => ({
                                item_detail_id: item.item_detail_id,
                                notes: null
                            }))
                        };

                        console.log('Submitting transaction:', payload);

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

                        if (response.ok && data.success) {
                            const itemsCount = this.selectedItems.length;
                            const typeInfo = this.getTransactionTypeInfo(this.form.transaction_type);

                            let successMessage = '✅ Transaksi berhasil dibuat!\n\n';
                            successMessage += `Transaction ID: ${data.transaction.transaction_id}\n`;
                            successMessage += `Type: ${typeInfo.label}\n`;
                            successMessage += `Items: ${itemsCount} barang\n`;

                            // 🆕 TAMBAH DAMAGE INFO KE SUCCESS MESSAGE
                            if (this.form.transaction_type === 'DAMAGED' && this.form.damage_level) {
                                successMessage += `Damage Level: ${this.form.damage_level}\n`;
                                successMessage += `Damage Reason: ${this.form.damage_reason}\n`;
                            }
                            if (this.form.reference_id) {
                                successMessage += `Reference: ${this.form.reference_id}\n`;
                            }

                            if (this.form.from_location && this.form.to_location) {
                                successMessage += `Route: ${this.form.from_location} → ${this.form.to_location}\n`;
                            }

                            alert(successMessage);

                            // Redirect to transactions list
                            window.location.href = '{{ route('transactions.index') }}';
                        } else {
                            this.showNotification('Error: ' + (data.message || 'Failed to create transaction'),
                                'error');
                        }

                    } catch (error) {
                        console.error('Submit error:', error);
                        this.showNotification('Error: ' + error.message, 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                validateForm() {
                    const errors = [];

                    // Check transaction type
                    if (!this.form.transaction_type) {
                        errors.push('Pilih tipe transaksi');
                    } else if (!this.isValidTransactionType(this.form.transaction_type)) {
                        errors.push('Tipe transaksi tidak valid');
                    }

                    // Check selected items
                    if (this.selectedItems.length === 0) {
                        errors.push('Pilih minimal 1 barang');
                    }

                    // Check locations untuk tipe tertentu
                    if (['IN', 'OUT', 'MOVE', 'REPAIR', 'RETURN'].includes(this.form.transaction_type)) {
                        if (!this.form.from_location?.trim()) {
                            errors.push('Lokasi asal diperlukan untuk tipe transaksi ini');
                        }
                        if (!this.form.to_location?.trim()) {
                            errors.push('Lokasi tujuan diperlukan untuk tipe transaksi ini');
                        }
                    }

                    // Check reference untuk tipe OUT
                    if (this.form.transaction_type === 'OUT' && this.form.reference_type === 'ticket' && !this.form
                        .reference_id) {
                        errors.push('Reference ID diperlukan untuk barang keluar');
                    }

                    // Check lost reason untuk LOST type
                    if (this.form.transaction_type === 'LOST') {
                        if (!this.form.lost_reason) {
                            errors.push('Alasan hilang diperlukan');
                        }
                        if (!this.form.notes?.trim()) {
                            errors.push('Catatan detail diperlukan untuk barang hilang');
                        }
                    }

                    // 🆕 TAMBAH VALIDATION UNTUK DAMAGED
                    if (this.form.transaction_type === 'DAMAGED') {
                        if (!this.form.damage_level) {
                            errors.push('Level kerusakan diperlukan untuk barang rusak');
                        }
                        if (!this.form.damage_reason) {
                            errors.push('Alasan kerusakan diperlukan untuk barang rusak');
                        }
                        if (!this.form.notes?.trim() || this.form.notes.trim().length < 10) {
                            errors.push('Catatan detail minimal 10 karakter untuk barang rusak');
                        }
                        // Repair estimate wajib untuk heavy damage
                        if (this.form.damage_level === 'heavy' && !this.form.repair_estimate) {
                            errors.push('Estimasi biaya repair wajib untuk kerusakan berat');
                        }
                    }
                    return errors;
                },

                resetForm() {
                    // Don't change transaction type if it's fixed from URL
                    const shouldKeepType = this.isTransactionTypeFixed();
                    const currentType = shouldKeepType ? this.form.transaction_type : '';

                    this.selectedItems = [];
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.stopQRScanner();
                    this.stopHardwareScanner();

                    this.form = {
                        transaction_type: currentType,
                        reference_type: '',
                        reference_id: '',
                        from_location: '',
                        to_location: '',
                        notes: '',
                        kondisi: 'good',
                        lost_reason: '',
                        // 🆕 RESET DAMAGED FIELDS
                        damage_level: '',
                        damage_reason: '',
                        repair_estimate: ''
                    };

                    // Clear API data
                    this.apiError = null;
                    this.lastScannedCode = null;
                    this.ticketIds = [];

                    // Auto-set locations jika transaction type sudah ada
                    if (this.form.transaction_type) {
                        this.autoSetLocations();
                    }

                    this.showNotification(
                        shouldKeepType ? 'Form direset (transaction type tetap fixed)' : 'Form telah direset',
                        'info'
                    );
                },

                // ================================================================
                // TRANSACTION TYPE MANAGEMENT
                // ================================================================

                isValidTransactionType(type) {
                    return this.availableTransactionTypes.some(t => t.value === type);
                },

                getTransactionTypeInfo(type) {
                    return this.availableTransactionTypes.find(t => t.value === type) || {
                        value: type,
                        label: type,
                        description: 'Unknown transaction type'
                    };
                },

                handleTransactionTypeChange() {
                    const typeInfo = this.getTransactionTypeInfo(this.form.transaction_type);
                    console.log('🔄 Transaction type changed to:', typeInfo);

                    // Auto-set locations berdasarkan tipe
                    this.autoSetLocations();

                    // Reset fields yang tidak diperlukan
                    if (this.form.transaction_type !== 'LOST') {
                        this.form.lost_reason = '';
                    }

                    // Show notification
                    this.showNotification(`Tipe transaksi: ${typeInfo.label}`, 'info');
                },

                autoSetLocations() {
                    const type = this.form.transaction_type;

                    switch (type) {
                        case 'IN':
                            this.form.from_location = this.form.from_location || 'Supplier';
                            this.form.to_location = this.form.to_location || 'Warehouse';
                            break;

                        case 'OUT':
                            this.form.from_location = this.form.from_location || 'Warehouse';
                            this.form.to_location = this.form.to_location || 'Customer';
                            break;

                        case 'REPAIR':
                            this.form.from_location = this.form.from_location || 'Warehouse';
                            this.form.to_location = this.form.to_location || 'Repair Center';
                            break;

                        case 'RETURN':
                            this.form.from_location = this.form.from_location || 'Repair Center';
                            this.form.to_location = this.form.to_location || 'Warehouse';
                            break;

                        case 'MOVE':
                            // Keep current locations for move
                            break;

                        case 'LOST':
                            this.form.from_location = this.form.from_location || 'Last Known Location';
                            this.form.to_location = 'LOST';
                            break;

                        case 'MAINTENANCE':
                            this.form.from_location = this.form.from_location || 'Warehouse';
                            this.form.to_location = this.form.to_location || 'Maintenance Area';
                            break;

                        case 'DAMAGED':
                            this.form.from_location = this.form.from_location || 'Current Location';
                            this.form.to_location = this.form.to_location || 'Damage Assessment';
                            break;
                    }
                },

                // ================================================================
                // API TICKET INTEGRATION
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
                            signal: AbortSignal.timeout(10000) // 10 seconds timeout
                        });

                        if (!response.ok) {
                            throw new Error(`API Error: ${response.status} ${response.statusText}`);
                        }

                        const data = await response.json();

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
                        this.showTicketApiError(error.message);
                    } finally {
                        this.loadingTickets = false;
                    }
                },

                showTicketApiError(errorMessage) {
                    console.warn('API Ticket Error:', errorMessage);
                    setTimeout(() => {
                        if (this.ticketIds.length === 0) {
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

                    return (now - lastFetch) > 5 * 60 * 1000; // 5 minutes
                },

                getReferenceIdPlaceholder() {
                    const placeholders = {
                        'ticket': 'Pilih dari dropdown ticket',
                        'po': 'Nomor Purchase Order',
                        'maintenance': 'ID Maintenance Request',
                        'manual': 'Input manual reference ID',
                        '': 'Pilih reference type terlebih dahulu'
                    };

                    return placeholders[this.form.reference_type] || placeholders[''];
                },

                // ================================================================
                // SCANNER STATUS HELPERS
                // ================================================================

                getScannerStatusClass() {
                    const classes = {
                        'disconnected': 'text-gray-500',
                        'connected': 'text-blue-500',
                        'scanning': 'text-green-500'
                    };
                    return classes[this.scannerStatus] || 'text-gray-500';
                },

                getScannerStatusText() {
                    const texts = {
                        'disconnected': 'Tidak Terhubung',
                        'connected': 'Terhubung',
                        'scanning': 'Aktif Scanning'
                    };
                    return texts[this.scannerStatus] || 'Unknown';
                },

                // ================================================================
                // UTILITIES
                // ================================================================

                playBeepSound() {
                    try {
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
                }
            }
        }
    </script>
@endpush
