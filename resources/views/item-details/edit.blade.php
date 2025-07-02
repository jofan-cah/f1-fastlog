@extends('layouts.app')

@section('title', 'Edit Item Detail - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="itemDetailEdit()">
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
                    <a href="{{ route('item-details.index') }}"
                        class="text-sm font-medium text-gray-700 hover:text-orange-600">
                        Item Details
                    </a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="{{ route('item-details.show', $itemDetail) }}"
                        class="text-sm font-medium text-gray-700 hover:text-orange-600">
                        {{ $itemDetail->serial_number }}
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
            <div class="w-16 h-16 bg-gradient-to-br from-orange-600 to-orange-700 rounded-2xl flex items-center justify-center">
                <i class="fas fa-edit text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Item Detail</h1>
                <p class="text-gray-600 mt-1">Edit {{ $itemDetail->serial_number }}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('item-details.show', $itemDetail) }}"
               class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <form method="POST" action="{{ route('item-details.update', $itemDetail) }}" x-ref="editForm" @submit="validateForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-orange-600"></i>
                            Informasi Dasar
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Serial Number -->
                            <div>
                                <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Serial Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="serial_number" name="serial_number"
                                    value="{{ old('serial_number', $itemDetail->serial_number) }}"
                                    x-model="formData.serial_number"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all @error('serial_number') border-red-500 @enderror"
                                    required>
                                @error('serial_number')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select id="status" name="status"
                                    x-model="formData.status"
                                    @change="updateLocationSuggestion()"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all @error('status') border-red-500 @enderror"
                                    required>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}"
                                                {{ old('status', $itemDetail->status) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Location -->
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                                    Lokasi
                                </label>
                                <input type="text" id="location" name="location"
                                    value="{{ old('location', $itemDetail->location) }}"
                                    x-model="formData.location"
                                    list="location-options"
                                    placeholder="Masukkan lokasi item..."
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all @error('location') border-red-500 @enderror">

                                <!-- Datalist for autocomplete -->
                                <datalist id="location-options">
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc }}">
                                    @endforeach
                                </datalist>

                                @error('location')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- QR Code -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    QR Code
                                </label>
                                <div class="p-3 bg-gray-50 rounded-xl border">
                                    <span class="text-sm font-mono text-gray-600">
                                        {{ $itemDetail->qr_code ?: 'Belum digenerate' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mt-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan
                            </label>
                            <textarea id="notes" name="notes" rows="3"
                                x-model="formData.notes"
                                placeholder="Tambahkan catatan untuk item ini..."
                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all @error('notes') border-red-500 @enderror">{{ old('notes', $itemDetail->notes) }}</textarea>
                            @error('notes')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Custom Attributes Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-cogs mr-2 text-purple-600"></i>
                                Custom Attributes
                            </h3>
                            <div class="text-sm text-gray-500">
                                Kategori: {{ $itemDetail->item->category->category_name }}
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        @if(!empty($attributeTemplates))
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @php
                                    $currentAttributes = $itemDetail->custom_attributes ?: [];
                                @endphp

                                @foreach($attributeTemplates as $key => $label)
                                    <div>
                                        <label for="attr_{{ $key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ $label }}
                                        </label>
                                        <input type="text"
                                               id="attr_{{ $key }}"
                                               name="custom_attributes[{{ $key }}]"
                                               value="{{ old('custom_attributes.' . $key, $currentAttributes[$key] ?? '') }}"
                                               x-model="formData.custom_attributes['{{ $key }}']"
                                               placeholder="Masukkan {{ strtolower($label) }}..."
                                               class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                    </div>
                                @endforeach
                            </div>

                            <!-- Helper Actions -->
                            <div class="mt-6 flex items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-lightbulb mr-1"></i>
                                    Kosongkan field yang tidak diperlukan
                                </div>
                                <button type="button" @click="clearAllAttributes()"
                                    class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 text-sm flex items-center space-x-2">
                                    <i class="fas fa-trash"></i>
                                    <span>Clear All</span>
                                </button>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-info-circle text-4xl text-gray-300 mb-4"></i>
                                <h4 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Template</h4>
                                <p class="text-gray-500">Tidak ada template custom attributes untuk kategori ini.</p>
                            </div>
                        @endif
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
                            <h4 class="font-semibold text-gray-900">{{ $itemDetail->item->item_name }}</h4>
                            <p class="text-sm text-gray-500">{{ $itemDetail->item->item_code }}</p>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Kategori</span>
                                <span class="text-sm font-medium">{{ $itemDetail->item->category->category_name ?? 'N/A' }}</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Status Saat Ini</span>
                                @php $statusInfo = $itemDetail->getStatusInfo() @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusInfo['class'] }}">
                                    {{ $statusInfo['text'] }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Goods Received</span>
                                <span class="text-sm font-medium">{{ $itemDetail->goodsReceivedDetail->goodsReceived->receive_number }}</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Tanggal Terima</span>
                                <span class="text-sm font-medium">{{ $itemDetail->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 space-y-4">
                        <button type="submit"
                                :disabled="!canSubmit"
                                class="w-full px-4 py-3 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-xl hover:from-orange-700 hover:to-orange-800 transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-save"></i>
                            <span>Update Item Detail</span>
                        </button>

                        <a href="{{ route('item-details.show', $itemDetail) }}"
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
                            Ringkasan Edit
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Serial Number</span>
                            <span class="text-sm font-mono font-medium" x-text="formData.serial_number"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Status</span>
                            <span class="text-sm font-medium" x-text="getStatusText(formData.status)"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Lokasi</span>
                            <span class="text-sm font-medium" x-text="formData.location || 'Tidak diisi'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Custom Attributes</span>
                            <span class="text-sm font-medium" x-text="Object.keys(formData.custom_attributes || {}).filter(key => formData.custom_attributes[key]).length + ' field'"></span>
                        </div>
                    </div>
                </div>

                <!-- Tips Card -->
                <div class="bg-orange-50 rounded-2xl border border-orange-200 p-6">
                    <h4 class="text-lg font-semibold text-orange-900 mb-3 flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Tips Edit
                    </h4>
                    <ul class="text-sm text-orange-800 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check text-orange-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Serial number harus unik</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-orange-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Lokasi akan ter-suggest berdasarkan status</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-orange-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Custom attributes sesuai kategori item</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-orange-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Gunakan Ctrl+S untuk save cepat</span>
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
    function itemDetailEdit() {
        return {
            formData: {
                serial_number: '{{ $itemDetail->serial_number }}',
                status: '{{ $itemDetail->status }}',
                location: '{{ $itemDetail->location }}',
                notes: '{{ $itemDetail->notes }}',
                custom_attributes: @json($itemDetail->custom_attributes ?: [])
            },
            originalData: {},

            get canSubmit() {
                return this.formData.serial_number.trim() !== '' &&
                       this.formData.status !== '';
            },

            init() {
                console.log('Initializing item detail edit form');

                // Store original data for reset
                this.originalData = JSON.parse(JSON.stringify(this.formData));

                // Add CSRF token
                const token = document.querySelector('meta[name="csrf-token"]');
                if (token) {
                    window.csrfToken = token.getAttribute('content');
                }
            },

            updateLocationSuggestion() {
                // Auto-suggest location based on status if location is empty
                if (!this.formData.location || this.formData.location.trim() === '') {
                    switch(this.formData.status) {
                        case 'available':
                            this.formData.location = 'GUDANG-A';
                            break;
                        case 'used':
                            this.formData.location = 'DEPLOYMENT';
                            break;
                        case 'maintenance':
                            this.formData.location = 'WORKSHOP';
                            break;
                        case 'damaged':
                            this.formData.location = 'DISPOSAL';
                            break;
                        case 'reserved':
                            this.formData.location = 'RESERVED-AREA';
                            break;
                    }
                }
            },

            clearAllAttributes() {
                if (confirm('Yakin ingin menghapus semua custom attributes?')) {
                    this.formData.custom_attributes = {};

                    // Clear form inputs
                    document.querySelectorAll('input[name^="custom_attributes"]').forEach(input => {
                        input.value = '';
                    });

                    this.showToast('Custom attributes berhasil dihapus', 'info');
                }
            },

            resetForm() {
                if (confirm('Yakin ingin mereset form? Semua perubahan akan hilang.')) {
                    // Reset to original data
                    this.formData = JSON.parse(JSON.stringify(this.originalData));

                    // Reset form fields
                    document.getElementById('serial_number').value = this.originalData.serial_number;
                    document.getElementById('status').value = this.originalData.status;
                    document.getElementById('location').value = this.originalData.location;
                    document.getElementById('notes').value = this.originalData.notes;

                    // Reset custom attributes
                    Object.keys(this.originalData.custom_attributes || {}).forEach(key => {
                        const input = document.querySelector(`input[name="custom_attributes[${key}]"]`);
                        if (input) {
                            input.value = this.originalData.custom_attributes[key] || '';
                        }
                    });

                    this.showToast('Form berhasil direset!', 'info');
                }
            },

            validateForm(event) {
                if (!this.canSubmit) {
                    event.preventDefault();
                    this.showToast('Serial number dan status wajib diisi!', 'error');
                    return false;
                }

                // Additional validation
                if (this.formData.serial_number.trim().length < 3) {
                    event.preventDefault();
                    this.showToast('Serial number minimal 3 karakter!', 'error');
                    return false;
                }

                return true;
            },

            getStatusText(status) {
                const statusMap = {
                    'available': 'Tersedia',
                    'used': 'Terpakai',
                    'damaged': 'Rusak',
                    'maintenance': 'Maintenance',
                    'reserved': 'Reserved'
                };
                return statusMap[status] || status;
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
            // Ctrl + S for save
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                const form = document.querySelector('form');
                if (form) {
                    form.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            }
        });
    });
</script>
@endpush
