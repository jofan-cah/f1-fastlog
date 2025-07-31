@php
    $userLevel = Auth::user()->user_level_id ?? null;
@endphp

<div class="flex flex-wrap gap-3">
    {{-- Logistic Actions --}}
    @if($permissions['can_edit_logistic'])
        <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}"
            class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
            <i class="fas fa-edit"></i>
            <span>Edit PO</span>
        </a>

        <form method="POST" action="{{ route('purchase-orders.submit-to-finance-f1', $purchaseOrder) }}" class="inline">
            @csrf
            <button type="submit"
                onclick="return confirm('Apakah Anda yakin ingin mengirim PO ini ke Finance F1 untuk review?')"
                class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-paper-plane"></i>
                <span>Submit ke Finance F1</span>
            </button>
        </form>
    @endif

    {{-- Finance F1 Actions --}}
    @if($permissions['can_process_f1'])
        <button @click="showFinanceF1Modal = true"
            class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
            <i class="fas fa-check"></i>
            <span>Process F1</span>
        </button>
    @endif

    @if($permissions['can_reject_f1'])
        <button @click="showRejectF1Modal = true"
            class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center space-x-2">
            <i class="fas fa-times"></i>
            <span>Reject F1</span>
        </button>
    @endif

    {{-- Finance F2 Actions --}}
    @if($permissions['can_process_f2'])
        <button @click="showFinanceF2Modal = true"
            class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2">
            <i class="fas fa-check-circle"></i>
            <span>Approve F2</span>
        </button>
    @endif

    @if($permissions['can_reject_f2'])
        <button @click="showRejectF2Modal = true"
            class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center space-x-2">
            <i class="fas fa-times"></i>
            <span>Reject F2</span>
        </button>
    @endif

    {{-- Admin Actions --}}
    @if($permissions['can_return_from_reject'])
        <form method="POST" action="{{ route('purchase-orders.return-from-reject', $purchaseOrder) }}" class="inline">
            @csrf
            <button type="submit"
                onclick="return confirm('Apakah Anda yakin ingin mengembalikan PO ini ke status Draft Logistic?')"
                class="px-4 py-2 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-undo"></i>
                <span>Return to Draft</span>
            </button>
        </form>
    @endif

    @if($permissions['can_cancel'])
        <button @click="showCancelModal = true"
            class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2">
            <i class="fas fa-ban"></i>
            <span>Cancel PO</span>
        </button>
    @endif

    {{-- General Actions --}}
    <a href="{{ route('purchase-orders.print', $purchaseOrder) }}" target="_blank"
        class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center space-x-2">
        <i class="fas fa-print"></i>
        <span>Print</span>
    </a>

    <button @click="showDuplicateModal = true"
        class="px-4 py-2 bg-gradient-to-r from-teal-600 to-teal-700 text-white rounded-xl hover:from-teal-700 hover:to-teal-800 transition-all duration-200 flex items-center space-x-2">
        <i class="fas fa-copy"></i>
        <span>Duplicate</span>
    </button>
</div>
