@extends('layouts.app')

@section('title', 'Detail Transaksi - LogistiK Admin')

@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
    <div class="space-y-6" x-data="transactionShow()">
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
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-sm font-medium text-gray-500">{{ $transaction->transaction_number }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                @php
                    $typeInfo = $transaction->getTypeInfo();
                    $statusInfo = $transaction->getStatusInfo();
                @endphp
                <div
                    class="w-16 h-16 bg-gradient-to-br {{ $typeInfo['gradient'] ?? 'from-blue-600 to-blue-700' }} rounded-2xl flex items-center justify-center">
                    <i class="{{ $typeInfo['icon'] }} text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $transaction->transaction_number }}</h1>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $typeInfo['class'] }}">
                            {{ $typeInfo['text'] }}
                        </span>
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusInfo['class'] }}">
                            <i class="{{ $statusInfo['icon'] }} mr-1"></i>
                            {{ $statusInfo['text'] }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                @if ($transaction->status === 'pending' && $transaction->created_by === auth()->id())
                    <a href="{{ route('transactions.edit', $transaction) }}"
                        class="px-4 py-2 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-edit"></i>
                        <span>Edit</span>
                    </a>
                @endif



                <a href="{{ route('transactions.index') }}"
                    class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Transaction Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                            Informasi Transaksi
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Transaksi</label>
                                <div class="p-3 bg-gray-50 rounded-xl border">
                                    <span
                                        class="text-sm font-mono text-gray-900">{{ $transaction->transaction_number }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Transaksi</label>
                                <div class="p-3 bg-gray-50 rounded-xl border">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $typeInfo['class'] }}">
                                        {{ $typeInfo['text'] }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <div class="p-3 bg-gray-50 rounded-xl border">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusInfo['class'] }}">
                                        <i class="{{ $statusInfo['icon'] }} mr-1"></i>
                                        {{ $statusInfo['text'] }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Transaksi</label>
                                <div class="p-3 bg-gray-50 rounded-xl border">
                                    <span
                                        class="text-sm text-gray-900">{{ $transaction->transaction_date->format('d M Y H:i') }}</span>
                                </div>
                            </div>

                            @if ($transaction->from_location)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Asal</label>
                                    <div class="p-3 bg-gray-50 rounded-xl border">
                                        <span class="text-sm text-gray-900">{{ $transaction->from_location }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($transaction->to_location)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Tujuan</label>
                                    <div class="p-3 bg-gray-50 rounded-xl border">
                                        <span class="text-sm text-gray-900">{{ $transaction->to_location }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($transaction->reference_id)
                                <div data-reference-id="{{ $transaction->reference_id }}">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference ID</label>
                                    <div class="p-3 bg-gray-50 rounded-xl border">
                                        <span
                                            class="text-sm font-mono text-gray-900">{{ $transaction->reference_id }}</span>
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Item</label>
                                <div class="p-3 bg-gray-50 rounded-xl border">
                                    <span class="text-sm font-semibold text-gray-900">{{ $transaction->quantity }}
                                        item(s)</span>
                                </div>
                            </div>

                        </div>

                        @if ($transaction->notes)
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                                <div class="p-4 bg-gray-50 rounded-xl border">
                                    <p class="text-sm text-gray-900 whitespace-pre-line">{{ $transaction->notes }}</p>
                                </div>
                            </div>
                        @endif
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
                        @if ($transaction->transactionDetails->count() > 0)
                            <div class="space-y-4">
                                @foreach ($transaction->transactionDetails as $detail)
                                    @php
                                        $itemDetail = $detail->itemDetail;
                                        $statusChange = $detail->getStatusChangeInfo();
                                        $changeImpact = $detail->getChangeImpact();
                                    @endphp
                                    <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                            <div class="flex items-center space-x-4">
                                                <div
                                                    class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                                                    <i class="fas fa-microchip text-white"></i>
                                                </div>
                                                <div>
                                                    <h4 class="font-semibold text-gray-900">
                                                        {{ $itemDetail->item->item_name ?? 'Unknown Item' }}</h4>
                                                    <p class="text-sm text-gray-500">
                                                        {{ $itemDetail->item->item_code ?? 'N/A' }}</p>
                                                    <p class="text-xs font-mono text-gray-600">SN:
                                                        {{ $itemDetail->serial_number ?? 'N/A' }}</p>
                                                    @if ($itemDetail->kondisi == 'good' || $itemDetail->kondisi === null)
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <span
                                                                class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
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
                                                </div>
                                            </div>

                                            <div class="flex flex-col md:flex-row items-start md:items-center gap-2">
                                                @if ($detail->hasStatusChanged())
                                                    <div class="flex items-center space-x-2">
                                                        <span
                                                            class="px-2 py-1 rounded-full text-xs font-medium {{ $statusChange['before']['class'] }}">
                                                            {{ $statusChange['before']['text'] }}
                                                        </span>
                                                        <i
                                                            class="{{ $changeImpact['icon'] }} {{ $changeImpact['class'] }}"></i>
                                                        <span
                                                            class="px-2 py-1 rounded-full text-xs font-medium {{ $statusChange['after']['class'] }}">
                                                            {{ $statusChange['after']['text'] }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <span
                                                        class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ $detail->status_before ?? 'No Change' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($detail->notes)
                                            <div class="mt-3 pt-3 border-t border-gray-100">
                                                <p class="text-sm text-gray-600">
                                                    <i class="fas fa-sticky-note mr-1"></i>
                                                    {{ $detail->notes }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
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

                <!-- Timeline/History Card -->
                @if ($transaction->status !== 'pending')
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-history mr-2 text-green-600"></i>
                                Timeline Transaksi
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="flow-root">
                                <ul role="list" class="-mb-8">
                                    <li>
                                        <div class="relative pb-8">
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                                aria-hidden="true"></span>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span
                                                        class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                        <i class="fas fa-plus text-white text-sm"></i>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">Transaksi dibuat oleh <span
                                                                class="font-medium text-gray-900">{{ $transaction->createdBy->full_name ?? 'Unknown' }}</span>
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        {{ $transaction->created_at->format('d M Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    @if ($transaction->approved_date)
                                        <li>
                                            <div class="relative">
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        @if ($transaction->status === 'approved')
                                                            <span
                                                                class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                                <i class="fas fa-check text-white text-sm"></i>
                                                            </span>
                                                        @else
                                                            <span
                                                                class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                                <i class="fas fa-times text-white text-sm"></i>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-500">
                                                                Transaksi
                                                                {{ $transaction->status === 'approved' ? 'disetujui' : 'ditolak' }}
                                                                oleh
                                                                <span
                                                                    class="font-medium text-gray-900">{{ $transaction->approvedBy->full_name ?? 'Unknown' }}</span>
                                                            </p>
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            {{ $transaction->approved_date->format('d M Y H:i') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column - Actions & Info -->
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
                            <div
                                class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-microchip text-white text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-900">
                                {{ $transaction->item->item_name ?? 'Multiple Items' }}</h4>
                            <p class="text-sm text-gray-500">{{ $transaction->item->item_code ?? 'Mixed Codes' }}</p>
                        </div>

                        <div class="space-y-3">
                            @if ($transaction->item)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Kategori</span>
                                    <span
                                        class="text-sm font-medium">{{ $transaction->item->category->category_name ?? 'N/A' }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Item</span>
                                <span class="text-sm font-medium">{{ $transaction->quantity }} pcs</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Dibuat</span>
                                <span class="text-sm font-medium">{{ $transaction->created_at->format('d/m/Y') }}</span>
                            </div>

                            @if ($transaction->approved_date)
                                <div class="flex justify-between items-center">
                                    <span
                                        class="text-sm text-gray-600">{{ $transaction->status === 'approved' ? 'Disetujui' : 'Ditolak' }}</span>
                                    <span
                                        class="text-sm font-medium">{{ $transaction->approved_date->format('d/m/Y') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Transaction Summary -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-calculator mr-2 text-green-600"></i>
                            Ringkasan
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">ID Transaksi</span>
                            <span class="text-sm font-mono font-medium">{{ $transaction->transaction_id }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Nomor</span>
                            <span class="text-sm font-mono font-medium">{{ $transaction->transaction_number }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Tipe</span>
                            <span class="text-sm font-medium">{{ $typeInfo['text'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Status</span>
                            <span class="text-sm font-medium">{{ $statusInfo['text'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Dibuat oleh</span>
                            <span class="text-sm font-medium">{{ $transaction->createdBy->full_name ?? 'Unknown' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="bg-blue-50 rounded-2xl border border-blue-200 p-6">
                    <h4 class="text-lg font-semibold text-blue-900 mb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Info
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Status pending menunggu approval</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Approved otomatis update stock</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Rejected transaksi dibatalkan</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-blue-600 mr-2 mt-0.5 text-xs"></i>
                            <span>Edit hanya untuk status pending</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Approval Modal -->
        <div x-show="showApprovalModal" x-cloak @keydown.escape.window="showApprovalModal = false"
            class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showApprovalModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true" @click="showApprovalModal = false"></div>

                <div x-show="showApprovalModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="{{ route('approvals.approve', $transaction) }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-check text-green-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Approve Transaksi
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Yakin ingin approve transaksi
                                            <strong>{{ $transaction->transaction_number }}</strong>?
                                            Stock akan otomatis terupdate setelah approval.
                                        </p>
                                        <div class="mt-4">
                                            <label for="approval_notes"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                Catatan Approval (Opsional)
                                            </label>
                                            <textarea id="approval_notes" name="notes" rows="3"
                                                class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                placeholder="Tambahkan catatan approval..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <i class="fas fa-check mr-2"></i>
                                Approve
                            </button>
                            <button type="button" @click="showApprovalModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Rejection Modal -->
        <div x-show="showRejectionModal" x-cloak @keydown.escape.window="showRejectionModal = false"
            class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showRejectionModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true" @click="showRejectionModal = false"></div>

                <div x-show="showRejectionModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="{{ route('approvals.reject', $transaction) }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-times text-red-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Reject Transaksi
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Yakin ingin reject transaksi
                                            <strong>{{ $transaction->transaction_number }}</strong>?
                                            Transaksi akan dibatalkan dan tidak dapat diubah lagi.
                                        </p>
                                        <div class="mt-4">
                                            <label for="rejection_reason"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                Alasan Rejection <span class="text-red-500">*</span>
                                            </label>
                                            <textarea id="rejection_reason" name="reason" rows="3"
                                                class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                                placeholder="Jelaskan alasan rejection..." required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <i class="fas fa-times mr-2"></i>
                                Reject
                            </button>
                            <button type="button" @click="showRejectionModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
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
@endsection

@push('scripts')
    <script>
        function transactionShow() {
            return {
                showApprovalModal: false,
                showRejectionModal: false,

                init() {
                    console.log('Initializing transaction show page');

                    // Add CSRF token
                    const token = document.querySelector('meta[name="csrf-token"]');
                    if (token) {
                        window.csrfToken = token.getAttribute('content');
                    }
                },

                async quickApprove() {
                    if (!confirm('Yakin ingin quick approve transaksi ini?')) {
                        return;
                    }

                    try {
                        const response = await fetch(`{{ route('approvals.approve', $transaction) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                notes: 'Quick approval from show page'
                            })
                        });


                        if (response.ok && data.success) {
                            this.showToast('Transaksi berhasil di-approve!', 'success');

                            // Reload page after short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            throw new Error(data.message || 'Gagal approve transaksi');
                        }
                    } catch (error) {
                        console.error('Error approving transaction:', error);
                        this.showToast(error.message || 'Terjadi kesalahan saat approve', 'error');
                    }
                },

                async cancelTransaction() {
                    if (!confirm('Yakin ingin membatalkan transaksi ini? Tindakan ini tidak dapat dibatalkan.')) {
                        return;
                    }

                    try {
                        const response = await fetch(`{{ route('transactions.cancel', $transaction) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken,
                                'Accept': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.showToast('Transaksi berhasil dibatalkan!', 'success');

                            // Redirect to transactions index after short delay
                            setTimeout(() => {
                                window.location.href = '{{ route('transactions.index') }}';
                            }, 1500);
                        } else {
                            throw new Error(data.message || 'Gagal membatalkan transaksi');
                        }
                    } catch (error) {
                        console.error('Error canceling transaction:', error);
                        this.showToast(error.message || 'Terjadi kesalahan saat membatalkan', 'error');
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
        // Config API URL
        // Config API URL
        const API_BASE_URL = 'https://befast.fiberone.net.id';

        // Auto search ketika page load
        document.addEventListener('DOMContentLoaded', function() {
            // Cari semua element yang ada reference_id
            const referenceElements = document.querySelectorAll('[data-reference-id]');

            referenceElements.forEach(element => {
                const referenceId = element.getAttribute('data-reference-id');
                if (referenceId) {
                    searchAndReplaceReferenceId(element, referenceId);
                }
            });
        });

        async function searchAndReplaceReferenceId(element, referenceId) {
            try {
                const response = await fetch(`${API_BASE_URL}/api/ticket/find/${referenceId}`);
                const result = await response.json();

                if (result.success) {
                    const ticket = result.data;

                    // Replace isi element dengan data dari API
                    element.innerHTML = `
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference ID</label>
                <div class="p-3 bg-gray-50 rounded-xl border space-y-2">
                    <div class="text-sm">
                        <span class="font-medium">Jenis Tiket:</span>
                        <span class="font-mono text-gray-900">${ticket.jenis_tiket}</span>
                    </div>
                    <div class="text-sm">
                        <span class="font-medium">ID Pelanggan:</span>
                        <span class="font-mono text-gray-900">${ticket.subscription_id}</span>
                    </div>
                    <div class="text-sm">
                        <span class="font-medium">Nama Pelanggan:</span>
                        <span class="font-mono text-gray-900">${ticket.nama_pelanggan}</span>
                    </div>
                </div>
            `;
                }
                // Kalau gagal, biarkan tetap tampil reference_id asli
            } catch (error) {
                console.error('Error fetching ticket data:', error);
                // Kalau error, biarkan tetap tampil reference_id asli
            }
        }


        // Keyboard shortcuts
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('keydown', function(e) {
                // Ctrl + A for approve (if pending and user can approve)
                if (e.ctrlKey && e.key === 'a' && '{{ $transaction->status }}' === 'pending' &&
                    {{ \App\Models\Transaction::canUserApprove() ? 'true' : 'false' }}) {
                    e.preventDefault();
                    const transactionShow = window.Alpine.findClosest(document.body, x => x.__x_$data
                        ?.showApprovalModal !== undefined)?.__x_$data;
                    if (transactionShow) {
                        transactionShow.showApprovalModal = true;
                    }
                }

                // Ctrl + E for edit (if pending and user is creator)
                if (e.ctrlKey && e.key === 'e' && '{{ $transaction->status }}' === 'pending' &&
                    {{ $transaction->created_by === auth()->id() ? 'true' : 'false' }}) {
                    e.preventDefault();
                    window.location.href = '{{ route('transactions.edit', $transaction) }}';
                }
            });
        });
    </script>
@endpush
