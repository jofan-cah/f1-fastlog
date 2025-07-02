@extends('layouts.app')

@section('title', 'Detail Item - LogistiK Admin')

@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
    <div class="space-y-6" x-data="itemDetailShowManager()">
        <!-- Breadcrumb & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-blue-600">Dashboard</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <a href="{{ route('item-details.index') }}" class="hover:text-blue-600">Item Details</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="text-gray-900">{{ $itemDetail->serial_number }}</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900">Detail Item</h1>
                <p class="text-gray-600 mt-1">Informasi lengkap item {{ $itemDetail->serial_number }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button @click="showUpdateStatusModal()"
                    class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-edit"></i>
                    <span>Update Status</span>
                </button>
                <button @click="showQRModal()"
                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-qrcode"></i>
                    <span>Show QR</span>
                </button>
                <a href="{{ route('item-details.print-qr', $itemDetail->item_detail_id) }}" target="_blank"
                    class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-print"></i>
                    <span>Print QR</span>
                </a>
            </div>
        </div>

        <!-- Status Alert -->
        @if ($itemDetail->status == 'damaged')
            <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-red-900 mb-1">Item Rusak</h3>
                        <p class="text-red-700">Item ini berstatus rusak dan tidak dapat digunakan sampai diperbaiki.</p>
                    </div>
                </div>
            </div>
        @elseif ($itemDetail->status == 'maintenance')
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6">
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-wrench text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-yellow-900 mb-1">Sedang Maintenance</h3>
                        <p class="text-yellow-700">Item ini sedang dalam proses maintenance dan tidak tersedia sementara.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Info Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Item Information Card -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                            <i class="fas fa-box text-white text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $itemDetail->item->item_name }}</h2>
                            <p class="text-gray-600">{{ $itemDetail->item->item_code }}</p>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusInfo['class'] }} mt-2">
                                <span
                                    class="w-1.5 h-1.5 rounded-full mr-1.5
                                    {{ $itemDetail->status == 'available'
                                        ? 'bg-green-400'
                                        : ($itemDetail->status == 'damaged'
                                            ? 'bg-red-400'
                                            : ($itemDetail->status == 'maintenance'
                                                ? 'bg-yellow-400'
                                                : 'bg-blue-400')) }}"></span>
                                {{ $statusInfo['text'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Info -->
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Serial Number</label>
                            <p class="text-lg font-mono text-gray-900 bg-gray-50 p-3 rounded-lg">
                                {{ $itemDetail->serial_number }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">QR Code</label>
                            <p class="text-lg font-mono text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $itemDetail->qr_code }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Kategori</label>
                            <p class="text-lg text-gray-900">
                                {{ $itemDetail->item->category->category_name ?? 'No Category' }}</p>
                        </div>
                    </div>

                    <!-- Location & Additional Info -->
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Lokasi</label>
                            <p class="text-lg text-gray-900">
                                @if ($itemDetail->location)
                                    <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-lg">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ $itemDetail->location }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Lokasi belum diset</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Diterima</label>
                            <p class="text-lg text-gray-900">
                                {{ $itemDetail->goodsReceivedDetail->goodsReceived->received_date ?? $itemDetail->created_at->format('d/m/Y') }}
                            </p>
                            <p class="text-sm text-gray-500">{{ $itemDetail->created_at->diffForHumans() }}</p>
                        </div>
                        @if ($itemDetail->notes)
                            <div>
                                <label class="text-sm font-medium text-gray-500">Catatan</label>
                                <p class="text-lg text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $itemDetail->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>




                <!-- Custom Attributes -->
                @if ($formattedAttributes && count($formattedAttributes) > 0)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Atribut Khusus</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($formattedAttributes as $attribute)
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-gray-500">{{ $attribute['key'] }}</label>
                                    <p class="text-lg text-gray-900">{{ $attribute['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Sidebar -->
            <div class="space-y-6">
                       <!-- QR Code Display Section -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-qrcode mr-2 text-purple-600"></i>
                            QR Code
                        </h3>
                    </div>
                    <div class="p-6">
                        @if ($itemDetail->qr_code)

                            <div class="text-center">
                                @if ($itemDetail->qr_code)
                                    <!-- QR Code Image exists -->
                                    <div class="mb-4">
                                        <img src="{{ asset('storage/qr-codes/item-details/' . $itemDetail->qr_code) }}" alt="QR Code for {{ $itemDetail->serial_number }}"
                                            class="mx-auto border border-gray-200 rounded-lg" style="max-width: 200px;">
                                    </div>
                                @else
                                    <!-- QR Code string exists but image missing -->
                                    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <i class="fas fa-exclamation-triangle text-yellow-600 mb-2"></i>
                                        <p class="text-yellow-800 text-sm">QR Code image tidak ditemukan</p>
                                        <button onclick="regenerateQRImage('{{ $itemDetail->item_detail_id }}')"
                                            class="mt-2 px-3 py-1 bg-yellow-600 text-white rounded text-sm hover:bg-yellow-700">
                                            Regenerate Image
                                        </button>
                                    </div>
                                @endif

                                <!-- QR Code String -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">QR Code</label>
                                    <div class="p-3 bg-gray-50 rounded-lg border">
                                        <code class="text-sm text-gray-900">{{ $itemDetail->qr_code }}</code>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- No QR Code generated yet -->
                            <div class="text-center py-8">
                                <i class="fas fa-qrcode text-4xl text-gray-300 mb-4"></i>
                                <h4 class="text-lg font-medium text-gray-900 mb-2">QR Code Belum Digenerate</h4>
                                <p class="text-gray-500 mb-4">QR Code akan digenerate otomatis saat update item atau klik
                                    tombol di bawah</p>

                                <button onclick="generateQRCode('{{ $itemDetail->item_detail_id }}')"
                                    class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200">
                                    <i class="fas fa-qrcode mr-2"></i>Generate QR Code
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                <!-- Quick Actions Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <button @click="showUpdateStatusModal()"
                            class="w-full flex items-center justify-between p-3 text-left bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-edit text-green-600"></i>
                                <span class="text-green-900 font-medium">Update Status</span>
                            </div>
                            <i class="fas fa-chevron-right text-green-600"></i>
                        </button>

                        <button @click="showQRModal()"
                            class="w-full flex items-center justify-between p-3 text-left bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors duration-200">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-qrcode text-purple-600"></i>
                                <span class="text-purple-900 font-medium">Show QR Code</span>
                            </div>
                            <i class="fas fa-chevron-right text-purple-600"></i>
                        </button>

                        <a href="{{ route('item-details.edit', $itemDetail->item_detail_id) }}"
                            class="w-full flex items-center justify-between p-3 text-left bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-pen text-blue-600"></i>
                                <span class="text-blue-900 font-medium">Edit Item</span>
                            </div>
                            <i class="fas fa-chevron-right text-blue-600"></i>
                        </a>

                        <a href="{{ route('item-details.print-qr', $itemDetail->item_detail_id) }}" target="_blank"
                            class="w-full flex items-center justify-between p-3 text-left bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-print text-gray-600"></i>
                                <span class="text-gray-900 font-medium">Print QR Code</span>
                            </div>
                            <i class="fas fa-external-link-alt text-gray-600"></i>
                        </a>
                    </div>
                </div>

                <!-- Related Item Info -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Item Master</h3>
                    <div class="space-y-3">
                        <a href="{{ route('items.show', $itemDetail->item->item_id) }}"
                            class="block p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-cube text-white"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $itemDetail->item->item_name }}</p>
                                    <p class="text-sm text-gray-500">{{ $itemDetail->item->item_code }}</p>
                                </div>
                            </div>
                        </a>

                        @if ($itemDetail->goodsReceivedDetail && $itemDetail->goodsReceivedDetail->goodsReceived)
                            <a href="{{ route('goods-received.show', $itemDetail->goodsReceivedDetail->goodsReceived->gr_id) }}"
                                class="block p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-truck text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Goods Received</p>
                                        <p class="text-sm text-gray-500">
                                            {{ $itemDetail->goodsReceivedDetail->goodsReceived->gr_number }}</p>
                                    </div>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Statistics -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Usia Item</span>
                            <span class="font-semibold text-gray-900">{{ $itemDetail->created_at->diffInDays() }}
                                hari</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Status Changes</span>
                            <span class="font-semibold text-gray-900">{{ count($usageHistory) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Last Updated</span>
                            <span
                                class="font-semibold text-gray-900">{{ $itemDetail->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage History -->
        @if ($usageHistory && count($usageHistory) > 0)
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-history mr-2 text-blue-600"></i>
                        Riwayat Penggunaan
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach ($usageHistory as $history)
                            <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                                <div
                                    class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-clock text-blue-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-medium text-gray-900">{{ ucfirst($history['action']) }}</h4>
                                        <span class="text-sm text-gray-500">{{ $history['date'] }}</span>
                                    </div>
                                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                                        @if ($history['status_before'] && $history['status_after'])
                                            <span>
                                                Status:
                                                <span class="font-medium">{{ ucfirst($history['status_before']) }}</span>
                                                →
                                                <span class="font-medium">{{ ucfirst($history['status_after']) }}</span>
                                            </span>
                                        @endif
                                        <span>oleh: <span class="font-medium">{{ $history['user'] }}</span></span>
                                    </div>
                                    @if ($history['notes'])
                                        <p class="text-sm text-gray-600 mt-1 italic">{{ $history['notes'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Update Status Modal -->
        <div x-show="updateStatusModal.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.self="hideUpdateStatusModal()"
            @keydown.escape.window="hideUpdateStatusModal()"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="updateStatusModal.show" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-edit text-2xl text-green-600"></i>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Update Status Item</h3>
                    <p class="text-gray-600 text-center mb-6">
                        Update status untuk item <span
                            class="font-semibold text-gray-900">{{ $itemDetail->serial_number }}</span>
                    </p>

                    <form @submit.prevent="confirmUpdateStatus()">
                        <!-- Current Status -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Saat Ini</label>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusInfo['class'] }}">
                                    {{ $statusInfo['text'] }}
                                </span>
                            </div>
                        </div>

                        <!-- New Status Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Baru</label>
                            <select x-model="updateStatusModal.newStatus"
                                class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                required>
                                <option value="">Pilih Status Baru</option>
                                <option value="available">Tersedia</option>
                                <option value="used">Terpakai</option>
                                <option value="damaged">Rusak</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="reserved">Reserved</option>
                            </select>
                        </div>

                        <!-- Location -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                            <input type="text" x-model="updateStatusModal.location"
                                value="{{ $itemDetail->location }}" placeholder="Masukkan lokasi item..."
                                class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan
                                <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <textarea x-model="updateStatusModal.notes" placeholder="Tambahkan catatan perubahan status..."
                                class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                rows="3"></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-4">
                            <button type="button" @click="hideUpdateStatusModal()"
                                class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-times"></i>
                                <span>Batal</span>
                            </button>
                            <button type="submit" :disabled="updateStatusModal.loading || !updateStatusModal.newStatus"
                                class="flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl disabled:opacity-50">
                                <i class="fas fa-save"
                                    :class="{ 'animate-spin fa-spinner': updateStatusModal.loading }"></i>
                                <span x-text="updateStatusModal.loading ? 'Menyimpan...' : 'Update Status'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- QR Code Modal -->
        <div x-show="qrModal.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.self="hideQRModal()" @keydown.escape.window="hideQRModal()"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="qrModal.show" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-qrcode text-2xl text-purple-600"></i>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mb-2">QR Code</h3>
                    <p class="text-gray-600 mb-6">
                        QR Code untuk item <span
                            class="font-semibold text-gray-900">{{ $itemDetail->serial_number }}</span>
                    </p>

                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <div class="text-lg font-mono bg-white p-3 rounded border">{{ $itemDetail->qr_code }}</div>
                    </div>

                    <div class="flex gap-3">
                        <button @click="hideQRModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200">
                            Tutup
                        </button>
                        <a href="{{ route('item-details.print-qr', $itemDetail->item_detail_id) }}" target="_blank"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 text-center">
                            Print QR
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
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

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
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
        function itemDetailShowManager() {
            return {
                updateStatusModal: {
                    show: false,
                    newStatus: '',
                    location: '{{ $itemDetail->location }}',
                    notes: '',
                    loading: false
                },
                qrModal: {
                    show: false
                },

                // Update Status Modal Functions
                showUpdateStatusModal() {
                    this.updateStatusModal = {
                        show: true,
                        newStatus: '',
                        location: '{{ $itemDetail->location }}',
                        notes: '',
                        loading: false
                    };
                },

                hideUpdateStatusModal() {
                    this.updateStatusModal.show = false;
                    setTimeout(() => {
                        this.updateStatusModal = {
                            show: false,
                            newStatus: '',
                            location: '{{ $itemDetail->location }}',
                            notes: '',
                            loading: false
                        };
                    }, 300);
                },

                async confirmUpdateStatus() {
                    if (!this.updateStatusModal.newStatus) {
                        this.showToast('Status baru harus dipilih', 'error');
                        return;
                    }

                    this.updateStatusModal.loading = true;

                    try {
                        const response = await fetch(`/item-details/{{ $itemDetail->item_detail_id }}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                status: this.updateStatusModal.newStatus,
                                location: this.updateStatusModal.location,
                                notes: this.updateStatusModal.notes
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.hideUpdateStatusModal();
                            this.showToast('Status item berhasil diubah!', 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            this.showToast(data.message || 'Gagal mengubah status item', 'error');
                        }
                    } catch (error) {
                        this.showToast('Terjadi kesalahan saat mengubah status item', 'error');
                    } finally {
                        this.updateStatusModal.loading = false;
                    }
                },

                // QR Modal Functions
                showQRModal() {
                    this.qrModal.show = true;
                },

                hideQRModal() {
                    this.qrModal.show = false;
                },

                // Helper function for toast notifications
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
                        </div>
                    `;

                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.remove();
                    }, 5000);
                }
            }
        }
    </script>
    <script>
// JavaScript functions for QR code actions
async function generateQRCode(itemDetailId) {
    try {
        const response = await fetch(`/item-details/${itemDetailId}/generate-qr`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast('QR Code berhasil digenerate!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.message || 'Gagal generate QR Code', 'error');
        }
    } catch (error) {
        showToast('Terjadi kesalahan saat generate QR Code', 'error');
    }
}

async function regenerateQRImage(itemDetailId) {
    try {
        const response = await fetch(`/item-details/${itemDetailId}/regenerate-qr-image`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast('QR Code image berhasil diregenerate!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.message || 'Gagal regenerate QR Code image', 'error');
        }
    } catch (error) {
        showToast('Terjadi kesalahan saat regenerate QR Code image', 'error');
    }
}

function printQR(imageUrl) {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Print QR Code</title>
                <style>
                    body { margin: 0; padding: 20px; text-align: center; }
                    img { max-width: 100%; height: auto; }
                    @media print { body { margin: 0; padding: 0; } }
                </style>
            </head>
            <body>
                <img src="${imageUrl}" alt="QR Code" onload="window.print();">
            </body>
        </html>
    `);
}

function showToast(message, type = 'info') {
    // Toast notification function (sama seperti sebelumnya)
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
    setTimeout(() => toast.remove(), 5000);
}
</script>

    <style>
        /* Custom animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.3s ease-out;
        }

        /* Hover effects for cards */
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Status indicator pulse animation */
        .status-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }
    </style>
@endpush
