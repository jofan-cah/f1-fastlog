@extends('layouts.app')

@section('title', 'Detail Purchase Order - LogistiK Admin')

@push('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
    <div class="space-y-6" x-data="purchaseOrderDetailManager()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <a href="{{ route('purchase-orders.index') }}"
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                        <i class="fas fa-arrow-left text-gray-600"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $purchaseOrder->po_number }}</h1>
                    @include('purchase-orders.partials.status-badge', ['purchaseOrder' => $purchaseOrder])
                </div>
                <p class="text-gray-600">
                    Created {{ $purchaseOrder->created_at->format('d M Y, H:i') }} by {{ $purchaseOrder->createdBy->name ?? 'System' }}
                </p>
            </div>

            <!-- Action Buttons -->
            @include('purchase-orders.partials.workflow-buttons', [
                'purchaseOrder' => $purchaseOrder,
                'permissions' => $permissions
            ])
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - PO Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- PO Information -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Purchase Order Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PO Number</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $purchaseOrder->po_number }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PO Date</label>
                            <p class="text-gray-900">{{ $purchaseOrder->po_date->format('d M Y') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expected Date</label>
                            <p class="text-gray-900">
                                @if($purchaseOrder->expected_date)
                                    {{ $purchaseOrder->expected_date->format('d M Y') }}
                                    @if($purchaseOrder->isOverdue())
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Overdue
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">Not set</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount</label>
                            <p class="text-xl font-bold text-green-600">
                                Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                            @if($purchaseOrder->supplier)
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-building text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $purchaseOrder->supplier->supplier_name }}</p>
                                        <p class="text-sm text-gray-600">{{ $purchaseOrder->supplier->supplier_code }}</p>
                                        @if($purchaseOrder->supplier->contact_person)
                                            <p class="text-sm text-gray-500">{{ $purchaseOrder->supplier->contact_person }}</p>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center space-x-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                    <div>
                                        <p class="text-yellow-800 font-medium">Supplier belum dipilih</p>
                                        <p class="text-yellow-700 text-sm">Akan dipilih saat proses Finance F1</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($purchaseOrder->notes)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <p class="text-gray-700 whitespace-pre-line">{{ $purchaseOrder->notes }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Information (if exists) -->
                @if($paymentStatusInfo)
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-credit-card mr-2 text-green-600"></i>
                            Payment Information
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                <p class="text-gray-900">
                                    {{ \App\Constants\PurchaseOrderConstants::getPaymentMethods()[$purchaseOrder->payment_method] ?? $purchaseOrder->payment_method }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount</label>
                                <p class="text-lg font-semibold text-green-600">
                                    Rp {{ number_format($purchaseOrder->payment_amount, 0, ',', '.') }}
                                </p>
                            </div>

                            @if($purchaseOrder->payment_due_date)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                                    <p class="text-gray-900">
                                        {{ $purchaseOrder->payment_due_date->format('d M Y') }}
                                        @if($purchaseOrder->isPaymentOverdue())
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Overdue
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            @endif
                            

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $paymentStatusInfo['class'] }}">
                                    {{ $paymentStatusInfo['text'] }}
                                </span>
                            </div>

                            @if($purchaseOrder->bank_name || $purchaseOrder->virtual_account_number)
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Banking Details</label>
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        @if($purchaseOrder->bank_name)
                                            <p class="text-sm"><strong>Bank:</strong> {{ $purchaseOrder->bank_name }}</p>
                                        @endif
                                        @if($purchaseOrder->account_number)
                                            <p class="text-sm"><strong>Account:</strong> {{ $purchaseOrder->account_number }}</p>
                                        @endif
                                        @if($purchaseOrder->account_holder)
                                            <p class="text-sm"><strong>Holder:</strong> {{ $purchaseOrder->account_holder }}</p>
                                        @endif
                                        @if($purchaseOrder->virtual_account_number)
                                            <p class="text-sm"><strong>Virtual Account:</strong> {{ $purchaseOrder->virtual_account_number }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- PO Items -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-list mr-2 text-blue-600"></i>
                            Items ({{ $purchaseOrder->poDetails->count() }})
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($purchaseOrder->poDetails as $detail)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-lg flex items-center justify-center">
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ number_format($detail->quantity_ordered) }}</div>
                                            <div class="text-xs text-gray-500">{{ $detail->item->unit }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">Rp {{ number_format($detail->total_price, 0, ',', '.') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ number_format($detail->quantity_received) }}</div>
                                            @if($detail->quantity_received > 0)
                                                <div class="text-xs text-green-600">{{ number_format(($detail->quantity_received / $detail->quantity_ordered) * 100, 1) }}%</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $progress = $detail->quantity_ordered > 0 ? ($detail->quantity_received / $detail->quantity_ordered) * 100 : 0;
                                            @endphp
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                                    <div class="bg-gradient-to-r from-blue-600 to-green-600 h-2 rounded-full"
                                                         style="width: {{ $progress }}%"></div>
                                                </div>
                                                <span class="text-xs text-gray-600">{{ number_format($progress, 0) }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total:</td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                        Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td colspan="2" class="px-6 py-4 text-sm text-gray-600">
                                        {{ $summaryInfo['completion_percentage'] }}% Complete
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column - Timeline & Summary -->
            <div class="space-y-6">
                <!-- Summary Card -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-chart-bar mr-2 text-green-600"></i>
                        Summary
                    </h3>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total Items</span>
                            <span class="font-semibold text-gray-900">{{ $summaryInfo['total_items'] }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total Quantity</span>
                            <span class="font-semibold text-gray-900">{{ number_format($summaryInfo['total_quantity']) }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Received</span>
                            <span class="font-semibold text-green-600">{{ number_format($summaryInfo['total_received']) }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Progress</span>
                            <span class="font-semibold text-blue-600">{{ $summaryInfo['completion_percentage'] }}%</span>
                        </div>

                        <div class="pt-2 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Amount</span>
                                <span class="text-lg font-bold text-green-600">
                                    Rp {{ number_format($summaryInfo['total_amount'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        @if($summaryInfo['days_until_expected'] !== null)
                            <div class="pt-2 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Expected</span>
                                    <span class="font-semibold {{ $summaryInfo['is_overdue'] ? 'text-red-600' : 'text-gray-900' }}">
                                        @if($summaryInfo['is_overdue'])
                                            {{ abs($summaryInfo['days_until_expected']) }} days overdue
                                        @elseif($summaryInfo['days_until_expected'] == 0)
                                            Today
                                        @else
                                            {{ $summaryInfo['days_until_expected'] }} days left
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Workflow Timeline -->
                @include('purchase-orders.partials.timeline', ['purchaseOrder' => $purchaseOrder])
            </div>
        </div>

        <!-- Modals -->
        @include('purchase-orders.partials.finance-f1-form', ['purchaseOrder' => $purchaseOrder, 'suppliers' => \App\Models\Supplier::active()->get()])
        @include('purchase-orders.partials.finance-f2-form', ['purchaseOrder' => $purchaseOrder])
        @include('purchase-orders.partials.rejection-form', ['purchaseOrder' => $purchaseOrder])

        <!-- Standard Modals -->
        @include('purchase-orders.partials.cancel-modal')
        @include('purchase-orders.partials.duplicate-modal')
    </div>
@endsection

@push('scripts')
    <script>
        function purchaseOrderDetailManager() {
            return {
                showFinanceF1Modal: false,
                showFinanceF2Modal: false,
                showRejectF1Modal: false,
                showRejectF2Modal: false,
                showCancelModal: false,
                showDuplicateModal: false,
            }
        }
    </script>
@endpush
