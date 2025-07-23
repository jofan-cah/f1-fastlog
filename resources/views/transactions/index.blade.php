@extends('layouts.app')

@section('title', 'Transaksi - ' . ($currentType['text'] ?? 'Semua') . ' - LogistiK Admin')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@section('content')
<div x-data="transactionManager()" class="space-y-6">

    @php
    $label = [
        'IN' => 'Transaksi Masuk',
        'OUT' => 'Transaksi Keluar',
        'LOST' => 'Transaksi Hilang',
        'REPAIR' => 'Transaksi Perbaikan',
    ];

    $judul = $label[$currentType ?? ''] ?? 'Semua Transaksi';
@endphp
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-gradient-to-br {{ $currentType['gradient'] ?? 'from-blue-600 to-blue-700' }} rounded-lg flex items-center justify-center">
                        <i class="{{ $currentType['icon'] ?? 'fas fa-exchange-alt' }} text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $judul }}</h1>
                        <p class="text-sm text-gray-600">{{ $currentType['description'] ?? 'Kelola semua transaksi sistem' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- QR Scanner Button -->
                {{-- <button @click="openQRScanner()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                    <i class="fas fa-qrcode"></i>
                    <span>Scan QR</span>
                </button> --}}

                <!-- Create Transaction Button -->
                <a href="{{ route('transactions.create', ['type' => request()->get('type')]) }}"
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    <span>Buat Transaksi</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.total_transactions">{{ $stats['total_transactions'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-list text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending Approval</p>
                    <p class="text-2xl font-bold text-yellow-600" x-text="stats.pending_count">{{ $stats['pending_count'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Approved Hari Ini</p>
                    <p class="text-2xl font-bold text-green-600" x-text="stats.approved_today">{{ $stats['approved_today'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Success Rate</p>
                    <p class="text-2xl font-bold text-purple-600" x-text="stats.success_rate + '%'">{{ $stats['success_rate'] ?? 0 }}%</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
            <!-- Transaction Type Pills (jika bukan filter by type) -->
            @if(!request()->get('type'))
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('transactions.index') }}"
                   class="px-3 py-1 rounded-full text-sm {{ !request()->get('type') ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition-colors">
                    Semua
                </a>
                @foreach($transactionTypes as $key => $name)
                <a href="{{ route('transactions.index', ['type' => $key]) }}"
                   class="px-3 py-1 rounded-full text-sm {{ request()->get('type') == $key ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition-colors">
                    {{ $name }}
                </a>
                @endforeach
            </div>
            @endif

            <!-- Search & Status Filter -->
            <div class="flex flex-col sm:flex-row gap-3 lg:ml-auto">
                <div class="relative">
                    <input type="text"
                           x-model="search"
                           @input="filterTransactions()"
                           placeholder="Cari transaksi..."
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>

                <select x-model="statusFilter"
                        @change="filterTransactions()"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    @foreach($transactionStatuses as $key => $name)
                    <option value="{{ $key }}">{{ $name }}</option>
                    @endforeach
                </select>

                <button @click="refreshData()"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    <i class="fas fa-sync-alt" :class="{'animate-spin': loading}"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                Daftar Transaksi
                <span x-show="filteredTransactions.length > 0"
                      x-text="'(' + filteredTransactions.length + ')'"
                      class="text-sm text-gray-500"></span>
            </h3>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="p-8 text-center">
            <i class="fas fa-spinner fa-spin text-gray-400 text-2xl mb-2"></i>
            <p class="text-gray-500">Loading transaksi...</p>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && filteredTransactions.length === 0" class="p-8 text-center">
            <i class="fas fa-inbox text-gray-300 text-4xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada transaksi</h3>
            <p class="text-gray-500 mb-4">Mulai dengan membuat transaksi baru atau scan QR code</p>
            <button @click="openQRScanner()"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-qrcode mr-2"></i>
                Scan QR Code
            </button>
        </div>

        <!-- Transactions List -->
        <div x-show="!loading && filteredTransactions.length > 0" class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="transaction in filteredTransactions" :key="transaction.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900" x-text="transaction.transaction_number"></div>
                                    <div class="text-sm text-gray-500" x-text="transaction.created_by_name"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="transaction.type_class">
                                    <i :class="transaction.type_icon" class="mr-1"></i>
                                    <span x-text="transaction.type_text"></span>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900" x-text="transaction.item_name"></div>
                                    <div class="text-sm text-gray-500" x-text="transaction.item_code"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="transaction.status_class">
                                    <i :class="transaction.status_icon" class="mr-1"></i>
                                    <span x-text="transaction.status_text"></span>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div x-text="transaction.transaction_date"></div>
                                <div x-show="transaction.approved_date" x-text="'Approved: ' + transaction.approved_date" class="text-xs text-green-600"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <a :href="'/transactions/' + transaction.id"
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button x-show="transaction.can_edit"
                                            @click="editTransaction(transaction.id)"
                                            class="text-yellow-600 hover:text-yellow-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button x-show="transaction.can_approve"
                                            @click="quickApprove(transaction.id)"
                                            class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div x-show="!loading && filteredTransactions.length > 0" class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span x-text="paginationStart"></span> to <span x-text="paginationEnd"></span> of <span x-text="filteredTransactions.length"></span> results
                </div>
                <div class="flex items-center gap-2">
                    <button @click="previousPage()"
                            :disabled="currentPage === 1"
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50">
                        Previous
                    </button>
                    <span class="px-3 py-1 text-sm" x-text="currentPage + ' / ' + totalPages"></span>
                    <button @click="nextPage()"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Scanner Modal -->
    <div x-show="showQRModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Scan QR Code</h3>
                    <p class="text-sm text-gray-500">Arahkan kamera ke QR code pada barang</p>
                </div>

                <div id="qr-scanner" class="mb-4 bg-black rounded-lg overflow-hidden" style="height: 300px;"></div>

                <div class="flex items-center justify-end gap-3">
                    <button @click="closeQRScanner()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Html5-QrCode Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
function transactionManager() {
    return {
        // Data
        transactions: @json($transactions ?? []),
        filteredTransactions: [],
        search: '',
        statusFilter: '',
        loading: false,
        showQRModal: false,
        html5QrCode: null,

        // Pagination
        currentPage: 1,
        perPage: 10,

        // Stats
        stats: @json($stats ?? []),

        // Initialize
        init() {
            this.filteredTransactions = this.transactions;
            this.filterTransactions();
        },

        // Computed properties
        get paginationStart() {
            return (this.currentPage - 1) * this.perPage + 1;
        },

        get paginationEnd() {
            return Math.min(this.currentPage * this.perPage, this.filteredTransactions.length);
        },

        get totalPages() {
            return Math.ceil(this.filteredTransactions.length / this.perPage);
        },

        // Methods
        filterTransactions() {
            let filtered = this.transactions;

            // Search filter
            if (this.search) {
                filtered = filtered.filter(transaction =>
                    transaction.transaction_number.toLowerCase().includes(this.search.toLowerCase()) ||
                    transaction.item_name.toLowerCase().includes(this.search.toLowerCase()) ||
                    transaction.item_code.toLowerCase().includes(this.search.toLowerCase()) ||
                    transaction.created_by_name.toLowerCase().includes(this.search.toLowerCase())
                );
            }

            // Status filter
            if (this.statusFilter) {
                filtered = filtered.filter(transaction => transaction.status === this.statusFilter);
            }

            this.filteredTransactions = filtered;
            this.currentPage = 1;
        },

        async refreshData() {
            this.loading = true;
            try {
                const response = await fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                this.transactions = data.transactions || [];
                this.stats = data.stats || {};
                this.filterTransactions();
            } catch (error) {
                console.error('Error refreshing data:', error);
            } finally {
                this.loading = false;
            }
        },

        // QR Scanner
        openQRScanner() {
            this.showQRModal = true;
            this.$nextTick(() => {
                this.initQRScanner();
            });
        },

        closeQRScanner() {
            if (this.html5QrCode) {
                this.html5QrCode.stop();
            }
            this.showQRModal = false;
        },

        initQRScanner() {
            this.html5QrCode = new Html5Qrcode("qr-scanner");
            this.html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (qrText) => {
                    this.handleQRScan(qrText);
                },
                (error) => {
                    console.log(error);
                }
            );
        },

        async handleQRScan(qrText) {
            try {
                const qrData = JSON.parse(qrText);
                this.closeQRScanner();

                // Redirect to create transaction with QR data
                const params = new URLSearchParams({
                    qr_data: qrText,
                    type: '{{ request()->get("type") }}'
                });
                window.location.href = '{{ route("transactions.create") }}?' + params.toString();

            } catch (error) {
                alert('QR Code tidak valid');
            }
        },

        // Actions
        editTransaction(id) {
            window.location.href = `/transactions/${id}/edit`;
        },

        async quickApprove(id) {
            if (!confirm('Approve transaksi ini?')) return;

            try {
                const response = await fetch(`/approvals/${id}/quick-approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.refreshData();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error approving transaction');
            }
        },

        // Pagination
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        }
    }
}
</script>
@endpush
