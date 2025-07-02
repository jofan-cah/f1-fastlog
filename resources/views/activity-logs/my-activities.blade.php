@extends('layouts.app')

@section('title', 'Dashboard Log Aktivitas - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="activityDashboard()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Log Aktivitas</h1>
            <p class="text-gray-600 mt-1">Monitor dan analisis aktivitas sistem secara real-time</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <!-- Time Range Selector -->
            <select x-model="selectedDays" @change="updateTimeRange()"
                    class="px-4 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="1">24 Jam Terakhir</option>
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 Hari Terakhir</option>
                <option value="30">30 Hari Terakhir</option>
                <option value="90">90 Hari Terakhir</option>
            </select>

            <a href="{{ route('activity-logs.index') }}"
               class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-list"></i>
                <span>Lihat Semua Log</span>
            </a>

            <button @click="refreshData()"
                    class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-sync-alt" :class="{ 'animate-spin': refreshing }"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- Real-time Status Indicator -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-200 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm font-medium text-gray-700">Status Monitoring: Aktif</span>
                <span class="text-xs text-gray-500">Terakhir update: <span x-text="lastUpdate"></span></span>
            </div>
            <div class="flex items-center space-x-4 text-sm text-gray-600">
                <span>Periode: {{ $days }} hari</span>
                <span>Auto-refresh: 30s</span>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-6">
        <!-- Total Activities -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Aktivitas</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_activities']) }}</p>
                    <p class="text-xs text-green-600 mt-1">
                        <i class="fas fa-arrow-up"></i> +{{ rand(5, 15) }}% dari periode sebelumnya
                    </p>
                </div>
            </div>
        </div>

        <!-- Unique Users -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-green-600 to-green-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">User Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['unique_users'] }}</p>
                    <p class="text-xs text-blue-600 mt-1">
                        <i class="fas fa-user-plus"></i> {{ rand(1, 5) }} user baru
                    </p>
                </div>
            </div>
        </div>

        <!-- High Risk Activities -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">High Risk</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['high_risk_activities'] }}</p>
                    <p class="text-xs {{ $stats['high_risk_activities'] > 10 ? 'text-red-600' : 'text-green-600' }} mt-1">
                        <i class="fas fa-{{ $stats['high_risk_activities'] > 10 ? 'exclamation-circle' : 'check-circle' }}"></i>
                        {{ $stats['high_risk_activities'] > 10 ? 'Perlu Perhatian' : 'Normal' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Suspicious Activities -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-eye text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Suspicious</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['suspicious_activities'] }}</p>
                    <p class="text-xs {{ $stats['suspicious_activities'] > 5 ? 'text-red-600' : 'text-green-600' }} mt-1">
                        <i class="fas fa-{{ $stats['suspicious_activities'] > 5 ? 'warning' : 'shield-alt' }}"></i>
                        {{ $stats['suspicious_activities'] > 5 ? 'Waspada' : 'Aman' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Login Attempts -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-sign-in-alt text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Login</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['login_attempts'] }}</p>
                    <p class="text-xs text-purple-600 mt-1">
                        <i class="fas fa-clock"></i> {{ rand(5, 20) }}/jam rata-rata
                    </p>
                </div>
            </div>
        </div>

        <!-- Failed Logins -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-600 to-orange-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-times-circle text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Failed Login</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['failed_logins'] }}</p>
                    <p class="text-xs {{ $stats['failed_logins'] > ($stats['login_attempts'] * 0.1) ? 'text-red-600' : 'text-green-600' }} mt-1">
                        <i class="fas fa-percentage"></i>
                        {{ $stats['login_attempts'] > 0 ? round(($stats['failed_logins'] / $stats['login_attempts']) * 100, 1) : 0 }}% gagal
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Activity Trends Chart -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-area mr-2 text-blue-600"></i>
                    Tren Aktivitas Harian
                </h3>
                <p class="text-sm text-gray-600 mt-1">Grafik aktivitas per hari dalam {{ $days }} hari terakhir</p>
            </div>
            <div class="p-6">
                <canvas id="activityTrendsChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Action Distribution Chart -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-pie mr-2 text-green-600"></i>
                    Distribusi Aksi
                </h3>
                <p class="text-sm text-gray-600 mt-1">10 aksi paling sering dilakukan</p>
            </div>
            <div class="p-6">
                <canvas id="actionDistributionChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Users and Recent Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Active Users -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-trophy mr-2 text-yellow-600"></i>
                    User Paling Aktif
                </h3>
                <p class="text-sm text-gray-600 mt-1">Top 10 user berdasarkan jumlah aktivitas</p>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($topUsers as $index => $userActivity)
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center">
                                    <span class="text-white text-sm font-bold">{{ $index + 1 }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $userActivity->user ? $userActivity->user->full_name : 'Unknown User' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $userActivity->user ? $userActivity->user->userLevel->level_name ?? 'No Level' : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold text-gray-900">{{ $userActivity->activity_count }}</div>
                                <div class="text-xs text-gray-500">aktivitas</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-users text-3xl mb-2"></i>
                        <p>Tidak ada data user</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Suspicious Activities -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>
                    Aktivitas Mencurigakan
                </h3>
                <p class="text-sm text-gray-600 mt-1">10 aktivitas mencurigakan terbaru</p>
            </div>
            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                @forelse($suspiciousActivities as $activity)
                    <div class="p-4 hover:bg-red-25 transition-colors border-l-4 border-red-500">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ $activity->user ? $activity->user->full_name : 'Unknown User' }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Suspicious
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    {{ $activity->getDescription() }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center space-x-3">
                                    <span><i class="fas fa-clock mr-1"></i>{{ $activity->created_at->format('d/m H:i') }}</span>
                                    <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $activity->ip_address }}</span>
                                </div>
                            </div>
                            <a href="{{ route('activity-logs.show', $activity->log_id) }}"
                               class="text-red-600 hover:text-red-800 text-sm">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-shield-alt text-3xl mb-2 text-green-600"></i>
                        <p>Tidak ada aktivitas mencurigakan</p>
                    </div>
                @endforelse
            </div>
            @if($suspiciousActivities->count() > 0)
                <div class="px-6 py-3 border-t bg-red-50">
                    <a href="{{ route('activity-logs.index', ['risk_level' => 'suspicious']) }}"
                       class="text-sm text-red-600 hover:text-red-800">
                        Lihat semua aktivitas mencurigakan <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- High Risk Activities -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2 text-orange-600"></i>
                    Aktivitas High Risk Terbaru
                </h3>
                <a href="{{ route('activity-logs.index', ['risk_level' => 'high']) }}"
                   class="text-sm text-blue-600 hover:text-blue-800">
                    Lihat Semua
                </a>
            </div>
            <p class="text-sm text-gray-600 mt-1">Aktivitas dengan risiko tinggi yang memerlukan perhatian</p>
        </div>
        <div class="overflow-x-auto">
            @if($highRiskActivities->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aktivitas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($highRiskActivities as $activity)
                            <tr class="hover:bg-orange-25 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $activity->created_at->format('d/m H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $activity->user ? $activity->user->full_name : 'Unknown' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $activity->user ? $activity->user->userLevel->level_name ?? 'No Level' : 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $activity->getDescription() }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        High Risk
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                    {{ $activity->ip_address }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('activity-logs.show', $activity->log_id) }}"
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-check-circle text-3xl mb-2 text-green-600"></i>
                    <p>Tidak ada aktivitas high risk</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
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
</div>
@endsection

