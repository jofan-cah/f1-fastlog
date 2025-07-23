@extends('layouts.app')

@section('title', 'Edit Stock - LogistiK Admin')

@section('content')
<div class="space-y-6" x-data="stockEdit()">
    <!-- Breadcrumb -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-orange-600">
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="{{ route('stocks.index') }}"
                        class="text-sm font-medium text-gray-700 hover:text-orange-600">
                        Stock Management
                    </a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="{{ route('stocks.show', $stock) }}"
                        class="text-sm font-medium text-gray-700 hover:text-orange-600">
                        {{ $stock->item->item_name }}
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
                <h1 class="text-2xl font-bold text-gray-900">Edit Stock</h1>
                <p class="text-gray-600 mt-1">{{ $stock->item->item_name }} - {{ $stock->item->item_code }}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('stocks.show', $stock) }}"
               class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Sync Status Alert -->
    @if(!$syncStatus['consistent'])
        <div class="bg-yellow-50 border border-yellow-400 rounded-xl p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Stock Tidak Konsisten dengan Item Details
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>{{ $syncStatus['message'] }}</p>
                        <ul class="list-disc list-inside mt-1">
                            @foreach($syncStatus['discrepancies'] as $discrepancy)
                                <li>{{ $discrepancy }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Form -->
    <form method="POST" action="{{ route('stocks.update', $stock) }}" x-ref="editForm" @submit="handleSubmit">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Panel - Item Management -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Quick Filters & Search -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-search mr-2 text-blue-600"></i>
                            Filter & Search
                        </h3>
                        <div class="text-sm text-gray-600">
                            Total: {{ $stock->item->itemDetails->count() }} items
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search -->
                        <div>
                            <input type="text"
                                   x-model="searchQuery"
                                   placeholder="Cari serial number..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <select x-model="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">Semua Status</option>
                                <option value="stock">Stock (Gudang)</option>
                                <option value="available">Available (Siap Pakai)</option>
                            </select>
                        </div>

                        <!-- Quick Select -->
                        <div>
                            <select @change="quickSelect($event.target.value)" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">Quick Select...</option>
                                <option value="all_stock">Semua Stock</option>
                                <option value="all_available">Semua Available</option>
                                <option value="range">Range SN...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Item Grid -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-list mr-2 text-blue-600"></i>
                                Item Details (<span x-text="filteredItems.length"></span>)
                            </h3>

                            <!-- Select All -->
                            <label class="flex items-center">
                                <input type="checkbox"
                                       :checked="selectedItems.length === filteredItems.length && filteredItems.length > 0"
                                       @change="toggleSelectAll()"
                                       class="mr-2 rounded">
                                <span class="text-sm">
                                    Select All
                                    <span x-show="selectedItems.length > 0"
                                          class="text-blue-600">
                                        (<span x-text="selectedItems.length"></span> terpilih)
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Items Grid -->
                    <div class="p-6">
                        <!-- Bulk Action Bar -->
                        <div x-show="selectedItems.length > 0"
                             x-transition
                             class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-blue-900">
                                    <span x-text="selectedItems.length"></span> items terpilih
                                </div>
                                <div class="flex space-x-2">
                                    <button type="button" @click="bulkToggleStatus()"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm transition-colors">
                                        <i class="fas fa-exchange-alt mr-2"></i>
                                        Toggle Status
                                    </button>
                                    <button type="button" @click="clearSelection()"
                                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm transition-colors">
                                        <i class="fas fa-times mr-2"></i>
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Items Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 max-h-96 overflow-y-auto">
                            <template x-for="item in filteredItems" :key="item.item_detail_id">
                                <div class="border border-gray-200 rounded-lg p-3 transition-all"
                                     :class="selectedItems.includes(item.item_detail_id) ? 'ring-2 ring-blue-500 bg-blue-50' : 'hover:shadow-md'">

                                    <!-- Header dengan Checkbox dan Status -->
                                    <div class="flex items-center justify-between mb-2">
                                        <input type="checkbox"
                                               :checked="selectedItems.includes(item.item_detail_id)"
                                               @change="toggleSelection(item.item_detail_id)"
                                               class="rounded">
                                        <span :class="item.status === 'stock' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                              class="px-2 py-1 rounded-full text-xs font-medium"
                                              x-text="item.status === 'stock' ? 'Stock' : 'Available'"></span>
                                    </div>

                                    <!-- Serial Number -->
                                    <div class="font-medium text-sm text-gray-900 mb-1" x-text="item.serial_number"></div>
                                    <div class="text-xs text-gray-500 mb-2" x-text="item.location || 'No location'"></div>

                                    <!-- Toggle Button -->
                                    <button type="button"
                                            @click="toggleItemStatus(item)"
                                            :class="item.status === 'stock' ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'"
                                            class="w-full px-2 py-1 text-white rounded text-xs transition-colors">
                                        <i class="fas" :class="item.status === 'stock' ? 'fa-hand-holding' : 'fa-warehouse'" class="mr-1"></i>
                                        <span x-text="item.status === 'stock' ? 'To Available' : 'To Stock'"></span>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Empty State -->
                        <div x-show="filteredItems.length === 0" class="text-center py-8 text-gray-500">
                            <i class="fas fa-search text-4xl mb-4"></i>
                            <p>Tidak ada item ditemukan</p>
                            <p class="text-sm">Coba ubah filter atau pencarian</p>
                        </div>
                    </div>
                </div>

                <!-- Changes Summary -->
                <div x-show="changedItems.length > 0"
                     x-transition
                     class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <h4 class="text-lg font-semibold text-yellow-900 mb-3 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Perubahan Pending (<span x-text="changedItems.length"></span>)
                    </h4>
                    <div class="max-h-32 overflow-y-auto space-y-2">
                        <template x-for="change in changedItems" :key="change.item_detail_id">
                            <div class="flex items-center justify-between text-sm bg-white p-2 rounded">
                                <span class="font-medium" x-text="change.serial_number"></span>
                                <span class="text-yellow-700">
                                    <span :class="change.old_status === 'stock' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                          class="px-2 py-1 rounded-full text-xs font-medium"
                                          x-text="change.old_status === 'stock' ? 'Stock' : 'Available'"></span>
                                    <i class="fas fa-arrow-right mx-2"></i>
                                    <span :class="change.new_status === 'stock' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                          class="px-2 py-1 rounded-full text-xs font-medium"
                                          x-text="change.new_status === 'stock' ? 'Stock' : 'Available'"></span>
                                </span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Form Fields -->
                <div x-show="changedItems.length > 0" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Form Submission</h4>

                    <!-- Hidden Fields -->
                    <input type="hidden" name="adjustment_type" value="manual">
                    <input type="hidden" name="quantity_available" :value="newStockCount">
                    <input type="hidden" name="quantity_used" :value="newAvailableCount">
                    <input type="hidden" name="sync_item_details" value="1">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Reason -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Alasan Perubahan <span class="text-red-500">*</span>
                            </label>
                            <select name="reason" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">Pilih alasan...</option>
                                <option value="Perpindahan status item details">Perpindahan status item details</option>
                                <option value="Koreksi lokasi item">Koreksi lokasi item</option>
                                <option value="Update status manual">Update status manual</option>
                                <option value="Audit dan penyesuaian">Audit dan penyesuaian</option>
                            </select>
                        </div>

                        <!-- Preview New Stock -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preview Stock Baru</label>
                            <div class="flex space-x-2">
                                <div class="flex-1 bg-blue-50 px-3 py-2 rounded-lg text-center">
                                    <div class="text-sm text-blue-700">Stock</div>
                                    <div class="text-lg font-bold text-blue-900" x-text="newStockCount"></div>
                                </div>
                                <div class="flex-1 bg-green-50 px-3 py-2 rounded-lg text-center">
                                    <div class="text-sm text-green-700">Available</div>
                                    <div class="text-lg font-bold text-green-900" x-text="newAvailableCount"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan tambahan (opsional)"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3 mt-6">
                        <button type="submit"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-save"></i>
                            <span>Simpan Perubahan</span>
                        </button>

                        <button type="button" @click="resetChanges()"
                                class="px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-undo"></i>
                            <span>Reset</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="space-y-6">
                <!-- Current Status -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
                            Status Saat Ini
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Stock (Gudang)</span>
                            <span class="text-sm font-medium">{{ $stock->quantity_available }} {{ $stock->item->unit }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Available (Siap Pakai)</span>
                            <span class="text-sm font-medium">{{ $stock->quantity_used }} {{ $stock->item->unit }}</span>
                        </div>
                        <div class="flex justify-between items-center border-t pt-3">
                            <span class="text-sm font-medium text-gray-900">Total</span>
                            <span class="text-sm font-bold">{{ $stock->total_quantity }} {{ $stock->item->unit }}</span>
                        </div>
                    </div>
                </div>

                <!-- Item Info -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-box mr-2 text-blue-600"></i>
                            Item Info
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="text-center mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-microchip text-white text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-900">{{ $stock->item->item_name }}</h4>
                            <p class="text-sm text-gray-500">{{ $stock->item->item_code }}</p>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Kategori</span>
                                <span class="text-sm font-medium">{{ $stock->item->category->category_name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Unit</span>
                                <span class="text-sm font-medium">{{ $stock->item->unit }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Items</span>
                                <span class="text-sm font-medium">{{ $stock->item->itemDetails->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="bg-blue-50 rounded-2xl border border-blue-200 p-6">
                    <h4 class="text-lg font-semibold text-blue-900 mb-3 flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Tips
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Klik <strong>individual button</strong> untuk toggle 1 item</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Pilih beberapa item → <strong>Toggle Status</strong> untuk bulk</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Gunakan <strong>filter</strong> untuk cari SN tertentu</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span><strong>Stock</strong> = di gudang, <strong>Available</strong> = siap pakai</span>
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
<script>
    function stockEdit() {
        return {
            // Data
            allItems: @json($stock->item->itemDetails->whereIn('status', ['stock', 'available'])->values() ?? []),
            selectedItems: [],
            changedItems: [],
            searchQuery: '',
            statusFilter: '',

            get filteredItems() {
                return this.allItems.filter(item => {
                    const matchesSearch = !this.searchQuery ||
                        item.serial_number.toLowerCase().includes(this.searchQuery.toLowerCase());
                    const matchesStatus = !this.statusFilter || item.status === this.statusFilter;
                    return matchesSearch && matchesStatus;
                });
            },

            get newStockCount() {
                return this.allItems.filter(item => {
                    const changed = this.changedItems.find(c => c.item_detail_id === item.item_detail_id);
                    const status = changed ? changed.new_status : item.status;
                    return status === 'stock';
                }).length;
            },

            get newAvailableCount() {
                return this.allItems.filter(item => {
                    const changed = this.changedItems.find(c => c.item_detail_id === item.item_detail_id);
                    const status = changed ? changed.new_status : item.status;
                    return status === 'available';
                }).length;
            },

            init() {
                console.log('Initializing simple toggle stock edit');

                // Store original data
                this.originalItems = JSON.parse(JSON.stringify(this.allItems));

                // Add CSRF token
                const token = document.querySelector('meta[name="csrf-token"]');
                if (token) {
                    window.csrfToken = token.getAttribute('content');
                }
            },

            // Selection methods
            toggleSelection(itemId) {
                const index = this.selectedItems.indexOf(itemId);
                if (index > -1) {
                    this.selectedItems.splice(index, 1);
                } else {
                    this.selectedItems.push(itemId);
                }
            },

            toggleSelectAll() {
                if (this.selectedItems.length === this.filteredItems.length) {
                    this.selectedItems = [];
                } else {
                    this.selectedItems = this.filteredItems.map(item => item.item_detail_id);
                }
            },

            clearSelection() {
                this.selectedItems = [];
            },

            // Quick select methods
            quickSelect(pattern) {
                if (!pattern) return;

                if (pattern === 'all_stock') {
                    this.selectedItems = this.filteredItems
                        .filter(item => item.status === 'stock')
                        .map(item => item.item_detail_id);
                    this.showToast(`Terpilih ${this.selectedItems.length} items Stock`, 'success');

                } else if (pattern === 'all_available') {
                    this.selectedItems = this.filteredItems
                        .filter(item => item.status === 'available')
                        .map(item => item.item_detail_id);
                    this.showToast(`Terpilih ${this.selectedItems.length} items Available`, 'success');

                } else if (pattern === 'range') {
                    const start = prompt('Masukkan SN awal (contoh: SN001):');
                    const end = prompt('Masukkan SN akhir (contoh: SN050):');

                    if (start && end) {
                        const startNum = parseInt(start.replace(/\D/g, ''));
                        const endNum = parseInt(end.replace(/\D/g, ''));

                        this.selectedItems = this.filteredItems
                            .filter(item => {
                                const sn = parseInt(item.serial_number.replace(/\D/g, ''));
                                return sn >= startNum && sn <= endNum;
                            })
                            .map(item => item.item_detail_id);

                        this.showToast(`Terpilih ${this.selectedItems.length} items dalam range ${start}-${end}`, 'success');
                    }
                }

                // Reset select dropdown
                event.target.value = '';
            },

            // Toggle methods
            toggleItemStatus(item) {
                const newStatus = item.status === 'stock' ? 'available' : 'stock';
                this.changeItemStatus(item, newStatus);
                this.showToast(`${item.serial_number} dipindah ke ${newStatus}`, 'success');
            },

            bulkToggleStatus() {
                if (this.selectedItems.length === 0) {
                    this.showToast('Pilih items terlebih dahulu', 'error');
                    return;
                }

                const selectedItemsData = this.allItems.filter(item =>
                    this.selectedItems.includes(item.item_detail_id)
                );

                let changedCount = 0;
                selectedItemsData.forEach(item => {
                    const newStatus = item.status === 'stock' ? 'available' : 'stock';
                    this.changeItemStatus(item, newStatus);
                    changedCount++;
                });

                this.showToast(`${changedCount} items berhasil di-toggle`, 'success');
                this.clearSelection();
            },

            changeItemStatus(item, newStatus) {
                const oldStatus = item.status;

                if (oldStatus === newStatus) return;

                // Remove existing change for this item
                this.changedItems = this.changedItems.filter(c => c.item_detail_id !== item.item_detail_id);

                // Add new change
                this.changedItems.push({
                    item_detail_id: item.item_detail_id,
                    serial_number: item.serial_number,
                    old_status: oldStatus,
                    new_status: newStatus
                });

                // Update item status in display
                item.status = newStatus;
                item.location = newStatus === 'stock' ? 'Warehouse - Stock' : 'Office - Ready';
            },

            resetChanges() {
                if (this.changedItems.length === 0) {
                    this.showToast('Tidak ada perubahan untuk direset', 'info');
                    return;
                }

                if (!confirm('Yakin ingin membatalkan semua perubahan?')) {
                    return;
                }

                // Restore original data
                this.allItems = JSON.parse(JSON.stringify(this.originalItems));
                this.changedItems = [];
                this.selectedItems = [];

                this.showToast('Perubahan berhasil direset!', 'info');
            },

            handleSubmit(event) {
                if (this.changedItems.length === 0) {
                    event.preventDefault();
                    this.showToast('Tidak ada perubahan untuk disimpan', 'error');
                    return false;
                }

                // Show loading
                const submitBtn = event.target.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
                submitBtn.disabled = true;

                return true;
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
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 hover:opacity-70">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;

                document.body.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 5000);
            }
        }
    }

    // Keyboard shortcuts
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('keydown', function(e) {
            const app = Alpine.$data(document.querySelector('[x-data="stockEdit()"]'));
            if (!app) return;

            // Ctrl + A untuk select all filtered
            if (e.ctrlKey && e.key === 'a') {
                e.preventDefault();
                app.toggleSelectAll();
            }

            // Escape untuk clear selection
            if (e.key === 'Escape') {
                if (app.selectedItems.length > 0) {
                    app.clearSelection();
                }
            }

            // Ctrl + Z untuk reset changes
            if (e.ctrlKey && e.key === 'z') {
                e.preventDefault();
                app.resetChanges();
            }

            // Space untuk toggle selected items (jika ada selection)
            if (e.key === ' ' && app.selectedItems.length > 0) {
                e.preventDefault();
                app.bulkToggleStatus();
            }

            // Ctrl + S untuk submit
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                if (app.changedItems.length > 0) {
                    document.querySelector('form').submit();
                } else {
                    app.showToast('Tidak ada perubahan untuk disimpan', 'error');
                }
            }
        });

        // Auto-focus search input
        setTimeout(() => {
            const searchInput = document.querySelector('input[x-model="searchQuery"]');
            if (searchInput) {
                searchInput.focus();
            }
        }, 500);
    });
</script>
@endpush
