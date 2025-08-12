@extends('layouts.app')

@section('title', 'Report Dashboard - LogistiK')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <i class="fas fa-chart-bar text-red-600"></i>
                    Report Dashboard
                </h1>
                <p class="text-gray-600 mt-1">Analisis transaksi dan performance sistem</p>
            </div>

            <!-- Filter Controls -->
            <div class="flex flex-col sm:flex-row gap-3">
                <form method="GET" class="flex flex-col sm:flex-row gap-3" id="filterForm">
                    <div class="flex gap-2">
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <select name="transaction_type"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">Semua Tipe</option>
                        @foreach($transactionTypes as $key => $label)
                            <option value="{{ $key }}" {{ $transactionType == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>

                    <select name="user_level"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">Semua Level</option>
                        @foreach($userLevels as $key => $label)
                            <option value="{{ $key }}" {{ $userLevel == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-filter text-sm"></i>
                        Filter
                    </button>
                </form>

                <!-- Export Button -->
            <!-- Ganti export dropdown di dashboard.blade.php -->
<div class="relative" x-data="{ showExport: false }">
    <button @click="showExport = !showExport"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center gap-2">
        <i class="fas fa-download text-sm"></i>
        Export
        <i class="fas fa-chevron-down text-xs"></i>
    </button>

    <div x-show="showExport" @click.away="showExport = false" x-transition
         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
        <div class="py-2">
            <!-- Excel Export Form -->
            <form method="POST" action="{{ route('reports.export.excel') }}" class="inline">
                @csrf
                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                <input type="hidden" name="date_to" value="{{ $dateTo }}">
                <input type="hidden" name="transaction_type" value="{{ $transactionType }}">
                <input type="hidden" name="format" value="summary">
                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2 text-sm">
                    <i class="fas fa-file-excel text-green-600"></i>
                    Export Excel
                </button>
            </form>

            <!-- CSV Export -->
            <form method="POST" action="{{ route('reports.export.excel') }}" class="inline">
                @csrf
                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                <input type="hidden" name="date_to" value="{{ $dateTo }}">
                <input type="hidden" name="transaction_type" value="{{ $transactionType }}">
                <input type="hidden" name="format" value="detailed">
                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2 text-sm">
                    <i class="fas fa-file-csv text-blue-600"></i>
                    Export CSV
                </button>
            </form>

            <!-- PDF Export -->
            <form method="POST" action="{{ route('reports.export.pdf') }}" class="inline">
                @csrf
                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                <input type="hidden" name="date_to" value="{{ $dateTo }}">
                <input type="hidden" name="transaction_type" value="{{ $transactionType }}">
                <input type="hidden" name="include_charts" value="1">
                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2 text-sm">
                    <i class="fas fa-file-pdf text-red-600"></i>
                    Export PDF
                </button>
            </form>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Transactions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['totals']['transactions']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['period']['days'] }} hari</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending Approval</p>
                    <p class="text-2xl font-bold text-orange-600">{{ number_format($stats['totals']['pending']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Menunggu persetujuan</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Approval Rate -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Approval Rate</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['totals']['approval_rate'] }}%</p>
                    <p class="text-xs text-gray-500 mt-1">{{ number_format($stats['totals']['approved']) }} disetujui</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Damage Analysis -->
        @if($stats['damage_analysis'])
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Barang Rusak</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($stats['damage_analysis']['total_damaged']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Rp {{ number_format($stats['damage_analysis']['total_repair_estimate']) }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Rejection Rate</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['totals']['rejection_rate'] }}%</p>
                    <p class="text-xs text-gray-500 mt-1">{{ number_format($stats['totals']['rejected']) }} ditolak</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Trends Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Trend Bulanan</h3>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span class="text-xs text-gray-600">Total</span>
                    <div class="w-3 h-3 bg-green-500 rounded-full ml-3"></div>
                    <span class="text-xs text-gray-600">Approved</span>
                </div>
            </div>
            <div class="h-64">
                <canvas id="monthlyTrendsChart"></canvas>
            </div>
        </div>

        <!-- Transaction Type Distribution -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Distribusi Tipe Transaksi</h3>
                <button onclick="refreshChart('typeDistribution')"
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="h-64">
                <canvas id="typeDistributionChart"></canvas>
            </div>
        </div>

        <!-- Daily Activity Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Aktivitas Harian (30 hari terakhir)</h3>
                <span class="text-sm text-gray-500">{{ now()->subDays(30)->format('d M') }} - {{ now()->format('d M Y') }}</span>
            </div>
            <div class="h-64">
                <canvas id="dailyActivityChart"></canvas>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Status Transaksi</h3>
                <div class="text-sm text-gray-500">
                    {{ $stats['totals']['transactions'] }} total
                </div>
            </div>
            <div class="h-64">
                <canvas id="statusDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Damage Analysis Chart (if applicable) -->
    @if($stats['damage_analysis'] && $stats['damage_analysis']['total_damaged'] > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Analisis Kerusakan</h3>
            <div class="flex items-center gap-4 text-sm text-gray-600">
                <span>Total: {{ $stats['damage_analysis']['total_damaged'] }} items</span>
                <span>Estimasi: Rp {{ number_format($stats['damage_analysis']['total_repair_estimate']) }}</span>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Level Kerusakan</h4>
                <div class="h-48">
                    <canvas id="damageLevelChart"></canvas>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Penyebab Kerusakan</h4>
                <div class="space-y-2">
                    @foreach($stats['damage_analysis']['by_reason'] as $reason => $data)
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <span class="text-sm text-gray-700">{{ ucfirst(str_replace('_', ' ', $reason)) }}</span>
                        <div class="text-right">
                            <span class="text-sm font-medium">{{ $data['count'] }} items</span>
                            @if($data['total_estimate'] > 0)
                            <br><span class="text-xs text-gray-500">Rp {{ number_format($data['total_estimate']) }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- User Performance & Recent Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Performance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Performance User</h3>
                <span class="text-sm text-gray-500">Top 10</span>
            </div>
            <div class="space-y-3">
                @foreach($stats['user_performance'] as $user)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $user->full_name }}</p>
                        <p class="text-sm text-gray-600">{{ $user->level_name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-medium text-gray-900">{{ $user->total_transactions }} transaksi</p>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-green-600">{{ $user->success_rate }}%</span>
                            <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full"
                                     style="width: {{ $user->success_rate }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Aktivitas Terbaru</h3>
                <button onclick="refreshActivities()"
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="space-y-3" id="recentActivities">
                @foreach($recentActivities as $activity)
                <div class="flex items-start gap-3 p-3 hover:bg-gray-50 rounded-lg transition-colors">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user text-blue-600 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900">
                            <span class="font-medium">{{ $activity['user_name'] }}</span>
                            {{ $activity['description'] }}
                        </p>
                        <p class="text-xs text-gray-500">{{ $activity['created_at_human'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Metrics Performance</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Average Processing Time -->
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-clock text-blue-600 text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $performanceMetrics['avg_processing_time'] }}h</p>
                <p class="text-sm text-gray-600">Rata-rata Waktu Proses</p>
            </div>

            <!-- Peak Hours -->
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-chart-line text-green-600 text-xl"></i>
                </div>
                <p class="text-lg font-bold text-gray-900">
                    @foreach($performanceMetrics['peak_hours'] as $hour)
                        {{ $hour['hour'] }}@if(!$loop->last), @endif
                    @endforeach
                </p>
                <p class="text-sm text-gray-600">Jam Tersibuk</p>
            </div>

            <!-- Most Active Items -->
            <div class="text-center">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-box text-orange-600 text-xl"></i>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $performanceMetrics['frequent_items']->count() }}</p>
                <p class="text-sm text-gray-600">Item Aktif</p>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 flex items-center gap-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-red-600"></div>
        <span class="text-gray-900">Loading...</span>
    </div>
</div>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .chart-container {
        position: relative;
        height: 300px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Chart configurations
    const chartConfig = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 15
                }
            }
        }
    };

    // Chart data from backend
    const chartData = @json($chartData);

    // Initialize Charts
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();

        // Auto refresh every 5 minutes
        setInterval(refreshDashboard, 300000);
    });

    function initializeCharts() {
        // Monthly Trends Chart
        if (document.getElementById('monthlyTrendsChart')) {
            const ctx = document.getElementById('monthlyTrendsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.monthly_trends.map(item => item.month),
                    datasets: [{
                        label: 'Total',
                        data: chartData.monthly_trends.map(item => item.total),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Approved',
                        data: chartData.monthly_trends.map(item => item.approved),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    ...chartConfig,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Type Distribution Chart
        if (document.getElementById('typeDistributionChart')) {
            const ctx = document.getElementById('typeDistributionChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: chartData.type_distribution.map(item => item.label),
                    datasets: [{
                        data: chartData.type_distribution.map(item => item.count),
                        backgroundColor: [
                            'rgb(34, 197, 94)',  // Green for IN
                            'rgb(59, 130, 246)',  // Blue for OUT
                            'rgb(251, 191, 36)', // Yellow for REPAIR
                            'rgb(239, 68, 68)',  // Red for LOST
                            'rgb(147, 51, 234)', // Purple for RETURN
                            'rgb(249, 115, 22)'  // Orange for DAMAGED
                        ]
                    }]
                },
                options: chartConfig
            });
        }

        // Daily Activity Chart
        if (document.getElementById('dailyActivityChart')) {
            const ctx = document.getElementById('dailyActivityChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.daily_activity.map(item => item.date),
                    datasets: [{
                        label: 'Transaksi',
                        data: chartData.daily_activity.map(item => item.count),
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    ...chartConfig,
                    scales: {
                        x: {
                            display: false
                        },
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Status Distribution Chart
        if (document.getElementById('statusDistributionChart')) {
            const ctx = document.getElementById('statusDistributionChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: chartData.status_distribution.map(item => item.label),
                    datasets: [{
                        data: chartData.status_distribution.map(item => item.count),
                        backgroundColor: [
                            'rgb(251, 191, 36)', // Yellow for pending
                            'rgb(34, 197, 94)',  // Green for approved
                            'rgb(239, 68, 68)',  // Red for rejected
                            'rgb(59, 130, 246)', // Blue for completed
                            'rgb(107, 114, 128)' // Gray for cancelled
                        ]
                    }]
                },
                options: chartConfig
            });
        }

        // Damage Level Chart (if exists)
        if (document.getElementById('damageLevelChart') && chartData.damage_levels.length > 0) {
            const ctx = document.getElementById('damageLevelChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.damage_levels.map(item => item.label),
                    datasets: [{
                        label: 'Jumlah',
                        data: chartData.damage_levels.map(item => item.count),
                        backgroundColor: [
                            'rgb(251, 191, 36)', // Yellow for light
                            'rgb(249, 115, 22)', // Orange for medium
                            'rgb(239, 68, 68)',  // Red for heavy
                            'rgb(107, 114, 128)' // Gray for total
                        ]
                    }]
                },
                options: {
                    ...chartConfig,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }

    // Export functions
    function exportData(format) {
        showLoading();

        const formData = new FormData(document.getElementById('filterForm'));
        formData.append('format', format);

        let url = '';
        if (format === 'excel' || format === 'csv') {
            url = '{{ route("reports.export.excel") }}';
        } else if (format === 'pdf') {
            url = '{{ route("reports.export.pdf") }}';
        }

        // Create temporary form for file download
        const tempForm = document.createElement('form');
        tempForm.method = 'POST';
        tempForm.action = url;
        tempForm.style.display = 'none';

        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        tempForm.appendChild(csrfInput);

        // Add form data
        for (let [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            tempForm.appendChild(input);
        }

        document.body.appendChild(tempForm);
        tempForm.submit();
        document.body.removeChild(tempForm);

        hideLoading();
    }

    // Refresh functions
    function refreshChart(chartType) {
        // Implementation for refreshing specific charts
        showLoading();

        fetch('{{ route("reports.api.chart-data") }}?' + new URLSearchParams({
            chart_type: chartType,
            date_from: document.querySelector('input[name="date_from"]').value,
            date_to: document.querySelector('input[name="date_to"]').value,
            transaction_type: document.querySelector('select[name="transaction_type"]').value
        }))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update chart with new data
                // Implementation depends on specific chart
            }
        })
        .finally(() => {
            hideLoading();
        });
    }

    function refreshActivities() {
        showLoading();

        fetch('{{ route("reports.api.real-time-stats") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update activities list
                // Implementation for updating recent activities
            }
        })
        .finally(() => {
            hideLoading();
        });
    }

    function refreshDashboard() {
        // Refresh all dashboard data
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);

        fetch(window.location.pathname + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stats cards
                updateStatsCards(data.stats);
                // Reinitialize charts with new data
                chartData = data.chartData;
                initializeCharts();
            }
        })
        .catch(error => {
            console.error('Error refreshing dashboard:', error);
        });
    }

    function updateStatsCards(stats) {
        // Update stats cards with new data
        // Implementation for updating stat cards
    }

    // Utility functions
    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    // Filter form auto-submit
    document.getElementById('filterForm').addEventListener('change', function() {
        // Auto-submit on filter change (optional)
        // this.submit();
    });
</script>
@endpush
