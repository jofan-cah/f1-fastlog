@extends('layouts.app')

@section('title', 'Detail Level Pengguna - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="userLevelShow()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('user-levels.index') }}"
               class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Level Pengguna</h1>
                <p class="text-gray-600 mt-1">Informasi lengkap {{ $userLevel->level_name }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('user-levels.edit', $userLevel->user_level_id) }}"
               class="px-4 py-2 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-edit"></i>
                <span>Edit Level</span>
            </a>

            <!-- Delete Button -->
            <button @click="showDeleteModal()"
                    class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-trash"></i>
                <span>Hapus</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Level Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-8 text-center bg-gradient-to-br from-red-50 to-red-100">
                    <!-- Avatar -->
                    <div class="w-24 h-24 bg-gradient-to-br from-red-600 to-red-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    @php
                        $levelIcons = [
                            'Admin' => 'crown',
                            'Logistik' => 'boxes',
                            'Teknisi' => 'tools'
                        ];
                        $levelIcon = $levelIcons[$userLevel->level_name] ?? 'user-tag';
                    @endphp
                    <i class="fas fa-{{ $levelIcon }} text-white text-3xl"></i>
                    </div>

                    <!-- Level Info -->
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $userLevel->level_name }}</h2>
                    <p class="text-sm text-gray-600 mb-1">{{ $userLevel->description ?? 'Tidak ada deskripsi' }}</p>
                    <p class="text-xs text-gray-500 mb-4">ID: {{ $userLevel->user_level_id }}</p>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 gap-4 mt-6">
                        <div class="text-center p-3 bg-white rounded-xl border border-red-200">
                            <div class="text-2xl font-bold text-red-600">{{ $userLevel->users_count }}</div>
                            <div class="text-xs text-gray-600">Total User</div>
                        </div>
                        <div class="text-center p-3 bg-white rounded-xl border border-red-200">
                            <div class="text-2xl font-bold text-red-600">
                                {{ is_array($userLevel->permissions) ? count($userLevel->permissions) : 0 }}
                            </div>
                            <div class="text-xs text-gray-600">Modul Akses</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('user-levels.edit', $userLevel->user_level_id) }}"
                           class="flex items-center justify-center px-3 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                            <i class="fas fa-edit mr-2"></i>
                            Edit
                        </a>
                        <button @click="showDeleteModal()"
                                class="flex items-center justify-center px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                            <i class="fas fa-trash mr-2"></i>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Informasi Level
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ID Level</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="text-gray-900 font-mono">{{ $userLevel->user_level_id }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Level</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="text-gray-900 font-semibold">{{ $userLevel->level_name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total Pengguna</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="text-gray-900">{{ $userLevel->users_count }} user</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $userLevel->users_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                            {{ $userLevel->users_count > 0 ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                                        {{ $userLevel->users_count > 0 ? 'Aktif Digunakan' : 'Tidak Digunakan' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($userLevel->description)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                                <p class="text-gray-900 leading-relaxed">{{ $userLevel->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Permissions Details -->
            @if($userLevel->permissions && is_array($userLevel->permissions))
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-key mr-2 text-yellow-600"></i>
                                Hak Akses & Permissions
                            </h3>
                            <div class="flex items-center space-x-4">
                                <button @click="viewMode = 'grid'"
                                        :class="viewMode === 'grid' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-600'"
                                        class="px-3 py-1 rounded-lg transition-colors text-sm">
                                    <i class="fas fa-th mr-1"></i>
                                    Grid
                                </button>
                                <button @click="viewMode = 'list'"
                                        :class="viewMode === 'list' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-600'"
                                        class="px-3 py-1 rounded-lg transition-colors text-sm">
                                    <i class="fas fa-list mr-1"></i>
                                    List
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <!-- Grid View -->
                        <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($userLevel->permissions as $module => $actions)
                                <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-center space-x-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-gray-600 to-gray-700 rounded-lg flex items-center justify-center">
                                            @php
                                                $moduleIcons = [
                                                    'dashboard' => 'tachometer-alt',
                                                    'users' => 'users',
                                                    'user_levels' => 'user-cog',
                                                    'categories' => 'tags',
                                                    'suppliers' => 'truck',
                                                    'items' => 'box',
                                                    'purchase_orders' => 'shopping-cart',
                                                    'goods_receiveds' => 'dolly',
                                                    'stocks' => 'warehouse',
                                                    'transactions' => 'exchange-alt',
                                                    'reports' => 'chart-bar',
                                                    'activity_logs' => 'history',
                                                    'qr_scanner' => 'qrcode',
                                                    'settings' => 'cog'
                                                ];
                                                $icon = $moduleIcons[$module] ?? 'cog';
                                            @endphp
                                            <i class="fas fa-{{ $icon }} text-white text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 capitalize">
                                                {{ str_replace('_', ' ', $module) }}
                                            </h4>
                                            <p class="text-sm text-gray-500">{{ count($actions) }} permissions</p>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        @foreach($actions as $action)
                                            <div class="flex items-center justify-between p-2 bg-green-50 rounded-lg">
                                                <div class="flex items-center space-x-2">
                                                @php
                                            $actionIcons = [
                                                'create' => 'plus',
                                                'read' => 'eye',
                                                'update' => 'edit',
                                                'delete' => 'trash',
                                                'approve' => 'check',
                                                'export' => 'download',
                                                'scan' => 'qrcode',
                                                'adjust' => 'cog'
                                            ];
                                            $actionIcon = $actionIcons[$action] ?? 'cog';
                                        @endphp
                                        <i class="fas fa-{{ $actionIcon }} text-green-600 text-sm"></i>
                                                    <span class="text-sm font-medium text-green-800 capitalize">{{ $action }}</span>
                                                </div>
                                                <i class="fas fa-check text-green-600 text-sm"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- List View -->
                        <div x-show="viewMode === 'list'" class="space-y-4">
                            @foreach($userLevel->permissions as $module => $actions)
                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-gradient-to-br from-gray-600 to-gray-700 rounded-lg flex items-center justify-center">
                                            @php
                                                $moduleIcons = [
                                                    'dashboard' => 'tachometer-alt',
                                                    'users' => 'users',
                                                    'user_levels' => 'user-cog',
                                                    'categories' => 'tags',
                                                    'suppliers' => 'truck',
                                                    'items' => 'box',
                                                    'purchase_orders' => 'shopping-cart',
                                                    'goods_receiveds' => 'dolly',
                                                    'stocks' => 'warehouse',
                                                    'transactions' => 'exchange-alt',
                                                    'reports' => 'chart-bar',
                                                    'activity_logs' => 'history',
                                                    'qr_scanner' => 'qrcode',
                                                    'settings' => 'cog'
                                                ];
                                                $icon = $moduleIcons[$module] ?? 'cog';
                                            @endphp
                                            <i class="fas fa-{{ $icon }} text-white text-sm"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-900 capitalize">{{ str_replace('_', ' ', $module) }}</h4>
                                                <p class="text-xs text-gray-500">{{ count($actions) }} permissions aktif</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($actions as $action)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                @php
                                                    $actionIcons = [
                                                        'create' => 'plus',
                                                        'read' => 'eye',
                                                        'update' => 'edit',
                                                        'delete' => 'trash',
                                                        'approve' => 'check',
                                                        'export' => 'download',
                                                        'scan' => 'qrcode',
                                                        'adjust' => 'cog'
                                                    ];
                                                    $actionIcon = $actionIcons[$action] ?? 'cog';
                                                @endphp
                                                <i class="fas fa-{{ $actionIcon }} mr-1"></i>
                                                    {{ ucfirst($action) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Permission Summary -->
                        <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-200">
                            <h4 class="font-semibold text-blue-900 mb-4 flex items-center">
                                <i class="fas fa-chart-pie mr-2"></i>
                                Ringkasan Permissions
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ count($userLevel->permissions) }}</div>
                                    <div class="text-blue-700">Modul</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">
                                        @php
                                            $totalPermissions = 0;
                                            foreach($userLevel->permissions as $actions) {
                                                $totalPermissions += count($actions);
                                            }
                                        @endphp
                                        {{ $totalPermissions }}
                                    </div>
                                    <div class="text-green-700">Total Permissions</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-yellow-600">
                                        @php
                                            $readOnlyCount = 0;
                                            foreach($userLevel->permissions as $actions) {
                                                if(count($actions) === 1 && in_array('read', $actions)) {
                                                    $readOnlyCount++;
                                                }
                                            }
                                        @endphp
                                        {{ $readOnlyCount }}
                                    </div>
                                    <div class="text-yellow-700">Read Only</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-600">
                                        @php
                                            $fullAccessCount = 0;
                                            $defaultPermissions = [
                                                'dashboard' => ['read'],
                                                'users' => ['create', 'read', 'update', 'delete'],
                                                'user_levels' => ['create', 'read', 'update', 'delete'],
                                                'categories' => ['create', 'read', 'update', 'delete'],
                                                'suppliers' => ['create', 'read', 'update', 'delete'],
                                                'items' => ['create', 'read', 'update', 'delete'],
                                                'purchase_orders' => ['create', 'read', 'update', 'delete', 'approve'],
                                                'goods_receiveds' => ['create', 'read', 'update', 'delete'],
                                                'stocks' => ['create', 'read', 'update', 'delete', 'adjust'],
                                                'transactions' => ['create', 'read', 'update', 'delete', 'approve'],
                                                'reports' => ['read', 'export'],
                                                'activity_logs' => ['read'],
                                                'qr_scanner' => ['read', 'scan'],
                                                'settings' => ['read', 'update']
                                            ];

                                            foreach($userLevel->permissions as $module => $actions) {
                                                if(isset($defaultPermissions[$module]) && count($actions) === count($defaultPermissions[$module])) {
                                                    $hasAll = true;
                                                    foreach($defaultPermissions[$module] as $defaultAction) {
                                                        if(!in_array($defaultAction, $actions)) {
                                                            $hasAll = false;
                                                            break;
                                                        }
                                                    }
                                                    if($hasAll) $fullAccessCount++;
                                                }
                                            }
                                        @endphp
                                        {{ $fullAccessCount }}
                                    </div>
                                    <div class="text-red-700">Full Access</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-key mr-2 text-yellow-600"></i>
                            Hak Akses & Permissions
                        </h3>
                    </div>
                    <div class="p-12 text-center">
                        <i class="fas fa-lock text-6xl text-gray-300 mb-4"></i>
                        <h4 class="text-xl font-medium text-gray-900 mb-2">Tidak Ada Permissions</h4>
                        <p class="text-gray-500 mb-6">Level ini belum memiliki hak akses yang dikonfigurasi.</p>
                        <a href="{{ route('user-levels.edit', $userLevel->user_level_id) }}"
                           class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 inline-flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>Atur Permissions</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Users with this Level -->
            @if($userLevel->users->count() > 0)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-users mr-2 text-purple-600"></i>
                            Pengguna dengan Level Ini ({{ $userLevel->users->count() }})
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($userLevel->users as $user)
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                    <div class="w-10 h-10 bg-gradient-to-br from-red-600 to-red-700 rounded-full flex items-center justify-center">
                                        <span class="text-white text-sm font-medium">
                                            {{ strtoupper(substr($user->full_name ?? $user->username, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->full_name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->username }} • {{ $user->email }}</div>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($userLevel->users->count() > 6)
                            <div class="mt-4 text-center">
                                <a href="{{ route('users.index', ['level' => $userLevel->user_level_id]) }}"
                                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                    Lihat Semua User ({{ $userLevel->users->count() }})
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Level Timeline -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock mr-2 text-green-600"></i>
                        Timeline Level
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-plus text-green-600 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Level dibuat</p>
                                <p class="text-xs text-gray-500">{{ $userLevel->created_at->format('d M Y H:i') }} ({{ $userLevel->created_at->diffForHumans() }})</p>
                            </div>
                        </div>

                        @if($userLevel->created_at != $userLevel->updated_at)
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-edit text-blue-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Terakhir diperbarui</p>
                                    <p class="text-xs text-gray-500">{{ $userLevel->updated_at->format('d M Y H:i') }} ({{ $userLevel->updated_at->diffForHumans() }})</p>
                                </div>
                            </div>
                        @endif

                        @if($userLevel->users_count > 0)
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-users text-purple-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Status penggunaan</p>
                                    <p class="text-xs text-gray-500">Digunakan oleh {{ $userLevel->users_count }} user aktif</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hideDeleteModal()"
         @keydown.escape.window="hideDeleteModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="deleteModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <!-- Icon -->
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash text-2xl text-red-600"></i>
                </div>

                <!-- Title -->
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Hapus Level Pengguna</h3>

                <!-- Message -->
                <div class="text-gray-600 text-center mb-6">
                    <p class="mb-2">
                        Apakah Anda yakin ingin menghapus level <span class="font-semibold text-gray-900">{{ $userLevel->level_name }}</span>?
                    </p>

                    <!-- Warning if has users -->
                    @if($userLevel->users_count > 0)
                        <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center justify-center space-x-2 text-yellow-800">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span class="text-sm font-medium">
                                    Level ini digunakan oleh {{ $userLevel->users_count }} user
                                </span>
                            </div>
                            <p class="text-xs text-yellow-700 mt-1">Level tidak dapat dihapus jika masih digunakan</p>
                        </div>
                    @else
                        <div class="text-sm text-red-600 mt-2">
                            Tindakan ini tidak dapat dibatalkan.
                        </div>
                    @endif
                </div>

                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            @click="hideDeleteModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="button"
                            @click="confirmDelete()"
                            @if($userLevel->users_count > 0) disabled @endif
                            class="flex-1 px-4 py-3 {{ $userLevel->users_count > 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-gradient-to-r from-red-600 to-red-700 text-white hover:from-red-700 hover:to-red-800 shadow-lg hover:shadow-xl' }} rounded-xl transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-trash"></i>
                        <span>Hapus</span>
                    </button>
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
    function userLevelShow() {
        return {
            viewMode: 'grid',
            deleteModal: {
                show: false
            },

            showDeleteModal() {
                this.deleteModal.show = true;
            },

            hideDeleteModal() {
                this.deleteModal.show = false;
            },

            confirmDelete() {
                @if($userLevel->users_count === 0)
                    // Create and submit delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('user-levels.destroy', $userLevel->user_level_id) }}';
                    form.style.display = 'none';

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);

                    this.hideDeleteModal();
                    form.submit();
                @endif
            }
        }
    }
</script>
@endpush
