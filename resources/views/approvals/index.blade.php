@extends('layouts.app')

@section('title', 'Approval Center - LogistiK Admin')

@section('content')
<div x-data="approvalCenter()" class="space-y-6">

    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-orange-600 to-red-700 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-clipboard-check text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Approval Center</h1>
                    <p class="text-gray-600 mt-1">Kelola semua transaksi yang menunggu approval</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button @click="showBulkActions = !showBulkActions"
                        :class="selectedTransactions.length > 0 ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                        :disabled="selectedTransactions.length === 0"
                        class="px-4 py-2 text-white rounded-lg transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-tasks"></i>
                    <span>Bulk Actions (<span x-text="selectedTransactions.length"></span>)</span>
                </button>

                <button @click="refreshData()"
                        :disabled="loading"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all duration-200 flex items-center space-x-2 disabled:opacity-50">
                    <i :class="loading ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Pending -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Pending</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_pending'] }}</p>
                </div>
                <div class="w-14 h-14 bg-orange-100 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-orange-600">{{ $stats['urgent_pending'] }}</span>
                <span class="text-gray-500 ml-1">urgent (>24h)</span>
            </div>
        </div>

        <!-- By Transaction Type -->
        @foreach(['OUT' => ['icon' => 'fa-arrow-up', 'color' => 'blue'], 'IN' => ['icon' => 'fa-arrow-down', 'color' => 'green'], 'REPAIR' => ['icon' => 'fa-wrench', 'color' => 'yellow']] as $type => $config)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">{{ $transactionTypes[$type] ?? $type }}</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['by_type'][$type] ?? 0 }}</p>
                </div>
                <div class="w-14 h-14 bg-{{ $config['color'] }}-100 rounded-2xl flex items-center justify-center">
                    <i class="fas {{ $config['icon'] }} text-{{ $config['color'] }}-600 text-xl"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-filter mr-2 text-blue-600"></i>
                Filter Transaksi
            </h3>
            <button @click="clearFilters()" class="text-sm text-blue-600 hover:text-blue-800">
                <i class="fas fa-times mr-1"></i>
                Clear Filters
            </button>
        </div>

        <form @submit.prevent="applyFilters()" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Transaction Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Transaksi</label>
                <select x-model="filters.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Tipe</option>
                    @foreach($transactionTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- User Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dibuat Oleh</label>
                <select x-model="filters.created_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->user_id }}">{{ $user->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                <input type="date" x-model="filters.date_from" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                <input type="date" x-model="filters.date_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Apply Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-search mr-2"></i>
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Bulk Actions Panel -->
    <div x-show="showBulkActions && selectedTransactions.length > 0"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3">
                <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                <span class="font-medium text-blue-900">
                    <span x-text="selectedTransactions.length"></span> transaksi dipilih
                </span>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button @click="bulkApprove()"
                        :disabled="bulkProcessing"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center space-x-2">
                    <i :class="bulkProcessing ? 'fas fa-spinner fa-spin' : 'fas fa-check'"></i>
                    <span>Approve Semua</span>
                </button>

                <button @click="bulkReject()"
                        :disabled="bulkProcessing"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center space-x-2">
                    <i :class="bulkProcessing ? 'fas fa-spinner fa-spin' : 'fas fa-times'"></i>
                    <span>Reject Semua</span>
                </button>

                <button @click="clearSelection()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Clear Selection
                </button>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list mr-2 text-purple-600"></i>
                    Pending Transactions
                </h3>
                <div class="flex items-center space-x-4">
                    <!-- Select All -->
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox"
                               @change="toggleSelectAll($event.target.checked)"
                               :checked="selectAllChecked"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-600">Select All</span>
                    </label>

                    <span class="text-sm text-gray-500">
                        {{ $pendingTransactions->total() }} total transactions
                    </span>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="p-8 text-center">
            <div class="inline-flex items-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                <span class="text-gray-600">Loading transactions...</span>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && transactions.length === 0" class="p-8 text-center">
            <i class="fas fa-check-circle text-6xl text-green-300 mb-4"></i>
            <h3 class="text-xl font-medium text-gray-900 mb-2">Tidak Ada Pending Approval</h3>
            <p class="text-gray-500">Semua transaksi sudah diproses atau belum ada transaksi baru</p>
        </div>

        <!-- Transactions List -->
        <div x-show="!loading && transactions.length > 0" class="divide-y divide-gray-200">
            <template x-for="transaction in transactions" :key="transaction.transaction_id">
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start space-x-4">
                        <!-- Checkbox -->
                        <div class="flex-shrink-0 pt-1">
                            <input type="checkbox"
                                   :value="transaction.transaction_id"
                                   @change="toggleTransaction(transaction.transaction_id, $event.target.checked)"
                                   :checked="selectedTransactions.includes(transaction.transaction_id)"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </div>

                        <!-- Transaction Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <h4 class="text-lg font-medium text-gray-900" x-text="transaction.transaction_number"></h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="getTypeClass(transaction.transaction_type)">
                                        <i :class="getTypeIcon(transaction.transaction_type)" class="mr-1"></i>
                                        <span x-text="getTypeName(transaction.transaction_type)"></span>
                                    </span>
                                    <span x-show="isUrgent(transaction.transaction_date)"
                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Urgent
                                    </span>
                                </div>

                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    <span x-text="formatDate(transaction.transaction_date)"></span>
                                </div>
                            </div>

                            <!-- Item Info -->
                            <div class="mb-3">
                                <div class="flex items-center space-x-2 text-sm text-gray-600">
                                    <i class="fas fa-cube"></i>
                                    <span x-text="transaction.item?.item_name || 'Unknown Item'"></span>
                                    <span class="text-gray-400">•</span>
                                    <span x-text="transaction.item?.item_code || 'N/A'"></span>
                                </div>

                                <!-- Item Details -->
                                <div x-show="transaction.transaction_details && transaction.transaction_details.length > 0" class="mt-2">
                                    <template x-for="detail in transaction.transaction_details" :key="detail.transaction_detail_id">
                                        <div class="text-sm text-gray-500">
                                            <i class="fas fa-barcode mr-1"></i>
                                            <span x-text="detail.item_detail?.serial_number || 'No SN'"></span>
                                            <span class="mx-2 text-gray-400">→</span>
                                            <span x-text="detail.status_before + ' → ' + (detail.status_after || 'Pending')"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Creator & Notes -->
                            <div class="mb-4">
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-user"></i>
                                        <span x-text="transaction.created_by?.full_name || 'Unknown User'"></span>
                                    </div>

                                    <div x-show="transaction.reference_id" class="flex items-center space-x-1">
                                        <i class="fas fa-link"></i>
                                        <span x-text="transaction.reference_id"></span>
                                    </div>
                                </div>

                                <div x-show="transaction.notes" class="mt-2 text-sm text-gray-700">
                                    <i class="fas fa-sticky-note mr-1"></i>
                                    <span x-text="transaction.notes"></span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-3">
                                {{-- <button @click="quickApprove(transaction.transaction_id)"
                                        :disabled="actionLoading[transaction.transaction_id]"
                                        class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center space-x-2">
                                    <i :class="actionLoading[transaction.transaction_id] ? 'fas fa-spinner fa-spin' : 'fas fa-check'" class="text-sm"></i>
                                    <span>Quick Approve</span>
                                </button>

                                <button @click="showRejectModal(transaction)"
                                        :disabled="actionLoading[transaction.transaction_id]"
                                        class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center space-x-2">
                                    <i class="fas fa-times text-sm"></i>
                                    <span>Reject</span>
                                </button> --}}

                                <a :href="`/approvals/${transaction.transaction_id}`"
                                   class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center justify-center space-x-2">
                                    <i class="fas fa-eye text-sm"></i>
                                    <span>Detail</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $pendingTransactions->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectDialog"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="fixed inset-0 z-50 overflow-y-auto">

        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75" @click="closeRejectModal()"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Reject Transaction
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-4">
                                    Anda akan menolak transaksi <strong x-text="rejectTransaction?.transaction_number"></strong>.
                                    Berikan alasan penolakan:
                                </p>
                                <textarea x-model="rejectReason"
                                         rows="3"
                                         placeholder="Masukkan alasan penolakan..."
                                         class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="confirmReject()"
                            :disabled="!rejectReason.trim() || rejectLoading"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <i :class="rejectLoading ? 'fas fa-spinner fa-spin' : 'fas fa-times'" class="mr-2"></i>
                        <span x-show="!rejectLoading">Reject</span>
                        <span x-show="rejectLoading">Processing...</span>
                    </button>

                    <button @click="closeRejectModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div x-show="notifications.length > 0" class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="(notification, index) in notifications" :key="index">
            <div x-show="notification.show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-full"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform translate-x-full"
                 :class="{
                     'bg-green-500': notification.type === 'success',
                     'bg-red-500': notification.type === 'error',
                     'bg-blue-500': notification.type === 'info',
                     'bg-yellow-500': notification.type === 'warning'
                 }"
                 class="text-white px-6 py-4 rounded-lg shadow-lg max-w-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i :class="{
                            'fas fa-check-circle': notification.type === 'success',
                            'fas fa-exclamation-circle': notification.type === 'error',
                            'fas fa-info-circle': notification.type === 'info',
                            'fas fa-exclamation-triangle': notification.type === 'warning'
                        }"></i>
                        <span x-text="notification.message"></span>
                    </div>
                    <button @click="removeNotification(index)" class="ml-4 hover:opacity-75">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
function approvalCenter() {
    return {
        // Data properties
        transactions: @json($pendingTransactions->items()),
        selectedTransactions: [],
        selectAllChecked: false,
        loading: false,
        actionLoading: {},
        bulkProcessing: false,

        // UI state
        showBulkActions: false,
        showRejectDialog: false,
        rejectTransaction: null,
        rejectReason: '',
        rejectLoading: false,

        // Filters
        filters: {
            type: '{{ request()->get('type', '') }}',
            created_by: '{{ request()->get('created_by', '') }}',
            date_from: '{{ request()->get('date_from', '') }}',
            date_to: '{{ request()->get('date_to', '') }}'
        },

        // Notifications
        notifications: [],

        // Transaction types mapping
        transactionTypes: @json($transactionTypes),

        init() {
            console.log('Approval Center initialized');

            // Auto refresh every 30 seconds
            setInterval(() => {
                this.refreshData();
            }, 30000);
        },

        // Selection methods
        toggleTransaction(transactionId, checked) {
            if (checked) {
                if (!this.selectedTransactions.includes(transactionId)) {
                    this.selectedTransactions.push(transactionId);
                }
            } else {
                const index = this.selectedTransactions.indexOf(transactionId);
                if (index > -1) {
                    this.selectedTransactions.splice(index, 1);
                }
            }
            this.updateSelectAllState();
        },

        toggleSelectAll(checked) {
            if (checked) {
                this.selectedTransactions = this.transactions.map(t => t.transaction_id);
            } else {
                this.selectedTransactions = [];
            }
            this.selectAllChecked = checked;
        },

        updateSelectAllState() {
            this.selectAllChecked = this.transactions.length > 0 &&
                                  this.selectedTransactions.length === this.transactions.length;
        },

        clearSelection() {
            this.selectedTransactions = [];
            this.selectAllChecked = false;
            this.showBulkActions = false;
        },

        // Action methods
        async quickApprove(transactionId) {
            this.actionLoading[transactionId] = true;

            try {
                const response = await fetch(`/approvals/${transactionId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        approval_notes: 'Quick approval'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showNotification('Transaction approved successfully!', 'success');
                    this.removeTransactionFromList(transactionId);
                } else {
                    this.showNotification(data.message || 'Failed to approve transaction', 'error');
                }
            } catch (error) {
                this.showNotification('Error approving transaction: ' + error.message, 'error');
            } finally {
                this.actionLoading[transactionId] = false;
            }
        },

        showRejectModal(transaction) {
            this.rejectTransaction = transaction;
            this.rejectReason = '';
            this.showRejectDialog = true;
        },

        closeRejectModal() {
            this.showRejectDialog = false;
            this.rejectTransaction = null;
            this.rejectReason = '';
        },

        async confirmReject() {
            if (!this.rejectReason.trim()) return;

            this.rejectLoading = true;

            try {
                const response = await fetch(`/approvals/${this.rejectTransaction.transaction_id}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        reason: this.rejectReason
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showNotification('Transaction rejected successfully!', 'success');
                    this.removeTransactionFromList(this.rejectTransaction.transaction_id);
                    this.closeRejectModal();
                } else {
                    this.showNotification(data.message || 'Failed to reject transaction', 'error');
                }
            } catch (error) {
                this.showNotification('Error rejecting transaction: ' + error.message, 'error');
            } finally {
                this.rejectLoading = false;
            }
        },

        // Bulk actions
        async bulkApprove() {
            if (this.selectedTransactions.length === 0) return;

            if (!confirm(`Approve ${this.selectedTransactions.length} transactions?`)) return;

            this.bulkProcessing = true;

            try {
                const response = await fetch('/approvals/bulk-approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        transaction_ids: this.selectedTransactions,
                        notes: 'Bulk approval'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showNotification(`${data.details.success_count} transactions approved successfully!`, 'success');

                    // Remove approved transactions from list
                    this.selectedTransactions.forEach(id => {
                        this.removeTransactionFromList(id);
                    });

                    this.clearSelection();

                    if (data.details.error_count > 0) {
                        this.showNotification(`${data.details.error_count} transactions failed to approve`, 'warning');
                    }
                } else {
                    this.showNotification(data.message || 'Failed to approve transactions', 'error');
                }
            } catch (error) {
                this.showNotification('Error approving transactions: ' + error.message, 'error');
            } finally {
                this.bulkProcessing = false;
            }
        },

        async bulkReject() {
            if (this.selectedTransactions.length === 0) return;

            const reason = prompt(`Reject ${this.selectedTransactions.length} transactions?\n\nPlease provide a reason:`);
            if (!reason || !reason.trim()) return;

            this.bulkProcessing = true;

            try {
                const response = await fetch('/approvals/bulk-reject', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        transaction_ids: this.selectedTransactions,
                        reason: reason.trim()
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showNotification(`${data.details.success_count} transactions rejected successfully!`, 'success');

                    // Remove rejected transactions from list
                    this.selectedTransactions.forEach(id => {
                        this.removeTransactionFromList(id);
                    });

                    this.clearSelection();

                    if (data.details.error_count > 0) {
                        this.showNotification(`${data.details.error_count} transactions failed to reject`, 'warning');
                    }
                } else {
                    this.showNotification(data.message || 'Failed to reject transactions', 'error');
                }
            } catch (error) {
                this.showNotification('Error rejecting transactions: ' + error.message, 'error');
            } finally {
                this.bulkProcessing = false;
            }
        },

        // Utility methods
        removeTransactionFromList(transactionId) {
            const index = this.transactions.findIndex(t => t.transaction_id === transactionId);
            if (index > -1) {
                this.transactions.splice(index, 1);
            }

            // Remove from selected if exists
            const selectedIndex = this.selectedTransactions.indexOf(transactionId);
            if (selectedIndex > -1) {
                this.selectedTransactions.splice(selectedIndex, 1);
            }

            this.updateSelectAllState();
        },

        async refreshData() {
            this.loading = true;

            try {
                const urlParams = new URLSearchParams();

                // Add filters to URL
                Object.keys(this.filters).forEach(key => {
                    if (this.filters[key]) {
                        urlParams.append(key, this.filters[key]);
                    }
                });

                const response = await fetch(`/approvals?${urlParams.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.transactions = data.transactions;
                    this.clearSelection();
                    this.showNotification('Data refreshed successfully', 'success');
                } else {
                    this.showNotification('Failed to refresh data', 'error');
                }
            } catch (error) {
                this.showNotification('Error refreshing data: ' + error.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        // Filter methods
        applyFilters() {
            const urlParams = new URLSearchParams();

            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    urlParams.append(key, this.filters[key]);
                }
            });

            // Redirect with filters
            window.location.href = `/approvals?${urlParams.toString()}`;
        },

        clearFilters() {
            this.filters = {
                type: '',
                created_by: '',
                date_from: '',
                date_to: ''
            };

            // Redirect without filters
            window.location.href = '/approvals';
        },

        // Helper methods
        getTypeClass(type) {
            const classes = {
                'OUT': 'bg-blue-100 text-blue-800',
                'IN': 'bg-green-100 text-green-800',
                'REPAIR': 'bg-yellow-100 text-yellow-800',
                'TRANSFER': 'bg-purple-100 text-purple-800'
            };
            return classes[type] || 'bg-gray-100 text-gray-800';
        },

        getTypeIcon(type) {
            const icons = {
                'OUT': 'fas fa-arrow-up',
                'IN': 'fas fa-arrow-down',
                'REPAIR': 'fas fa-wrench',
                'TRANSFER': 'fas fa-exchange-alt'
            };
            return icons[type] || 'fas fa-question';
        },

        getTypeName(type) {
            return this.transactionTypes[type] || type;
        },

        isUrgent(transactionDate) {
            const date = new Date(transactionDate);
            const now = new Date();
            const diffHours = (now - date) / (1000 * 60 * 60);
            return diffHours > 24;
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now - date);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays === 1) {
                return 'Today';
            } else if (diffDays === 2) {
                return 'Yesterday';
            } else if (diffDays < 7) {
                return `${diffDays - 1} days ago`;
            } else {
                return date.toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }
        },

        // Notification methods
        // showNotification(message, type = 'info') {
        //     const notification = {
        //         message,
        //         type,
        //         show: true
        //     };

        //     this.notifications.push(notification);

        //     // // Auto remove after 5 seconds
        //     // setTimeout(() => {
        //     //     this.removeNotification(this.notifications.length - 1);
        //     // }, 5000);
        // },

        removeNotification(index) {
            if (this.notifications[index]) {
                this.notifications[index].show = false;

                // Remove from array after animation
                setTimeout(() => {
                    this.notifications.splice(index, 1);
                }, 300);
            }
        }
    }
}
</script>
@endpush
