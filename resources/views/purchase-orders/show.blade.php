@extends('layouts.app')

@section('title', 'Detail Purchase Order - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="purchaseOrderDetail()">
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
                    <a href="{{ route('purchase-orders.index') }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                        Purchase Orders
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">{{ $purchaseOrder->po_number }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">

            <div class="w-16 h-16 bg-gradient-to-br {{ $statusInfo['text'] == 'Draft' ? 'from-green-600 to-green-700' : ($statusInfo['text'] == 'Dibatalkan' ? 'from-red-600 to-red-700' : 'from-blue-600 to-blue-700') }} rounded-2xl flex items-center justify-center">
                <i class="fas fa-file-invoice text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $purchaseOrder->po_number }}</h1>
                <p class="text-gray-600 mt-1">{{ $purchaseOrder->supplier->supplier_name }} • {{ $purchaseOrder->po_date->format('d/m/Y') }}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            @if($purchaseOrder->canBeEdited())
                <a href="{{ route('purchase-orders.edit', $purchaseOrder->po_id) }}"
                   class="px-4 py-2 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-edit"></i>
                    <span>Edit PO</span>
                </a>
            @endif

            <button @click="showPrintModal()"
                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-print"></i>
                <span>Print</span>
            </button>

            @if($purchaseOrder->canReceiveGoods())
                <a href="{{ route('goods-received.create', ['po_id' => $purchaseOrder->po_id]) }}"
                   class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-truck"></i>
                    <span>Terima Barang</span>
                </a>
            @endif

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-ellipsis-v"></i>
                    <span>Lainnya</span>
                </button>

                <div x-show="open"
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 z-50">
                    <div class="py-2">
                        <button @click="showDuplicateModal(); open = false"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center space-x-2">
                            <i class="fas fa-copy w-4"></i>
                            <span>Duplikasi PO</span>
                        </button>

                        @if($purchaseOrder->status !== 'cancelled')
                            <button @click="showStatusModal(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center space-x-2">
                                <i class="fas fa-exchange-alt w-4"></i>
                                <span>Ubah Status</span>
                            </button>
                        @endif

                        @if($purchaseOrder->canBeCancelled())
                            <hr class="my-1">
                            <button @click="showCancelModal(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center space-x-2">
                                <i class="fas fa-times w-4"></i>
                                <span>Batalkan PO</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status & Progress Badges -->
    <div class="flex items-center space-x-3 flex-wrap">
        <!-- Status Badge -->
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusInfo['class'] }}">
            <span class="w-2 h-2 rounded-full mr-2 {{ $purchaseOrder->status == 'received' ? 'bg-green-400' : ($purchaseOrder->status == 'cancelled' ? 'bg-red-400' : 'bg-blue-400') }}"></span>
            {{ $statusInfo['text'] }}
        </span>

        <!-- Progress Badge -->
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <i class="fas fa-chart-pie mr-2"></i>
            {{ $summaryInfo['completion_percentage'] }}% Selesai
        </span>

        <!-- Overdue Badge -->
        @if($purchaseOrder->isOverdue())
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Terlambat {{ abs($purchaseOrder->getDaysUntilExpected()) }} hari
            </span>
        @endif

        <!-- Total Items Badge -->
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
            <i class="fas fa-boxes mr-2"></i>
            {{ $summaryInfo['total_items'] }} Items
        </span>

        <!-- PO ID Badge -->
        <span class="text-sm text-gray-500">ID: {{ $purchaseOrder->po_id }}</span>
    </div>

    <!-- Warning for Overdue -->
    @if($purchaseOrder->isOverdue())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-red-900 mb-2">Purchase Order Terlambat</h3>
                    <p class="text-red-700 text-sm">
                        PO ini sudah melewati tanggal yang diharapkan ({{ $purchaseOrder->expected_date->format('d/m/Y') }})
                        sebanyak {{ abs($purchaseOrder->getDaysUntilExpected()) }} hari.
                        Silakan hubungi supplier untuk konfirmasi status pengiriman.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- PO Information Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Informasi Purchase Order
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor PO</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm font-mono text-gray-900">{{ $purchaseOrder->po_number }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal PO</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $purchaseOrder->po_date->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Diharapkan</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">
                                    {{ $purchaseOrder->expected_date ? $purchaseOrder->expected_date->format('d/m/Y') : 'Tidak ditentukan' }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Total Amount</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm font-bold text-gray-900">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dibuat Oleh</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $purchaseOrder->createdBy->full_name }}</span>
                                <div class="text-xs text-gray-500 mt-1">{{ $purchaseOrder->createdBy->userLevel->level_name ?? 'No Level' }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dibuat</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $purchaseOrder->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    @if($purchaseOrder->notes)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $purchaseOrder->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Supplier Information Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-building mr-2 text-green-600"></i>
                        Informasi Supplier
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-600 to-green-700 rounded-xl flex items-center justify-center">
                            <i class="fas fa-building text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Supplier</label>
                                    <div class="text-lg font-medium text-gray-900">{{ $purchaseOrder->supplier->supplier_name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Supplier</label>
                                    <div class="text-sm text-gray-600 font-mono">{{ $purchaseOrder->supplier->supplier_code }}</div>
                                </div>
                                @if($purchaseOrder->supplier->contact_person)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                                        <div class="text-sm text-gray-600">{{ $purchaseOrder->supplier->contact_person }}</div>
                                    </div>
                                @endif
                                @if($purchaseOrder->supplier->phone)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                                        <div class="text-sm text-gray-600">{{ $purchaseOrder->supplier->phone }}</div>
                                    </div>
                                @endif
                            </div>
                            @if($purchaseOrder->supplier->address)
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                                    <div class="text-sm text-gray-600">{{ $purchaseOrder->supplier->address }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- PO Items -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-list mr-2 text-purple-600"></i>
                            Detail Items ({{ $purchaseOrder->poDetails->count() }})
                        </h3>
                        @if($purchaseOrder->canBeEdited())
                            <button @click="showAddItemModal()"
                                    class="px-3 py-1 bg-blue-600 text-white text-xs rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus mr-1"></i>
                                Tambah Item
                            </button>
                        @endif
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                                @if($purchaseOrder->canBeEdited())
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($purchaseOrder->poDetails as $detail)
                                @php $detailStatus = $detail->getStatusInfo(); @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-box text-white text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $detail->item->item_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $detail->item->item_code }}</div>
                                                <div class="text-xs text-gray-400">{{ $detail->item->category->category_name ?? 'No Category' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $detail->quantity_ordered }} {{ $detail->item->unit }}</div>
                                        @if($detail->quantity_received > 0)
                                            <div class="text-xs text-green-600">{{ $detail->quantity_received }} diterima</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</div>
                                        <div class="text-xs text-gray-500">per {{ $detail->item->unit }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">Rp {{ number_format($detail->total_price, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-gradient-to-r from-blue-600 to-green-600 h-2 rounded-full"
                                                     style="width: {{ $detail->getCompletionPercentage() }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600">{{ $detail->getCompletionPercentage() }}%</span>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $detailStatus['class'] }} mt-1">
                                            {{ $detailStatus['text'] }}
                                        </span>
                                    </td>
                                    @if($purchaseOrder->canBeEdited())
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end space-x-1">
                                                <button @click="editDetail('{{ $detail->po_detail_id }}')"
                                                        class="text-blue-600 hover:text-blue-800 p-1 rounded"
                                                        title="Edit">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </button>
                                                <button @click="removeDetail('{{ $detail->po_detail_id }}')"
                                                        class="text-red-600 hover:text-red-800 p-1 rounded"
                                                        title="Hapus">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t">
                            <tr>
                                <td colspan="{{ $purchaseOrder->canBeEdited() ? '4' : '3' }}" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                    Total Amount:
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                    Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}
                                </td>
                                @if($purchaseOrder->canBeEdited())
                                    <td class="px-6 py-4"></td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column - Summary & Actions -->
        <div class="space-y-6">
            <!-- Summary Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
                        Ringkasan
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Progress Circle -->
                        <div class="text-center">
                            <div class="relative inline-block">
                                <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="40" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                                    <circle cx="50" cy="50" r="40" stroke="#3b82f6" stroke-width="8" fill="none"
                                            stroke-dasharray="{{ 2 * pi() * 40 }}"
                                            stroke-dashoffset="{{ 2 * pi() * 40 * (1 - $summaryInfo['completion_percentage'] / 100) }}"
                                            stroke-linecap="round"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-xl font-bold text-gray-900">{{ $summaryInfo['completion_percentage'] }}%</span>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 mt-2">Progress Penerimaan</div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <div class="text-lg font-bold text-blue-600">{{ $summaryInfo['total_items'] }}</div>
                                <div class="text-xs text-gray-600">Total Items</div>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <div class="text-lg font-bold text-green-600">{{ $summaryInfo['total_quantity'] }}</div>
                                <div class="text-xs text-gray-600">Total Qty</div>
                            </div>
                            <div class="text-center p-3 bg-purple-50 rounded-lg">
                                <div class="text-lg font-bold text-purple-600">{{ $summaryInfo['total_received'] }}</div>
                                <div class="text-xs text-gray-600">Diterima</div>
                            </div>
                            <div class="text-center p-3 bg-yellow-50 rounded-lg">
                                <div class="text-lg font-bold text-yellow-600">{{ $summaryInfo['total_quantity'] - $summaryInfo['total_received'] }}</div>
                                <div class="text-xs text-gray-600">Sisa</div>
                            </div>
                        </div>

                        <!-- Days info -->
                        @if($purchaseOrder->expected_date)
                            <div class="text-center p-3 {{ $summaryInfo['is_overdue'] ? 'bg-red-50 text-red-700' : 'bg-gray-50 text-gray-700' }} rounded-lg">
                                @if($summaryInfo['is_overdue'])
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Terlambat {{ abs($summaryInfo['days_until_expected']) }} hari
                                @else
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $summaryInfo['days_until_expected'] }} hari lagi
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bolt mr-2 text-yellow-600"></i>
                        Aksi Cepat
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @if($purchaseOrder->canReceiveGoods())
                            <a href="{{ route('goods-received.create', ['po_id' => $purchaseOrder->po_id]) }}"
                               class="w-full px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-truck"></i>
                                <span>Terima Barang</span>
                            </a>
                        @endif

                        <button @click="showPrintModal()"
                                class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-print"></i>
                            <span>Print PO</span>
                        </button>

                        <a href="{{ route('purchase-orders.index', ['supplier_id' => $purchaseOrder->supplier_id]) }}"
                           class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-search"></i>
                            <span>PO Supplier Ini</span>
                        </a>

                        <button @click="showDuplicateModal()"
                                class="w-full px-4 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-copy"></i>
                            <span>Duplikasi PO</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Goods Received -->
            @if($purchaseOrder->goodsReceived->count() > 0)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-truck mr-2 text-green-600"></i>
                            Penerimaan Terbaru
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @foreach($purchaseOrder->goodsReceived as $gr)
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $gr->gr_number }}</div>
                                        <div class="text-sm text-gray-500">{{ $gr->received_date?->format('d/m/Y') ?? ''  }}</div>
                                        <div class="text-xs text-gray-400">{{ $gr->goodsReceivedDetails?->count() ?? '' }} items</div>
                                    </div>
                                    <a href="{{ route('goods-received.show', $gr->gr_id) }}"
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="px-6 py-3 border-t bg-gray-50">
                        <a href="{{ route('goods-received.index', ['po_id' => $purchaseOrder->po_id]) }}"
                           class="text-sm text-blue-600 hover:text-blue-800">
                            Lihat semua penerimaan <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Metadata Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info mr-2 text-gray-600"></i>
                        Metadata
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">PO ID:</span>
                            <span class="text-gray-900 font-mono">{{ $purchaseOrder->po_id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Supplier ID:</span>
                            <span class="text-gray-900 font-mono">{{ $purchaseOrder->supplier_id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Dibuat:</span>
                            <span class="text-gray-900">{{ $purchaseOrder->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Diupdate:</span>
                            <span class="text-gray-900">{{ $purchaseOrder->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Items:</span>
                            <span class="text-gray-900">{{ $purchaseOrder->poDetails->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Modal -->
    <div x-show="printModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hidePrintModal()"
         @keydown.escape.window="hidePrintModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="printModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-print text-2xl text-purple-600"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Print Purchase Order</h3>

                <p class="text-gray-600 text-center mb-6">
                    Cetak PO {{ $purchaseOrder->po_number }}? Pastikan printer sudah siap dan terhubung.
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            @click="hidePrintModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="button"
                            @click="confirmPrint()"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-print"></i>
                        <span>Print</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Duplicate Modal -->
    <div x-show="duplicateModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hideDuplicateModal()"
         @keydown.escape.window="hideDuplicateModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="duplicateModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-copy text-2xl text-green-600"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Duplikasi Purchase Order</h3>

                <p class="text-gray-600 text-center mb-6">
                    Buat duplikasi dari PO {{ $purchaseOrder->po_number }}? PO baru akan dibuat dengan status draft.
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            @click="hideDuplicateModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="button"
                            @click="confirmDuplicate()"
                            :disabled="duplicateModal.loading"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl disabled:opacity-50">
                        <i class="fas fa-copy" :class="{ 'animate-spin fa-spinner': duplicateModal.loading }"></i>
                        <span x-text="duplicateModal.loading ? 'Menduplikasi...' : 'Duplikasi'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Modal -->
    <div x-show="statusModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hideStatusModal()"
         @keydown.escape.window="hideStatusModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="statusModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exchange-alt text-2xl text-blue-600"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Ubah Status PO</h3>

                <form @submit.prevent="confirmStatusChange()">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Baru</label>
                        <select x-model="statusModal.newStatus"
                                class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @if($purchaseOrder->status == 'draft')
                                <option value="sent">Terkirim</option>
                                <option value="cancelled">Dibatalkan</option>
                            @elseif($purchaseOrder->status == 'sent')
                                <option value="partial">Sebagian Diterima</option>
                                <option value="received">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            @elseif($purchaseOrder->status == 'partial')
                                <option value="received">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            @endif
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                        <textarea x-model="statusModal.notes"
                                  placeholder="Berikan catatan perubahan status..."
                                  class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  rows="3"></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button"
                                @click="hideStatusModal()"
                                class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </button>
                        <button type="submit"
                                :disabled="statusModal.loading || !statusModal.newStatus"
                                class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl disabled:opacity-50">
                            <i class="fas fa-save" :class="{ 'animate-spin fa-spinner': statusModal.loading }"></i>
                            <span x-text="statusModal.loading ? 'Menyimpan...' : 'Ubah Status'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div x-show="cancelModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hideCancelModal()"
         @keydown.escape.window="hideCancelModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="cancelModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-times text-2xl text-red-600"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Batalkan Purchase Order</h3>

                <p class="text-gray-600 text-center mb-4">
                    Apakah Anda yakin ingin membatalkan PO {{ $purchaseOrder->po_number }}?
                </p>

                <form @submit.prevent="confirmCancel()">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Pembatalan</label>
                        <textarea x-model="cancelModal.reason"
                                  placeholder="Berikan alasan pembatalan..."
                                  class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                  rows="3" required></textarea>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                        <div class="flex items-start space-x-2 text-yellow-800">
                            <i class="fas fa-exclamation-triangle mt-0.5"></i>
                            <div class="text-sm">
                                <p class="font-medium">Perhatian!</p>
                                <p class="text-xs mt-1">PO yang dibatalkan tidak dapat dikembalikan ke status sebelumnya.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button"
                                @click="hideCancelModal()"
                                class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-arrow-left"></i>
                            <span>Kembali</span>
                        </button>
                        <button type="submit"
                                :disabled="cancelModal.loading || !cancelModal.reason.trim()"
                                class="flex-1 px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl disabled:opacity-50">
                            <i class="fas fa-times" :class="{ 'animate-spin fa-spinner': cancelModal.loading }"></i>
                            <span x-text="cancelModal.loading ? 'Membatalkan...' : 'Batalkan PO'"></span>
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
    function purchaseOrderDetail() {
        return {
            printModal: { show: false },
            duplicateModal: { show: false, loading: false },
            statusModal: {
                show: false,
                newStatus: '',
                notes: '',
                loading: false
            },
            cancelModal: {
                show: false,
                reason: '',
                loading: false
            },

            // Print Modal Functions
            showPrintModal() {
                this.printModal.show = true;
            },

            hidePrintModal() {
                this.printModal.show = false;
            },

            confirmPrint() {
                const printUrl = '{{ route("purchase-orders.print", $purchaseOrder->po_id) }}';
                window.open(printUrl, '_blank');
                this.hidePrintModal();
            },

            // Duplicate Modal Functions
            showDuplicateModal() {
                this.duplicateModal.show = true;
                this.duplicateModal.loading = false;
            },

            hideDuplicateModal() {
                this.duplicateModal.show = false;
                this.duplicateModal.loading = false;
            },

            async confirmDuplicate() {
                this.duplicateModal.loading = true;

                try {
                    const response = await fetch('{{ route("purchase-orders.duplicate", $purchaseOrder->po_id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.hideDuplicateModal();
                        this.showToast('PO berhasil diduplikasi!', 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 1000);
                    } else {
                        this.showToast(data.message || 'Gagal menduplikasi PO', 'error');
                    }
                } catch (error) {
                    this.showToast('Terjadi kesalahan saat menduplikasi PO', 'error');
                } finally {
                    this.duplicateModal.loading = false;
                }
            },

            // Status Modal Functions
            showStatusModal() {
                this.statusModal.show = true;
                this.statusModal.newStatus = '';
                this.statusModal.notes = '';
                this.statusModal.loading = false;
            },

            hideStatusModal() {
                this.statusModal.show = false;
            },

            async confirmStatusChange() {
                if (!this.statusModal.newStatus) return;

                this.statusModal.loading = true;

                try {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("purchase-orders.update-status", $purchaseOrder->po_id) }}';
                    form.style.display = 'none';

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const statusField = document.createElement('input');
                    statusField.type = 'hidden';
                    statusField.name = 'status';
                    statusField.value = this.statusModal.newStatus;

                    const notesField = document.createElement('input');
                    notesField.type = 'hidden';
                    notesField.name = 'notes';
                    notesField.value = this.statusModal.notes;

                    form.appendChild(csrfToken);
                    form.appendChild(statusField);
                    form.appendChild(notesField);
                    document.body.appendChild(form);

                    this.hideStatusModal();
                    form.submit();
                } catch (error) {
                    this.showToast('Terjadi kesalahan saat mengubah status', 'error');
                    this.statusModal.loading = false;
                }
            },

            // Cancel Modal Functions
            showCancelModal() {
                this.cancelModal.show = true;
                this.cancelModal.reason = '';
                this.cancelModal.loading = false;
            },

            hideCancelModal() {
                this.cancelModal.show = false;
            },

            async confirmCancel() {
                if (!this.cancelModal.reason.trim()) {
                    this.showToast('Alasan pembatalan wajib diisi', 'error');
                    return;
                }

                this.cancelModal.loading = true;

                try {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("purchase-orders.cancel", $purchaseOrder->po_id) }}';
                    form.style.display = 'none';

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const reasonField = document.createElement('input');
                    reasonField.type = 'hidden';
                    reasonField.name = 'reason';
                    reasonField.value = this.cancelModal.reason;

                    form.appendChild(csrfToken);
                    form.appendChild(reasonField);
                    document.body.appendChild(form);

                    this.hideCancelModal();
                    form.submit();
                } catch (error) {
                    this.showToast('Terjadi kesalahan saat membatalkan PO', 'error');
                    this.cancelModal.loading = false;
                }
            },

            // Helper Functions
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

<style>
    /* Progress circle animation */
    svg circle {
        transition: stroke-dashoffset 0.3s ease-in-out;
    }

    /* Custom hover effects */
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    /* Progress bar animation */
    .progress-bar {
        transition: width 0.3s ease-in-out;
    }
</style>
@endpush
