@extends('layouts.app')

@section('title', 'Edit Supplier - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="supplierEditForm()">
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
                    <a href="{{ route('suppliers.index') }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                        Supplier
                    </a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="{{ route('suppliers.show', $supplier->supplier_id) }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                        {{ $supplier->supplier_name }}
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
                <h1 class="text-2xl font-bold text-gray-900">Edit Supplier</h1>
                <p class="text-gray-600 mt-1">{{ $supplier->supplier_code }} • {{ $supplier->supplier_name }}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('suppliers.show', $supplier->supplier_id) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-eye"></i>
                <span>Lihat Detail</span>
            </a>
            <a href="{{ route('suppliers.index') }}"
               class="px-4 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Current Status Alert -->
    @if($supplier->is_active)
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4">
            <div class="flex items-center space-x-3">
                <i class="fas fa-info-circle text-green-600"></i>
                <div>
                    <h4 class="font-medium text-green-900">Status Saat Ini: {{ $supplier->getStatusText() }}</h4>
                    <p class="text-sm text-green-700">Supplier ini sedang aktif dan dapat digunakan untuk transaksi.</p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
            <div class="flex items-center space-x-3">
                <i class="fas fa-info-circle text-red-600"></i>
                <div>
                    <h4 class="font-medium text-red-900">Status Saat Ini: {{ $supplier->getStatusText() }}</h4>
                    <p class="text-sm text-red-700">Supplier ini tidak aktif dan tidak dapat digunakan untuk transaksi baru.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-edit mr-2 text-blue-600"></i>
                Form Edit Supplier
            </h3>
        </div>

        <form action="{{ route('suppliers.update', $supplier->supplier_id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Information Section -->
            <div class="space-y-6">
                <div class="border-l-4 border-blue-500 pl-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Informasi Dasar</h4>
                    <p class="text-sm text-gray-600">Informasi utama tentang supplier</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Supplier Code -->
                    <div>
                        <label for="supplier_code" class="block text-sm font-medium text-gray-700 mb-2">
                            Kode Supplier <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="supplier_code"
                                   name="supplier_code"
                                   value="{{ old('supplier_code', $supplier->supplier_code) }}"
                                   placeholder="Contoh: SUP001"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('supplier_code') border-red-500 bg-red-50 @enderror"
                                   maxlength="20">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-barcode text-gray-400"></i>
                            </div>
                        </div>
                        @error('supplier_code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Kode unik untuk mengidentifikasi supplier (maks. 20 karakter)</p>
                    </div>

                    <!-- Supplier Name -->
                    <div>
                        <label for="supplier_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Supplier <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="supplier_name"
                                   name="supplier_name"
                                   value="{{ old('supplier_name', $supplier->supplier_name) }}"
                                   placeholder="Contoh: PT. Teknologi Maju Indonesia"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('supplier_name') border-red-500 bg-red-50 @enderror"
                                   maxlength="100">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-building text-gray-400"></i>
                            </div>
                        </div>
                        @error('supplier_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Nama lengkap perusahaan supplier (maks. 100 karakter)</p>
                    </div>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="space-y-6">
                <div class="border-l-4 border-green-500 pl-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Informasi Kontak</h4>
                    <p class="text-sm text-gray-600">Detail kontak untuk komunikasi dengan supplier</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Contact Person -->
                    <div>
                        <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-2">
                            Kontak Person
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="contact_person"
                                   name="contact_person"
                                   value="{{ old('contact_person', $supplier->contact_person) }}"
                                   placeholder="Contoh: Budi Santoso"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('contact_person') border-red-500 bg-red-50 @enderror"
                                   maxlength="100">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                        </div>
                        @error('contact_person')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Nama orang yang dapat dihubungi</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Nomor Telepon
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone', $supplier->phone) }}"
                                   placeholder="Contoh: 081234567890"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('phone') border-red-500 bg-red-50 @enderror"
                                   maxlength="20">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                        </div>
                        @error('phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Nomor telepon yang bisa dihubungi</p>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <div class="relative">
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email', $supplier->email) }}"
                               placeholder="Contoh: kontak@supplier.com"
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('email') border-red-500 bg-red-50 @enderror"
                               maxlength="100">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Alamat email untuk komunikasi resmi</p>
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat
                    </label>
                    <textarea id="address"
                              name="address"
                              rows="4"
                              placeholder="Contoh: Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta 10110"
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all resize-none @error('address') border-red-500 bg-red-50 @enderror">{{ old('address', $supplier->address) }}</textarea>
                    @error('address')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Alamat lengkap supplier</p>
                </div>
            </div>

            <!-- Status Section -->
            <div class="space-y-6">
                <div class="border-l-4 border-purple-500 pl-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Pengaturan</h4>
                    <p class="text-sm text-gray-600">Pengaturan status dan konfigurasi supplier</p>
                </div>

                <!-- Status Toggle -->
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-600 to-green-700 rounded-lg flex items-center justify-center">
                            <i class="fas fa-toggle-on text-white"></i>
                        </div>
                        <div>
                            <h5 class="font-medium text-gray-900">Status Supplier</h5>
                            <p class="text-sm text-gray-600">Aktifkan supplier untuk dapat digunakan dalam transaksi</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}
                                   class="sr-only peer"
                                   id="status_toggle">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-900" id="status_label">{{ old('is_active', $supplier->is_active) ? 'Aktif' : 'Nonaktif' }}</span>
                        </label>
                    </div>
                </div>

                <!-- Warning if supplier has transactions -->
                @if($supplier->hasTransactions())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                            <div>
                                <h5 class="font-medium text-yellow-900">Peringatan</h5>
                                <p class="text-sm text-yellow-800 mt-1">
                                    Supplier ini memiliki {{ $supplier->getTotalPurchaseOrders() }} Purchase Order.
                                    Menonaktifkan supplier akan mencegah pembuatan PO baru, tetapi tidak akan mempengaruhi PO yang sudah ada.
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
                        <span class="text-gray-600">ID Supplier:</span>
                        <span class="ml-2 font-mono text-gray-900">{{ $supplier->supplier_id }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Dibuat:</span>
                        <span class="ml-2 text-gray-900">{{ $supplier->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Terakhir Diupdate:</span>
                        <span class="ml-2 text-gray-900">{{ $supplier->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-save"></i>
                    <span>Update Supplier</span>
                </button>
                <button type="button"
                        onclick="resetForm()"
                        class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-undo"></i>
                    <span>Reset Form</span>
                </button>
                <a href="{{ route('suppliers.show', $supplier->supplier_id) }}"
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total PO -->
                <div class="text-center p-4 bg-blue-50 rounded-xl">
                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-shopping-cart text-white"></i>
                    </div>
                    <div class="text-2xl font-bold text-blue-600">{{ $supplier->getTotalPurchaseOrders() }}</div>
                    <div class="text-sm text-gray-600">Total Purchase Order</div>
                </div>

                <!-- Active PO -->
                <div class="text-center p-4 bg-yellow-50 rounded-xl">
                    <div class="w-12 h-12 bg-yellow-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $supplier->getActivePurchaseOrders() }}</div>
                    <div class="text-sm text-gray-600">PO Aktif</div>
                </div>

                <!-- Days Active -->
                <div class="text-center p-4 bg-green-50 rounded-xl">
                    <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-calendar text-white"></i>
                    </div>
                    <div class="text-2xl font-bold text-green-600">{{ $supplier->created_at->diffInDays(now()) }}</div>
                    <div class="text-sm text-gray-600">Hari Terdaftar</div>
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
                <h4 class="text-lg font-semibold text-blue-900 mb-2">Tips Edit Supplier</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• <strong>Kode Supplier:</strong> Pastikan kode tetap unik setelah diubah</li>
                    <li>• <strong>Email:</strong> Jika diubah, pastikan email baru masih valid dan aktif</li>
                    <li>• <strong>Status:</strong> Menonaktifkan supplier akan mencegah pembuatan PO baru</li>
                    <li>• <strong>Kontak:</strong> Update informasi kontak jika ada perubahan di perusahaan supplier</li>
                    <li>• <strong>Riwayat:</strong> Semua perubahan akan tercatat dalam log aktivitas sistem</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function supplierEditForm() {
        return {
            init() {
                // Auto-format phone number while typing
                const phoneInput = document.getElementById('phone');
                if (phoneInput) {
                    phoneInput.addEventListener('input', function(e) {
                        // Remove all non-digit characters
                        let value = e.target.value.replace(/\D/g, '');

                        // Limit to reasonable phone number length
                        if (value.length > 15) {
                            value = value.substring(0, 15);
                        }

                        e.target.value = value;
                    });
                }

                // Auto-uppercase supplier code
                const codeInput = document.getElementById('supplier_code');
                if (codeInput) {
                    codeInput.addEventListener('input', function(e) {
                        e.target.value = e.target.value.toUpperCase();
                    });
                }

                // Update toggle label
                const toggleInput = document.getElementById('status_toggle');
                const toggleLabel = document.getElementById('status_label');

                if (toggleInput && toggleLabel) {
                    toggleInput.addEventListener('change', function() {
                        toggleLabel.textContent = this.checked ? 'Aktif' : 'Nonaktif';
                    });
                }
            }
        }
    }

    // Reset form function
    function resetForm() {
        if (confirm('Yakin ingin mereset semua perubahan ke nilai semula?')) {
            // Reset to original values
            document.getElementById('supplier_code').value = '{{ $supplier->supplier_code }}';
            document.getElementById('supplier_name').value = '{{ $supplier->supplier_name }}';
            document.getElementById('contact_person').value = '{{ $supplier->contact_person ?? '' }}';
            document.getElementById('phone').value = '{{ $supplier->phone ?? '' }}';
            document.getElementById('email').value = '{{ $supplier->email ?? '' }}';
            document.getElementById('address').value = `{{ $supplier->address ?? '' }}`;
            document.getElementById('status_toggle').checked = {{ $supplier->is_active ? 'true' : 'false' }};

            // Update toggle label
            const toggleLabel = document.getElementById('status_label');
            if (toggleLabel) {
                toggleLabel.textContent = '{{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}';
            }
        }
    }
</script>
@endpush
