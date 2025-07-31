<div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
        <i class="fas fa-history mr-2 text-blue-600"></i>
        Workflow Timeline
    </h3>

    <div class="relative">
        <!-- Timeline Line -->
        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

        <!-- Timeline Items -->
        <div class="space-y-6">
            <!-- Created -->
            <div class="relative flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-plus text-blue-600 text-sm"></i>
                </div>
                <div class="ml-4 flex-1">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-medium text-gray-900">PO Created</h4>
                        <span class="text-xs text-gray-500">{{ $purchaseOrder->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <p class="text-sm text-gray-600">Created by {{ $purchaseOrder->createdBy->name ?? 'System' }}</p>
                    @if($purchaseOrder->notes)
                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($purchaseOrder->notes, 100) }}</p>
                    @endif
                </div>
            </div>

            <!-- Logistic Approval -->
            @if($purchaseOrder->logistic_approved_at)
                <div class="relative flex items-start">
                    <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-green-600 text-sm"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-900">Submitted to Finance F1</h4>
                            <span class="text-xs text-gray-500">{{ $purchaseOrder->logistic_approved_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="text-sm text-gray-600">Approved by {{ $purchaseOrder->logisticUser->name ?? 'Logistic' }}</p>
                    </div>
                </div>
            @endif

            <!-- Finance F1 Processing -->
            @if($purchaseOrder->finance_f1_approved_at)
                <div class="relative flex items-start">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clipboard-check text-blue-600 text-sm"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-900">Processed by Finance F1</h4>
                            <span class="text-xs text-gray-500">{{ $purchaseOrder->finance_f1_approved_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="text-sm text-gray-600">Processed by {{ $purchaseOrder->financeF1User->name ?? 'Finance F1' }}</p>
                        @if($purchaseOrder->finance_f1_notes)
                            <p class="text-xs text-gray-500 mt-1">{{ $purchaseOrder->finance_f1_notes }}</p>
                        @endif
                        @if($purchaseOrder->available_payment_options)
                            <div class="mt-2">
                                <p class="text-xs text-gray-600 font-medium">Payment Options:</p>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($purchaseOrder->available_payment_options as $option)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ \App\Constants\PurchaseOrderConstants::getPaymentMethods()[$option] ?? $option }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Finance F2 Approval -->
            @if($purchaseOrder->finance_f2_approved_at)
                <div class="relative flex items-start">
                    <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-sm"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-900">Approved by Finance F2</h4>
                            <span class="text-xs text-gray-500">{{ $purchaseOrder->finance_f2_approved_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="text-sm text-gray-600">Approved by {{ $purchaseOrder->financeF2User->name ?? 'Finance F2' }}</p>
                        @if($purchaseOrder->finance_f2_notes)
                            <p class="text-xs text-gray-500 mt-1">{{ $purchaseOrder->finance_f2_notes }}</p>
                        @endif
                        @if($purchaseOrder->payment_method)
                            <div class="mt-2">
                                <p class="text-xs text-gray-600 font-medium">Payment Details:</p>
                                <div class="space-y-1 mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        {{ \App\Constants\PurchaseOrderConstants::getPaymentMethods()[$purchaseOrder->payment_method] ?? $purchaseOrder->payment_method }}
                                    </span>
                                    @if($purchaseOrder->payment_amount)
                                        <p class="text-xs text-gray-600">Amount: Rp {{ number_format($purchaseOrder->payment_amount, 0, ',', '.') }}</p>
                                    @endif
                                    @if($purchaseOrder->payment_due_date)
                                        <p class="text-xs text-gray-600">Due: {{ $purchaseOrder->payment_due_date->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Rejections -->
            @if($purchaseOrder->rejected_at)
                <div class="relative flex items-start">
                    <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-times text-red-600 text-sm"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-900">
                                Rejected by {{ $purchaseOrder->rejected_by_level === 'F1' ? 'Finance F1' : 'Finance F2' }}
                            </h4>
                            <span class="text-xs text-gray-500">{{ $purchaseOrder->rejected_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="text-sm text-gray-600">
                            Rejected by {{
                                $purchaseOrder->rejected_by_level === 'F1' ?
                                ($purchaseOrder->financeF1User->name ?? 'Finance F1') :
                                ($purchaseOrder->financeF2User->name ?? 'Finance F2')
                            }}
                        </p>
                        @if($purchaseOrder->rejection_reason)
                            <div class="mt-2 p-2 bg-red-50 rounded-lg">
                                <p class="text-xs text-red-700 font-medium">Reason:</p>
                                <p class="text-xs text-red-600">{{ $purchaseOrder->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Current Status -->
            <div class="relative flex items-start">
                @php
                    $statusInfo = $purchaseOrder->getWorkflowStatusInfo();
                    $isCompleted = in_array($purchaseOrder->workflow_status, [
                        \App\Constants\PurchaseOrderConstants::WORKFLOW_STATUS_SENT,
                        \App\Constants\PurchaseOrderConstants::WORKFLOW_STATUS_RECEIVED,
                        \App\Constants\PurchaseOrderConstants::WORKFLOW_STATUS_CANCELLED
                    ]);
                @endphp
                <div class="flex-shrink-0 w-8 h-8 {{ $isCompleted ? 'bg-green-100' : 'bg-orange-100' }} rounded-full flex items-center justify-center border-2 border-white shadow">
                    @if($isCompleted)
                        <i class="fas fa-flag-checkered text-green-600 text-sm"></i>
                    @else
                        <i class="fas fa-clock text-orange-600 text-sm"></i>
                    @endif
                </div>
                <div class="ml-4 flex-1">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-medium text-gray-900">Current Status</h4>
                        <span class="text-xs text-gray-500">{{ now()->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="mt-1">
                        @include('purchase-orders.partials.status-badge', ['purchaseOrder' => $purchaseOrder])
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $statusInfo['description'] ?? '' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
