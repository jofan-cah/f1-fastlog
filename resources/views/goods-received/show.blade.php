@extends('layouts.app')

@section('title', 'Detail Penerimaan Barang - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="goodsReceivedDetail()">
    <!-- Breadcrumb -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-green-600">
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="{{ route('goods-received.index') }}"
                        class="text-sm font-medium text-gray-700 hover:text-green-600">
                        Penerimaan Barang
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">{{ $goodsReceived->receive_number }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-br from-green-600 to-green-700 rounded-2xl flex items-center justify-center">
                <i class="fas fa-truck text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Penerimaan Barang</h1>
                <p class="text-gray-600 mt-1">{{ $goodsReceived->receive_number }} • {{ $goodsReceived->supplier->supplier_name }}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            @if($goodsReceived->status == 'partial')
                <a href="{{ route('goods-received.edit', $goodsReceived->gr_id) }}"
                   class="px-4 py-2 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-edit"></i>
                    <span>Edit Penerimaan</span>
                </a>
            @endif

            <button @click="showPrintModal()"
                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-print"></i>
                <span>Print</span>
            </button>

            <a href="{{ route('goods-received.index') }}"
               class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium {{ $statusInfo['class'] }}">
                <span class="w-2 h-2 rounded-full mr-2 {{ $goodsReceived->status == 'complete' ? 'bg-green-400' : 'bg-yellow-400' }}"></span>
                {{ $statusInfo['text'] }}
            </span>
            <div class="text-sm text-gray-600">
                Diterima pada {{ $goodsReceived->receive_date->format('d F Y') }}
            </div>
        </div>
        <div class="text-sm text-gray-500">
            ID: {{ $goodsReceived->gr_id }}
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- GR Header Info -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-green-600"></i>
                        Informasi Penerimaan
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor GR</label>
                            <div class="text-lg font-semibold text-gray-900">{{ $goodsReceived->receive_number }}</div>
                        </div>



                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Penerimaan</label>
                            <div class="text-lg font-semibold text-gray-900">{{ $goodsReceived->receive_date->format('d F Y') }}</div>
                            <div class="text-sm text-gray-500">{{ $goodsReceived->receive_date->format('H:i') }} WIB</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Diterima Oleh</label>
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-green-600 text-sm"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $goodsReceived->receivedBy->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $goodsReceived->receivedBy->userLevel->level_name ?? 'Staff' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($goodsReceived->notes)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <div class="bg-gray-50 rounded-lg p-4 border">
                                <p class="text-gray-700">{{ $goodsReceived->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Items Details -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-boxes mr-2 text-purple-600"></i>
                            Detail Items Diterima
                        </h3>
                        <span class="text-sm text-gray-600">{{ $goodsReceived->grDetails->count() }} items</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Diterima</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alokasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Info Tambahan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($goodsReceived->grDetails as $detail)
                                @php
                                    $splitInfo = $detail->getSplitInfo();
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-box text-white text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $detail->item->item_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $detail->item->item_code }}</div>
                                                @if($detail->item->category)
                                                    <div class="text-xs text-gray-400">{{ $detail->item->category->category_name }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ number_format($detail->quantity_received) }}</div>
                                        <div class="text-xs text-gray-500">{{ $detail->item->unit }}</div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="space-y-2">
                                            <!-- Stock Allocation -->
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-gray-600">Stok:</span>
                                                <span class="text-sm font-medium text-blue-600">{{ number_format($detail->quantity_to_stock) }}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $splitInfo['stock_percentage'] }}%"></div>
                                            </div>

                                            <!-- Ready Allocation -->
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-gray-600">Siap Pakai:</span>
                                                <span class="text-sm font-medium text-green-600">{{ number_format($detail->quantity_to_ready) }}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-green-600 h-1.5 rounded-full" style="width: {{ $splitInfo['ready_percentage'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</div>
                                        <div class="text-xs text-gray-500">per {{ $detail->item->unit }}</div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">Rp {{ number_format($detail->getTotalValue(), 0, ',', '.') }}</div>
                                    </td>

                                    <td class="px-6 py-4">
                                        @if($detail->batch_number)
                                            <div class="text-xs text-gray-600">
                                                <span class="font-medium">Batch:</span> {{ $detail->batch_number }}
                                            </div>
                                        @endif
                                        @if($detail->expiry_date)
                                            <div class="text-xs text-gray-600">
                                                <span class="font-medium">Exp:</span> {{ $detail->expiry_date->format('d/m/Y') }}
                                            </div>
                                        @endif
                                        @if($detail->notes)
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ Str::limit($detail->notes, 50) }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Items Summary -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900">{{ number_format($summaryInfo['total_quantity']) }}</div>
                            <div class="text-xs text-gray-600">Total Qty</div>
                        </div>
                        {{-- <div class="text-center">
                            <div class="text-lg font-bold text-blue-600">{{ number_format($summaryInfo['total_to_stock']) ?? '' }}</div>
                            <div class="text-xs text-gray-600">Ke Stok</div>
                        </div> --}}
                        {{-- <div class="text-center">
                            <div class="text-lg font-bold text-green-600">{{ number_format($summaryInfo['total_to_ready']) }}</div>
                            <div class="text-xs text-gray-600">Siap Pakai</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-purple-600">Rp {{ number_format($summaryInfo['total_value'], 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-600">Total Nilai</div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Sidebar Info -->
        <div class="space-y-6">
            <!-- Supplier Info -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-building mr-2 text-blue-600"></i>
                        Supplier
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Nama Supplier</label>
                            <div class="text-lg font-semibold text-gray-900">{{ $goodsReceived->supplier->supplier_name }}</div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">Kode Supplier</label>
                            <div class="text-gray-900">{{ $goodsReceived->supplier->supplier_code }}</div>
                        </div>

                        @if($goodsReceived->supplier->contact_person)
                            <div>
                                <label class="text-sm font-medium text-gray-700">Contact Person</label>
                                <div class="text-gray-900">{{ $goodsReceived->supplier->contact_person }}</div>
                            </div>
                        @endif

                        @if($goodsReceived->supplier->phone)
                            <div>
                                <label class="text-sm font-medium text-gray-700">Telepon</label>
                                <div class="text-gray-900">
                                    <a href="tel:{{ $goodsReceived->supplier->phone }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $goodsReceived->supplier->phone }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if($goodsReceived->supplier->email)
                            <div>
                                <label class="text-sm font-medium text-gray-700">Email</label>
                                <div class="text-gray-900">
                                    <a href="mailto:{{ $goodsReceived->supplier->email }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $goodsReceived->supplier->email }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if($goodsReceived->supplier->address)
                            <div>
                                <label class="text-sm font-medium text-gray-700">Alamat</label>
                                <div class="text-gray-900 text-sm">{{ $goodsReceived->supplier->address }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- PO Progress Info -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-line mr-2 text-indigo-600"></i>
                        Progress PO
                    </h3>
                </div>
              <div class="p-6">
    @if($goodsReceived->isPOBased() && $goodsReceived->purchaseOrder)
        {{-- PO-based receipt --}}
        @php
            $poSummary = $goodsReceived->purchaseOrder->getSummaryInfo() ?? [];
        @endphp
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Status PO</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $goodsReceived->purchaseOrder->getStatusInfo()['class'] }}">
                    {{ $goodsReceived->purchaseOrder->getStatusInfo()['text'] }}
                </span>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">Progress Penerimaan</span>
                    <span class="text-sm font-medium">{{ $poSummary['completion_percentage'] ?? 0 }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-gradient-to-r from-blue-600 to-green-600 h-3 rounded-full transition-all duration-300"
                         style="width: {{ $poSummary['completion_percentage'] ?? 0 }}%"></div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-2">
                <div class="text-center">
                    <div class="text-lg font-bold text-gray-900">{{ number_format($poSummary['total_received'] ?? 0) }}</div>
                    <div class="text-xs text-gray-600">Diterima</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-gray-600">{{ number_format($poSummary['total_quantity'] ?? 0) }}</div>
                    <div class="text-xs text-gray-600">Total Order</div>
                </div>
            </div>
        </div>
    @else
        {{-- Direct receipt --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Jenis Penerimaan</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    Penerimaan Langsung
                </span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Status</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $goodsReceived->getStatusInfo()['class'] }}">
                    {{ $goodsReceived->getStatusInfo()['text'] }}
                </span>
            </div>

            {{-- External References --}}
            @if($goodsReceived->delivery_note_number || $goodsReceived->invoice_number || $goodsReceived->external_reference)
                <div class="pt-2 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Referensi Eksternal</h4>
                    @if($goodsReceived->delivery_note_number)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">No. Surat Jalan:</span>
                            <span class="font-medium">{{ $goodsReceived->delivery_note_number }}</span>
                        </div>
                    @endif
                    @if($goodsReceived->invoice_number)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">No. Invoice:</span>
                            <span class="font-medium">{{ $goodsReceived->invoice_number }}</span>
                        </div>
                    @endif
                    @if($goodsReceived->external_reference)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Referensi Lain:</span>
                            <span class="font-medium">{{ $goodsReceived->external_reference }}</span>
                        </div>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-200">
                <div class="text-center">
                    <div class="text-lg font-bold text-gray-900">{{ number_format($summaryInfo['total_quantity']) }}</div>
                    <div class="text-xs text-gray-600">Total Diterima</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-green-600">{{ number_format($summaryInfo['total_value'], 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-600">Total Nilai (Rp)</div>
                </div>
            </div>
        </div>
    @endif
</div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bolt mr-2 text-yellow-600"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    {{-- <button @click="showPrintModal()"
                            class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-print"></i>
                        <span>Print GR</span>
                    </button> --}}

                    <button @click="showReceiptModal()"
                            class="w-full px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-receipt"></i>
                        <span>Print Tanda Terima</span>
                    </button>

                    {{-- @if($goodsReceived->status == 'partial')
                        <a href="{{ route('goods-received.edit', $goodsReceived->gr_id) }}"
                           class="w-full px-4 py-3 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-edit"></i>
                            <span>Edit Penerimaan</span>
                        </a>
                    @endif --}}

                    {{-- <a href="{{ route('purchase-orders.show', $goodsReceived->purchaseOrder->po_id) }}"
                       class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-file-invoice"></i>
                        <span>Lihat PO</span>
                    </a> --}}

                    {{-- <a href="{{ route('goods-received.create', ['po_id' => $goodsReceived->po_id]) }}"
                       class="w-full px-4 py-3 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-xl hover:from-orange-700 hover:to-orange-800 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Penerimaan Lanjutan</span> --}}
                    </a>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-history mr-2 text-gray-600"></i>
                        Timeline
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-truck text-green-600 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">Barang Diterima</div>
                                {{-- <div class="text-xs text-gray-500">{{ $goodsReceived->created_at->format('d/m/Y H:i') }}</div> --}}
                                <div class="text-xs text-gray-400">Oleh {{ $goodsReceived->receivedBy->full_name }}</div>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-invoice text-blue-600 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">PO Dibuat</div>
                                {{-- <div class="text-xs text-gray-500">{{ $goodsReceived->purchaseOrder->created_at->format('d/m/Y H:i') }}</div> --}}
                                {{-- <div class="text-xs text-gray-400">Oleh {{ $goodsReceived->purchaseOrder->createdBy->full_name }}</div> --}}
                            </div>
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

                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Print Options</h3>
                <p class="text-gray-600 text-center mb-6">Pilih jenis dokumen yang akan dicetak</p>

                <div class="space-y-3">
                    <button type="button"
                            @click="printGR()"
                            class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-file-alt"></i>
                        <span>Print Goods Received</span>
                    </button>

                    <button type="button"
                            @click="printReceipt()"
                            class="w-full px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-receipt"></i>
                        <span>Print Tanda Terima</span>
                    </button>

                    <button type="button"
                            @click="hidePrintModal()"
                            class="w-full px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div x-show="receiptModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hideReceiptModal()"
         @keydown.escape.window="hideReceiptModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="receiptModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-receipt text-2xl text-green-600"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Cetak Tanda Terima</h3>

                <p class="text-gray-600 text-center mb-6">
                    Cetak tanda terima untuk GR {{ $goodsReceived->receive_number }}?
                    Dokumen ini akan digunakan sebagai bukti penerimaan barang.
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            @click="hideReceiptModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="button"
                            @click="confirmPrintReceipt()"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-receipt"></i>
                        <span>Cetak</span>
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
    function goodsReceivedDetail() {
        return {
            printModal: {
                show: false
            },
            receiptModal: {
                show: false
            },

            // Print Modal Functions
            showPrintModal() {
                this.printModal.show = true;
            },

            hidePrintModal() {
                this.printModal.show = false;
            },

            printGR() {
                // Open GR print page in new window
                const printUrl = `{{ route('goods-received.show', $goodsReceived->gr_id) }}/print`;
                window.open(printUrl, '_blank');
                this.hidePrintModal();
            },

            printReceipt() {
                // Open receipt print page in new window
                const receiptUrl = `{{ route('goods-received.show', $goodsReceived->gr_id) }}/receipt`;
                window.open(receiptUrl, '_blank');
                this.hidePrintModal();
            },

            // Receipt Modal Functions
            showReceiptModal() {
                this.receiptModal.show = true;
            },

            hideReceiptModal() {
                this.receiptModal.show = false;
            },

            confirmPrintReceipt() {
                // Open receipt print page in new window
                const receiptUrl = `{{ route('goods-received.show', $goodsReceived->gr_id) }}/receipt`;
                window.open(receiptUrl, '_blank');
                this.hideReceiptModal();
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

    // Additional functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + P for print
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                // Trigger Alpine.js print modal
                const alpineComponent = document.querySelector('[x-data]');
                if (alpineComponent && alpineComponent._x_dataStack) {
                    const alpineData = alpineComponent._x_dataStack[0];
                    if (alpineData && alpineData.showPrintModal) {
                        alpineData.showPrintModal();
                    }
                }
            }

            // Ctrl + E for edit (if allowed)
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                @if($goodsReceived->status == 'partial')
                    window.location.href = '{{ route("goods-received.edit", $goodsReceived->gr_id) }}';
                @endif
            }

            // Ctrl + B for back
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                window.location.href = '{{ route("goods-received.index") }}';
            }
        });

        // Auto-refresh data every 5 minutes (for real-time updates)
        setInterval(function() {
            // Check if any modals are open
            const modalsOpen = document.querySelector('[x-show="printModal.show"]') ||
                              document.querySelector('[x-show="receiptModal.show"]');

            if (!modalsOpen) {
                // Could implement AJAX refresh here
                console.log('Auto-refresh: Checking for updates...');
            }
        }, 300000); // 5 minutes

        // Initialize tooltips or additional UI enhancements
        initializeTooltips();
    });

    function initializeTooltips() {
        // Add tooltips to action buttons
        const tooltipElements = document.querySelectorAll('[title]');
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', function() {
                // Could implement custom tooltip here
            });
        });
    }

    // Print functionality for direct calls
    function printDocument(type) {
        let url;
        if (type === 'gr') {
            url = `{{ route('goods-received.show', $goodsReceived->gr_id) }}/print`;
        } else if (type === 'receipt') {
            url = `{{ route('goods-received.show', $goodsReceived->gr_id) }}/receipt`;
        }

        if (url) {
            window.open(url, '_blank');
        }
    }
