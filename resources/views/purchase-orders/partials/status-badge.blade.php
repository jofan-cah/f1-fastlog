@php
    $workflowStatusInfo = $purchaseOrder->getWorkflowStatusInfo();
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $workflowStatusInfo['class'] }}">
    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $workflowStatusInfo['badge_class'] }}"></span>
    {{ $workflowStatusInfo['text'] }}
</span>

@if($purchaseOrder->isPaymentOverdue())
    <div class="mt-1">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
            <i class="fas fa-credit-card mr-1"></i>
            Payment Overdue
        </span>
    </div>
@endif
