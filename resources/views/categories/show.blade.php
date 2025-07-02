@extends('layouts.app')

@section('title', 'Detail Kategori - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="categoryShow()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('categories.index') }}"
               class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Kategori</h1>
                <p class="text-gray-600 mt-1">Informasi lengkap {{ $category->category_name }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('categories.edit', $category->category_id) }}"
               class="px-4 py-2 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-edit"></i>
                <span>Edit Kategori</span>
            </a>

            <!-- Toggle Status Button -->
            <button @click="showToggleModal()"
                    class="px-4 py-2 rounded-xl transition-all duration-200 flex items-center space-x-2 {{ $category->is_active ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }} text-white">
                <i class="fas fa-{{ $category->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                <span>{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
            </button>

            <!-- Delete Button -->
            <button @click="showDeleteModal()"
                    class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-trash"></i>
                <span>Hapus</span>
            </button>
        </div>
    </div>

    <!-- Breadcrumb -->
    @if(count($breadcrumb) > 1)
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    @foreach($breadcrumb as $index => $crumb)
                        <li class="inline-flex items-center">
                            @if($index > 0)
                                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            @endif
                            @if($loop->last)
                                <span class="ml-1 text-sm font-medium text-gray-900 md:ml-2">{{ $crumb['name'] }}</span>
                            @else
                                <a href="{{ route('categories.show', $crumb['id']) }}"
                                   class="ml-1 text-sm font-medium text-blue-600 hover:text-blue-800 md:ml-2">
                                    {{ $crumb['name'] }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Category Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-8 text-center bg-gradient-to-br from-red-50 to-red-100">
                    <!-- Avatar -->
                    <div class="w-24 h-24 bg-gradient-to-br from-red-600 to-red-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-{{ $category->parent_id ? 'tag' : 'tags' }} text-white text-3xl"></i>
                    </div>

                    <!-- Category Info -->
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $category->category_name }}</h2>
                    <p class="text-sm text-gray-600 mb-1">{{ $category->description ?? 'Tidak ada deskripsi' }}</p>
                    <p class="text-xs text-gray-500 mb-4">ID: {{ $category->category_id }}</p>

                    <!-- Status Badge -->
                    <div class="flex justify-center mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <span class="w-2 h-2 rounded-full mr-2
                                {{ $category->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                            {{ $category->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>

                    <!-- Type Badge -->
                    <div class="flex justify-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $category->parent_id ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            <i class="fas fa-{{ $category->parent_id ? 'tag' : 'tags' }} mr-2"></i>
                            {{ $category->parent_id ? 'Sub-Kategori' : 'Kategori Utama' }}
                        </span>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-blue-600">{{ $category->children->count() }}</div>
                            <div class="text-xs text-gray-600">Sub-kategori</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600">{{ $category->items->count() }}</div>
                            <div class="text-xs text-gray-600">Barang</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Informasi Kategori
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ID Kategori</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="text-gray-900 font-mono">{{ $category->category_id }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="text-gray-900 font-semibold">{{ $category->category_name }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Level/Depth</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="text-gray-900">Level {{ $category->getLevel() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Induk</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    @if($category->parent)
                                        <a href="{{ route('categories.show', $category->parent->category_id) }}"
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            {{ $category->parent->category_name }}
                                        </a>
                                    @else
                                        <span class="text-gray-500">Tidak ada (Kategori Utama)</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="inline-flex items-center text-sm font-medium
                                        {{ $category->is_active ? 'text-green-800' : 'text-red-800' }}">
                                        <span class="w-2 h-2 rounded-full mr-2
                                            {{ $category->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                        {{ $category->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Path Lengkap</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="text-gray-900 text-sm">{{ $category->getFullPath() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($category->description)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                                <p class="text-gray-900 leading-relaxed">{{ $category->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sub-Categories -->
            @if($category->children->count() > 0)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-layer-group mr-2 text-green-600"></i>
                            Sub-Kategori ({{ $category->children->count() }})
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($category->children as $child)
                                <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-tag text-white"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <h4 class="font-semibold text-gray-900">{{ $child->category_name }}</h4>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                    {{ $child->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $child->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">{{ $child->description ?? 'Tidak ada deskripsi' }}</p>
                                            <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                                <span><i class="fas fa-layer-group mr-1"></i>{{ $child->children->count() }} Sub</span>
                                                <span><i class="fas fa-box mr-1"></i>{{ $child->items->count() }} Barang</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-1">
                                            <a href="{{ route('categories.show', $child->category_id) }}"
                                               class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('categories.edit', $child->category_id) }}"
                                               class="p-2 text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 rounded-lg transition-all duration-200"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($category->children->count() > 4)
                            <div class="mt-4 text-center">
                                <a href="{{ route('categories.index', ['parent' => $category->category_id]) }}"
                                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                    Lihat Semua Sub-Kategori ({{ $category->children->count() }})
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Items in Category -->
            @if($category->items->count() > 0)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-boxes mr-2 text-purple-600"></i>
                            Barang dalam Kategori ({{ $category->items->count() }})
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($category->items->take(6) as $item)
                                <div class="border border-gray-200 rounded-xl p-3 hover:shadow-md transition-shadow">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-white text-sm"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-medium text-gray-900 truncate">{{ $item->item_name }}</h4>
                                            <p class="text-xs text-gray-500">{{ $item->item_code }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($category->items->count() > 6)
                            <div class="mt-4 text-center">
                                <a href="{{ route('items.index', ['category' => $category->category_id]) }}"
                                   class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm">
                                    Lihat Semua Barang ({{ $category->items->count() }})
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Category Statistics -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-pie mr-2 text-indigo-600"></i>
                        Statistik Kategori
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-xl">
                            <div class="text-2xl font-bold text-blue-600">{{ $category->getLevel() }}</div>
                            <div class="text-sm text-gray-600">Level</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-xl">
                            <div class="text-2xl font-bold text-green-600">{{ $category->children->count() }}</div>
                            <div class="text-sm text-gray-600">Sub-kategori</div>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-xl">
                            <div class="text-2xl font-bold text-purple-600">{{ $category->items->count() }}</div>
                            <div class="text-sm text-gray-600">Barang Langsung</div>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-xl">
                            @php
                                $totalItems = $category->items->count();
                                foreach($category->children as $child) {
                                    $totalItems += $child->items->count();
                                }
                            @endphp
                            <div class="text-2xl font-bold text-yellow-600">{{ $totalItems }}</div>
                            <div class="text-sm text-gray-600">Total Barang</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Timeline -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock mr-2 text-green-600"></i>
                        Timeline Kategori
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-plus text-green-600 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Kategori dibuat</p>
                                <p class="text-xs text-gray-500">{{ $category->created_at->format('d M Y H:i') }} ({{ $category->created_at->diffForHumans() }})</p>
                            </div>
                        </div>

                        @if($category->created_at != $category->updated_at)
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-edit text-blue-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Terakhir diperbarui</p>
                                    <p class="text-xs text-gray-500">{{ $category->updated_at->format('d M Y H:i') }} ({{ $category->updated_at->diffForHumans() }})</p>
                                </div>
                            </div>
                        @endif

                        @if($category->children->count() > 0)
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-layer-group text-purple-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Sub-kategori</p>
                                    <p class="text-xs text-gray-500">Memiliki {{ $category->children->count() }} kategori turunan</p>
                                </div>
                            </div>
                        @endif

                        @if($category->items->count() > 0)
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-boxes text-yellow-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Barang terdaftar</p>
                                    <p class="text-xs text-gray-500">{{ $category->items->count() }} barang dalam kategori ini</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        Apakah Anda yakin ingin menghapus kategori <span class="font-semibold text-gray-900">{{ $category->category_name }}</span>?
                    </p>

                    @if($category->children->count() > 0 || $category->items->count() > 0)
                        <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start space-x-2 text-yellow-800">
                                <i class="fas fa-exclamation-triangle mt-0.5"></i>
                                <div class="text-sm">
                                    @if($category->children->count() > 0)
                                        <div class="font-medium">Kategori ini memiliki {{ $category->children->count() }} sub-kategori</div>
                                    @endif
                                    @if($category->items->count() > 0)
                                        <div class="font-medium">Kategori ini memiliki {{ $category->items->count() }} barang</div>
                                    @endif
                                    <p class="text-xs mt-1">Kategori tidak dapat dihapus jika masih memiliki sub-kategori atau barang</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-sm text-red-600 mt-2">
                            Tindakan ini tidak dapat dibatalkan.
                        </div>
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            @click="hideDeleteModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="button"
                            @click="confirmDelete()"
                            @if($category->children->count() > 0 || $category->items->count() > 0) disabled @endif
                            class="{{ ($category->children->count() > 0 || $category->items->count() > 0) ? 'flex-1 px-4 py-3 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed' : 'flex-1 px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 shadow-lg hover:shadow-xl' }} transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-trash"></i>
                        <span>Hapus</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Status Modal -->
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
                <div class="{{ $category->is_active ? 'w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4' : 'w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4' }}">
                    <i class="fas fa-{{ $category->is_active ? 'toggle-off' : 'toggle-on' }} text-2xl {{ $category->is_active ? 'text-orange-600' : 'text-green-600' }}"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Kategori</h3>

                <p class="text-gray-600 text-center mb-6">
                    Apakah Anda yakin ingin {{ $category->is_active ? 'menonaktifkan' : 'mengaktifkan' }} kategori <span class="font-semibold text-gray-900">{{ $category->category_name }}</span>?
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
                            class="flex-1 px-4 py-3 {{ $category->is_active ? 'bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800' : 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' }} text-white rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-{{ $category->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                        <span>{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
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
    function categoryShow() {
        return {
            deleteModal: {
                show: false
            },
            toggleModal: {
                show: false
            },

            showDeleteModal() {
                this.deleteModal.show = true;
            },

            hideDeleteModal() {
                this.deleteModal.show = false;
            },

            confirmDelete() {
                @if($category->children->count() === 0 && $category->items->count() === 0)
                    // Create and submit delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('categories.destroy', $category->category_id) }}';
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
                @endif
            },

            showToggleModal() {
                this.toggleModal.show = true;
            },

            hideToggleModal() {
                this.toggleModal.show = false;
            },

            confirmToggle() {
                // Create and submit toggle form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('categories.toggle-status', $category->category_id) }}';
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
</script>
@endpush