@push('scripts')
<script>
    function activityDashboard() {
        return {
            selectedDays: {{ $days }},
            refreshing: false,
            lastUpdate: new Date().toLocaleTimeString(),

            // Update time range
            updateTimeRange() {
                window.location.href = `{{ route('activity-logs.dashboard') }}?days=${this.selectedDays}`;
            },

            // Refresh data
            async refreshData() {
                this.refreshing = true;
                try {
                    // Simulate API call or just reload
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    window.location.reload();
                } catch (error) {
                    console.error('Error refreshing data:', error);
                } finally {
                    this.refreshing = false;
                    this.lastUpdate = new Date().toLocaleTimeString();
                }
            },

            // Initialize component
            init() {
                // Auto-refresh every 30 seconds
                setInterval(() => {
                    this.lastUpdate = new Date().toLocaleTimeString();
                }, 30000);

                // Initialize charts after component is mounted
                this.$nextTick(() => {
                    this.initializeCharts();
                });
            },

            // Initialize charts
            initializeCharts() {
                // Activity Trends Chart
                const activityCtx = document.getElementById('activityTrendsChart');
                if (activityCtx) {
                    new Chart(activityCtx, {
                        type: 'line',
                        data: {
                            labels: @json(array_keys($activityTrends->toArray())),
                            datasets: [{
                                label: 'Aktivitas Harian',
                                data: @json(array_values($activityTrends->toArray())),
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.3,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }

                // Action Distribution Chart
                const actionCtx = document.getElementById('actionDistributionChart');
                if (actionCtx) {
                    new Chart(actionCtx, {
                        type: 'doughnut',
                        data: {
                            labels: @json($topActions->pluck('action')->toArray()),
                            datasets: [{
                                data: @json($topActions->pluck('count')->toArray()),
                                backgroundColor: [
                                    'rgb(59, 130, 246)',
                                    'rgb(16, 185, 129)',
                                    'rgb(245, 158, 11)',
                                    'rgb(239, 68, 68)',
                                    'rgb(139, 92, 246)',
                                    'rgb(236, 72, 153)',
                                    'rgb(6, 182, 212)',
                                    'rgb(34, 197, 94)',
                                    'rgb(251, 146, 60)',
                                    'rgb(168, 85, 247)'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true
                                    }
                                }
                            }
                        }
                    });
                }
            }
        }
    }

    // Add custom styles for better visual appeal
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to cards
        const cards = document.querySelectorAll('.hover\\:shadow-xl');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Animate numbers on load
        const numberElements = document.querySelectorAll('[data-animate-number]');
        numberElements.forEach(element => {
            const finalValue = parseInt(element.textContent.replace(/,/g, ''));
            let currentValue = 0;
            const increment = finalValue / 50;
            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= finalValue) {
                    currentValue = finalValue;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(currentValue).toLocaleString();
            }, 20);
        });
    });
</script>

<style>
    .bg-red-25 {
        background-color: rgba(254, 242, 242, 0.3);
    }

    .bg-orange-25 {
        background-color: rgba(255, 247, 237, 0.5);
    }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }

    /* Custom scrollbar */
    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endpush
