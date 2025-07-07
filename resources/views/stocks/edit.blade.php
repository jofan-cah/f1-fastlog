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
                    <div class="mt-4">
                        <button @click="autoSync()"
                                class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                            <i class="fas fa-sync mr-2"></i>
                            Auto-Sync Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Form -->
    <form method="POST" action="{{ route('stocks.update', $stock) }}" x-ref="editForm" @submit="validateForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Item Details Management -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Item Details Drag & Drop Interface -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-exchange-alt mr-2 text-blue-600"></i>
                            Kelola Item Details
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Drag & drop item details untuk mengubah status dan update stock
                        </p>
                    </div>
                    <div class="p-6">
                        <!-- Drag & Drop Containers -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Stock (Gudang) Column -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-warehouse mr-2 text-blue-600"></i>
                                        Stock (Gudang)
                                    </h4>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium"
                                          x-text="stockItems.length + ' items'"></span>
                                </div>

                                <div class="min-h-[400px] bg-blue-50 border-2 border-dashed border-blue-300 rounded-xl p-4"
                                     @drop="handleDrop($event, 'stock')"
                                     @dragover.prevent
                                     @dragenter.prevent>

                                    <template x-for="item in stockItems" :key="item.item_detail_id">
                                        <div class="bg-white border border-blue-200 rounded-lg p-3 mb-3 cursor-move shadow-sm hover:shadow-md transition-all"
                                             draggable="true"
                                             @dragstart="handleDragStart($event, item)"
                                             x-data="{ isHovering: false }"
                                             @mouseenter="isHovering = true"
                                             @mouseleave="isHovering = false">

                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="font-medium text-gray-900" x-text="item.serial_number"></div>
                                                    <div class="text-sm text-gray-500" x-text="item.item_detail_id"></div>
                                                    <div class="text-xs text-gray-400" x-text="item.location || 'No location'"></div>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <i class="fas fa-grip-vertical text-gray-400"
                                                       :class="{ 'text-blue-600': isHovering }"></i>
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                                                        Stock
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="stockItems.length === 0"
                                         class="text-center text-gray-500 py-8">
                                        <i class="fas fa-box-open text-4xl mb-4"></i>
                                        <p>Tidak ada item di gudang</p>
                                        <p class="text-sm">Drop item di sini untuk pindah ke gudang</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Available (Siap Pakai) Column -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-hand-holding mr-2 text-green-600"></i>
                                        Available (Siap Pakai)
                                    </h4>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium"
                                          x-text="availableItems.length + ' items'"></span>
                                </div>

                                <div class="min-h-[400px] bg-green-50 border-2 border-dashed border-green-300 rounded-xl p-4"
                                     @drop="handleDrop($event, 'available')"
                                     @dragover.prevent
                                     @dragenter.prevent>

                                    <template x-for="item in availableItems" :key="item.item_detail_id">
                                        <div class="bg-white border border-green-200 rounded-lg p-3 mb-3 cursor-move shadow-sm hover:shadow-md transition-all"
                                             draggable="true"
                                             @dragstart="handleDragStart($event, item)"
                                             x-data="{ isHovering: false }"
                                             @mouseenter="isHovering = true"
                                             @mouseleave="isHovering = false">

                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="font-medium text-gray-900" x-text="item.serial_number"></div>
                                                    <div class="text-sm text-gray-500" x-text="item.item_detail_id"></div>
                                                    <div class="text-xs text-gray-400" x-text="item.location || 'No location'"></div>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <i class="fas fa-grip-vertical text-gray-400"
                                                       :class="{ 'text-green-600': isHovering }"></i>
                                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">
                                                        Available
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="availableItems.length === 0"
                                         class="text-center text-gray-500 py-8">
                                        <i class="fas fa-hand-holding text-4xl mb-4"></i>
                                        <p>Tidak ada item siap pakai</p>
                                        <p class="text-sm">Drop item di sini untuk siap pakai</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 mt-6 pt-6 border-t">
                            <button type="button"
                                    @click="saveChanges()"
                                    :disabled="!hasChanges"
                                    class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-save"></i>
                                <span>Simpan Perubahan</span>
                                <span x-show="hasChanges"
                                      class="px-2 py-1 bg-blue-800 rounded-full text-xs"
                                      x-text="changedItems.length + ' item'"></span>
                            </button>

                            <button type="button"
                                    @click="resetChanges()"
                                    :disabled="!hasChanges"
                                    class="px-4 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-undo"></i>
                                <span>Reset</span>
                            </button>

                            <button type="button"
                                    @click="previewChanges()"
                                    :disabled="!hasChanges"
                                    class="px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-eye"></i>
                                <span>Preview</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Changes Summary -->
                <div x-show="hasChanges"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <h4 class="text-lg font-semibold text-yellow-900 mb-3 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Perubahan Pending
                    </h4>
                    <div class="space-y-2">
                        <template x-for="change in changedItems" :key="change.item_detail_id">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium" x-text="change.serial_number"></span>
                                <span class="text-yellow-700">
                                    <span x-text="change.old_status"></span>
                                    <i class="fas fa-arrow-right mx-2"></i>
                                    <span x-text="change.new_status"></span>
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right Column - Info & Actions -->
            <div class="space-y-6">
                <!-- Item Info Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-box mr-2 text-blue-600"></i>
                            Informasi Item
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
                                <span class="text-sm text-gray-600">Min Stock</span>
                                <span class="text-sm font-medium">{{ $stock->item->min_stock ?? 0 }} {{ $stock->item->unit }}</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Status Stock</span>
                                @php $statusInfo = $stock->getStockStatus() @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusInfo['class'] }}">
                                    {{ $statusInfo['text'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Stock Values -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-chart-bar mr-2 text-green-600"></i>
                            Nilai Saat Ini
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Available (Gudang)</span>
                            <span class="text-sm font-medium">{{ $stock->quantity_available }} {{ $stock->item->unit }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Used (Siap Pakai)</span>
                            <span class="text-sm font-medium">{{ $stock->quantity_used }} {{ $stock->item->unit }}</span>
                        </div>
                        <div class="flex justify-between items-center border-t pt-3">
                            <span class="text-sm font-medium text-gray-900">Total</span>
                            <span class="text-sm font-bold">{{ $stock->total_quantity }} {{ $stock->item->unit }}</span>
                        </div>
                    </div>
                </div>

                <!-- Item Details Breakdown -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-list mr-2 text-indigo-600"></i>
                            Item Details Breakdown
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @foreach($itemDetailsBreakdown['by_status'] as $status => $count)
                            @if($count > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 capitalize">{{ $status }}</span>
                                    <span class="text-sm font-medium">{{ $count }}</span>
                                </div>
                            @endif
                        @endforeach

                        <div class="border-t pt-3 mt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-900">Total Trackable</span>
                                <span class="text-sm font-bold">{{ $itemDetailsBreakdown['comparison']['item_details']['total_trackable'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 space-y-4">
                        <button type="submit"
                                :disabled="!canSubmit"
                                class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-save"></i>
                            <span x-text="formData.adjustment_type === 'sync_auto' ? 'Auto-Sync Stock' : 'Update Stock'"></span>
                        </button>

                        <a href="{{ route('stocks.show', $stock) }}"
                           class="w-full px-4 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </a>

                        <button type="button"
                                @click="resetForm()"
                                class="w-full px-4 py-3 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-undo"></i>
                            <span>Reset Form</span>
                        </button>
                    </div>
                </div>

                <!-- Edit Summary -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-calculator mr-2 text-green-600"></i>
                            Preview Perubahan
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Available</span>
                            <span class="text-sm font-medium">
                                <span class="text-gray-400">{{ $stock->quantity_available }}</span>
                                <i class="fas fa-arrow-right mx-2"></i>
                                <span x-text="formData.quantity_available"></span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Used</span>
                            <span class="text-sm font-medium">
                                <span class="text-gray-400">{{ $stock->quantity_used }}</span>
                                <i class="fas fa-arrow-right mx-2"></i>
                                <span x-text="formData.quantity_used"></span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center border-t pt-3">
                            <span class="text-sm font-medium text-gray-900">Total Baru</span>
                            <span class="text-sm font-bold" x-text="(formData.quantity_available + formData.quantity_used) + ' {{ $stock->item->unit }}'"></span>
                        </div>
                    </div>
                </div>

                <!-- Tips Card -->
                <div class="bg-blue-50 rounded-2xl border border-blue-200 p-6">
                    <h4 class="text-lg font-semibold text-blue-900 mb-3 flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Tips Stock Edit
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span><strong>Available</strong> = barang di gudang</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span><strong>Used</strong> = barang siap pakai di kantor</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Auto-sync untuk sinkronkan dengan item details</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Manual untuk edit nilai langsung</span>
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

<!-- Alpine.js Modals -->
<div x-data="stockEdit()">
    <!-- Save Confirmation Modal -->
    <div x-show="showSaveModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showSaveModal = false"></div>

            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-save text-blue-600 text-2xl"></i>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Simpan</h3>
                    <p class="text-gray-600 mb-6">
                        Yakin ingin menyimpan <span class="font-semibold" x-text="changedItems.length"></span> perubahan?
                    </p>

                    <div class="flex space-x-3">
                        <button @click="showSaveModal = false"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 transition-colors">
                            Batal
                        </button>
                        <button @click="confirmSaveChanges()"
                                :disabled="isSaving"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors disabled:opacity-50">
                            <span x-show="!isSaving">Simpan</span>
                            <span x-show="isSaving">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Confirmation Modal -->
    <div x-show="showResetModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showResetModal = false"></div>

            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-undo text-yellow-600 text-2xl"></i>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Reset</h3>
                    <p class="text-gray-600 mb-6">
                        Yakin ingin membatalkan semua perubahan? Semua data akan kembali ke state awal.
                    </p>

                    <div class="flex space-x-3">
                        <button @click="showResetModal = false"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 transition-colors">
                            Batal
                        </button>
                        <button @click="confirmResetChanges()"
                                class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-colors">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-Sync Confirmation Modal -->
    <div x-show="showSyncModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showSyncModal = false"></div>

            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-sync text-green-600 text-2xl"></i>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Auto-Sync</h3>
                    <p class="text-gray-600 mb-6">
                        Yakin ingin melakukan auto-sync? Stock akan disesuaikan dengan item details saat ini.
                    </p>

                    <div class="flex space-x-3">
                        <button @click="showSyncModal = false"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 transition-colors">
                            Batal
                        </button>
                        <button @click="confirmAutoSync()"
                                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors">
                            Auto-Sync
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div x-show="showPreviewModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showPreviewModal = false"></div>

            <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Preview Perubahan</h3>
                    <button @click="showPreviewModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="space-y-4" x-show="previewData">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <h4 class="font-medium text-gray-900 mb-3">Perubahan yang akan disimpan:</h4>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <template x-for="change in previewData?.changes || []" :key="change.item_detail_id">
                                <div class="flex items-center justify-between text-sm bg-white p-2 rounded">
                                    <span class="font-medium" x-text="change.serial_number"></span>
                                    <span class="text-gray-600">
                                        <span x-text="change.old_status"></span>
                                        <i class="fas fa-arrow-right mx-2"></i>
                                        <span x-text="change.new_status"></span>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="bg-blue-50 p-3 rounded-xl text-center">
                            <div class="font-semibold text-blue-900">Total Stock</div>
                            <div class="text-2xl font-bold text-blue-600" x-text="previewData?.totalStock || 0"></div>
                        </div>
                        <div class="bg-green-50 p-3 rounded-xl text-center">
                            <div class="font-semibold text-green-900">Total Available</div>
                            <div class="text-2xl font-bold text-green-600" x-text="previewData?.totalAvailable || 0"></div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button @click="showPreviewModal = false"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function stockEdit() {
        return {
            // Item Details Data
            stockItems: @json($itemDetailsBreakdown['by_status']['stock'] ? $stock->item->itemDetails->where('status', 'stock')->values() : []),
            availableItems: @json($itemDetailsBreakdown['by_status']['available'] ? $stock->item->itemDetails->where('status', 'available')->values() : []),

            // Original data for reset
            originalStockItems: [],
            originalAvailableItems: [],

            // Changed items tracking
            changedItems: [],

            // Drag & Drop state
            draggedItem: null,

            // Modal states
            showSaveModal: false,
            showResetModal: false,
            showSyncModal: false,
            showPreviewModal: false,
            isSaving: false,
            previewData: null,

            get hasChanges() {
                return this.changedItems.length > 0;
            },

            get totalStock() {
                return this.stockItems.length;
            },

            get totalAvailable() {
                return this.availableItems.length;
            },

            init() {
                console.log('Initializing stock edit with drag & drop');

                // Store original data for reset
                this.originalStockItems = JSON.parse(JSON.stringify(this.stockItems));
                this.originalAvailableItems = JSON.parse(JSON.stringify(this.availableItems));

                // Add CSRF token
                const token = document.querySelector('meta[name="csrf-token"]');
                if (token) {
                    window.csrfToken = token.getAttribute('content');
                }
            },

            handleDragStart(event, item) {
                this.draggedItem = item;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/html', event.target.outerHTML);

                // Add visual feedback
                event.target.style.opacity = '0.5';
            },

            handleDrop(event, targetStatus) {
                event.preventDefault();

                if (!this.draggedItem) return;

                const item = this.draggedItem;
                const currentStatus = item.status;

                // Don't do anything if dropped on same status
                if (currentStatus === targetStatus) {
                    this.draggedItem = null;
                    return;
                }

                // Remove from current array
                if (currentStatus === 'stock') {
                    this.stockItems = this.stockItems.filter(i => i.item_detail_id !== item.item_detail_id);
                } else if (currentStatus === 'available') {
                    this.availableItems = this.availableItems.filter(i => i.item_detail_id !== item.item_detail_id);
                }

                // Update item status and location
                item.status = targetStatus;
                item.location = targetStatus === 'stock' ? 'Warehouse - Stock' : 'Office - Ready';

                // Add to target array
                if (targetStatus === 'stock') {
                    this.stockItems.push(item);
                } else if (targetStatus === 'available') {
                    this.availableItems.push(item);
                }

                // Track the change
                this.trackChange(item, currentStatus, targetStatus);

                // Reset drag state
                this.draggedItem = null;

                this.showToast(`${item.serial_number} dipindah ke ${targetStatus}`, 'success');
            },

            trackChange(item, oldStatus, newStatus) {
                // Remove existing change for this item
                this.changedItems = this.changedItems.filter(c => c.item_detail_id !== item.item_detail_id);

                // Add new change
                this.changedItems.push({
                    item_detail_id: item.item_detail_id,
                    serial_number: item.serial_number,
                    old_status: oldStatus,
                    new_status: newStatus,
                    location: item.location
                });
            },

            async saveChanges() {
                if (!this.hasChanges) {
                    this.showToast('Tidak ada perubahan untuk disimpan', 'info');
                    return;
                }

                if (!confirm(`Yakin ingin menyimpan ${this.changedItems.length} perubahan?`)) {
                    return;
                }

                try {
                    // Show loading
                    const saveButton = document.querySelector('button[\\@click="saveChanges()"]');
                    const originalText = saveButton.innerHTML;
                    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
                    saveButton.disabled = true;

                    // Prepare data for bulk update
                    const updateData = {
                        changes: this.changedItems.map(change => ({
                            item_detail_id: change.item_detail_id,
                            status: change.new_status,
                            location: change.location,
                            notes: `Status changed from ${change.old_status} to ${change.new_status} via stock management`
                        }))
                    };

                    // Send bulk update request
                    const response = await fetch('/api/item-details/bulk-update-status-from-stock', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken
                        },
                        body: JSON.stringify(updateData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showToast(`Berhasil menyimpan ${result.data.updated_count} perubahan!`, 'success');

                        // Clear changed items
                        this.changedItems = [];

                        // Update original data
                        this.originalStockItems = JSON.parse(JSON.stringify(this.stockItems));
                        this.originalAvailableItems = JSON.parse(JSON.stringify(this.availableItems));

                        // Show auto-sync info if stock was synced
                        if (result.data.stocks_synced_count > 0) {
                            this.showToast('Stock telah disinkronkan otomatis!', 'info');
                        }

                    } else {
                        this.showToast('Gagal menyimpan: ' + result.message, 'error');
                    }

                } catch (error) {
                    console.error('Save changes error:', error);
                    this.showToast('Terjadi kesalahan saat menyimpan', 'error');
                } finally {
                    // Restore button
                    const saveButton = document.querySelector('button[\\@click="saveChanges()"]');
                    saveButton.innerHTML = originalText;
                    saveButton.disabled = false;
                }
            },

            resetChanges() {
                if (!this.hasChanges) {
                    this.showToast('Tidak ada perubahan untuk direset', 'info');
                    return;
                }

                if (!confirm('Yakin ingin membatalkan semua perubahan?')) {
                    return;
                }

                // Restore original data
                this.stockItems = JSON.parse(JSON.stringify(this.originalStockItems));
                this.availableItems = JSON.parse(JSON.stringify(this.originalAvailableItems));

                // Clear changes
                this.changedItems = [];

                this.showToast('Perubahan berhasil direset!', 'info');
            },

            previewChanges() {
                if (!this.hasChanges) {
                    this.showToast('Tidak ada perubahan untuk dipreview', 'info');
                    return;
                }

                let preview = 'Perubahan yang akan disimpan:\n\n';
                this.changedItems.forEach(change => {
                    preview += `• ${change.serial_number}: ${change.old_status} → ${change.new_status}\n`;
                });

                preview += `\nTotal Stock: ${this.totalStock} items`;
                preview += `\nTotal Available: ${this.totalAvailable} items`;

                alert(preview);
            },

            async autoSync() {
                if (!confirm('Yakin ingin melakukan auto-sync? Stock akan disesuaikan dengan item details.')) {
                    return;
                }

                try {
                    const response = await fetch(`/api/stocks/{{ $stock->stock_id }}/sync-item-details`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showToast('Auto-sync berhasil! Halaman akan direfresh.', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showToast('Auto-sync gagal: ' + result.message, 'error');
                    }
                } catch (error) {
                    console.error('Auto-sync error:', error);
                    this.showToast('Terjadi kesalahan saat auto-sync', 'error');
                }
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

    // Prevent default drag behaviors
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('dragover', function(e) {
            e.preventDefault();
        });

        document.addEventListener('drop', function(e) {
            e.preventDefault();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + S for save
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                // Trigger save changes if there are changes
                const app = Alpine.$data(document.querySelector('[x-data="stockEdit()"]'));
                if (app && app.hasChanges) {
                    app.saveChanges(); // This will show modal instead of confirm
                }
            }

            // Escape to cancel/close modals
            if (e.key === 'Escape') {
                const app = Alpine.$data(document.querySelector('[x-data="stockEdit()"]'));
                if (app) {
                    // Close any open modals first
                    if (app.showSaveModal) {
                        app.showSaveModal = false;
                        return;
                    }
                    if (app.showResetModal) {
                        app.showResetModal = false;
                        return;
                    }
                    if (app.showSyncModal) {
                        app.showSyncModal = false;
                        return;
                    }
                    if (app.showPreviewModal) {
                        app.showPreviewModal = false;
                        return;
                    }

                    // If no modals open and has changes, ask to leave
                    if (app.hasChanges) {
                        app.showResetModal = true; // Show reset modal as confirmation
                    } else {
                        window.location.href = '{{ route('stocks.show', $stock) }}';
                    }
                }
            }

            // Ctrl + Z for reset
            if (e.ctrlKey && e.key === 'z') {
                e.preventDefault();
                const app = Alpine.$data(document.querySelector('[x-data="stockEdit()"]'));
                if (app && app.hasChanges) {
                    app.resetChanges(); // This will show modal instead of confirm
                }
            }

            // Ctrl + P for preview
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                const app = Alpine.$data(document.querySelector('[x-data="stockEdit()"]'));
                if (app && app.hasChanges) {
                    app.previewChanges(); // This will show modal
                }
            }
        });
    });

    // Add drag & drop visual feedback
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('dragend', function(e) {
            // Restore opacity
            e.target.style.opacity = '1';
        });
    });
 </script>
@endpush
