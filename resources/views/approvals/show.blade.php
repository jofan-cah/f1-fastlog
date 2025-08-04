@extends('layouts.app')

@section('title', 'Transaction Detail - LogistiK Admin')

@section('content')
    <div x-data="transactionDetail()" class="space-y-6">

        <!-- Header Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('approvals.index') }}" class="text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-700 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-file-invoice text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $transaction->transaction_number }}</h1>
                        <p class="text-gray-600 mt-1">Transaction Detail & Approval</p>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="flex items-center space-x-3">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    {{ $transaction->status === 'pending'
                        ? 'bg-yellow-100 text-yellow-800'
                        : ($transaction->status === 'approved'
                            ? 'bg-green-100 text-green-800'
                            : 'bg-red-100 text-red-800') }}">
                        <i
                            class="fas {{ $transaction->status === 'pending'
                                ? 'fa-clock'
                                : ($transaction->status === 'approved'
                                    ? 'fa-check-circle'
                                    : 'fa-times-circle') }} mr-2"></i>
                        {{ ucfirst($transaction->status) }}
                    </span>

                    @if ($transaction->status === 'pending')
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                            <i class="fas fa-hourglass-half mr-2"></i>
                            Awaiting Approval
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Transaction Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Basic Information
                    </h3>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Type:</span>
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        {{ $transaction->transaction_type === 'OUT'
                            ? 'bg-blue-100 text-blue-800'
                            : ($transaction->transaction_type === 'IN'
                                ? 'bg-green-100 text-green-800'
                                : 'bg-yellow-100 text-yellow-800') }}">
                            <i
                                class="fas {{ $transaction->transaction_type === 'OUT'
                                    ? 'fa-arrow-up'
                                    : ($transaction->transaction_type === 'IN'
                                        ? 'fa-arrow-down'
                                        : 'fa-wrench') }} mr-1"></i>
                            {{ $transaction->transaction_type }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Date:</span>
                        <span
                            class="text-sm font-medium text-gray-900">{{ $transaction->transaction_date->format('d M Y, H:i') }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Created by:</span>
                        <span
                            class="text-sm font-medium text-gray-900">{{ $transaction->createdBy->full_name ?? 'Unknown' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Created:</span>
                        <span class="text-sm text-gray-600">{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Item Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-cube text-green-600 mr-2"></i>
                        Item Information
                    </h3>
                </div>

                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600">Item Name:</span>
                        <p class="text-sm font-medium text-gray-900">{{ $transaction->item->item_name ?? 'Unknown Item' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-sm text-gray-600">Item Code:</span>
                        <p class="text-sm font-medium text-gray-900">{{ $transaction->item->item_code ?? 'N/A' }}</p>
                    </div>

                    {{-- <div>
                    <span class="text-sm text-gray-600">Category:</span>
                    <p class="text-sm text-gray-600">{{ $transaction->item->category ?? 'N/A' }}</p>
                </div> --}}

                    <div>
                        <span class="text-sm text-gray-600">Current Stock:</span>
                        <div class="flex items-center space-x-2 mt-1">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Available: {{ $transaction->item->stock->quantity_available ?? 0 }}
                            </span>
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Used: {{ $transaction->item->stock->quantity_used ?? 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-map-marker-alt text-purple-600 mr-2"></i>
                        Location Information
                    </h3>
                </div>

                <div class="space-y-3">
                    @if ($transaction->from_location)
                        <div>
                            <span class="text-sm text-gray-600">From Location:</span>
                            <p class="text-sm font-medium text-gray-900">{{ $transaction->from_location }}</p>
                        </div>
                    @endif

                    @if ($transaction->to_location)
                        <div>
                            <span class="text-sm text-gray-600">To Location:</span>
                            <p class="text-sm font-medium text-gray-900">{{ $transaction->to_location }}</p>
                        </div>
                    @endif

                    @if ($transaction->reference_id)
                        <div data-reference-id="{{ $transaction->reference_id }}">
                            <span class="text-sm text-gray-600">Reference ID:</span>
                            <p class="text-sm font-medium text-gray-900">{{ $transaction->reference_id }}</p>
                        </div>
                    @endif

                    @if (!$transaction->from_location && !$transaction->to_location && !$transaction->reference_id)
                        <p class="text-sm text-gray-500 italic">No location information available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list-ul text-indigo-600 mr-2"></i>
                    Transaction Details
                </h3>
            </div>

            <div class="p-6">
                @if ($transaction->transactionDetails->count() > 0)
                    <div class="space-y-4">
                        @foreach ($transaction->transactionDetails as $detail)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-barcode text-blue-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-medium text-gray-900">
                                                    {{ $detail->itemDetail->serial_number ?? 'No Serial Number' }}</h4>
                                                <p class="text-sm text-gray-600">
                                                    {{ $detail->itemDetail->item->item_name ?? 'Unknown Item' }}</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                                            <div>
                                                <span class="text-xs text-gray-500">Current Status:</span>
                                                <div class="mt-1">
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    {{ $detail->status_before === 'available'
                                                        ? 'bg-green-100 text-green-800'
                                                        : ($detail->status_before === 'used'
                                                            ? 'bg-blue-100 text-blue-800'
                                                            : 'bg-yellow-100 text-yellow-800') }}">
                                                        {{ ucfirst($detail->status_before) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div>
                                                <span class="text-xs text-gray-500">Expected Status:</span>
                                                <div class="mt-1">
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                   ">
                                                        {{-- {{ ucfirst($detail->getExpectedStatusAfter()) }} --}}
                                                    </span>
                                                </div>
                                            </div>

                                            <div>
                                                <span class="text-xs text-gray-500">Location:</span>
                                                <p class="text-sm text-gray-700 mt-1">
                                                    {{ $detail->itemDetail->location ?? 'Unknown' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center">
                                        <i class="fas fa-arrow-right text-gray-400 text-lg"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500">No transaction details available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notes Section -->
        @if ($transaction->notes)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>
                    Notes
                </h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $transaction->notes }}</p>
                </div>
            </div>
        @endif

        <!-- Approval Actions -->
        @if ($transaction->status === 'pending')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-clipboard-check text-green-600 mr-2"></i>
                    Approval Actions
                </h3>

                <div class="flex flex-col sm:flex-row gap-4">
                    <button @click="approveTransaction()" :disabled="processing"
                        class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2">
                        <i :class="processing ? 'fas fa-spinner fa-spin' : 'fas fa-check'" class="text-sm"></i>
                        <span x-show="!processing">Approve Transaction</span>
                        <span x-show="processing">Processing...</span>
                    </button>
                    <button @click="editTransaction('{{ $transaction->transaction_id }}')"
                        class="flex-1 px-6 py-3 bg-yellow-400 hover:bg-yellow-500 text-white rounded-lg font-medium transition-colors flex items-center justify-center space-x-2">
                        <i class="fas fa-edit text-sm"></i>
                        <span>Edit </span>
                    </button>


                    <button @click="showRejectModal = true" :disabled="processing"
                        class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2">
                        <i class="fas fa-times text-sm"></i>
                        <span>Reject Transaction</span>
                    </button>
                </div>

                <!-- Approval Notes -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Approval Notes (Optional)</label>
                    <textarea x-model="approvalNotes" rows="3" placeholder="Add any notes about your approval decision..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>
            </div>
        @endif

        <!-- Approval History -->
        @if ($transaction->status !== 'pending')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-history text-gray-600 mr-2"></i>
                    Approval History
                </h3>

                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center
                        {{ $transaction->status === 'approved' ? 'bg-green-100' : 'bg-red-100' }}">
                            <i
                                class="fas {{ $transaction->status === 'approved' ? 'fa-check text-green-600' : 'fa-times text-red-600' }}"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span
                                    class="font-medium text-gray-900">{{ $transaction->approvedBy->full_name ?? 'Unknown' }}</span>
                                <span
                                    class="text-sm text-gray-500">{{ $transaction->status === 'approved' ? 'approved' : 'rejected' }}
                                    this transaction</span>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                {{ $transaction->approved_date ? $transaction->approved_date->format('d M Y, H:i') : 'Unknown date' }}
                            </div>
                            @if ($transaction->approval_notes)
                                <div class="mt-2 bg-gray-50 rounded-lg p-3">
                                    <p class="text-sm text-gray-700">{{ $transaction->approval_notes }}</p>
                                </div>
                            @endif
                            @if ($transaction->rejection_reason)
                                <div class="mt-2 bg-red-50 rounded-lg p-3">
                                    <p class="text-sm text-red-700">{{ $transaction->rejection_reason }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Reject Modal -->
        <div x-show="showRejectModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90" class="fixed inset-0 z-50 overflow-y-auto">

            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" @click="showRejectModal = false"></div>
                </div>

                <div
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Reject Transaction
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        You are about to reject transaction
                                        <strong>{{ $transaction->transaction_number }}</strong>.
                                        Please provide a reason for rejection:
                                    </p>
                                    <textarea x-model="rejectReason" rows="4" placeholder="Enter rejection reason..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="rejectTransaction()" :disabled="!rejectReason.trim() || processing"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <i :class="processing ? 'fas fa-spinner fa-spin' : 'fas fa-times'" class="mr-2"></i>
                            <span x-show="!processing">Reject</span>
                            <span x-show="processing">Processing...</span>
                        </button>

                        <button @click="showRejectModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notifications -->
        <div x-show="notification.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-full"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-full" class="fixed top-4 right-4 z-50">
            <div :class="{
                'bg-green-500': notification.type === 'success',
                'bg-red-500': notification.type === 'error',
                'bg-blue-500': notification.type === 'info',
                'bg-yellow-500': notification.type === 'warning'
            }"
                class="text-white px-6 py-4 rounded-lg shadow-lg max-w-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i
                            :class="{
                                'fas fa-check-circle': notification.type === 'success',
                                'fas fa-exclamation-circle': notification.type === 'error',
                                'fas fa-info-circle': notification.type === 'info',
                                'fas fa-exclamation-triangle': notification.type === 'warning'
                            }"></i>
                        <span x-text="notification.message"></span>
                    </div>
                    <button @click="notification.show = false" class="ml-4 hover:opacity-75">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
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

                    // Replace isi element dengan format inline (ID - Keterangan)
                    element.innerHTML = `
                <div>
                    <span class="text-sm text-gray-600">Reference ID:</span>
                    <p class="text-sm font-medium text-gray-900">${referenceId} - ${ticket.jenis_tiket}<br>${ticket.customer_id} - ${ticket.nama_pelanggan}</p>
                </div>
            `;
                }
                // Kalau gagal, biarkan tetap tampil reference_id asli dengan format yang sama
            } catch (error) {
                console.error('Error fetching ticket data:', error);
                // Kalau error, biarkan tetap tampil reference_id asli
            }
        }

        function transactionDetail() {
            return {
                processing: false,
                showRejectModal: false,
                approvalNotes: '',
                rejectReason: '',
                notification: {
                    show: false,
                    type: 'info',
                    message: ''
                },

                editTransaction(id) {
                    window.location.href = `/transactions/${id}/edit`;
                },

                async approveTransaction() {
                    this.processing = true;

                    try {
                        const response = await fetch(`/approvals/{{ $transaction->transaction_id }}/approve`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                approval_notes: this.approvalNotes
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.showNotification('Transaction approved successfully!', 'success');

                            // Redirect to approvals index after 2 seconds
                            setTimeout(() => {
                                window.location.href = '/approvals';
                            }, 2000);
                        } else {
                            this.showNotification(data.message || 'Failed to approve transaction', 'error');
                        }
                    } catch (error) {
                        this.showNotification('Error approving transaction: ' + error.message, 'error');
                    } finally {
                        this.processing = false;
                    }
                },

                async rejectTransaction() {
                    if (!this.rejectReason.trim()) return;

                    this.processing = true;

                    try {
                        const response = await fetch(`/approvals/{{ $transaction->transaction_id }}/reject`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                reason: this.rejectReason
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.showNotification('Transaction rejected successfully!', 'success');
                            this.showRejectModal = false;

                            // Redirect to approvals index after 2 seconds
                            setTimeout(() => {
                                window.location.href = '/approvals';
                            }, 2000);
                        } else {
                            this.showNotification(data.message || 'Failed to reject transaction', 'error');
                        }
                    } catch (error) {
                        this.showNotification('Error rejecting transaction: ' + error.message, 'error');
                    } finally {
                        this.processing = false;
                    }
                },

                showNotification(message, type = 'info') {
                    this.notification = {
                        show: true,
                        type: type,
                        message: message
                    };

                    // Auto hide after 5 seconds
                    setTimeout(() => {
                        this.notification.show = false;
                    }, 5000);
                }
            }
        }
    </script>
@endpush
