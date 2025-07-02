@extends('layouts.app')

@section('title', 'Tambah Barang - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="itemForm()">
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
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">Tambah Barang</span>
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
                <h1 class="text-2xl font-bold text-gray-900">Tambah Barang</h1>
                <p class="text-gray-600 mt-1">Tambahkan barang baru ke dalam sistem inventori</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('items.index') }}"
               class="px-4 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-edit mr-2 text-blue-600"></i>
                Form Barang
            </h3>
        </div>

        <form action="{{ route('items.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

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
                                   value="{{ old('item_code', $nextCode) }}"
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
                                   value="{{ old('item_name') }}"
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
                                    <option value="{{ $category['value'] }}" {{ old('category_id') == $category['value'] ? 'selected' : '' }}>
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
                                    <option value="{{ $key }}" {{ old('unit') == $key ? 'selected' : '' }}>
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
                               value="{{ old('min_stock', 0) }}"
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
                    <p class="mt-1 text-xs text-gray-500">Batas minimum stok untuk peringatan stok rendah</p>
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
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all resize-none @error('description') border-red-500 bg-red-50 @enderror">{{ old('description') }}</textarea>
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
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="sr-only peer"
                                   id="status_toggle">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-900" id="status_label">{{ old('is_active', true) ? 'Aktif' : 'Nonaktif' }}</span>
                        </label>
                    </div>
                </div>

                <!-- QR Code Generation -->
                <div class="flex items-center justify-between p-4 bg-purple-50 rounded-xl border border-purple-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg flex items-center justify-center">
                            <i class="fas fa-qrcode text-white"></i>
                        </div>
                        <div>
                            <h5 class="font-medium text-gray-900">Generate QR Code</h5>
                            <p class="text-sm text-gray-600">Generate QR Code otomatis untuk tracking barang</p>
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
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-save"></i>
                    <span>Simpan Barang</span>
                </button>
                <button type="reset"
                        class="flex-1 px-6 py-3 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-undo"></i>
                    <span>Reset Form</span>
                </button>
                <a href="{{ route('items.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>Batal</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tips Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-lightbulb text-white"></i>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-blue-900 mb-2">Tips Mengisi Form</h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• <strong>Kode Barang:</strong> Gunakan format yang konsisten seperti ITM001, ITM002, dst.</li>
                        <li>• <strong>Nama Barang:</strong> Gunakan nama yang jelas dan mudah dikenali</li>
                        <li>• <strong>Kategori:</strong> Pilih kategori yang paling sesuai untuk memudahkan pencarian</li>
                        <li>• <strong>Minimum Stok:</strong> Tentukan batas untuk peringatan stok rendah</li>
                        <li>• <strong>QR Code:</strong> Berguna untuk tracking dan identifikasi cepat</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Preview Card -->
        <div class="bg-green-50 border border-green-200 rounded-2xl p-6">
            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-eye text-white"></i>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-green-900 mb-2">Preview Barang</h4>
                    <div class="space-y-2 text-sm text-green-800">
                        <div class="flex justify-between">
                            <span>Kode:</span>
                            <span class="font-medium" id="preview_code">{{ $nextCode }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Nama:</span>
                            <span class="font-medium" id="preview_name">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Kategori:</span>
                            <span class="font-medium" id="preview_category">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Satuan:</span>
                            <span class="font-medium" id="preview_unit">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Min. Stok:</span>
                            <span class="font-medium" id="preview_min_stock">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function itemForm() {
        return {
            init() {
                // Auto-uppercase item code
                const codeInput = document.getElementById('item_code');
                if (codeInput) {
                    codeInput.addEventListener('input', function(e) {
                        e.target.value = e.target.value.toUpperCase();
                        document.getElementById('preview_code').textContent = e.target.value || '{{ $nextCode }}';
                    });
                }

                // Update preview on form changes
                const nameInput = document.getElementById('item_name');
                if (nameInput) {
                    nameInput.addEventListener('input', function(e) {
                        document.getElementById('preview_name').textContent = e.target.value || '-';
                    });
                }

                const categorySelect = document.getElementById('category_id');
                if (categorySelect) {
                    categorySelect.addEventListener('change', function(e) {
                        const selectedText = e.target.options[e.target.selectedIndex].text;
                        document.getElementById('preview_category').textContent = e.target.value ? selectedText : '-';
                    });
                }

                const unitSelect = document.getElementById('unit');
                if (unitSelect) {
                    unitSelect.addEventListener('change', function(e) {
                        const selectedText = e.target.options[e.target.selectedIndex].text;
                        document.getElementById('preview_unit').textContent = e.target.value ? selectedText : '-';
                    });
                }

                const minStockInput = document.getElementById('min_stock');
                if (minStockInput) {
                    minStockInput.addEventListener('input', function(e) {
                        document.getElementById('preview_min_stock').textContent = e.target.value || '0';
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

                // QR toggle
                const qrToggle = document.getElementById('qr_toggle');
                const qrLabel = document.getElementById('qr_label');
                if (qrToggle && qrLabel) {
                    qrToggle.addEventListener('change', function() {
                        qrLabel.textContent = this.checked ? 'Ya' : 'Tidak';
                    });
                }

                // Initialize preview with old values if any
                this.updatePreviewFromForm();
            },

            updatePreviewFromForm() {
                // Update preview with current form values (for validation errors)
                const nameInput = document.getElementById('item_name');
                if (nameInput && nameInput.value) {
                    document.getElementById('preview_name').textContent = nameInput.value;
                }

                const categorySelect = document.getElementById('category_id');
                if (categorySelect && categorySelect.value) {
                    const selectedText = categorySelect.options[categorySelect.selectedIndex].text;
                    document.getElementById('preview_category').textContent = selectedText;
                }

                const unitSelect = document.getElementById('unit');
                if (unitSelect && unitSelect.value) {
                    const selectedText = unitSelect.options[unitSelect.selectedIndex].text;
                    document.getElementById('preview_unit').textContent = selectedText;
                }

                const minStockInput = document.getElementById('min_stock');
                if (minStockInput && minStockInput.value) {
                    document.getElementById('preview_min_stock').textContent = minStockInput.value;
                }
            }
        }
    }
</script>
@endpush
