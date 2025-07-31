@extends('layouts.app')

@section('title', 'Purchase Order - LogistiK Admin')

@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
    <div class="space-y-6" x-data="purchaseOrderManager()">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Purchase Order</h1>
                <p class="text-gray-600 mt-1">Kelola purchase order dan workflow approval</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                @if(Auth::user()->user_level_id === 'LVL002' || Auth::user()->user_level_id === 'LVL001')
                    <a href="{{ route('purchase-orders.create', ['low_stock' => true]) }}"
                        class="px-4 py-2 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>PO Stok Rendah</span>
                    </a>
                    <a href="{{ route('purchase-orders.create') }}"
                        class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-plus"></i>
                        <span>Buat PO Baru</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Workflow Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-9 gap-4">
            <!-- Total PO -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-file-invoice text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total PO</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($statistics['total']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Draft Logistic -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-gray-600 to-gray-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-edit text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Draft</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['draft_logistic'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending Finance F1 -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-600 to-orange-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-clock text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending F1</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['pending_finance_f1'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending Finance F2 -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-hourglass-half text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending F2</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['pending_finance_f2'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Approved -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-600 to-green-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Approved</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['approved'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Rejected -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-times-circle text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Rejected</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['rejected'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Sent -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-paper-plane text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Sent</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['sent'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Received -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-teal-600 to-teal-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-box-open text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Received</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['received'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Overdue -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Overdue</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['overdue'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Overdue Alert -->
        @if ($statistics['payment_overdue'] > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6">
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-credit-card text-yellow-600"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-semibold text-yellow-900 mb-2">
                            Payment Overdue ({{ $statistics['payment_overdue'] }})
                        </h3>
                        <p class="text-yellow-800">Ada {{ $statistics['payment_overdue'] }} PO dengan pembayaran yang terlambat. Segera lakukan pengecekan.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Overdue POs Alert -->
        @if ($overduePOs->count() > 0)
            <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-semibold text-red-900 mb-2">
                            PO Terlambat ({{ $overduePOs->count() }})
                        </h3>
                        <div class="space-y-2">
                            @foreach ($overduePOs as $overduePO)
                                <div class="flex items-center justify-between bg-white rounded-lg p-3">
                                    <div class="flex items-center space-x-3">
                                        <span class="font-medium text-gray-900">{{ $overduePO->po_number }}</span>
                                        <span class="text-gray-600">{{ $overduePO->supplier->supplier_name ?? 'No Supplier' }}</span>
                                        @include('purchase-orders.partials.status-badge', ['purchaseOrder' => $overduePO])
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="text-sm text-red-600">
                                            Terlambat {{ abs($overduePO->getDaysUntilExpected()) }} hari
                                        </span>
                                        <a href="{{ route('purchase-orders.show', $overduePO->po_id) }}"
                                            class="text-blue-600 hover:text-blue-800 text-sm">
                                            Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filters Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <form method="GET" action="{{ route('purchase-orders.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari nomor PO atau nama supplier..."
                                class="pl-10 pr-4 py-3 w-full bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Supplier Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                        <select name="supplier_id"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                            <option value="">Semua Supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->supplier_id }}"
                                    {{ request('supplier_id') == $supplier->supplier_id ? 'selected' : '' }}>
                                    {{ $supplier->supplier_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Workflow Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Workflow</label>
                        <select name="workflow_status"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                            <option value="">Semua Status</option>
                            @foreach ($workflowStatuses as $key => $status)
                                <option value="{{ $key }}" {{ request('workflow_status') == $key ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter Tanggal</label>
                        <select onchange="showDateInputs(this.value)"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                            <option value="">Semua Tanggal</option>
                            <option value="today">Hari Ini</option>
                            <option value="week">7 Hari Terakhir</option>
                            <option value="month">30 Hari Terakhir</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                </div>

                <!-- Custom Date Range (Hidden by default) -->
                <div id="customDateRange" class="grid grid-cols-1 md:grid-cols-2 gap-4" style="display: none;">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit"
                        class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-filter"></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('purchase-orders.index') }}"
                        class="px-6 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Reset</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Purchase Orders Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-file-invoice mr-2 text-blue-600"></i>
                        Daftar Purchase Order
                    </h3>
                    <span class="text-sm text-gray-600">Total: {{ $purchaseOrders->total() }} PO</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('purchase-orders.index', array_merge(request()->query(), ['sort' => 'po_number', 'direction' => $sortField == 'po_number' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>PO Number</span>
                                    @if ($sortField == 'po_number')
                                        <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }} text-red-500"></i>
                                    @else
                                        <i class="fas fa-sort text-gray-400"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Supplier
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('purchase-orders.index', array_merge(request()->query(), ['sort' => 'po_date', 'direction' => $sortField == 'po_date' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Tanggal</span>
                                    @if ($sortField == 'po_date')
                                        <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }} text-red-500"></i>
                                    @else
                                        <i class="fas fa-sort text-gray-400"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('purchase-orders.index', array_merge(request()->query(), ['sort' => 'total_amount', 'direction' => $sortField == 'total_amount' && $sortDirection == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Total</span>
                                    @if ($sortField == 'total_amount')
                                        <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }} text-red-500"></i>
                                    @else
                                        <i class="fas fa-sort text-gray-400"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Workflow Status
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Progress
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($purchaseOrders as $po)
                            @php
                                $summaryInfo = $po->getSummaryInfo();
                                $workflowStatusInfo = $po->getWorkflowStatusInfo();
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors duration-200 {{ $po->isOverdue() ? 'bg-red-25 border-l-4 border-red-500' : '' }}">
                                <!-- PO Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-file-invoice text-white text-lg"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $po->po_number }}</div>
                                            <div class="text-sm text-gray-500">{{ $po->po_details_count }} items</div>
                                            @if ($po->isOverdue())
                                                <div class="text-xs text-red-600 mt-1">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    Terlambat {{ abs($po->getDaysUntilExpected()) }} hari
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Supplier -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($po->supplier)
                                        <div class="text-sm text-gray-900">{{ $po->supplier->supplier_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $po->supplier->supplier_code }}</div>
                                    @else
                                        <div class="text-sm text-gray-400 italic">Belum dipilih</div>
                                        <div class="text-xs text-gray-400">Akan dipilih di Finance F1</div>
                                    @endif
                                </td>

                                <!-- Dates -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $po->po_date->format('d/m/Y') }}</div>
                                    @if ($po->expected_date)
                                        <div class="text-sm text-gray-500">
                                            Target: {{ $po->expected_date->format('d/m/Y') }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Total Amount -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $summaryInfo['total_quantity'] }} qty
                                    </div>
                                </td>

                                <!-- Workflow Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @include('purchase-orders.partials.status-badge', ['purchaseOrder' => $po])
                                </td>

                                <!-- Progress -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-gradient-to-r from-blue-600 to-green-600 h-2 rounded-full"
                                                style="width: {{ $summaryInfo['completion_percentage'] }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600">{{ $summaryInfo['completion_percentage'] }}%</span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $summaryInfo['total_received'] }}/{{ $summaryInfo['total_quantity'] }} diterima
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('purchase-orders.show', $po->po_id) }}"
                                            class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                            title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if ($po->canBeEditedByLogistic() && Auth::user()->user_level_id === 'LVL002')
                                            <a href="{{ route('purchase-orders.edit', $po->po_id) }}"
                                                class="text-yellow-600 hover:text-yellow-900 p-2 hover:bg-yellow-50 rounded-lg transition-all duration-200"
                                                title="Edit PO">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        <button
                                            @click="showPrintModal('{{ $po->po_id }}', '{{ addslashes($po->po_number) }}')"
                                            class="text-purple-600 hover:text-purple-900 p-2 hover:bg-purple-50 rounded-lg transition-all duration-200"
                                            title="Print PO">
                                            <i class="fas fa-print"></i>
                                        </button>

                                        <button
                                            @click="showDuplicateModal('{{ $po->po_id }}', '{{ addslashes($po->po_number) }}')"
                                            class="text-green-600 hover:text-green-900 p-2 hover:bg-green-50 rounded-lg transition-all duration-200"
                                            title="Duplikasi PO">
                                            <i class="fas fa-copy"></i>
                                        </button>

                                        @if ($po->canBeCancelled() && Auth::user()->user_level_id === 'LVL001')
                                            <button
                                                @click="showCancelModal('{{ $po->po_id }}', '{{ addslashes($po->po_number) }}')"
                                                class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded-lg transition-all duration-200"
                                                title="Batalkan PO">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-file-invoice text-4xl text-gray-300 mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada Purchase Order</h3>
                                        <p class="text-gray-500 mb-4">
                                            @if(Auth::user()->user_level_id === 'LVL002')
                                                Belum ada PO yang dibuat. Mulai dengan membuat PO baru.
                                            @elseif(Auth::user()->user_level_id === 'LVL004')
                                                Tidak ada PO yang menunggu persetujuan Finance F1.
                                            @elseif(Auth::user()->user_level_id === 'LVL005')
                                                Tidak ada PO yang menunggu persetujuan Finance F2.
                                            @else
                                                Tidak ada PO dalam sistem saat ini.
                                            @endif
                                        </p>
                                        {{-- @if(Auth::user()->user_level_id === 'LVL002' ||  Auth::user()->user_level_id === 'LVL001') --}}
                                            <a href="{{ route('purchase-orders.create') }}"
                                                class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200">
                                                Buat PO Pertama
                                            </a>
                                        {{-- @endif --}}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if ($purchaseOrders->hasPages())
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ $purchaseOrders->firstItem() }} sampai {{ $purchaseOrders->lastItem() }}
                        dari {{ $purchaseOrders->total() }} hasil
                    </div>
                    <div class="flex items-center space-x-2">
                        {{ $purchaseOrders->appends(request()->query())->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Print Modal -->
        <div x-show="printModal.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.self="hidePrintModal()" @keydown.escape.window="hidePrintModal()"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="printModal.show" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
                <div class="p-6">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-print text-2xl text-purple-600"></i>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Print Purchase Order</h3>

                    <p class="text-gray-600 text-center mb-6">
                        Cetak PO <span x-text="printModal.poNumber" class="font-semibold text-gray-900"></span>?
                        Pastikan printer sudah siap dan terhubung.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" @click="hidePrintModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </button>
                        <button type="button" @click="confirmPrint()"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                            <i class="fas fa-print"></i>
                            <span>Print</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Duplicate Modal -->
        <div x-show="duplicateModal.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.self="hideDuplicateModal()"
            @keydown.escape.window="hideDuplicateModal()"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="duplicateModal.show" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
                <div class="p-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-copy text-2xl text-green-600"></i>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Duplikasi Purchase Order</h3>

                    <p class="text-gray-600 text-center mb-6">
                        Buat duplikasi dari PO <span x-text="duplicateModal.poNumber"
                            class="font-semibold text-gray-900"></span>?
                        PO baru akan dibuat dengan status draft dan dapat diedit.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" @click="hideDuplicateModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </button>
                        <button type="button" @click="confirmDuplicate()" :disabled="duplicateModal.loading"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl disabled:opacity-50">
                            <i class="fas fa-copy" :class="{ 'animate-spin fa-spinner': duplicateModal.loading }"></i>
                            <span x-text="duplicateModal.loading ? 'Menduplikasi...' : 'Duplikasi'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div x-show="cancelModal.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.self="hideCancelModal()" @keydown.escape.window="hideCancelModal()"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="cancelModal.show" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
                <div class="p-6">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-times text-2xl text-red-600"></i>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Batalkan Purchase Order</h3>

                    <p class="text-gray-600 text-center mb-4">
                        Apakah Anda yakin ingin membatalkan PO <span x-text="cancelModal.poNumber"
                            class="font-semibold text-gray-900"></span>?
                    </p>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Pembatalan</label>
                        <textarea x-model="cancelModal.reason" placeholder="Berikan alasan pembatalan..."
                            class="w-full p-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            rows="3"></textarea>
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
                        <button type="button" @click="hideCancelModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-arrow-left"></i>
                            <span>Kembali</span>
                        </button>
                        <button type="button" @click="confirmCancel()" :disabled="cancelModal.loading"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl disabled:opacity-50">
                            <i class="fas fa-times" :class="{ 'animate-spin fa-spinner': cancelModal.loading }"></i>
                            <span x-text="cancelModal.loading ? 'Membatalkan...' : 'Batalkan PO'"></span>
                        </button>
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
        function purchaseOrderManager() {
            return {
                printModal: {
                    show: false,
                    poId: '',
                    poNumber: ''
                },
                duplicateModal: {
                    show: false,
                    poId: '',
                    poNumber: '',
                    loading: false
                },
                cancelModal: {
                    show: false,
                    poId: '',
                    poNumber: '',
                    reason: '',
                    loading: false
                },

                // Print Modal Functions
                showPrintModal(poId, poNumber) {
                    this.printModal = {
                        show: true,
                        poId: poId,
                        poNumber: poNumber
                    };
                },

                hidePrintModal() {
                    this.printModal.show = false;
                    setTimeout(() => {
                        this.printModal = {
                            show: false,
                            poId: '',
                            poNumber: ''
                        };
                    }, 300);
                },

                confirmPrint() {
                    const printUrl = `{{ route('purchase-orders.index') }}/${this.printModal.poId}/print`;
                    window.open(printUrl, '_blank');
                    this.hidePrintModal();
                },

                // Duplicate Modal Functions
                showDuplicateModal(poId, poNumber) {
                    this.duplicateModal = {
                        show: true,
                        poId: poId,
                        poNumber: poNumber,
                        loading: false
                    };
                },

                hideDuplicateModal() {
                    this.duplicateModal.show = false;
                    setTimeout(() => {
                        this.duplicateModal = {
                            show: false,
                            poId: '',
                            poNumber: '',
                            loading: false
                        };
                    }, 300);
                },

                async confirmDuplicate() {
                    this.duplicateModal.loading = true;

                    try {
                        const response = await fetch(
                            `{{ route('purchase-orders.index') }}/${this.duplicateModal.poId}/duplicate`, {
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

                // Cancel Modal Functions
                showCancelModal(poId, poNumber) {
                    this.cancelModal = {
                        show: true,
                        poId: poId,
                        poNumber: poNumber,
                        reason: '',
                        loading: false
                    };
                },

                hideCancelModal() {
                    this.cancelModal.show = false;
                    setTimeout(() => {
                        this.cancelModal = {
                            show: false,
                            poId: '',
                            poNumber: '',
                            reason: '',
                            loading: false
                        };
                    }, 300);
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
                        form.action = `{{ route('purchase-orders.index') }}/${this.cancelModal.poId}/cancel`;
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

        // Custom date range toggle
        function showDateInputs(value) {
            const customDateRange = document.getElementById('customDateRange');
            const startDateInput = document.querySelector('input[name="start_date"]');
            const endDateInput = document.querySelector('input[name="end_date"]');

            if (value === 'custom') {
                customDateRange.style.display = 'grid';
            } else {
                customDateRange.style.display = 'none';

                const today = new Date();
                let startDate = new Date();

                switch (value) {
                    case 'today':
                        startDate = today;
                        break;
                    case 'week':
                        startDate.setDate(today.getDate() - 7);
                        break;
                    case 'month':
                        startDate.setDate(today.getDate() - 30);
                        break;
                    default:
                        startDateInput.value = '';
                        endDateInput.value = '';
                        return;
                }

                startDateInput.value = startDate.toISOString().split('T')[0];
                endDateInput.value = today.toISOString().split('T')[0];
            }
        }
    </script>

    <style>
        .bg-red-25 {
            background-color: rgba(254, 242, 242, 0.3);
        }

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

        .progress-bar {
            transition: width 0.3s ease-in-out;
        }

        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush
