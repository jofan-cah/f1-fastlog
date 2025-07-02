@extends('layouts.app')

@section('title', 'Edit Barang - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="itemEditForm()">
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
                    <a href="{{ route('items.index') }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                        Barang
                    </a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="{{ route('items.show', $item->item_id) }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                        {{ $item->item_name }}
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
            <div class="w-16 h-16 bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-2xl flex items-center justify-center">
                <i class="fas fa-edit text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Barang</h1>
                <p class="text-gray-600 mt-1">{{ $item->item_code }} • {{ $item->item_name }}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('items.show', $item->item_id) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-eye"></i>
                <span>Lihat Detail</span>
            </a>
            <a href="{{ route('items.index') }}"
               class="px-4 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Current Status Alert -->
    @php
        $stockInfo = $item->getStockInfo();
    @endphp
    <div class="bg-{{ $item->is_active ? 'green' : 'red' }}-50 border border-{{ $item->is_active ? 'green' : 'red' }}-200 rounded-2xl p-4">
        <div class="flex items-center space-x-3">
            <i class="fas fa-info-circle text-{{ $item->is_active ? 'green' : 'red' }}-600"></i>
            <div>
                <h4 class="font-medium text-{{ $item->is_active ? 'green' : 'red' }}-900">
                    Status: {{ $item->getStatusText() }} • Stok: {{ $stockInfo['available'] }}/{{ $stockInfo['total'] }} {{ $item->unit }}
                </h4>
                <p class="text-sm text-{{ $item->is_active ? 'green' : 'red' }}-700">
                    @if($item->is_active)
                        Barang ini sedang aktif dan dapat digunakan dalam transaksi.
                    @else
                        Barang ini tidak aktif dan tidak dapat digunakan dalam transaksi baru.
                    @endif
                    @if($stockInfo['status'] == 'low')
                        <span class="font-medium text-yellow-700">⚠️ Stok rendah!</span>
                    @elseif($stockInfo['status'] == 'empty')
                        <span class="font-medium text-red-700">❌ Stok habis!</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-edit mr-2 text-blue-600"></i>
                Form Edit Barang
            </h3>
        </div>

        <form action="{{ route('items.update', $item->item_id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Information Section -->
            <div class="space-y-6">
                <div class="border-l-4 border-blue-500 pl-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Informasi Dasar</h4>
                    <p class="text-sm text-gray-600">Informasi utama tentang barang</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Item Code -->
                    <div>
                        <label for="item_code" class="block text-sm font-medium text-gray-700 mb-2">
                            Kode Barang <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="item_code"
                                   name="item_code"
                                   value="{{ old('item_code', $item->item_code) }}"
                                   placeholder="Contoh: ITM001"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('item_code') border-red-500 bg-red-50 @enderror"
                                   maxlength="50">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-barcode text-gray-400"></i>
                            </div>
                        </div>
                        @error('item_code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Kode unik untuk mengidentifikasi barang (maks. 50 karakter)</p>
                    </div>

                    <!-- Item Name -->
                    <div>
                        <label for="item_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Barang <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="item_name"
                                   name="item_name"
                                   value="{{ old('item_name', $item->item_name) }}"
                                   placeholder="Contoh: Router Wifi AC1200"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('item_name') border-red-500 bg-red-50 @enderror"
                                   maxlength="200">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-box text-gray-400"></i>
                            </div>
                        </div>
                        @error('item_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Nama lengkap barang (maks. 200 karakter)</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Kategori <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="category_id"
                                    name="category_id"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('category_id') border-red-500 bg-red-50 @enderror">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category['value'] }}" {{ $item->category_id == $category['value'] ? 'selected' : '' }}>
                                        {{ $category['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-tags text-gray-400"></i>
                            </div>
                        </div>
                        @error('category_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Pilih kategori yang sesuai dengan barang</p>
                    </div>

                    <!-- Unit -->
                    <div>
                        <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">
                            Satuan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="unit"
                                    name="unit"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('unit') border-red-500 bg-red-50 @enderror">
                                <option value="">Pilih Satuan</option>
                                @foreach($units as $key => $label)
                                    <option value="{{ $key }}" {{ old('unit', $item->unit) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-weight text-gray-400"></i>
                            </div>
                        </div>
                        @error('unit')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Satuan untuk menghitung barang</p>
                    </div>
                </div>

                <!-- Minimum Stock -->
                <div>
                    <label for="min_stock" class="block text-sm font-medium text-gray-700 mb-2">
                        Minimum Stok <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number"
                               id="min_stock"
                               name="min_stock"
                               value="{{ old('min_stock', $item->min_stock) }}"
                               min="0"
                               placeholder="Contoh: 10"
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('min_stock') border-red-500 bg-red-50 @enderror">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-chart-line text-gray-400"></i>
                        </div>
                    </div>
                    @error('min_stock')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        Batas minimum stok untuk peringatan stok rendah
                        @if($stockInfo['available'] <= $item->min_stock && $stockInfo['available'] > 0)
                            <span class="text-yellow-600 font-medium">(Saat ini stok rendah!)</span>
                        @endif
                    </p>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Deskripsi
                    </label>
                    <textarea id="description"
                              name="description"
                              rows="4"
                              placeholder="Contoh: Router wireless dual band dengan kecepatan hingga 1200 Mbps..."
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all resize-none @error('description') border-red-500 bg-red-50 @enderror">{{ old('description', $item->description) }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Deskripsi detail tentang barang (opsional)</p>
                </div>
            </div>

            <!-- Settings Section -->
            <div class="space-y-6">
                <div class="border-l-4 border-purple-500 pl-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Pengaturan</h4>
                    <p class="text-sm text-gray-600">Pengaturan status dan fitur tambahan</p>
                </div>

                <!-- Status Toggle -->
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-600 to-green-700 rounded-lg flex items-center justify-center">
                            <i class="fas fa-toggle-on text-white"></i>
                        </div>
                        <div>
                            <h5 class="font-medium text-gray-900">Status Barang</h5>
                            <p class="text-sm text-gray-600">Aktifkan barang untuk dapat digunakan dalam transaksi</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', $item->is_active) ? 'checked' : '' }}
                                   class="sr-only peer"
                                   id="status_toggle">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-900" id="status_label">{{ old('is_active', $item->is_active) ? 'Aktif' : 'Nonaktif' }}</span>
                        </label>
                    </div>
                </div>

                <!-- QR Code Section -->
                <div class="space-y-4">
                    @if($item->hasQRCode())
                        <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl border border-green-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-green-600 to-green-700 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-qrcode text-white"></i>
                                </div>
                                <div>
                                    <h5 class="font-medium text-gray-900">QR Code Tersedia</h5>
                                    <p class="text-sm text-gray-600">QR Code sudah digenerate untuk barang ini</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('items.download-qr', $item->item_id) }}"
                                   class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 flex items-center space-x-2 text-sm">
                                    <i class="fas fa-download"></i>
                                    <span>Download</span>
                                </a>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>
                                    Ada
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center justify-between p-4 bg-purple-50 rounded-xl border border-purple-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-qrcode text-white"></i>
                                </div>
                                <div>
                                    <h5 class="font-medium text-gray-900">Generate QR Code</h5>
                                    <p class="text-sm text-gray-600">Generate QR Code untuk tracking barang</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           name="generate_qr"
                                           value="1"
                                           {{ old('generate_qr', false) ? 'checked' : '' }}
                                           class="sr-only peer"
                                           id="qr_toggle">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900" id="qr_label">{{ old('generate_qr', false) ? 'Ya' : 'Tidak' }}</span>
                                </label>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Warning if item has stock or transactions -->
                @if($item->hasStock() || $item->transactions()->exists() || $item->poDetails()->exists())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                            <div>
                                <h5 class="font-medium text-yellow-900">Peringatan</h5>
                                <p class="text-sm text-yellow-800 mt-1">
                                    @if($item->hasStock())
                                        Barang ini memiliki stok ({{ $stockInfo['total'] }} {{ $item->unit }}).
                                    @endif
                                    @if($item->transactions()->exists() || $item->poDetails()->exists())
                                        Barang ini memiliki riwayat transaksi.
                                    @endif
                                    Menonaktifkan barang akan mencegah penggunaan dalam transaksi baru, tetapi tidak akan mempengaruhi stok atau transaksi yang sudah ada.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Metadata Information -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                <h5 class="font-medium text-gray-900 mb-3">Informasi Sistem</h5>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">ID Barang:</span>
                        <span class="ml-2 font-mono text-gray-900">{{ $item->item_id }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Dibuat:</span>
                        <span class="ml-2 text-gray-900">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Terakhir Diupdate:</span>
                        <span class="ml-2 text-gray-900">{{ $item->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-save"></i>
                    <span>Update Barang</span>
                </button>
                <button type="button"
                        onclick="resetForm()"
                        class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-undo"></i>
                    <span>Reset Form</span>
                </button>
                <a href="{{ route('items.show', $item->item_id) }}"
                   class="flex-1 px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>Batal</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-chart-bar mr-2 text-indigo-600"></i>
                Statistik & Riwayat
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Current Stock -->
                <div class="text-center p-4 bg-blue-50 rounded-xl">
                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-boxes text-white"></i>
                    </div>
                    <div class="text-2xl font-bold text-blue-600">{{ $stockInfo['available'] }}</div>
                    <div class="text-sm text-gray-600">Stok Tersedia</div>
                </div>

                <!-- Total Stock -->
                <div class="text-center p-4 bg-green-50 rounded-xl">
                    <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-warehouse text-white"></i>
                    </div>
                    <div class="text-2xl font-bold text-green-600">{{ $stockInfo['total'] }}</div>
                    <div class="text-sm text-gray-600">Total Stok</div>
                </div>

                <!-- Used Stock -->
                <div class="text-center p-4 bg-yellow-50 rounded-xl">
                    <div class="w-12 h-12 bg-yellow-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-shipping-fast text-white"></i>
                    </div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $stockInfo['used'] }}</div>
                    <div class="text-sm text-gray-600">Stok Terpakai</div>
                </div>

                <!-- Transactions -->
                <div class="text-center p-4 bg-purple-50 rounded-xl">
                    <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-exchange-alt text-white"></i>
                    </div>
                    <div class="text-2xl font-bold text-purple-600">{{ $item->transactions()->count() }}</div>
                    <div class="text-sm text-gray-600">Total Transaksi</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Card -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-info text-white"></i>
            </div>
            <div>
                <h4 class="text-lg font-semibold text-blue-900 mb-2">Tips Edit Barang</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• <strong>Kode Barang:</strong> Pastikan kode tetap unik setelah diubah</li>
                    <li>• <strong>Kategori:</strong> Perubahan kategori akan mempengaruhi pengelompokan barang</li>
                    <li>• <strong>Satuan:</strong> Hati-hati mengubah satuan jika sudah ada stok</li>
                    <li>• <strong>Minimum Stok:</strong> Sesuaikan dengan kebutuhan operasional</li>
                    <li>• <strong>Status:</strong> Menonaktifkan barang akan mencegah transaksi baru</li>
                    <li>• <strong>QR Code:</strong> Generate ulang jika diperlukan untuk tracking</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function itemEditForm() {
        return {
            init() {
                // Auto-uppercase item code
                const codeInput = document.getElementById('item_code');
                if (codeInput) {
                    codeInput.addEventListener('input', function(e) {
                        e.target.value = e.target.value.toUpperCase();
                    });
                }

                // Status toggle
                const statusToggle = document.getElementById('status_toggle');
                const statusLabel = document.getElementById('status_label');
                if (statusToggle && statusLabel) {
                    statusToggle.addEventListener('change', function() {
                        statusLabel.textContent = this.checked ? 'Aktif' : 'Nonaktif';
                    });
                }

                // QR toggle (only if QR not exists)
                const qrToggle = document.getElementById('qr_toggle');
                const qrLabel = document.getElementById('qr_label');
                if (qrToggle && qrLabel) {
                    qrToggle.addEventListener('change', function() {
                        qrLabel.textContent = this.checked ? 'Ya' : 'Tidak';
                    });
                }
            }
        }
    }

    // Reset form function
    function resetForm() {
        if (confirm('Yakin ingin mereset semua perubahan ke nilai semula?')) {
            // Reset to original values
            document.getElementById('item_code').value = '{{ $item->item_code }}';
            document.getElementById('item_name').value = '{{ $item->item_name }}';
            document.getElementById('category_id').value = '{{ $item->category_id }}';
            document.getElementById('unit').value = '{{ $item->unit }}';
            document.getElementById('min_stock').value = '{{ $item->min_stock }}';
            document.getElementById('description').value = `{{ $item->description ?? '' }}`;
            document.getElementById('status_toggle').checked = {{ $item->is_active ? 'true' : 'false' }};

            // Update toggle label
            const statusLabel = document.getElementById('status_label');
            if (statusLabel) {
                statusLabel.textContent = '{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}';
            }

            // Reset QR toggle if exists
            const qrToggle = document.getElementById('qr_toggle');
            const qrLabel = document.getElementById('qr_label');
            if (qrToggle && qrLabel) {
                qrToggle.checked = false;
                qrLabel.textContent = 'Tidak';
            }
        }
    }
</script>
@endpush
