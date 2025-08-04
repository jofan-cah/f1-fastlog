@extends('layouts.app')

@section('title', 'Item Details - LogistiK Admin')

@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
    <div class="space-y-6" x-data="itemDetailManager()">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Item Details</h1>
                <p class="text-gray-600 mt-1">Kelola detail item dan tracking asset individual</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                {{-- <button @click="showQRScanModal()"
                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-qrcode"></i>
                    <span>Scan QR</span>
                </button> --}}
                {{-- <a href="{{ route('item-details.create') }}"
                    class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Item Detail</span>
                </a> --}}
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-boxes text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Items</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($itemDetails->total()) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-green-600 to-green-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Ready to Use</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $totalByStatus['available'] ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-tools text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Used</p>
                        <p class="text-2xl font-bold text-gray-900">

                            {{ $totalByStatus['used'] ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Faulty</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $totalByStatus['damaged'] ?? 0 }}

                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-wrench text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Service</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $totalByStatus['maintenance'] ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Bar (Show when items selected) -->
        <div x-show="selectedItems.length > 0" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            class="bg-blue-50 border border-blue-200 rounded-2xl p-4" style="display: none;">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <span class="text-blue-900 font-medium">
                        <span x-text="selectedItems.length"></span> item dipilih
                    </span>
                    <button @click="clearSelection()" class="text-blue-700 hover:text-blue-900 text-sm underline">
                        Clear selection
                    </button>
                </div>
                <div class="flex items-center space-x-3">
                    <button @click="showBulkUpdateStatusModal()"
                        class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-edit"></i>
                        <span>Update Status</span>
                    </button>
                    <button @click="showBulkPrintModal()"
                        class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-print"></i>
                        <span>Print QR Labels</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <form method="GET" action="{{ route('item-details.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari serial number, QR code, atau nama item..."
                                class="pl-10 pr-4 py-3 w-full bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Item Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                        <select name="item_id"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Semua Item</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->item_id }}"
                                    {{ request('item_id') == $item->item_id ? 'selected' : '' }}>
                                    {{ $item->item_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Semua Status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    @switch($status)
                                        @case('stock')
                                            Stock
                                        @break

                                        @case('available')
                                            Ready To Use
                                        @break

                                        @case('used')
                                            Used
                                        @break

                                        @case('damaged')
                                            Faulty
                                        @break

                                        @case('maintenance')
                                            Service
                                        @break

                                        @case('reserved')
                                            Booking
                                        @break

                                        @default
                                            {{ ucfirst($status) }}
                                    @endswitch
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Location Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                        <select name="location"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Semua Lokasi</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location }}"
                                    {{ request('location') == $location ? 'selected' : '' }}>
                                    {{ $location }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filter by PO -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- TAMBAHKAN: Per Page Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Per Halaman</label>
                        <select name="per_page"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            @foreach ($perPageOptions as $option)
                                <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>
                                    {{ $option }} items
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                </div>


                <!-- Filter Buttons -->
                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit"
                        class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-filter"></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('item-details.index') }}"
                        class="px-6 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Reset</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Item Details Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-boxes mr-2 text-blue-600"></i>
                        Daftar Item Details
                    </h3>
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                        <span>Total: {{ $itemDetails->total() }} items</span>
                        <span class="text-gray-400">|</span>
                        <span>Menampilkan: {{ $perPage }} per halaman</span>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left">
                                <input type="checkbox" @change="toggleSelectAll()"
                                    :checked="selectedItems.length > 0 && selectedItems.length === allItemIds.length"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Item Info
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Serial / QR
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kondisi
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lokasi
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                PO / Received Date
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($itemDetails as $itemDetail)
                            @php
                                $statusInfo = $itemDetail->getStatusInfo();
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <!-- Checkbox -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" value="{{ $itemDetail->item_detail_id }}"
                                        x-model="selectedItems"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                </td>

                                <!-- Item Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-box text-white text-lg"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $itemDetail->item->item_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $itemDetail->item->item_code }} |
                                                {{ $itemDetail->item_detail_id }}</div>
                                            <div class="text-xs text-gray-400">
                                                {{ $itemDetail->item->category->category_name ?? 'No Category' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Serial / QR -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-mono">{{ $itemDetail->serial_number }}</div>
                                    <div class="text-sm text-gray-500 font-mono">
                                        {{ $itemDetail->qr_code ?: 'Not generated' }}</div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusInfo['class'] }}">
                                        @php
                                            $labels = [
                                                'stock' => 'Stock',
                                                'available' => 'Ready To Use',
                                                'used' => 'Used',
                                                'damaged' => 'Faulty',
                                                'maintenance' => 'Service',
                                                'reserved' => 'Booking',
                                            ];
                                        @endphp

                                        <span
                                            class="w-1.5 h-1.5 rounded-full mr-1.5
    {{ $itemDetail->status == 'available'
        ? 'bg-green-400'
        : ($itemDetail->status == 'damaged'
            ? 'bg-red-400'
            : ($itemDetail->status == 'maintenance'
                ? 'bg-yellow-400'
                : 'bg-blue-400')) }}"></span>
                                        {{ $labels[$itemDetail->status] ?? ucfirst($itemDetail->status) }}

                                </td>
                                <!-- Alternative: Simplified Badge Version -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($itemDetail->kondisi == 'good' || $itemDetail->kondisi === null)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Good
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                            <i class="fas fa-times-circle mr-1"></i>
                                            No Good
                                        </span>
                                    @endif
                                </td>

                                <!-- Location -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $itemDetail->location ?? '-' }}
                                    </div>
                                </td>

                                <!-- PO / Received Date -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if (
                                        $itemDetail->goodsReceivedDetail &&
                                            $itemDetail->goodsReceivedDetail->goodsReceived &&
                                            $itemDetail->goodsReceivedDetail->goodsReceived->purchaseOrder)
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $itemDetail->goodsReceivedDetail->goodsReceived->purchaseOrder->po_number }}
                                        </div>
                                    @endif
                                    <div class="text-sm text-gray-500">
                                        {{ $itemDetail->goodsReceivedDetail->goodsReceived->received_date ?? $itemDetail->created_at->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $itemDetail->created_at->diffForHumans() }}
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- View Detail -->
                                        <a href="{{ route('item-details.show', $itemDetail->item_detail_id) }}"
                                            class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                            title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Edit (Redirect to Edit Page) -->
                                        <a href="{{ route('item-details.edit', $itemDetail->item_detail_id) }}"
                                            class="text-orange-600 hover:text-orange-900 p-2 hover:bg-orange-50 rounded-lg transition-all duration-200"
                                            title="Edit Item Detail">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Quick Status Update (Modal) -->
                                        {{-- <button
                                            @click="showUpdateStatusModal('{{ $itemDetail->item_detail_id }}', '{{ addslashes($itemDetail->serial_number) }}', '{{ $itemDetail->status }}', '{{ addslashes($itemDetail->location ?? '') }}')"
                                            class="text-green-600 hover:text-green-900 p-2 hover:bg-green-50 rounded-lg transition-all duration-200"
                                            title="Quick Status Update">
                                            <i class="fas fa-sync-alt"></i>
                                        </button> --}}

                                        <!-- Show QR Code -->
                                        {{-- <button @click="showQRModal('{{ $itemDetail->qr_code ?: 'Not generated yet' }}', '{{ addslashes($itemDetail->serial_number) }}', '{{ $itemDetail->item_detail_id }}')"
                                            class="text-purple-600 hover:text-purple-900 p-2 hover:bg-purple-50 rounded-lg transition-all duration-200"
                                            title="Show QR Code">
                                            <i class="fas fa-qrcode"></i>
                                        </button>

                                        <!-- Print QR -->
                                        <a href="{{ route('item-details.print-qr', $itemDetail->item_detail_id) }}" target="_blank"
                                            class="text-gray-600 hover:text-gray-900 p-2 hover:bg-gray-50 rounded-lg transition-all duration-200"
                                            title="Print QR">
                                            <i class="fas fa-print"></i>
                                        </a>

                                        <!-- Dropdown for More Actions -->
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" @click.away="open = false"
                                                class="text-gray-400 hover:text-gray-600 p-2 hover:bg-gray-50 rounded-lg transition-all duration-200"
                                                title="More Actions">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="transform opacity-100 scale-100"
                                                x-transition:leave-end="transform opacity-0 scale-95"
                                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
                                                style="display: none;">
                                                <div class="py-1">
                                                    <a href="{{ route('item-details.edit', $itemDetail->item_detail_id) }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <i class="fas fa-edit mr-2"></i>Edit Full Detail
                                                    </a>
                                                    <button @click="generateQRCode('{{ $itemDetail->item_detail_id }}'); open = false"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <i class="fas fa-qrcode mr-2"></i>Generate QR Code
                                                    </button>
                                                    <a href="{{ route('item-details.show', $itemDetail->item_detail_id) }}"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <i class="fas fa-history mr-2"></i>View History
                                                    </a>
                                                </div>
                                            </div>
                                        </div> --}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-boxes text-4xl text-gray-300 mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Item Detail</h3>
                                        <p class="text-gray-500 mb-4">Belum ada item detail yang terdaftar dalam sistem.
                                        </p>
                                        <a href="{{ route('item-details.create') }}"
                                            class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200">
                                            Tambah Item Detail Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <!-- Pagination -->
        @if ($itemDetails->hasPages())
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ $itemDetails->firstItem() }} sampai {{ $itemDetails->lastItem() }}
                        dari {{ $itemDetails->total() }} hasil
                        <span class="text-gray-500">({{ $perPage }} per halaman)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        {{ $itemDetails->appends(request()->query())->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Keep existing modals from the original view -->
        <!-- ... (Include all the modal code from the original here) ... -->
        {{-- resources/views/item-details/partials/modals.blade.php --}}

        <!-- Bulk Print Modal -->
        <div x-show="bulkPrintModal.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.self="hideBulkPrintModal()"
            @keydown.escape.window="hideBulkPrintModal()"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="bulkPrintModal.show" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-2xl max-w-lg w-full">
                <div class="p-6">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-print text-2xl text-purple-600"></i>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Print QR Labels</h3>
                    <p class="text-gray-600 text-center mb-6">
                        Konfigurasi print untuk <span x-text="selectedItems.length"
                            class="font-semibold text-gray-900"></span> items
                    </p>

                    <form @submit.prevent="processBulkPrint()">
                        <!-- Label Size -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ukuran Label</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" x-model="bulkPrintModal.labelSize" value="sfp"
                                        class="sr-only peer" checked>
                                    <div
                                        class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-gray-300 transition-colors">
                                        <div class="text-sm font-medium text-gray-900">SFP/Media Converter</div>
                                        <div class="text-xs text-gray-500">1cm x 3cm (Sangat Kecil)</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" x-model="bulkPrintModal.labelSize" value="small"
                                        class="sr-only peer">
                                    <div
                                        class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-gray-300 transition-colors">
                                        <div class="text-sm font-medium text-gray-900">Small</div>
                                        <div class="text-xs text-gray-500">2cm x 4cm</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" x-model="bulkPrintModal.labelSize" value="medium"
                                        class="sr-only peer">
                                    <div
                                        class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-gray-300 transition-colors">
                                        <div class="text-sm font-medium text-gray-900">Medium</div>
                                        <div class="text-xs text-gray-500">3cm x 5cm</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" x-model="bulkPrintModal.labelSize" value="large"
                                        class="sr-only peer">
                                    <div
                                        class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-gray-300 transition-colors">
                                        <div class="text-sm font-medium text-gray-900">Large</div>
                                        <div class="text-xs text-gray-500">4cm x 6cm</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Labels Per Row -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Labels Per Row</label>
                            <select x-model="bulkPrintModal.labelsPerRow"
                                class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="2">2 Labels</option>
                                <option value="3">3 Labels</option>
                                <option value="4">4 Labels</option>
                                <option value="5">5 Labels</option>
                                <option value="6">6 Labels</option>
                            </select>
                        </div>

                        <!-- Print Options -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Print Options</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="bulkPrintModal.includeItemName"
                                        class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Include Item Name</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="bulkPrintModal.includeSerial"
                                        class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Include Serial Number</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="bulkPrintModal.includePO"
                                        class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Include PO Number</span>
                                </label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-4">
                            <button type="button" @click="hideBulkPrintModal()"
                                class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-times"></i>
                                <span>Batal</span>
                            </button>
                            <button type="submit" :disabled="bulkPrintModal.loading"
                                class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl disabled:opacity-50">
                                <i class="fas fa-print" :class="{ 'animate-spin fa-spinner': bulkPrintModal.loading }"></i>
                                <span x-text="bulkPrintModal.loading ? 'Processing...' : 'Print Labels'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk Update Status Modal -->
        <div x-show="bulkUpdateStatusModal.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.self="hideBulkUpdateStatusModal()"
            @keydown.escape.window="hideBulkUpdateStatusModal()"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="bulkUpdateStatusModal.show" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-2xl max-w-lg w-full">
                <div class="p-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-edit text-2xl text-green-600"></i>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Bulk Update Status</h3>
                    <p class="text-gray-600 text-center mb-6">
                        Update status untuk <span x-text="selectedItems.length"
                            class="font-semibold text-gray-900"></span> items
                    </p>

                    <form @submit.prevent="processBulkUpdateStatus()">
                        <!-- New Status -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Baru</label>
                            <select x-model="bulkUpdateStatusModal.newStatus"
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Lokasi Baru
                                <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="text" x-model="bulkUpdateStatusModal.location"
                                placeholder="Masukkan lokasi baru (kosongkan jika tidak diubah)..."
                                class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan
                                <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <textarea x-model="bulkUpdateStatusModal.notes" placeholder="Tambahkan catatan untuk perubahan status..."
                                class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                rows="3"></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-4">
                            <button type="button" @click="hideBulkUpdateStatusModal()"
                                class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-times"></i>
                                <span>Batal</span>
                            </button>
                            <button type="submit"
                                :disabled="bulkUpdateStatusModal.loading || !bulkUpdateStatusModal.newStatus"
                                class="flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl disabled:opacity-50">
                                <i class="fas fa-save"
                                    :class="{ 'animate-spin fa-spinner': bulkUpdateStatusModal.loading }"></i>
                                <span x-text="bulkUpdateStatusModal.loading ? 'Updating...' : 'Update Status'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Update Status Modal (Single Item) -->
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
                        <i class="fas fa-sync-alt text-2xl text-green-600"></i>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Quick Status Update</h3>
                    <p class="text-gray-600 text-center mb-6">
                        Update status untuk item <span x-text="updateStatusModal.serialNumber"
                            class="font-semibold text-gray-900"></span>
                    </p>

                    <form @submit.prevent="confirmUpdateStatus()">
                        <!-- Current Status -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Saat Ini</label>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <span x-text="getStatusText(updateStatusModal.currentStatus)"
                                    :class="getStatusClass(updateStatusModal.currentStatus)"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
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
                                placeholder="Masukkan lokasi item..."
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
                        QR Code untuk item <span x-text="qrModal.serialNumber" class="font-semibold text-gray-900"></span>
                    </p>

                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <div class="text-lg font-mono bg-white p-3 rounded border break-all" x-text="qrModal.qrCode">
                        </div>
                    </div>

                    <div class="text-sm text-gray-500 mb-4">
                        <template x-if="qrModal.qrCode === 'Not generated yet'">
                            <div class="text-orange-600">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                QR Code belum digenerate. Klik "Generate QR Code" untuk membuat.
                            </div>
                        </template>
                    </div>

                    <div class="flex gap-3">
                        <button @click="hideQRModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200">
                            Tutup
                        </button>
                        <template x-if="qrModal.qrCode === 'Not generated yet'">
                            <button @click="generateQRCodeFromModal()"
                                class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200">
                                Generate QR
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Scan Modal -->
        <div x-show="qrScanModal.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.self="hideQRScanModal()" @keydown.escape.window="hideQRScanModal()"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="qrScanModal.show" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
                <div class="p-6">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-camera text-2xl text-purple-600"></i>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Scan QR Code</h3>
                    <p class="text-gray-600 text-center mb-6">Masukkan atau scan QR code untuk mencari item</p>

                    <form @submit.prevent="scanQR()">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">QR Code</label>
                            <input type="text" x-model="qrScanModal.qrCode" placeholder="Masukkan QR code..."
                                class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                required>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="button" @click="hideQRScanModal()"
                                class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-times"></i>
                                <span>Batal</span>
                            </button>
                            <button type="submit" :disabled="qrScanModal.loading"
                                class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl disabled:opacity-50">
                                <i class="fas fa-search" :class="{ 'animate-spin fa-spinner': qrScanModal.loading }"></i>
                                <span x-text="qrScanModal.loading ? 'Mencari...' : 'Scan'"></span>
                            </button>
                        </div>
                    </form>
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

    <!-- Include all modals from original view -->
    @include('item-details.partials.modals')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const perPageSelect = document.querySelector('select[name="per_page"]');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    this.closest('form').submit();
                });
            }
        });

        function itemDetailManager() {
            return {
                selectedItems: [],
                allItemIds: [
                    @foreach ($itemDetails as $item)
                        '{{ $item->item_detail_id }}',
                    @endforeach
                ],

                // Modals
                bulkPrintModal: {
                    show: false,
                    labelSize: 'sfp',
                    labelsPerRow: 6,
                    includeItemName: true,
                    includeSerial: true,
                    includePO: false,
                    loading: false
                },

                bulkUpdateStatusModal: {
                    show: false,
                    newStatus: '',
                    location: '',
                    notes: '',
                    loading: false
                },

                updateStatusModal: {
                    show: false,
                    itemDetailId: '',
                    serialNumber: '',
                    currentStatus: '',
                    newStatus: '',
                    location: '',
                    notes: '',
                    loading: false
                },

                qrModal: {
                    show: false,
                    qrCode: '',
                    serialNumber: '',
                    itemDetailId: ''
                },

                qrScanModal: {
                    show: false,
                    qrCode: '',
                    loading: false
                },

                // Selection functions
                toggleSelectAll() {
                    if (this.selectedItems.length === this.allItemIds.length) {
                        this.selectedItems = [];
                    } else {
                        this.selectedItems = [...this.allItemIds];
                    }
                },

                clearSelection() {
                    this.selectedItems = [];
                },

                // Bulk Print Modal
                showBulkPrintModal() {
                    if (this.selectedItems.length === 0) {
                        this.showToast('Pilih item terlebih dahulu', 'error');
                        return;
                    }
                    this.bulkPrintModal.show = true;
                },

                hideBulkPrintModal() {
                    this.bulkPrintModal.show = false;
                    setTimeout(() => {
                        this.bulkPrintModal.loading = false;
                    }, 300);
                },

                async processBulkPrint() {
                    this.bulkPrintModal.loading = true;

                    try {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('item-details.bulk-print-labels') }}';
                        form.target = '_blank';
                        form.style.display = 'none';

                        // CSRF Token
                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        form.appendChild(csrfToken);

                        // Selected items
                        this.selectedItems.forEach(itemId => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'item_detail_ids[]';
                            input.value = itemId;
                            form.appendChild(input);
                        });

                        // Print options
                        const options = {
                            'label_size': this.bulkPrintModal.labelSize,
                            'labels_per_row': this.bulkPrintModal.labelsPerRow,
                            'include_item_name': this.bulkPrintModal.includeItemName,
                            'include_serial': this.bulkPrintModal.includeSerial,
                            'include_po': this.bulkPrintModal.includePO
                        };

                        Object.entries(options).forEach(([key, value]) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            form.appendChild(input);
                        });

                        document.body.appendChild(form);
                        form.submit();
                        document.body.removeChild(form);

                        this.hideBulkPrintModal();
                        this.showToast(`Print page opened for ${this.selectedItems.length} items`, 'success');

                    } catch (error) {
                        this.showToast('Terjadi kesalahan saat memproses print', 'error');
                    } finally {
                        this.bulkPrintModal.loading = false;
                    }
                },

                // Bulk Update Status Modal
                showBulkUpdateStatusModal() {
                    if (this.selectedItems.length === 0) {
                        this.showToast('Pilih item terlebih dahulu', 'error');
                        return;
                    }
                    this.bulkUpdateStatusModal.show = true;
                },

                hideBulkUpdateStatusModal() {
                    this.bulkUpdateStatusModal.show = false;
                    setTimeout(() => {
                        this.bulkUpdateStatusModal = {
                            show: false,
                            newStatus: '',
                            location: '',
                            notes: '',
                            loading: false
                        };
                    }, 300);
                },

                async processBulkUpdateStatus() {
                    if (!this.bulkUpdateStatusModal.newStatus) {
                        this.showToast('Status baru harus dipilih', 'error');
                        return;
                    }

                    this.bulkUpdateStatusModal.loading = true;

                    try {
                        const response = await fetch('{{ route('item-details.bulk-update-status') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                item_detail_ids: this.selectedItems,
                                status: this.bulkUpdateStatusModal.newStatus,
                                location: this.bulkUpdateStatusModal.location,
                                notes: this.bulkUpdateStatusModal.notes
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.hideBulkUpdateStatusModal();
                            this.clearSelection();
                            this.showToast(`${this.selectedItems.length} items berhasil diupdate!`, 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            this.showToast(data.message || 'Gagal mengupdate status items', 'error');
                        }
                    } catch (error) {
                        this.showToast('Terjadi kesalahan saat mengupdate status', 'error');
                    } finally {
                        this.bulkUpdateStatusModal.loading = false;
                    }
                },

                // Single Update Status Modal (Quick Status Update)
                showUpdateStatusModal(itemDetailId, serialNumber, currentStatus, location) {
                    this.updateStatusModal = {
                        show: true,
                        itemDetailId: itemDetailId,
                        serialNumber: serialNumber,
                        currentStatus: currentStatus,
                        newStatus: '',
                        location: location,
                        notes: '',
                        loading: false
                    };
                },

                hideUpdateStatusModal() {
                    this.updateStatusModal.show = false;
                    setTimeout(() => {
                        this.updateStatusModal = {
                            show: false,
                            itemDetailId: '',
                            serialNumber: '',
                            currentStatus: '',
                            newStatus: '',
                            location: '',
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
                        const response = await fetch(`/item-details/${this.updateStatusModal.itemDetailId}/status`, {
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
                showQRModal(qrCode, serialNumber, itemDetailId = null) {
                    this.qrModal = {
                        show: true,
                        qrCode: qrCode || 'Not generated yet',
                        serialNumber: serialNumber,
                        itemDetailId: itemDetailId
                    };
                },

                hideQRModal() {
                    this.qrModal.show = false;
                    setTimeout(() => {
                        this.qrModal = {
                            show: false,
                            qrCode: '',
                            serialNumber: '',
                            itemDetailId: ''
                        };
                    }, 300);
                },

                // QR Scan Modal Functions
                showQRScanModal() {
                    this.qrScanModal = {
                        show: true,
                        qrCode: '',
                        loading: false
                    };
                },

                hideQRScanModal() {
                    this.qrScanModal.show = false;
                    setTimeout(() => {
                        this.qrScanModal = {
                            show: false,
                            qrCode: '',
                            loading: false
                        };
                    }, 300);
                },

                async scanQR() {
                    if (!this.qrScanModal.qrCode.trim()) {
                        this.showToast('QR Code harus diisi', 'error');
                        return;
                    }

                    this.qrScanModal.loading = true;

                    try {
                        const response = await fetch('{{ route('item-details.scan-qr') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                qr_code: this.qrScanModal.qrCode
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.hideQRScanModal();
                            // Redirect to item detail page
                            window.location.href = `/item-details/${data.item_detail.item_detail_id}`;
                        } else {
                            this.showToast(data.error || 'Item tidak ditemukan', 'error');
                        }
                    } catch (error) {
                        this.showToast('Terjadi kesalahan saat scanning QR', 'error');
                    } finally {
                        this.qrScanModal.loading = false;
                    }
                },

                // Generate QR Code function
                async generateQRCode(itemDetailId) {
                    try {
                        const response = await fetch(`/item-details/${itemDetailId}/generate-qr`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showToast('QR Code berhasil digenerate!', 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            this.showToast(data.message || 'Gagal generate QR Code', 'error');
                        }
                    } catch (error) {
                        this.showToast('Terjadi kesalahan saat generate QR Code', 'error');
                    }
                },

                // Generate QR Code from modal
                async generateQRCodeFromModal() {
                    if (!this.qrModal.itemDetailId) {
                        this.showToast('Item Detail ID tidak ditemukan', 'error');
                        return;
                    }

                    try {
                        const response = await fetch(`/item-details/${this.qrModal.itemDetailId}/generate-qr`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Update modal with new QR code
                            this.qrModal.qrCode = data.qr_code;
                            this.showToast('QR Code berhasil digenerate!', 'success');
                        } else {
                            this.showToast(data.message || 'Gagal generate QR Code', 'error');
                        }
                    } catch (error) {
                        this.showToast('Terjadi kesalahan saat generate QR Code', 'error');
                    }
                },

                // **FIXED: Add missing getAttributeTemplates method**
                async getAttributeTemplates(categoryId) {
                    try {
                        const response = await fetch(`/item-details/ajax/attribute-templates/${categoryId}`, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            return data.templates;
                        } else {
                            this.showToast('Gagal mengambil template attributes', 'error');
                            return {};
                        }
                    } catch (error) {
                        this.showToast('Terjadi kesalahan saat mengambil template', 'error');
                        return {};
                    }
                },

                // Status helper functions
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

                getStatusClass(status) {
                    const classMap = {
                        'available': 'bg-green-100 text-green-800',
                        'used': 'bg-blue-100 text-blue-800',
                        'damaged': 'bg-red-100 text-red-800',
                        'maintenance': 'bg-yellow-100 text-yellow-800',
                        'reserved': 'bg-purple-100 text-purple-800'
                    };
                    return classMap[status] || 'bg-gray-100 text-gray-800';
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

        /* Selection highlight */
        tr:has(input[type="checkbox"]:checked) {
            background-color: rgba(59, 130, 246, 0.05);
            border-left: 3px solid #3b82f6;
        }

        /* Bulk action bar animation */
        .bulk-action-bar {
            animation: slideInFromTop 0.3s ease-out;
        }

        @keyframes slideInFromTop {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Action buttons responsive spacing */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: nowrap;
            justify-content: flex-end;
            align-items: center;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-wrap: wrap;
                gap: 0.25rem;
            }
        }

        /* Status indicator animations */
        .status-indicator {
            position: relative;
            overflow: hidden;
        }

        .status-indicator::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .status-indicator:hover::before {
            left: 100%;
        }
    </style>
@endpush
