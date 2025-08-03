@extends('layouts.app')

@section('title', 'Edit Transaksi - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="transactionEdit()">
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
                    <a href="{{ route('transactions.index') }}"
                        class="text-sm font-medium text-gray-700 hover:text-orange-600">
                        Transaksi
                    </a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="{{ route('transactions.show', $transaction) }}"
                        class="text-sm font-medium text-gray-700 hover:text-orange-600">
                        {{ $transaction->transaction_number }}
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
            @php
                $typeInfo = $transaction->getTypeInfo();
            @endphp
            <div class="w-16 h-16 bg-gradient-to-br from-orange-600 to-orange-700 rounded-2xl flex items-center justify-center">
                <i class="fas fa-edit text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Transaksi</h1>
                <p class="text-gray-600 mt-1">{{ $transaction->transaction_number }}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('transactions.show', $transaction) }}"
               class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <form method="POST" action="{{ route('transactions.update', $transaction) }}" x-ref="editForm" @submit="validateForm">
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
                            Informasi Transaksi
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Transaction Number (Read Only) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor Transaksi
                                </label>
                                <div class="p-3 bg-gray-50 rounded-xl border">
                                    <span class="text-sm font-mono text-gray-900">{{ $transaction->transaction_number }}</span>
                                </div>
                            </div>

                            <!-- Transaction Type -->
                            <div>
                                <label for="transaction_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipe Transaksi <span class="text-red-500">*</span>
                                </label>
                                <select  disabled id="transaction_type" name="transaction_type"
                                    x-model="formData.transaction_type"
                                    @change="updateLocationSuggestion()"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all @error('transaction_type') border-red-500 @enderror"
                                    required>
                                    @foreach($allowedTypes as $value => $label)
                                        <option value="{{ $value }}"
                                                {{ old('transaction_type', $transaction->transaction_type) == $value ? 'selected' : '' }}>
                                            {{ $transaction->transaction_type }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('transaction_type')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Reference ID -->
                            <div>
                                <label for="reference_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Reference ID
                                </label>
                                <input type="text" id="reference_id" name="reference_id"
                                    value="{{ old('reference_id', $transaction->reference_id) }}"
                                    x-model="formData.reference_id"
                                    placeholder="Masukkan reference ID..."
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all @error('reference_id') border-red-500 @enderror">
                                @error('reference_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Reference Type -->
                            <div>
                                <label for="reference_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Reference Type
                                </label>
                                <select id="reference_type" name="reference_type"
                                    x-model="formData.reference_type"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all @error('reference_type') border-red-500 @enderror">
                                    <option value="">Pilih reference type...</option>
                                    <option value="po" {{ old('reference_type', $transaction->reference_type) == 'po' ? 'selected' : '' }}>Purchase Order</option>
                                    <option value="gr" {{ old('reference_type', $transaction->reference_type) == 'gr' ? 'selected' : '' }}>Goods Received</option>
                                    <option value="maintenance" {{ old('reference_type', $transaction->reference_type) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="project" {{ old('reference_type', $transaction->reference_type) == 'project' ? 'selected' : '' }}>Project</option>
                                    <option value="other" {{ old('reference_type', $transaction->reference_type) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('reference_type')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- From Location -->
                            <div>
                                <label for="from_location" class="block text-sm font-medium text-gray-700 mb-2">
                                    Lokasi Asal
                                </label>
                                <input type="text" id="from_location" name="from_location"
                                    value="{{ old('from_location', $transaction->from_location) }}"
                                    x-model="formData.from_location"
                                    list="location-options"
                                    placeholder="Masukkan lokasi asal..."
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all @error('from_location') border-red-500 @enderror">
                                @error('from_location')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- To Location -->
                            <div>
                                <label for="to_location" class="block text-sm font-medium text-gray-700 mb-2">
                                    Lokasi Tujuan
                                </label>
                                <input type="text" id="to_location" name="to_location"
                                    value="{{ old('to_location', $transaction->to_location) }}"
                                    x-model="formData.to_location"
                                    list="location-options"
                                    placeholder="Masukkan lokasi tujuan..."
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all @error('to_location') border-red-500 @enderror">
                                @error('to_location')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Datalist for autocomplete -->
                            <datalist id="location-options">
                                <option value="GUDANG-A">
                                <option value="GUDANG-B">
                                <option value="DEPLOYMENT">
                                <option value="WORKSHOP">
                                <option value="DISPOSAL">
                                <option value="RESERVED-AREA">
                                <option value="FIELD-OPS">
                                <option value="MAINTENANCE">
                            </datalist>
                        </div>

                        <!-- Notes -->
                        <div class="mt-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan
                            </label>
                            <textarea id="notes" name="notes" rows="4"
                                x-model="formData.notes"
                                placeholder="Tambahkan catatan untuk transaksi ini..."
                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all @error('notes') border-red-500 @enderror">{{ old('notes', $transaction->notes) }}</textarea>
                            @error('notes')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Transaction Details Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-list mr-2 text-purple-600"></i>
                            Detail Item
                            <span class="ml-2 px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">
                                {{ $transaction->transactionDetails->count() }} item(s)
                            </span>
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($transaction->transactionDetails->count() > 0)
                            <div class="space-y-4">
                                @foreach($transaction->transactionDetails as $index => $detail)
                                    @php
                                        $itemDetail = $detail->itemDetail;
                                        $statusInfo = $itemDetail->getStatusInfo();
                                    @endphp
                                    <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow bg-gray-50">
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                                                    <i class="fas fa-microchip text-white"></i>
                                                </div>
                                                <div>
                                                    <h4 class="font-semibold text-gray-900">{{ $itemDetail->item->item_name ?? 'Unknown Item' }}</h4>
                                                    <p class="text-sm text-gray-500">{{ $itemDetail->item->item_code ?? 'N/A' }}</p>
                                                    <p class="text-xs font-mono text-gray-600">SN: {{ $itemDetail->serial_number ?? 'N/A' }}</p>
                                                </div>
                                            </div>

                                            <div class="flex flex-col md:flex-row items-start md:items-center gap-2">
                                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusInfo['class'] }}">
                                                    {{ $statusInfo['text'] }}
                                                </span>
                                                <span class="text-xs text-gray-500">{{ $itemDetail->location }}</span>
                                            </div>
                                        </div>

                                        <!-- Item Detail Notes -->
                                        <div class="mt-4">
                                            <label for="detail_notes_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-2">
                                                Catatan Item
                                            </label>
                                            <textarea
                                                id="detail_notes_{{ $index }}"
                                                name="detail_notes[{{ $detail->transaction_detail_id }}]"
                                                rows="2"
                                                placeholder="Tambahkan catatan khusus untuk item ini..."
                                                class="w-full py-2 px-3 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">{{ old('detail_notes.' . $detail->transaction_detail_id, $detail->notes) }}</textarea>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6 p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-yellow-600 mr-2 mt-0.5"></i>
                                    <div class="text-sm text-yellow-800">
                                        <p class="font-medium mb-1">Perhatian:</p>
                                        <p>Item yang terlibat dalam transaksi tidak dapat diubah pada tahap edit. Hanya informasi transaksi dan catatan yang dapat dimodifikasi.</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                <h4 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Detail</h4>
                                <p class="text-gray-500">Belum ada detail item untuk transaksi ini.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - Info & Actions -->
            <div class="space-y-6">
                <!-- Transaction Info Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-info mr-2 text-blue-600"></i>
                            Info Transaksi
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="text-center mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br {{ $typeInfo['gradient'] ?? 'from-blue-600 to-blue-700' }} rounded-xl flex items-center justify-center mx-auto mb-3">
                                <i class="{{ $typeInfo['icon'] }} text-white text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-900">{{ $transaction->transaction_number }}</h4>
                            <p class="text-sm text-gray-500">{{ $typeInfo['text'] }}</p>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Status</span>
                                @php $statusInfo = $transaction->getStatusInfo() @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusInfo['class'] }}">
                                    {{ $statusInfo['text'] }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Item</span>
                                <span class="text-sm font-medium">{{ $transaction->quantity }} pcs</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Dibuat</span>
                                <span class="text-sm font-medium">{{ $transaction->created_at->format('d/m/Y') }}</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Dibuat oleh</span>
                                <span class="text-sm font-medium">{{ $transaction->createdBy->full_name ?? 'Unknown' }}</span>
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
                            <span>Update Transaksi</span>
                        </button>

                        <a href="{{ route('transactions.show', $transaction) }}"
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

                        <button type="button"
                                @click="showCancelModal = true"
                                class="w-full px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-ban"></i>
                            <span>Cancel Transaksi</span>
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
                            <span class="text-sm text-gray-600">Tipe Transaksi</span>
                            <span class="text-sm font-medium" x-text="getTransactionTypeText(formData.transaction_type)"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Reference ID</span>
                            <span class="text-sm font-medium" x-text="formData.reference_id || 'Tidak diisi'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Lokasi Asal</span>
                            <span class="text-sm font-medium" x-text="formData.from_location || 'Tidak diisi'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Lokasi Tujuan</span>
                            <span class="text-sm font-medium" x-text="formData.to_location || 'Tidak diisi'"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Catatan</span>
                            <span class="text-sm font-medium" x-text="formData.notes ? (formData.notes.length > 20 ? formData.notes.substring(0, 20) + '...' : formData.notes) : 'Tidak diisi'"></span>
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
                            <span>Hanya transaksi pending yang bisa diedit</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-orange-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Item dalam transaksi tidak dapat diubah</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-orange-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Lokasi akan ter-suggest berdasarkan tipe</span>
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

    <!-- Cancel Transaction Modal -->
    <div x-show="showCancelModal"
         x-cloak
         @keydown.escape.window="showCancelModal = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title"
         role="dialog"
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCancelModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 aria-hidden="true"
                 @click="showCancelModal = false"></div>

            <div x-show="showCancelModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('transactions.cancel', $transaction) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-ban text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Cancel Transaksi
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Yakin ingin membatalkan transaksi <strong>{{ $transaction->transaction_number }}</strong>?
                                        Tindakan ini tidak dapat dibatalkan dan transaksi akan berstatus cancelled.
                                    </p>
                                    <div class="mt-4">
                                        <label for="cancel_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                            Alasan Pembatalan
                                        </label>
                                        <textarea id="cancel_reason" name="reason" rows="3"
                                            class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                            placeholder="Jelaskan alasan pembatalan..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <i class="fas fa-ban mr-2"></i>
                            Cancel Transaksi
                        </button>
                        <button type="button" @click="showCancelModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
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
    function transactionEdit() {
        return {
            formData: {
                transaction_type: '{{ $transaction->transaction_type }}',
                reference_id: '{{ $transaction->reference_id }}',
                reference_type: '{{ $transaction->reference_type }}',
                from_location: '{{ $transaction->from_location }}',
                to_location: '{{ $transaction->to_location }}',
                notes: '{{ $transaction->notes }}',
            },
            originalData: {},
            showCancelModal: false,

            get canSubmit() {
                return this.formData.transaction_type !== '';
            },

            init() {
                console.log('Initializing transaction edit form');

                // Store original data for reset
                this.originalData = JSON.parse(JSON.stringify(this.formData));

                // Add CSRF token
                const token = document.querySelector('meta[name="csrf-token"]');
                if (token) {
                    window.csrfToken = token.getAttribute('content');
                }
            },

            updateLocationSuggestion() {
                // Auto-suggest locations based on transaction type
                switch(this.formData.transaction_type) {
                    case 'IN':
                        if (!this.formData.from_location) this.formData.from_location = 'FIELD-OPS';
                        if (!this.formData.to_location) this.formData.to_location = 'GUDANG-A';
                        break;
                    case 'OUT':
                        if (!this.formData.from_location) this.formData.from_location = 'GUDANG-A';
                        if (!this.formData.to_location) this.formData.to_location = 'DEPLOYMENT';
                        break;
                    case 'REPAIR':
                        if (!this.formData.from_location) this.formData.from_location = 'DEPLOYMENT';
                        if (!this.formData.to_location) this.formData.to_location = 'WORKSHOP';
                        break;
                    case 'RETURN':
                        if (!this.formData.from_location) this.formData.from_location = 'DEPLOYMENT';
                        if (!this.formData.to_location) this.formData.to_location = 'GUDANG-A';
                        break;
                    case 'LOST':
                        if (!this.formData.from_location) this.formData.from_location = 'DEPLOYMENT';
                        if (!this.formData.to_location) this.formData.to_location = 'DISPOSAL';
                        break;
                }
            },

            resetForm() {
                if (confirm('Yakin ingin mereset form? Semua perubahan akan hilang.')) {
                    // Reset to original data
                    this.formData = JSON.parse(JSON.stringify(this.originalData));

                    // Reset form fields
                    document.getElementById('transaction_type').value = this.originalData.transaction_type;
                    document.getElementById('reference_id').value = this.originalData.reference_id;
                    document.getElementById('reference_type').value = this.originalData.reference_type;
                    document.getElementById('from_location').value = this.originalData.from_location;
                    document.getElementById('to_location').value = this.originalData.to_location;
                    document.getElementById('notes').value = this.originalData.notes;

                    this.showToast('Form berhasil direset!', 'info');
                }
            },

            validateForm(event) {
                if (!this.canSubmit) {
                    event.preventDefault();
                    this.showToast('Tipe transaksi wajib dipilih!', 'error');
                    return false;
                }

                return true;
            },

            getTransactionTypeText(type) {
                const typeMap = {
                    'IN': 'Barang Masuk',
                    'OUT': 'Barang Keluar',
                    'REPAIR': 'Barang Repair',
                    'LOST': 'Barang Hilang',
                    'RETURN': 'Pengembalian'
                };
                return typeMap[type] || type;
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
