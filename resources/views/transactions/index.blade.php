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
                        <div
                            class="w-10 h-10 bg-gradient-to-br {{ $currentType['gradient'] ?? 'from-blue-600 to-blue-700' }} rounded-lg flex items-center justify-center">
                            <i class="{{ $currentType['icon'] ?? 'fas fa-exchange-alt' }} text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $judul }}</h1>
                            <p class="text-sm text-gray-600">
                                {{ $currentType['description'] ?? 'Kelola semua transaksi sistem' }}</p>
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
                        <p class="text-2xl font-bold text-gray-900" x-text="stats.total_transactions">
                            {{ $stats['total_transactions'] ?? 0 }}</p>
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
                        <p class="text-2xl font-bold text-yellow-600" x-text="stats.pending_count">
                            {{ $stats['pending_count'] ?? 0 }}</p>
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
                        <p class="text-2xl font-bold text-green-600" x-text="stats.approved_today">
                            {{ $stats['approved_today'] ?? 0 }}</p>
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
                        <p class="text-2xl font-bold text-purple-600" x-text="stats.success_rate + '%'">
                            {{ $stats['success_rate'] ?? 0 }}%</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Status Filter -->
        <div class="flex flex-col sm:flex-row gap-3 lg:ml-auto">
            <!-- Search Input -->
            <div class="relative">
                <input type="text" x-model="search" @input="filterTransactions()" placeholder="Cari transaksi..."
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <!-- ✅ FIXED: Date From Input -->
            <div class="relative">
                <input type="date" x-model="dateFrom" @change="filterTransactions()"
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <label class="absolute -top-2 left-2 bg-white px-1 text-xs text-gray-500">Dari Tanggal</label>
            </div>

            <!-- ✅ FIXED: Date To Input -->
            <div class="relative">
                <input type="date" x-model="dateTo" @change="filterTransactions()"
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <label class="absolute -top-2 left-2 bg-white px-1 text-xs text-gray-500">Sampai Tanggal</label>
            </div>

            <!-- Status Filter -->
            <select x-model="statusFilter" @change="filterTransactions()"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <option value="">Semua Status</option>
                @foreach ($transactionStatuses as $key => $name)
                    <option value="{{ $key }}">{{ $name }}</option>
                @endforeach
            </select>

            <!-- Refresh Button -->
            <button @click="refreshData()"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                <i class="fas fa-sync-alt" :class="{ 'animate-spin': loading }"></i>
            </button>

            <!-- ✅ NEW: Quick Date Buttons -->
            <div class="flex gap-1">
                <button @click="setDateRange('today')"
                    class="px-2 py-2 text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition-colors"
                    title="Hari Ini">
                    <i class="fas fa-calendar-day"></i>
                </button>
                <button @click="setDateRange('week')"
                    class="px-2 py-2 text-xs bg-green-100 hover:bg-green-200 text-green-700 rounded-lg transition-colors"
                    title="7 Hari Terakhir">
                    <i class="fas fa-calendar-week"></i>
                </button>
                <button @click="clearDateFilters()" x-show="dateFrom || dateTo"
                    class="px-2 py-2 text-xs bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors"
                    title="Clear Date">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- ✅ NEW: Active Filters Display -->
        <div x-show="search || statusFilter || dateFrom || dateTo" x-transition class="mt-3 pt-3 border-t border-gray-200">
            <div class="flex flex-wrap gap-2 text-sm">
                <span class="text-gray-600">Active filters:</span>

                <!-- Search Badge -->
                <span x-show="search"
                    class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                    Search: <span x-text="search" class="ml-1 font-medium"></span>
                    <button @click="search = ''; filterTransactions()" class="ml-1 hover:text-blue-600">×</button>
                </span>

                <!-- Status Badge -->
                <span x-show="statusFilter"
                    class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                    Status: <span x-text="statusFilter" class="ml-1 font-medium"></span>
                    <button @click="statusFilter = ''; filterTransactions()" class="ml-1 hover:text-green-600">×</button>
                </span>

                <!-- Date Badge -->
                <span x-show="dateFrom || dateTo"
                    class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">
                    Date: <span x-text="(dateFrom || 'Any') + ' - ' + (dateTo || 'Any')" class="ml-1 font-medium"></span>
                    <button @click="clearDateFilters()" class="ml-1 hover:text-purple-600">×</button>
                </span>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Daftar Transaksi
                    <span x-show="filteredTransactions.length > 0" x-text="'(' + filteredTransactions.length + ')'"
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Transaksi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="transaction in filteredTransactions" :key="transaction.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"
                                            x-text="transaction.transaction_number"></div>
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
                                        <div class="text-sm font-medium text-gray-900" x-text="transaction.item_name">
                                        </div>
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
                                    <div x-show="transaction.approved_date"
                                        x-text="'Approved: ' + transaction.approved_date" class="text-xs text-green-600">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-2">
                                        <a :href="'/transactions/' + transaction.id"
                                            class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {{-- <button x-show="transaction.can_edit" @click="editTransaction(transaction.id)"
                                            class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button x-show="transaction.can_approve" @click="quickApprove(transaction.id)"
                                            class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-check"></i>
                                        </button> --}}
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
                        Showing <span x-text="paginationStart"></span> to <span x-text="paginationEnd"></span> of <span
                            x-text="filteredTransactions.length"></span> results
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="previousPage()" :disabled="currentPage === 1"
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50">
                            Previous
                        </button>
                        <span class="px-3 py-1 text-sm" x-text="currentPage + ' / ' + totalPages"></span>
                        <button @click="nextPage()" :disabled="currentPage === totalPages"
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Scanner Modal -->
        <div x-show="showQRModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div
                    class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
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
                dateFrom: '', // ✅ FIXED: Proper declaration
                dateTo: '', // ✅ FIXED: Proper declaration
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

                // ✅ FIXED: Filter method with proper date handling
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

                    // ✅ Date filter - Compare dates properly
                    if (this.dateFrom) {
                        filtered = filtered.filter(transaction => {
                            // Extract date from transaction_date (format: "23 Jan 2024 14:30")
                            const transactionDate = this.parseTransactionDate(transaction.transaction_date);
                            const filterFromDate = new Date(this.dateFrom);
                            return transactionDate >= filterFromDate;
                        });
                    }

                    if (this.dateTo) {
                        filtered = filtered.filter(transaction => {
                            // Extract date from transaction_date (format: "23 Jan 2024 14:30")
                            const transactionDate = this.parseTransactionDate(transaction.transaction_date);
                            const filterToDate = new Date(this.dateTo);
                            // Set end of day for "to" date
                            filterToDate.setHours(23, 59, 59, 999);
                            return transactionDate <= filterToDate;
                        });
                    }

                    this.filteredTransactions = filtered;
                    this.currentPage = 1;
                },

                // ✅ NEW: Helper method to parse transaction date
                parseTransactionDate(dateString) {
                    // Convert "23 Jan 2024 14:30" to proper Date object
                    const months = {
                        'Jan': 0,
                        'Feb': 1,
                        'Mar': 2,
                        'Apr': 3,
                        'May': 4,
                        'Jun': 5,
                        'Jul': 6,
                        'Aug': 7,
                        'Sep': 8,
                        'Oct': 9,
                        'Nov': 10,
                        'Dec': 11
                    };

                    const parts = dateString.split(' ');
                    if (parts.length >= 3) {
                        const day = parseInt(parts[0]);
                        const monthName = parts[1];
                        const year = parseInt(parts[2]);
                        const time = parts[3] || '00:00';
                        const [hours, minutes] = time.split(':').map(Number);

                        const month = months[monthName];
                        if (month !== undefined) {
                            return new Date(year, month, day, hours || 0, minutes || 0);
                        }
                    }

                    // Fallback: try to parse as-is
                    return new Date(dateString);
                },

                // ✅ UPDATED: Refresh with date parameters
                async refreshData() {
                    this.loading = true;
                    try {
                        // Build URL with current filters
                        const params = new URLSearchParams();
                        if (this.search) params.append('search', this.search);
                        if (this.statusFilter) params.append('status', this.statusFilter);
                        if (this.dateFrom) params.append('date_from', this.dateFrom);
                        if (this.dateTo) params.append('date_to', this.dateTo);

                        const url = window.location.pathname + '?' + params.toString();

                        const response = await fetch(url, {
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

                // ✅ NEW: Clear date filters
                clearDateFilters() {
                    this.dateFrom = '';
                    this.dateTo = '';
                    this.filterTransactions();
                },

                // ✅ NEW: Set quick date ranges
                setDateRange(range) {
                    const today = new Date();
                    const formatDate = (date) => date.toISOString().split('T')[0];

                    switch (range) {
                        case 'today':
                            this.dateFrom = formatDate(today);
                            this.dateTo = formatDate(today);
                            break;
                        case 'yesterday':
                            const yesterday = new Date(today);
                            yesterday.setDate(yesterday.getDate() - 1);
                            this.dateFrom = formatDate(yesterday);
                            this.dateTo = formatDate(yesterday);
                            break;
                        case 'week':
                            const weekAgo = new Date(today);
                            weekAgo.setDate(weekAgo.getDate() - 7);
                            this.dateFrom = formatDate(weekAgo);
                            this.dateTo = formatDate(today);
                            break;
                        case 'month':
                            const monthAgo = new Date(today);
                            monthAgo.setDate(monthAgo.getDate() - 30);
                            this.dateFrom = formatDate(monthAgo);
                            this.dateTo = formatDate(today);
                            break;
                    }
                    this.filterTransactions();
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
                    this.html5QrCode.start({
                            facingMode: "environment"
                        }, {
                            fps: 10,
                            qrbox: {
                                width: 250,
                                height: 250
                            }
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
                            type: '{{ request()->get('type') }}'
                        });
                        window.location.href = '{{ route('transactions.create') }}?' + params.toString();

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
