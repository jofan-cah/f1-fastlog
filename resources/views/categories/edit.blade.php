@extends('layouts.app')

@section('title', 'Edit Kategori - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="categoryEdit()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('categories.index') }}"
               class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Kategori</h1>
                <p class="text-gray-600 mt-1">Ubah informasi kategori {{ $category->category_name }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('categories.show', $category->category_id) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-eye"></i>
                <span>Lihat Detail</span>
            </a>
        </div>
    </div>

    <form action="{{ route('categories.update', $category->category_id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Information Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Informasi Kategori
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">ID:</span>
                        <span class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ $category->category_id }}</span>
                    </div>
                </div>
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
                           value="{{ old('category_name', $category->category_name) }}"
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
                              class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('description') border-red-500 @enderror">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category Preview -->
                <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                            <i class="fas fa-{{ $category->parent_id ? 'tag' : 'tags' }} text-white text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900" x-text="categoryName || '{{ $category->category_name }}'"></h4>
                            <p class="text-sm text-gray-600">Kategori yang sedang diedit</p>
                        </div>
                        @if($category->children->count() > 0)
                            <div class="ml-auto">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $category->children->count() }} Sub-kategori
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Structure Info -->
        @if($category->parent || $category->children->count() > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-info-circle text-yellow-600 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800">Informasi Struktur</h4>
                        <div class="text-sm text-yellow-700 mt-1">
                            @if($category->parent)
                                <p>• Kategori ini adalah sub-kategori dari: <strong>{{ $category->parent->category_name }}</strong></p>
                            @endif
                            @if($category->children->count() > 0)
                                <p>• Kategori ini memiliki <strong>{{ $category->children->count() }} sub-kategori</strong></p>
                            @endif
                            @if($category->items_count > 0)
                                <p>• Kategori ini memiliki <strong>{{ $category->items_count }} barang</strong></p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Parent Category Selection -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-sitemap mr-2 text-green-600"></i>
                    Kategori Induk
                </h3>
                <p class="text-sm text-gray-600 mt-1">Ubah hierarki kategori (opsional)</p>
            </div>
            <div class="p-6">
                <!-- Current Parent Display -->
                @if($category->parent)
                    <div class="mb-4 p-3 bg-blue-50 rounded-xl border border-blue-200">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-tags text-blue-600"></i>
                            <div>
                                <p class="text-sm text-blue-800"><strong>Kategori Induk Saat Ini:</strong></p>
                                <p class="text-blue-900 font-medium">{{ $category->parent->category_name }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mb-4 p-3 bg-green-50 rounded-xl border border-green-200">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-tags text-green-600"></i>
                            <div>
                                <p class="text-sm text-green-800"><strong>Status Saat Ini:</strong></p>
                                <p class="text-green-900 font-medium">Kategori Utama (Tidak memiliki induk)</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Category Type Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Tipe Kategori</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio"
                                   name="category_type"
                                   value="root"
                                   x-model="categoryType"
                                   :disabled="hasChildren"
                                   class="sr-only peer">
                            <div :class="hasChildren ? 'p-4 border-2 border-gray-200 rounded-xl bg-gray-100 cursor-not-allowed opacity-50' : 'p-4 border-2 border-gray-200 rounded-xl hover:border-green-400 transition-all peer-checked:border-green-500 peer-checked:bg-green-50 cursor-pointer'">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center peer-checked:bg-green-200">
                                        <i class="fas fa-tags text-green-600 text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">Kategori Utama</h4>
                                        <p class="text-sm text-gray-600">Tidak memiliki kategori induk</p>
                                        @if($category->children->count() > 0)
                                            <p class="text-xs text-orange-600 mt-1">⚠️ Tidak dapat diubah (memiliki sub-kategori)</p>
                                        @endif
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
                                        <p class="text-sm text-gray-600">Memiliki kategori induk</p>
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
                                    <option value="{{ $id }}" {{ old('parent_id', $category->parent_id) == $id ? 'selected' : '' }}>
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
                            Struktur Kategori Baru
                        </h4>
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2 text-sm text-blue-800">
                                <i class="fas fa-tags"></i>
                                <span x-text="getSelectedParentName()"></span>
                            </div>
                            <i class="fas fa-chevron-right text-blue-600"></i>
                            <div class="flex items-center space-x-2 text-sm font-medium text-blue-900">
                                <i class="fas fa-tag"></i>
                                <span x-text="categoryName || '{{ $category->category_name }}'"></span>
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
                                   {{ old('is_active', $category->is_active) == '1' ? 'checked' : '' }}
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
                                   {{ old('is_active', $category->is_active) == '0' ? 'checked' : '' }}
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
            </div>
        </div>

        <!-- Changes Summary -->
        <div x-show="hasChanges()" class="bg-orange-50 border border-orange-200 rounded-2xl p-4">
            <div class="flex items-center space-x-3">
                <i class="fas fa-exclamation-circle text-orange-600"></i>
                <div>
                    <h4 class="text-sm font-medium text-orange-800">Perubahan Terdeteksi</h4>
                    <p class="text-sm text-orange-700">Anda telah melakukan perubahan pada kategori. Pastikan untuk menyimpan sebelum meninggalkan halaman.</p>
                </div>
            </div>
        </div>

        <!-- Account Timeline -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-clock mr-2 text-green-600"></i>
                    Informasi Kategori
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-plus text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Kategori dibuat</p>
                                <p class="text-xs text-gray-500">{{ $category->created_at->format('d M Y H:i') }} ({{ $category->created_at->diffForHumans() }})</p>
                            </div>
                        </div>

                        @if($category->created_at != $category->updated_at)
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-edit text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Terakhir diperbarui</p>
                                    <p class="text-xs text-gray-500">{{ $category->updated_at->format('d M Y H:i') }} ({{ $category->updated_at->diffForHumans() }})</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-layer-group text-purple-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Sub-kategori</p>
                                <p class="text-xs text-gray-500">{{ $category->children->count() }} kategori turunan</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-box text-yellow-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Total Barang</p>
                                <p class="text-xs text-gray-500">{{ $category->items_count }} barang dalam kategori ini</p>
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
                    <span>Update Kategori</span>
                </button>

                <a href="{{ route('categories.show', $category->category_id) }}"
                   class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-eye"></i>
                    <span>Lihat Detail</span>
                </a>

                <button type="button"
                        @click="resetToOriginal()"
                        class="flex-1 px-6 py-3 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-undo"></i>
                    <span>Reset</span>
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
    function categoryEdit() {
        return {
            categoryName: '{{ old('category_name', $category->category_name) }}',
            categoryType: '{{ old('parent_id', $category->parent_id) ? 'child' : 'root' }}',
            selectedParent: '{{ old('parent_id', $category->parent_id) }}',
            isActive: '{{ old('is_active', $category->is_active) }}',
            hasChildren: {{ $category->children->count() > 0 ? 'true' : 'false' }},
            parentOptions: @json($parentOptions),

            // Original values for comparison
            originalCategoryName: '{{ $category->category_name }}',
            originalParentId: '{{ $category->parent_id }}',
            originalIsActive: '{{ $category->is_active }}',

            init() {
                // Initialize values
                if (this.hasChildren && this.categoryType === 'root') {
                    // Force to root if has children
                    this.categoryType = 'root';
                    this.selectedParent = '';
                }
            },

            getSelectedParentName() {
                if (!this.selectedParent || !this.parentOptions) return '';
                return this.parentOptions[this.selectedParent] || '';
            },

            hasChanges() {
                return this.categoryName !== this.originalCategoryName ||
                       this.selectedParent !== this.originalParentId ||
                       this.isActive !== this.originalIsActive;
            },

            resetToOriginal() {
                this.categoryName = this.originalCategoryName;
                this.selectedParent = this.originalParentId;
                this.categoryType = this.originalParentId ? 'child' : 'root';
                this.isActive = this.originalIsActive;

                // Reset form fields
                document.getElementById('category_name').value = this.originalCategoryName;
                document.getElementById('parent_id').value = this.originalParentId;

                // Reset radio buttons
                if (this.originalParentId) {
                    document.querySelector('input[name="category_type"][value="child"]').checked = true;
                } else {
                    document.querySelector('input[name="category_type"][value="root"]').checked = true;
                }

                if (this.originalIsActive === '1') {
                    document.querySelector('input[name="is_active"][value="1"]').checked = true;
                } else {
                    document.querySelector('input[name="is_active"][value="0"]').checked = true;
                }
            }
        }
    }

    // Warn user about unsaved changes
    window.addEventListener('beforeunload', function(e) {
        const alpine = document.querySelector('[x-data]').__x.$data;
        if (alpine.hasChanges()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>
@endpush
