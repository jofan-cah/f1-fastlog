@extends('layouts.app')

@section('title', 'Log Aktivitas - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="activityLogManager()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Log Aktivitas</h1>
            <p class="text-gray-600 mt-1">Monitor dan audit aktivitas sistem secara real-time</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('activity-logs.dashboard') }}"
               class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('activity-logs.export', request()->query()) }}"
               class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-download"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-list text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Aktivitas</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_activities']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-green-600 to-green-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">User Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['unique_users'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">High Risk</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['high_risk_activities'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-eye text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Suspicious</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['suspicious_activities'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-sign-in-alt text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Login</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['login_attempts'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-600 to-orange-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-times-circle text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Failed Login</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['failed_logins'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari IP, Record ID, atau nama user..."
                               class="pl-10 pr-4 py-3 w-full bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- User Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                    <select name="user_id"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        <option value="">Semua User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->user_id }}" {{ request('user_id') == $user->user_id ? 'selected' : '' }}>
                                {{ $user->full_name }} ({{ $user->username }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Table Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tabel</label>
                    <select name="table_name"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        <option value="">Semua Tabel</option>
                        @foreach($tables as $table)
                            <option value="{{ $table }}" {{ request('table_name') == $table ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $table)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Aksi</label>
                    <select name="action"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        <option value="">Semua Aksi</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Risk Level Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Risk Level</label>
                    <select name="risk_level"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        <option value="">Semua Level</option>
                        <option value="high" {{ request('risk_level') == 'high' ? 'selected' : '' }}>High Risk</option>
                        <option value="suspicious" {{ request('risk_level') == 'suspicious' ? 'selected' : '' }}>Suspicious</option>
                    </select>
                </div>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date"
                           name="start_date"
                           value="{{ request('start_date') }}"
                           class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                    <input type="date"
                           name="end_date"
                           value="{{ request('end_date') }}"
                           class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-filter"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('activity-logs.index') }}"
                   class="px-6 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Activity Logs Table -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-history mr-2 text-blue-600"></i>
                    Log Aktivitas
                </h3>
                <span class="text-sm text-gray-600">Total: {{ $logs->total() }} aktivitas</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivitas</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Level</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        @php
                            $riskLevel = $log->getRiskLevel();
                            $isSuspicious = $log->isSuspicious();
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors duration-200 {{ $isSuspicious ? 'bg-red-25 border-l-4 border-red-500' : '' }}">
                            <!-- Timestamp -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $log->created_at->format('d/m/Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>

                            <!-- User Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user text-white text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $log->getUserName() }}</div>
                                        <div class="text-sm text-gray-500">{{ $log->getUserLevel() }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Activity -->
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 font-medium">{{ $log->getDescription() }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ $log->getFormattedTableName() }}
                                    @if($log->record_id)
                                        • Record: {{ $log->record_id }}
                                    @endif
                                </div>
                                @if($isSuspicious)
                                    <div class="text-xs text-red-600 mt-1 flex items-center">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Suspicious Activity
                                    </div>
                                @endif
                            </td>

                            <!-- Risk Level -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $riskLevel['class'] }}">
                                    @if($riskLevel['level'] == 'high')
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                    @elseif($riskLevel['level'] == 'medium')
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                    @else
                                        <i class="fas fa-info-circle mr-1"></i>
                                    @endif
                                    {{ $riskLevel['text'] }}
                                </span>
                            </td>

                            <!-- IP Address -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-mono text-gray-900">{{ $log->ip_address }}</div>
                                <div class="text-xs text-gray-500">{{ substr($log->user_agent, 0, 30) }}...</div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('activity-logs.show', $log->log_id) }}"
                                       class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                       title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada log aktivitas</h3>
                                    <p class="text-gray-500 mb-4">Belum ada aktivitas yang tercatat dalam periode ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($logs->hasPages())
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Menampilkan {{ $logs->firstItem() }} sampai {{ $logs->lastItem() }}
                    dari {{ $logs->total() }} hasil
                </div>
                <div class="flex items-center space-x-2">
                    {{ $logs->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    @endif

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

    @if(session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-full"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-full"
             class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="ml-4 text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function activityLogManager() {
        return {
            // Real-time updates (optional)
            refreshData() {
                // Implement real-time refresh if needed
                window.location.reload();
            },

            // Initialize component
            init() {
                // Auto-refresh every 30 seconds for suspicious activities monitoring
                setInterval(() => {
                    if (window.location.pathname.includes('activity-logs')) {
                        // Only refresh if user is still on activity logs page
                        // You can implement AJAX refresh here instead of full reload
                    }
                }, 30000);
            }
        }
    }

    // Format timestamps for better readability
    document.addEventListener('DOMContentLoaded', function() {
        // Add any additional JavaScript for enhanced UX
        const suspiciousRows = document.querySelectorAll('tr.bg-red-25');
        suspiciousRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.classList.add('shadow-md');
            });
            row.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-md');
            });
        });
    });
</script>
@endpush
