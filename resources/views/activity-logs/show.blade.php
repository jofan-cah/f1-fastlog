
@extends('layouts.app')

@section('title', 'Detail Log Aktivitas - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="activityLogDetail()">
    <!-- Breadcrumb -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-red-600">
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="{{ route('activity-logs.index') }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                        Log Aktivitas
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">{{ $activityLog->log_id }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-br {{ $riskLevel['level'] == 'high' ? 'from-red-600 to-red-700' : ($riskLevel['level'] == 'medium' ? 'from-yellow-600 to-yellow-700' : 'from-green-600 to-green-700') }} rounded-2xl flex items-center justify-center">
                <i class="fas fa-{{ $riskLevel['level'] == 'high' ? 'exclamation-triangle' : ($riskLevel['level'] == 'medium' ? 'exclamation-circle' : 'info-circle') }} text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $activityLog->getFormattedAction() }}</h1>
                <p class="text-gray-600 mt-1">{{ $activityLog->getFormattedTableName() }} • {{ $activityLog->created_at->format('d/m/Y H:i:s') }}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('activity-logs.index') }}"
               class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Status & Risk Badges -->
    <div class="flex items-center space-x-3 flex-wrap">
        <!-- Risk Level Badge -->
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $riskLevel['class'] }}">
            @if($riskLevel['level'] == 'high')
                <i class="fas fa-exclamation-triangle mr-2"></i>
            @elseif($riskLevel['level'] == 'medium')
                <i class="fas fa-exclamation-circle mr-2"></i>
            @else
                <i class="fas fa-info-circle mr-2"></i>
            @endif
            Risk Level: {{ $riskLevel['text'] }}
        </span>

        <!-- Suspicious Badge -->
        @if($isSuspicious)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                <i class="fas fa-eye mr-2"></i>
                Suspicious Activity
            </span>
        @endif

        <!-- Action Type Badge -->
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <i class="fas fa-tag mr-2"></i>
            {{ $activityLog->action }}
        </span>

        <!-- Log ID Badge -->
        <span class="text-sm text-gray-500">ID: {{ $activityLog->log_id }}</span>
    </div>

    <!-- Warning for Suspicious Activity -->
    @if($isSuspicious)
        <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-red-900 mb-2">Aktivitas Mencurigakan Terdeteksi</h3>
                    <p class="text-red-700 text-sm">
                        Aktivitas ini telah ditandai sebagai mencurigakan berdasarkan pola yang tidak biasa.
                        Mohon lakukan investigasi lebih lanjut jika diperlukan.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Informasi Aktivitas
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Log ID</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm font-mono text-gray-900">{{ $activityLog->log_id }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Waktu</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $activityLog->created_at->format('d/m/Y H:i:s') }}</span>
                                <div class="text-xs text-gray-500 mt-1">{{ $activityLog->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Aksi</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $activityLog->getFormattedAction() }}</span>
                                <div class="text-xs text-gray-500 mt-1">{{ $activityLog->action }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tabel</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $activityLog->getFormattedTableName() }}</span>
                                <div class="text-xs text-gray-500 mt-1">{{ $activityLog->table_name }}</div>
                            </div>
                        </div>
                        @if($activityLog->record_id)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Record ID</label>
                                <div class="p-3 bg-gray-50 rounded-lg border">
                                    <span class="text-sm font-mono text-gray-900">{{ $activityLog->record_id }}</span>
                                </div>
                            </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $activityLog->getDescription() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Information Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-user mr-2 text-green-600"></i>
                        Informasi User
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                    <div class="text-lg font-medium text-gray-900">{{ $activityLog->getUserName() }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Level User</label>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ $activityLog->getUserLevel() }}
                                    </span>
                                </div>
                                @if($activityLog->user)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                        <div class="text-sm text-gray-600">{{ $activityLog->user->username }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <div class="text-sm text-gray-600">{{ $activityLog->user->email ?? 'N/A' }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Information Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-server mr-2 text-purple-600"></i>
                        Informasi Teknis
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">IP Address</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm font-mono text-gray-900">{{ $activityLog->ip_address }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">User Agent</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900 break-all">{{ $activityLog->user_agent }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Summary Card -->
            @if(count($changeSummary) > 0)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-exchange-alt mr-2 text-orange-600"></i>
                            Perubahan Data
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Field</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nilai Lama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nilai Baru</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($changeSummary as $change)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $change['field'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <div class="max-w-xs truncate" title="{{ $change['old_value'] }}">
                                                {{ $change['old_value'] }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="max-w-xs truncate font-medium" title="{{ $change['new_value'] }}">
                                                {{ $change['new_value'] }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Raw Data Card -->
            @if($activityLog->old_values || $activityLog->new_values)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-code mr-2 text-indigo-600"></i>
                            Data Mentah
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @if($activityLog->old_values)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Lama</label>
                                    <div class="p-3 bg-gray-50 rounded-lg border overflow-x-auto">
                                        <pre class="text-xs text-gray-900 whitespace-pre-wrap">{{ json_encode($activityLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            @endif
                            @if($activityLog->new_values)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Baru</label>
                                    <div class="p-3 bg-gray-50 rounded-lg border overflow-x-auto">
                                        <pre class="text-xs text-gray-900 whitespace-pre-wrap">{{ json_encode($activityLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column - Related Info -->
        <div class="space-y-6">
            <!-- Quick Actions Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bolt mr-2 text-yellow-600"></i>
                        Aksi Cepat
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @if($activityLog->user)
                            <a href="{{ route('activity-logs.index', ['user_id' => $activityLog->user_id]) }}"
                               class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-user"></i>
                                <span>Aktivitas User Ini</span>
                            </a>
                        @endif

                        <a href="{{ route('activity-logs.index', ['table_name' => $activityLog->table_name]) }}"
                           class="w-full px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-table"></i>
                            <span>Aktivitas Tabel Ini</span>
                        </a>

                        <a href="{{ route('activity-logs.index', ['action' => $activityLog->action]) }}"
                           class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-tag"></i>
                            <span>Aksi Serupa</span>
                        </a>

                        @if($activityLog->record_id)
                            <a href="{{ route('activity-logs.index', ['search' => $activityLog->record_id]) }}"
                               class="w-full px-4 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-search"></i>
                                <span>Riwayat Record</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Risk Assessment Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-shield-alt mr-2 text-red-600"></i>
                        Risk Assessment
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto mb-3 bg-gradient-to-br {{ $riskLevel['level'] == 'high' ? 'from-red-600 to-red-700' : ($riskLevel['level'] == 'medium' ? 'from-yellow-600 to-yellow-700' : 'from-green-600 to-green-700') }} rounded-full flex items-center justify-center">
                                <i class="fas fa-{{ $riskLevel['level'] == 'high' ? 'exclamation-triangle' : ($riskLevel['level'] == 'medium' ? 'exclamation-circle' : 'check-circle') }} text-white text-2xl"></i>
                            </div>
                            <div class="text-xl font-bold text-gray-900">{{ $riskLevel['text'] }}</div>
                            <div class="text-sm text-gray-600">Risk Level</div>
                        </div>

                        <div class="border-t pt-4">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Suspicious:</span>
                                    <span class="{{ $isSuspicious ? 'text-red-600 font-medium' : 'text-green-600' }}">
                                        {{ $isSuspicious ? 'Ya' : 'Tidak' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Action Type:</span>
                                    <span class="text-gray-900">{{ $activityLog->action }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Time of Day:</span>
                                    <span class="text-gray-900">{{ $activityLog->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Activities Card -->
            @if($relatedActivities->count() > 0)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-clock mr-2 text-blue-600"></i>
                            Aktivitas Terkait
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Aktivitas user dalam rentang ±5 menit</p>
                    </div>
                    <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                        @foreach($relatedActivities as $related)
                            @php $relatedRisk = $related->getRiskLevel(); @endphp
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $related->getFormattedAction() }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ $related->getFormattedTableName() }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $related->created_at->format('H:i:s') }}
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $relatedRisk['class'] }}">
                                        {{ $relatedRisk['text'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="px-6 py-3 border-t bg-gray-50">
                        <a href="{{ route('activity-logs.index', ['user_id' => $activityLog->user_id, 'start_date' => $activityLog->created_at->subHour()->format('Y-m-d'), 'end_date' => $activityLog->created_at->addHour()->format('Y-m-d')]) }}"
                           class="text-sm text-blue-600 hover:text-blue-800">
                            Lihat semua aktivitas dalam periode ini
                        </a>
                    </div>
                </div>
            @endif

            <!-- Metadata Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info mr-2 text-gray-600"></i>
                        Metadata
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Log ID:</span>
                            <span class="text-gray-900 font-mono">{{ $activityLog->log_id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">User ID:</span>
                            <span class="text-gray-900 font-mono">{{ $activityLog->user_id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Table:</span>
                            <span class="text-gray-900 font-mono">{{ $activityLog->table_name }}</span>
                        </div>
                        @if($activityLog->record_id)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Record ID:</span>
                                <span class="text-gray-900 font-mono">{{ $activityLog->record_id }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-600">Timestamp:</span>
                            <span class="text-gray-900">{{ $activityLog->created_at->toISOString() }}</span>
                        </div>
                    </div>
                </div>
            </div>
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
    function activityLogDetail() {
        return {
            // Copy log ID to clipboard
            copyLogId() {
                const logId = '{{ $activityLog->log_id }}';
                navigator.clipboard.writeText(logId).then(() => {
                    this.showToast('Log ID copied to clipboard', 'success');
                });
            },

            // Copy IP address to clipboard
            copyIpAddress() {
                const ipAddress = '{{ $activityLog->ip_address }}';
                navigator.clipboard.writeText(ipAddress).then(() => {
                    this.showToast('IP Address copied to clipboard', 'success');
                });
            },

            // Show toast notification
            showToast(message, type = 'info') {
                // Create toast element
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-xl shadow-lg transition-all duration-300 ${
                    type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
                    type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' :
                    'bg-blue-100 border border-blue-400 text-blue-700'
                }`;

                toast.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
                        <span>${message}</span>
                    </div>
                `;

                document.body.appendChild(toast);

                // Auto remove after 3 seconds
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            },

            // Initialize component
            init() {
                // Add click handlers for copyable elements
                document.addEventListener('click', (e) => {
                    if (e.target.closest('[data-copy="log-id"]')) {
                        this.copyLogId();
                    } else if (e.target.closest('[data-copy="ip-address"]')) {
                        this.copyIpAddress();
                    }
                });

                // Add keyboard shortcuts
                document.addEventListener('keydown', (e) => {
                    // Ctrl+C to copy log ID
                    if (e.ctrlKey && e.key === 'c' && !e.target.matches('input, textarea')) {
                        e.preventDefault();
                        this.copyLogId();
                    }

                    // Escape to go back
                    if (e.key === 'Escape') {
                        window.location.href = '{{ route("activity-logs.index") }}';
                    }
                });
            }
        }
    }

    // Add syntax highlighting for JSON data
    document.addEventListener('DOMContentLoaded', function() {
        // Pretty print JSON data
        const preElements = document.querySelectorAll('pre');
        preElements.forEach(pre => {
            try {
                const text = pre.textContent;
                const json = JSON.parse(text);
                pre.innerHTML = JSON.stringify(json, null, 2);

                // Add copy button for JSON data
                const copyBtn = document.createElement('button');
                copyBtn.className = 'absolute top-2 right-2 text-gray-400 hover:text-gray-600 text-sm';
                copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
                copyBtn.title = 'Copy JSON';
                copyBtn.onclick = () => {
                    navigator.clipboard.writeText(text);
                    copyBtn.innerHTML = '<i class="fas fa-check text-green-600"></i>';
                    setTimeout(() => {
                        copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
                    }, 2000);
                };

                pre.parentElement.style.position = 'relative';
                pre.parentElement.appendChild(copyBtn);
            } catch (e) {
                // Not valid JSON, leave as is
            }
        });

        // Add copy functionality to clickable elements
        const logIdElement = document.querySelector('[data-copy="log-id"]');
        if (logIdElement) {
            logIdElement.style.cursor = 'pointer';
            logIdElement.title = 'Click to copy';
        }

        const ipElement = document.querySelector('[data-copy="ip-address"]');
        if (ipElement) {
            ipElement.style.cursor = 'pointer';
            ipElement.title = 'Click to copy';
        }

        // Highlight suspicious activities
        const suspiciousElements = document.querySelectorAll('.suspicious-activity');
        suspiciousElements.forEach(element => {
            element.addEventListener('mouseenter', function() {
                this.classList.add('shadow-lg', 'scale-105');
            });
            element.addEventListener('mouseleave', function() {
                this.classList.remove('shadow-lg', 'scale-105');
            });
        });

        // Auto-scroll to main content if coming from direct link
        if (window.location.hash) {
            setTimeout(() => {
                const element = document.querySelector(window.location.hash);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }, 100);
        }
    });

    // Add real-time timestamp updates
    setInterval(() => {
        const timeElements = document.querySelectorAll('[data-time]');
        timeElements.forEach(element => {
            const timestamp = element.getAttribute('data-time');
            if (timestamp) {
                const date = new Date(timestamp);
                const now = new Date();
                const diff = now - date;

                // Update relative time
                if (diff < 60000) {
                    element.textContent = 'Just now';
                } else if (diff < 3600000) {
                    element.textContent = Math.floor(diff / 60000) + ' minutes ago';
                } else if (diff < 86400000) {
                    element.textContent = Math.floor(diff / 3600000) + ' hours ago';
                }
            }
        });
    }, 60000); // Update every minute
</script>

<!-- Add some custom styles for better UX -->
<style>
    /* Smooth hover effects */
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    /* JSON syntax highlighting */
    pre {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 12px;
        line-height: 1.5;
    }

    /* Scrollbar styling for better UX */
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

    /* Suspicious activity highlight */
    .bg-red-25 {
        background-color: rgba(254, 242, 242, 0.5);
    }

    /* Copy button positioning */
    .relative pre + button {
        position: absolute;
        top: 8px;
        right: 8px;
    }
</style>
@endpush
