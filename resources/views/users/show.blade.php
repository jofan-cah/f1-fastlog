@extends('layouts.app')

@section('title', 'Detail User - LogistiK Admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('users.index') }}"
               class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail User</h1>
                <p class="text-gray-600 mt-1">Informasi lengkap user {{ $user->full_name ?? $user->username }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('users.edit', $user->user_id) }}"
               class="px-4 py-2 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-edit"></i>
                <span>Edit User</span>
            </a>

            <!-- Toggle Status Button -->
            <form method="POST" action="{{ route('users.toggle-status', $user->user_id) }}" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="px-4 py-2 rounded-xl transition-all duration-200 flex items-center space-x-2 {{ $user->is_active ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }} text-white"
                        onclick="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }} user ini?')">
                    <i class="fas fa-{{ $user->is_active ? 'user-slash' : 'user-check' }}"></i>
                    <span>{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-8 text-center">
                    <!-- Avatar -->
                    <div class="w-24 h-24 bg-gradient-to-br from-red-600 to-red-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-2xl font-bold">
                            {{ strtoupper(substr($user->full_name ?? $user->username, 0, 1)) }}
                        </span>
                    </div>

                    <!-- User Info -->
                    <h2 class="text-xl font-bold text-gray-900 mb-1">{{ $user->full_name ?? 'N/A' }}</h2>
                    <p class="text-gray-600 mb-2">@{{ $user->username }}</p>
                    <p class="text-sm text-gray-500 mb-4">{{ $user->email }}</p>

                    <!-- Status Badge -->
                    <div class="flex justify-center mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <span class="w-2 h-2 rounded-full mr-2
                                {{ $user->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                            {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>

                    <!-- Level Badge -->
                    <div class="flex justify-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($user->userLevel?->level_name === 'Admin') bg-purple-100 text-purple-800
                            @elseif($user->userLevel?->level_name === 'Logistik') bg-blue-100 text-blue-800
                            @elseif($user->userLevel?->level_name === 'Teknisi') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            <i class="fas fa-{{ $user->userLevel?->level_name === 'Admin' ? 'crown' : ($user->userLevel?->level_name === 'Logistik' ? 'boxes' : 'tools') }} mr-2"></i>
                            {{ $user->userLevel?->level_name ?? 'N/A' }}
                        </span>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-gray-900">{{ $user->created_at->diffInDays() }}</div>
                            <div class="text-xs text-gray-500">Hari Bergabung</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900">{{ $user->activityLogs->count() ?? 0 }}</div>
                            <div class="text-xs text-gray-500">Aktivitas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Account Information -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-blue-600"></i>
                        Informasi Akun
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">User ID</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="text-gray-900 font-mono">{{ $user->user_id }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="text-gray-900">{{ $user->username }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="text-gray-900">{{ $user->email }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="text-gray-900">{{ $user->full_name ?? 'Belum diisi' }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Level User</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="inline-flex items-center text-sm font-medium
                                        @if($user->userLevel?->level_name === 'Admin') text-purple-800
                                        @elseif($user->userLevel?->level_name === 'Logistik') text-blue-800
                                        @elseif($user->userLevel?->level_name === 'Teknisi') text-green-800
                                        @else text-gray-800
                                        @endif">
                                        <i class="fas fa-{{ $user->userLevel?->level_name === 'Admin' ? 'crown' : ($user->userLevel?->level_name === 'Logistik' ? 'boxes' : 'tools') }} mr-2"></i>
                                        {{ $user->userLevel?->level_name ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Akun</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="inline-flex items-center text-sm font-medium
                                        {{ $user->is_active ? 'text-green-800' : 'text-red-800' }}">
                                        <span class="w-2 h-2 rounded-full mr-2
                                            {{ $user->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                        {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Permissions -->
            @if($user->userLevel && $user->userLevel->permissions)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-key mr-2 text-yellow-600"></i>
                            Hak Akses & Permissions
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @php
                                $permissions = is_string($user->userLevel->permissions)
                                    ? json_decode($user->userLevel->permissions, true)
                                    : $user->userLevel->permissions;
                                $modules = ['users', 'master_data', 'purchasing', 'inventory', 'transactions', 'reports', 'settings', 'tools'];
                                $actions = ['create', 'read', 'update', 'delete'];
                            @endphp

                            @foreach($modules as $module)
                                <div class="border border-gray-200 rounded-xl p-4">
                                    <h4 class="font-medium text-gray-900 mb-3 capitalize">{{ str_replace('_', ' ', $module) }}</h4>
                                    <div class="space-y-2">
                                        @foreach($actions as $action)
                                            @php
                                                $hasPermission = isset($permissions[$module][$action]) && $permissions[$module][$action];
                                            @endphp
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-gray-600 capitalize">{{ $action }}</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    {{ $hasPermission ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    <i class="fas fa-{{ $hasPermission ? 'check' : 'times' }} mr-1"></i>
                                                    {{ $hasPermission ? 'Ya' : 'Tidak' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Account Timeline -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock mr-2 text-green-600"></i>
                        Timeline Akun
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-plus text-green-600 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Akun dibuat</p>
                                <p class="text-xs text-gray-500">{{ $user->created_at->format('d M Y H:i') }} ({{ $user->created_at->diffForHumans() }})</p>
                            </div>
                        </div>

                        @if($user->created_at != $user->updated_at)
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-edit text-blue-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Terakhir diperbarui</p>
                                    <p class="text-xs text-gray-500">{{ $user->updated_at->format('d M Y H:i') }} ({{ $user->updated_at->diffForHumans() }})</p>
                                </div>
                            </div>
                        @endif

                        @if($user->email_verified_at)
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-envelope-check text-purple-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Email diverifikasi</p>
                                    <p class="text-xs text-gray-500">{{ $user->email_verified_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-envelope text-yellow-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Email belum diverifikasi</p>
                                    <p class="text-xs text-gray-500">Perlu verifikasi email</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Activity Summary -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-line mr-2 text-indigo-600"></i>
                        Ringkasan Aktivitas
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-xl">
                            <div class="text-2xl font-bold text-blue-600">{{ $user->purchaseOrders->count() ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Purchase Orders</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-xl">
                            <div class="text-2xl font-bold text-green-600">{{ $user->goodsReceived->count() ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Penerimaan Barang</div>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-xl">
                            <div class="text-2xl font-bold text-yellow-600">{{ $user->createdTransactions->count() ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Transaksi Dibuat</div>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-xl">
                            <div class="text-2xl font-bold text-purple-600">{{ $user->approvedTransactions->count() ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Transaksi Disetujui</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add some interactive elements
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to cards
        const cards = document.querySelectorAll('.bg-white');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '';
            });
        });
    });
</script>
@endpush
