@extends('layouts.app')

@section('title', 'Tambah Kategori - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="categoryCreate()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('categories.index') }}"
               class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Kategori Baru</h1>
                <p class="text-gray-600 mt-1">Buat kategori atau sub-kategori baru untuk mengorganisir barang</p>
            </div>
        </div>
    </div>

    <form action="{{ route('categories.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Basic Information Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                    Informasi Kategori
                </h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Category Name -->
                <div>
                    <label for="category_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="category_name"
                           name="category_name"
                           value="{{ old('category_name') }}"
                           placeholder="Contoh: Elektronik, Furniture, Alat Tulis"
                           x-model="categoryName"
                           class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('category_name') border-red-500 @enderror"
                           required>
                    @error('category_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Deskripsi
                    </label>
                    <textarea id="description"
                              name="description"
                              rows="4"
                              placeholder="Jelaskan kategori ini dan jenis barang yang termasuk di dalamnya..."
                              class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category Preview -->
                <div x-show="categoryName" class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                            <i class="fas fa-tags text-white text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900" x-text="categoryName"></h4>
                            <p class="text-sm text-gray-600">Preview kategori yang akan dibuat</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parent Category Selection -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-sitemap mr-2 text-green-600"></i>
                    Kategori Induk (Opsional)
                </h3>
                <p class="text-sm text-gray-600 mt-1">Pilih kategori induk jika ingin membuat sub-kategori</p>
            </div>
            <div class="p-6">
                <!-- Category Type Selection -->
                <div class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio"
                                   name="category_type"
                                   value="root"
                                   x-model="categoryType"
                                   class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 rounded-xl hover:border-green-400 transition-all peer-checked:border-green-500 peer-checked:bg-green-50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center peer-checked:bg-green-200">
                                        <i class="fas fa-tags text-green-600 text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">Kategori Utama</h4>
                                        <p class="text-sm text-gray-600">Buat kategori baru tanpa induk</p>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <label class="relative cursor-pointer">
                            <input type="radio"
                                   name="category_type"
                                   value="child"
                                   x-model="categoryType"
                                   class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 rounded-xl hover:border-blue-400 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center peer-checked:bg-blue-200">
                                        <i class="fas fa-tag text-blue-600 text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">Sub-Kategori</h4>
                                        <p class="text-sm text-gray-600">Buat kategori di bawah kategori yang ada</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Parent Category Selector -->
                <div x-show="categoryType === 'child'"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95">
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Kategori Induk <span class="text-red-500">*</span>
                        </label>
                        <select id="parent_id"
                                name="parent_id"
                                x-model="selectedParent"
                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('parent_id') border-red-500 @enderror">
                            <option value="">-- Pilih Kategori Induk --</option>
                            @foreach($parentOptions as $id => $name)
                                @if($id) <!-- Skip empty option -->
                                    <option value="{{ $id }}" {{ old('parent_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Selected Parent Preview -->
                    <div x-show="selectedParent" class="mt-4 p-4 bg-blue-50 rounded-xl border border-blue-200">
                        <h4 class="font-medium text-blue-900 mb-2 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            Struktur Kategori
                        </h4>
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2 text-sm text-blue-800">
                                <i class="fas fa-tags"></i>
                                <span x-text="getSelectedParentName()"></span>
                            </div>
                            <i class="fas fa-chevron-right text-blue-600"></i>
                            <div class="flex items-center space-x-2 text-sm font-medium text-blue-900">
                                <i class="fas fa-tag"></i>
                                <span x-text="categoryName || 'Kategori Baru'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Settings -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-cog mr-2 text-purple-600"></i>
                    Pengaturan Kategori
                </h3>
            </div>
            <div class="p-6">
                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Status Kategori</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                                   x-model="isActive"
                                   class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 rounded-xl hover:border-green-400 transition-all peer-checked:border-green-500 peer-checked:bg-green-50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center peer-checked:bg-green-200">
                                        <i class="fas fa-check text-green-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Aktif</h4>
                                        <p class="text-sm text-gray-600">Kategori dapat digunakan</p>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <label class="relative cursor-pointer">
                            <input type="radio"
                                   name="is_active"
                                   value="0"
                                   {{ old('is_active') == '0' ? 'checked' : '' }}
                                   x-model="isActive"
                                   class="sr-only peer">
                            <div class="p-4 border-2 border-gray-200 rounded-xl hover:border-red-400 transition-all peer-checked:border-red-500 peer-checked:bg-red-50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center peer-checked:bg-red-200">
                                        <i class="fas fa-times text-red-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Tidak Aktif</h4>
                                        <p class="text-sm text-gray-600">Kategori tidak dapat digunakan</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Category ID Preview -->
                <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <h4 class="font-medium text-gray-900 mb-2 flex items-center">
                        <i class="fas fa-barcode mr-2 text-gray-600"></i>
                        ID Kategori
                    </h4>
                    <p class="text-sm text-gray-600 mb-2">ID akan digenerate otomatis sesuai format sistem</p>
                    <div class="font-mono text-gray-800 bg-white px-3 py-2 rounded-lg border">
                        CAT### (contoh: CAT001, CAT002, dst.)
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Summary -->
        <div x-show="categoryName" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-clipboard-check mr-2 text-indigo-600"></i>
                    Ringkasan Kategori
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Kategori</label>
                            <p class="text-gray-900 font-medium" x-text="categoryName || '-'"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipe Kategori</label>
                            <p class="text-gray-900" x-text="categoryType === 'root' ? 'Kategori Utama' : 'Sub-Kategori'"></p>
                        </div>
                        <div x-show="categoryType === 'child'">
                            <label class="block text-sm font-medium text-gray-700">Kategori Induk</label>
                            <p class="text-gray-900" x-text="getSelectedParentName() || '-'"></p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <p class="text-gray-900" x-text="isActive === '1' ? 'Aktif' : 'Tidak Aktif'"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Hierarki</label>
                            <div class="flex items-center space-x-2 text-sm">
                                <span x-show="categoryType === 'child'" class="text-blue-600" x-text="getSelectedParentName()"></span>
                                <span x-show="categoryType === 'child'" class="text-gray-400">></span>
                                <span class="font-medium text-gray-900" x-text="categoryName || 'Kategori Baru'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-save"></i>
                    <span>Simpan Kategori</span>
                </button>

                <button type="button"
                        @click="resetForm()"
                        class="flex-1 px-6 py-3 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-redo"></i>
                    <span>Reset Form</span>
                </button>

                <a href="{{ route('categories.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>Batal</span>
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function categoryCreate() {
        return {
            categoryName: '{{ old('category_name') }}',
            categoryType: '{{ old('parent_id') ? 'child' : 'root' }}',
            selectedParent: '{{ old('parent_id') }}',
            isActive: '{{ old('is_active', '1') }}',
            parentOptions: @json($parentOptions),

            init() {
                // Set initial values if old input exists
                if (this.selectedParent) {
                    this.categoryType = 'child';
                }
            },

            getSelectedParentName() {
                if (!this.selectedParent || !this.parentOptions) return '';
                return this.parentOptions[this.selectedParent] || '';
            },

            resetForm() {
                this.categoryName = '';
                this.categoryType = 'root';
                this.selectedParent = '';
                this.isActive = '1';

                // Reset form fields
                document.getElementById('category_name').value = '';
                document.getElementById('description').value = '';
                document.getElementById('parent_id').value = '';

                // Reset radio buttons
                document.querySelector('input[name="category_type"][value="root"]').checked = true;
                document.querySelector('input[name="is_active"][value="1"]').checked = true;
            }
        }
    }
</script>
@endpush
