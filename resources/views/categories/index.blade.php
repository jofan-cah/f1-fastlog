@extends('layouts.app')

@section('title', 'Kategori Barang - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="categoryManager()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kategori Barang</h1>
            <p class="text-gray-600 mt-1">Kelola kategori dan sub-kategori barang</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <button onclick="window.location.href='{{ route('categories.create') }}'"
                    class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-plus"></i>
                <span>Tambah Kategori</span>
            </button>
        </div>
    </div>

    <!-- Filters & View Toggle Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <form method="GET" action="{{ route('categories.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari nama kategori atau deskripsi..."
                               class="pl-10 pr-4 py-3 w-full bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                <!-- View Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tampilan</label>
                    <select name="view"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        <option value="tree" {{ request('view', 'tree') == 'tree' ? 'selected' : '' }}>Tree Structure</option>
                        <option value="flat" {{ request('view') == 'flat' ? 'selected' : '' }}>List Flat</option>
                    </select>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-filter"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('categories.index') }}"
                   class="px-6 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Categories Display -->
    @if($viewType === 'tree')
        <!-- Tree View -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-sitemap mr-2 text-green-600"></i>
                        Struktur Kategori (Tree View)
                    </h3>
                    <div class="flex items-center space-x-3">
                        <button @click="expandAll()"
                                class="px-3 py-1 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm">
                            <i class="fas fa-expand-arrows-alt mr-1"></i>
                            Buka Semua
                        </button>
                        <button @click="collapseAll()"
                                class="px-3 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm">
                            <i class="fas fa-compress-arrows-alt mr-1"></i>
                            Tutup Semua
                        </button>
                        <span class="text-sm text-gray-600">Total: {{ $categories->total() }} kategori</span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                @forelse($categories as $category)
                    <div class="category-tree-item" x-data="{ expanded: true }">
                        <!-- Root Category -->
                        <div class="flex items-center space-x-3 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl mb-3 hover:shadow-md transition-all duration-200">
                            <!-- Expand/Collapse Button -->
                            @if($category->children->count() > 0)
                                <button @click="expanded = !expanded"
                                        class="w-8 h-8 bg-white rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-colors">
                                    <i class="fas transition-transform duration-200"
                                       :class="expanded ? 'fa-minus text-red-600' : 'fa-plus text-green-600'"></i>
                                </button>
                            @else
                                <div class="w-8 h-8 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                                    <i class="fas fa-circle text-gray-400 text-xs"></i>
                                </div>
                            @endif

                            <!-- Category Icon -->
                            <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                                <i class="fas fa-tags text-white text-lg"></i>
                            </div>

                            <!-- Category Info -->
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $category->category_name }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $category->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                    @if($category->children->count() > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $category->children->count() }} Sub-kategori
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ $category->items_count }} Barang
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ $category->description ?? 'Tidak ada deskripsi' }}</p>
                                <p class="text-xs text-gray-500 mt-1">ID: {{ $category->category_id }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('categories.show', $category->category_id) }}"
                                   class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                   title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('categories.edit', $category->category_id) }}"
                                   class="p-2 text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 rounded-lg transition-all duration-200"
                                   title="Edit Kategori">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button @click="showToggleModal('{{ $category->category_id }}', '{{ addslashes($category->category_name) }}', {{ $category->is_active ? 'true' : 'false' }})"
                                        class="p-2 rounded-lg transition-all duration-200 {{ $category->is_active ? 'text-orange-600 hover:text-orange-900 hover:bg-orange-50' : 'text-green-600 hover:text-green-900 hover:bg-green-50' }}"
                                        title="{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Kategori">
                                    <i class="fas fa-{{ $category->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                </button>
                                <button @click="showDeleteModal('{{ $category->category_id }}', '{{ addslashes($category->category_name) }}', {{ $category->children->count() }}, {{ $category->items_count }})"
                                        class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-all duration-200"
                                        title="Hapus Kategori">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Children Categories -->
                        @if($category->children->count() > 0)
                            <div x-show="expanded"
                                 x-transition:enter="transition-all duration-300 ease-out"
                                 x-transition:enter-start="opacity-0 max-h-0"
                                 x-transition:enter-end="opacity-100 max-h-screen"
                                 x-transition:leave="transition-all duration-300 ease-in"
                                 x-transition:leave-start="opacity-100 max-h-screen"
                                 x-transition:leave-end="opacity-0 max-h-0"
                                 class="ml-12 space-y-2 overflow-hidden">
                                @foreach($category->children as $child)
                                    <!-- Level 2 Category -->
                                    <div x-data="{ childExpanded: false }" class="space-y-2">
                                        <div class="flex items-center space-x-3 p-3 bg-white border-l-4 border-blue-400 rounded-lg hover:shadow-sm transition-all duration-200">
                                            <!-- Expand/Collapse for Level 2 -->
                                            @if($child->children->count() > 0)
                                                <button @click="childExpanded = !childExpanded"
                                                        class="w-6 h-6 bg-white rounded border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors">
                                                    <i class="fas transition-transform duration-200 text-xs"
                                                       :class="childExpanded ? 'fa-minus text-red-500' : 'fa-plus text-green-500'"></i>
                                                </button>
                                            @else
                                                <div class="w-6 h-6 bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                                                    <i class="fas fa-circle text-gray-300 text-xs"></i>
                                                </div>
                                            @endif

                                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-tag text-white text-sm"></i>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3">
                                                    <h5 class="font-medium text-gray-900">{{ $child->category_name }}</h5>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                        {{ $child->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $child->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                                    </span>
                                                    @if($child->children->count() > 0)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                            {{ $child->children->count() }} Sub
                                                        </span>
                                                    @endif
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                        {{ $child->items_count }} Barang
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600">{{ $child->description ?? 'Tidak ada deskripsi' }}</p>
                                                <p class="text-xs text-gray-500">ID: {{ $child->category_id }}</p>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('categories.show', $child->category_id) }}"
                                                   class="p-1.5 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                                   title="Lihat Detail">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </a>
                                                <a href="{{ route('categories.edit', $child->category_id) }}"
                                                   class="p-1.5 text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 rounded-lg transition-all duration-200"
                                                   title="Edit Kategori">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </a>
                                                <button @click="showToggleModal('{{ $child->category_id }}', '{{ addslashes($child->category_name) }}', {{ $child->is_active ? 'true' : 'false' }})"
                                                        class="p-1.5 rounded-lg transition-all duration-200 {{ $child->is_active ? 'text-orange-600 hover:text-orange-900 hover:bg-orange-50' : 'text-green-600 hover:text-green-900 hover:bg-green-50' }}"
                                                        title="{{ $child->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Kategori">
                                                    <i class="fas fa-{{ $child->is_active ? 'toggle-on' : 'toggle-off' }} text-sm"></i>
                                                </button>
                                                <button @click="showDeleteModal('{{ $child->category_id }}', '{{ addslashes($child->category_name) }}', {{ $child->children->count() }}, {{ $child->items_count }})"
                                                        class="p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-all duration-200"
                                                        title="Hapus Kategori">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Level 3 Categories (Grandchildren) -->
                                        @if($child->children->count() > 0)
                                            <div x-show="childExpanded"
                                                 x-transition:enter="transition-all duration-300 ease-out"
                                                 x-transition:enter-start="opacity-0 max-h-0"
                                                 x-transition:enter-end="opacity-100 max-h-screen"
                                                 x-transition:leave="transition-all duration-300 ease-in"
                                                 x-transition:leave-start="opacity-100 max-h-screen"
                                                 x-transition:leave-end="opacity-0 max-h-0"
                                                 class="ml-10 space-y-2 overflow-hidden">
                                                @foreach($child->children as $grandchild)
                                                    <!-- Level 3 Category -->
                                                    <div x-data="{ grandExpanded: false }" class="space-y-2">
                                                        <div class="flex items-center space-x-3 p-2.5 bg-white border-l-4 border-indigo-400 rounded-lg hover:shadow-sm transition-all duration-200">
                                                            <!-- Expand/Collapse for Level 3 (if has children) -->
                                                            @if($grandchild->children->count() > 0)
                                                                <button @click="grandExpanded = !grandExpanded"
                                                                        class="w-5 h-5 bg-white rounded border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors">
                                                                    <i class="fas transition-transform duration-200 text-xs"
                                                                       :class="grandExpanded ? 'fa-minus text-red-500' : 'fa-plus text-green-500'"></i>
                                                                </button>
                                                            @else
                                                                <div class="w-5 h-5 bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                                                                    <i class="fas fa-circle text-gray-300 text-xs"></i>
                                                                </div>
                                                            @endif

                                                            <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                                                <i class="fas fa-tag text-white text-xs"></i>
                                                            </div>
                                                            <div class="flex-1">
                                                                <div class="flex items-center space-x-2">
                                                                    <h6 class="font-medium text-gray-900 text-sm">{{ $grandchild->category_name }}</h6>
                                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium
                                                                        {{ $grandchild->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                                        {{ $grandchild->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                                                    </span>
                                                                    @if($grandchild->children->count() > 0)
                                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                                            {{ $grandchild->children->count() }} Sub
                                                                        </span>
                                                                    @endif
                                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                        {{ $grandchild->items_count }} Barang
                                                                    </span>
                                                                </div>
                                                                <p class="text-xs text-gray-600">{{ $grandchild->description ?? 'Tidak ada deskripsi' }}</p>
                                                                <p class="text-xs text-gray-400">ID: {{ $grandchild->category_id }}</p>
                                                            </div>
                                                            <div class="flex items-center space-x-1">
                                                                <a href="{{ route('categories.show', $grandchild->category_id) }}"
                                                                   class="p-1 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded transition-all duration-200"
                                                                   title="Lihat Detail">
                                                                    <i class="fas fa-eye text-xs"></i>
                                                                </a>
                                                                <a href="{{ route('categories.edit', $grandchild->category_id) }}"
                                                                   class="p-1 text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 rounded transition-all duration-200"
                                                                   title="Edit">
                                                                    <i class="fas fa-edit text-xs"></i>
                                                                </a>
                                                                <button @click="showToggleModal('{{ $grandchild->category_id }}', '{{ addslashes($grandchild->category_name) }}', {{ $grandchild->is_active ? 'true' : 'false' }})"
                                                                        class="p-1 rounded transition-all duration-200 {{ $grandchild->is_active ? 'text-orange-600 hover:text-orange-900 hover:bg-orange-50' : 'text-green-600 hover:text-green-900 hover:bg-green-50' }}"
                                                                        title="{{ $grandchild->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                                    <i class="fas fa-{{ $grandchild->is_active ? 'toggle-on' : 'toggle-off' }} text-xs"></i>
                                                                </button>
                                                                <button @click="showDeleteModal('{{ $grandchild->category_id }}', '{{ addslashes($grandchild->category_name) }}', {{ $grandchild->children->count() }}, {{ $grandchild->items_count }})"
                                                                        class="p-1 text-red-600 hover:text-red-900 hover:bg-red-50 rounded transition-all duration-200"
                                                                        title="Hapus">
                                                                    <i class="fas fa-trash text-xs"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Level 4+ Categories (jika ada) -->
                                                        @if($grandchild->children->count() > 0)
                                                            <div x-show="grandExpanded"
                                                                 x-transition:enter="transition-all duration-300 ease-out"
                                                                 x-transition:enter-start="opacity-0 max-h-0"
                                                                 x-transition:enter-end="opacity-100 max-h-screen"
                                                                 x-transition:leave="transition-all duration-300 ease-in"
                                                                 x-transition:leave-start="opacity-100 max-h-screen"
                                                                 x-transition:leave-end="opacity-0 max-h-0"
                                                                 class="ml-8 space-y-1 overflow-hidden">
                                                                @foreach($grandchild->children as $greatgrand)
                                                                    <div class="flex items-center space-x-2 p-2 bg-white border-l-4 border-purple-400 rounded-lg hover:shadow-sm transition-all duration-200">
                                                                        <div class="w-6 h-6 bg-gradient-to-br from-purple-500 to-purple-600 rounded flex items-center justify-center">
                                                                            <i class="fas fa-tag text-white text-xs"></i>
                                                                        </div>
                                                                        <div class="flex-1 min-w-0">
                                                                            <div class="flex items-center space-x-2">
                                                                                <h6 class="font-medium text-gray-900 text-xs truncate">{{ $greatgrand->category_name }}</h6>
                                                                                <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium
                                                                                    {{ $greatgrand->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                                                    {{ $greatgrand->is_active ? 'A' : 'N' }}
                                                                                </span>
                                                                                <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                                    {{ $greatgrand->items_count }}
                                                                                </span>
                                                                            </div>
                                                                            <p class="text-xs text-gray-400 truncate">{{ $greatgrand->category_id }}</p>
                                                                        </div>
                                                                        <div class="flex items-center space-x-1">
                                                                            <a href="{{ route('categories.show', $greatgrand->category_id) }}"
                                                                               class="p-1 text-blue-600 hover:bg-blue-50 rounded text-xs"
                                                                               title="Detail">
                                                                                <i class="fas fa-eye"></i>
                                                                            </a>
                                                                            <a href="{{ route('categories.edit', $greatgrand->category_id) }}"
                                                                               class="p-1 text-yellow-600 hover:bg-yellow-50 rounded text-xs"
                                                                               title="Edit">
                                                                                <i class="fas fa-edit"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                @endforeach

                                                                @if($grandchild->children->count() > 5)
                                                                    <div class="text-center py-2">
                                                                        <a href="{{ route('categories.show', $grandchild->category_id) }}"
                                                                           class="text-xs text-blue-600 hover:text-blue-800">
                                                                            Lihat {{ $grandchild->children->count() - 5 }} sub-kategori lainnya
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-12">
                        <i class="fas fa-tags text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-medium text-gray-900 mb-2">Tidak ada kategori</h3>
                        <p class="text-gray-500 mb-6">Belum ada kategori yang terdaftar dalam sistem.</p>
                        <a href="{{ route('categories.create') }}"
                           class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 inline-flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>Tambah Kategori Pertama</span>
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <!-- Flat List View -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-list mr-2 text-blue-600"></i>
                        Daftar Kategori (Flat View)
                    </h3>
                    <span class="text-sm text-gray-600">Total: {{ $categories->total() }} kategori</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sub-kategori</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($categories as $category)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <!-- Category Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-red-600 to-red-700 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-{{ $category->parent_id ? 'tag' : 'tags' }} text-white"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $category->category_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $category->description ?? 'Tidak ada deskripsi' }}</div>
                                            <div class="text-xs text-gray-400">ID: {{ $category->category_id }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Parent Category -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($category->parent)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $category->parent->category_name }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500">Root Category</span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                            {{ $category->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                        {{ $category->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>

                                <!-- Children Count -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $category->children->count() }}
                                </td>

                                <!-- Items Count -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $category->items_count }}
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('categories.show', $category->category_id) }}"
                                           class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('categories.edit', $category->category_id) }}"
                                           class="text-yellow-600 hover:text-yellow-900 p-2 hover:bg-yellow-50 rounded-lg transition-all duration-200"
                                           title="Edit Kategori">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button @click="showToggleModal('{{ $category->category_id }}', '{{ addslashes($category->category_name) }}', {{ $category->is_active ? 'true' : 'false' }})"
                                                class="p-2 rounded-lg transition-all duration-200 {{ $category->is_active ? 'text-orange-600 hover:text-orange-900 hover:bg-orange-50' : 'text-green-600 hover:text-green-900 hover:bg-green-50' }}"
                                                title="{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Kategori">
                                            <i class="fas fa-{{ $category->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                        </button>
                                        <button @click="showDeleteModal('{{ $category->category_id }}', '{{ addslashes($category->category_name) }}', {{ $category->children->count() }}, {{ $category->items_count }})"
                                                class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded-lg transition-all duration-200"
                                                title="Hapus Kategori">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-tags text-4xl text-gray-300 mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada kategori</h3>
                                        <p class="text-gray-500 mb-4">Belum ada kategori yang terdaftar dalam sistem.</p>
                                        <a href="{{ route('categories.create') }}"
                                           class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200">
                                            Tambah Kategori Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Pagination -->
    @if($categories->hasPages())
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Menampilkan {{ $categories->firstItem() }} sampai {{ $categories->lastItem() }}
                    dari {{ $categories->total() }} hasil
                </div>
                <div class="flex items-center space-x-2">
                    {{ $categories->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hideDeleteModal()"
         @keydown.escape.window="hideDeleteModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="deleteModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash text-2xl text-red-600"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Hapus Kategori</h3>

                <div class="text-gray-600 text-center mb-6">
                    <p class="mb-2">
                        Apakah Anda yakin ingin menghapus kategori <span x-text="deleteModal.categoryName" class="font-semibold text-gray-900"></span>?
                    </p>

                    <div x-show="deleteModal.childrenCount > 0 || deleteModal.itemsCount > 0" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-start space-x-2 text-yellow-800">
                            <i class="fas fa-exclamation-triangle mt-0.5"></i>
                            <div class="text-sm">
                                <div x-show="deleteModal.childrenCount > 0" class="font-medium">
                                    Kategori ini memiliki <span x-text="deleteModal.childrenCount"></span> sub-kategori
                                </div>
                                <div x-show="deleteModal.itemsCount > 0" class="font-medium">
                                    Kategori ini memiliki <span x-text="deleteModal.itemsCount"></span> barang
                                </div>
                                <p class="text-xs mt-1">Kategori tidak dapat dihapus jika masih memiliki sub-kategori atau barang</p>
                            </div>
                        </div>
                    </div>

                    <div x-show="deleteModal.childrenCount === 0 && deleteModal.itemsCount === 0" class="text-sm text-red-600 mt-2">
                        Tindakan ini tidak dapat dibatalkan.
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            @click="confirmDelete()"
                            :disabled="deleteModal.childrenCount > 0 || deleteModal.itemsCount > 0"
                            :class="(deleteModal.childrenCount > 0 || deleteModal.itemsCount > 0) ? 'flex-1 px-4 py-3 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed flex items-center justify-center space-x-2' : 'flex-1 px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl'">
                        <i class="fas fa-trash"></i>
                        <span>Hapus</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Status Confirmation Modal -->
    <div x-show="toggleModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hideToggleModal()"
         @keydown.escape.window="hideToggleModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="toggleModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div :class="toggleModal.isActive ? 'w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4' : 'w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4'">
                    <i :class="toggleModal.isActive ? 'fas fa-toggle-off text-2xl text-orange-600' : 'fas fa-toggle-on text-2xl text-green-600'"></i>
                </div>

                <h3 x-text="toggleModal.isActive ? 'Nonaktifkan Kategori' : 'Aktifkan Kategori'" class="text-xl font-bold text-gray-900 text-center mb-2"></h3>

                <p class="text-gray-600 text-center mb-6">
                    Apakah Anda yakin ingin <span x-text="toggleModal.isActive ? 'menonaktifkan' : 'mengaktifkan'" class="font-semibold"></span> kategori <span x-text="toggleModal.categoryName" class="font-semibold text-gray-900"></span>?
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            @click="hideToggleModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="button"
                            @click="confirmToggle()"
                            :class="toggleModal.isActive ? 'flex-1 px-4 py-3 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-xl hover:from-orange-700 hover:to-orange-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl' : 'flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl'">
                        <i :class="toggleModal.isActive ? 'fas fa-toggle-off' : 'fas fa-toggle-on'"></i>
                        <span x-text="toggleModal.isActive ? 'Nonaktifkan' : 'Aktifkan'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

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
    function categoryManager() {
        return {
            deleteModal: {
                show: false,
                categoryId: '',
                categoryName: '',
                childrenCount: 0,
                itemsCount: 0
            },
            toggleModal: {
                show: false,
                categoryId: '',
                categoryName: '',
                isActive: false
            },

            // Expand/Collapse functionality
            expandAll() {
                // Set all tree items to expanded
                document.querySelectorAll('.category-tree-item').forEach(item => {
                    const alpineData = Alpine.$data(item);
                    if (alpineData) {
                        alpineData.expanded = true;
                    }
                });
            },

            collapseAll() {
                // Set all tree items to collapsed
                document.querySelectorAll('.category-tree-item').forEach(item => {
                    const alpineData = Alpine.$data(item);
                    if (alpineData) {
                        alpineData.expanded = false;
                    }
                });
            },

            // Delete Modal Functions
            showDeleteModal(categoryId, categoryName, childrenCount, itemsCount) {
                this.deleteModal = {
                    show: true,
                    categoryId: categoryId,
                    categoryName: categoryName,
                    childrenCount: parseInt(childrenCount),
                    itemsCount: parseInt(itemsCount)
                };
            },

            hideDeleteModal() {
                this.deleteModal.show = false;
                setTimeout(() => {
                    this.deleteModal = {
                        show: false,
                        categoryId: '',
                        categoryName: '',
                        childrenCount: 0,
                        itemsCount: 0
                    };
                }, 300);
            },

            confirmDelete() {
                  console.log('confirmDelete called');
    console.log('categoryId:', this.deleteModal.categoryId);
    console.log('childrenCount:', this.deleteModal.childrenCount);
    console.log('itemsCount:', this.deleteModal.itemsCount);
                if ( this.deleteModal.childrenCount === 0) {
                    // Create and submit delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ route('categories.index') }}/${this.deleteModal.categoryId}`;
                    form.style.display = 'none';

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);

                    this.hideDeleteModal();
                    form.submit();
                }else{
                     console.log('Conditions not met');
                }
            },

            // Toggle Status Modal Functions
            showToggleModal(categoryId, categoryName, isActive) {
                this.toggleModal = {
                    show: true,
                    categoryId: categoryId,
                    categoryName: categoryName,
                    isActive: isActive === true || isActive === 'true'
                };
            },

            hideToggleModal() {
                this.toggleModal.show = false;
                setTimeout(() => {
                    this.toggleModal = {
                        show: false,
                        categoryId: '',
                        categoryName: '',
                        isActive: false
                    };
                }, 300);
            },

            confirmToggle() {
                if (this.toggleModal.categoryId) {
                    // Create and submit toggle form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ route('categories.index') }}/${this.toggleModal.categoryId}/toggle-status`;
                    form.style.display = 'none';

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'PATCH';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);

                    this.hideToggleModal();
                    form.submit();
                }
            }
        }
    }
</script>
@endpush