</script>

<style>
    /* Custom animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeInUp {
        animation: fadeInUp 0.3s ease-out;
    }

    /* Progress bar animations */
    .progress-bar {
        transition: width 0.6s ease-in-out;
    }

    /* Card hover effects */
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    /* Status badge improvements */
    .status-pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }

    /* Table row hover effects */
    .table-hover:hover {
        background-color: rgba(59, 130, 246, 0.02);
        transition: background-color 0.2s ease;
    }

    /* Button improvements */
    .btn-gradient {
        background: linear-gradient(135deg, var(--tw-gradient-from), var(--tw-gradient-to));
        transition: all 0.3s ease;
    }

    .btn-gradient:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Modal backdrop blur */
    .modal-backdrop {
        backdrop-filter: blur(4px);
    }

    /* Custom scrollbar */
    .overflow-x-auto::-webkit-scrollbar {
        height: 8px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Print styles */
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: white !important;
        }

        .shadow-lg {
            box-shadow: none !important;
        }
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .text-2xl {
            font-size: 1.5rem;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        .md\:grid-cols-4 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    /* Loading states */
    .loading {
        position: relative;
        overflow: hidden;
    }

    .loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% {
            left: -100%;
        }
        100% {
            left: 100%;
        }
    }

    /* Focus styles for accessibility */
    .focus-visible:focus {
        outline: 2px solid #10b981;
        outline-offset: 2px;
    }

    /* Timeline improvements */
    .timeline-item {
        position: relative;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 16px;
        top: 32px;
        bottom: -16px;
        width: 2px;
        background: #e5e7eb;
    }

    .timeline-item:last-child::before {
        display: none;
    }
</style>
@endpush
