@extends('layouts.app')

@section('title', 'Edit Level Pengguna - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="userLevelEdit()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('user-levels.index') }}"
               class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left text-gray-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Level Pengguna</h1>
                <p class="text-gray-600 mt-1">Ubah informasi dan hak akses {{ $userLevel->level_name }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('user-levels.show', $userLevel->user_level_id) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-eye"></i>
                <span>Lihat Detail</span>
            </a>
        </div>
    </div>

    <form action="{{ route('user-levels.update', $userLevel->user_level_id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Information Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Informasi Dasar
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">ID:</span>
                        <span class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ $userLevel->user_level_id }}</span>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <!-- Level Name -->
                <div>
                    <label for="level_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Level <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="level_name"
                           name="level_name"
                           value="{{ old('level_name', $userLevel->level_name) }}"
                           placeholder="Contoh: Manager, Supervisor, Staff"
                           x-model="levelName"
                           class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('level_name') border-red-500 @enderror"
                           required>
                    @error('level_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Deskripsi
                    </label>
                    <textarea id="description"
                              name="description"
                              rows="3"
                              placeholder="Jelaskan fungsi dan tanggung jawab level ini..."
                              class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('description') border-red-500 @enderror">{{ old('description', $userLevel->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level Preview -->
                <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                            <i class="fas fa-{{ $userLevel->level_name === 'Admin' ? 'crown' : ($userLevel->level_name === 'Logistik' ? 'boxes' : 'user-tag') }} text-white text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900" x-text="levelName || '{{ $userLevel->level_name }}'"></h4>
                            <p class="text-sm text-gray-600">Level yang sedang diedit</p>
                        </div>
                        <div class="ml-auto">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $userLevel->users_count }} User
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Users Info -->
        @if($userLevel->users_count > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800">Perhatian</h4>
                        <p class="text-sm text-yellow-700">
                            Level ini sedang digunakan oleh <strong>{{ $userLevel->users_count }} user</strong>.
                            Perubahan permissions akan mempengaruhi akses mereka.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Permissions Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-key mr-2 text-yellow-600"></i>
                        Hak Akses & Permissions
                    </h3>
                    <div class="flex items-center space-x-4">
                        <button type="button"
                                @click="selectAllPermissions()"
                                class="px-3 py-1 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm">
                            <i class="fas fa-check-double mr-1"></i>
                            Pilih Semua
                        </button>
                        <button type="button"
                                @click="clearAllPermissions()"
                                class="px-3 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm">
                            <i class="fas fa-times mr-1"></i>
                            Hapus Semua
                        </button>
                        <button type="button"
                                @click="resetToOriginal()"
                                class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm">
                            <i class="fas fa-undo mr-1"></i>
                            Reset
                        </button>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">Pilih modul dan aksi yang dapat diakses oleh level ini</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach($defaultPermissions as $module => $actions)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-gray-600 to-gray-700 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-{{ $module === 'dashboard' ? 'tachometer-alt' : ($module === 'users' ? 'users' : ($module === 'user_levels' ? 'user-cog' : ($module === 'categories' ? 'tags' : ($module === 'suppliers' ? 'truck' : ($module === 'items' ? 'box' : ($module === 'purchase_orders' ? 'shopping-cart' : ($module === 'goods_receiveds' ? 'dolly' : ($module === 'stocks' ? 'warehouse' : ($module === 'transactions' ? 'exchange-alt' : ($module === 'reports' ? 'chart-bar' : ($module === 'activity_logs' ? 'history' : ($module === 'qr_scanner' ? 'qrcode' : 'cog')))))))))))) }} text-white text-sm"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 capitalize">
                                            {{ str_replace('_', ' ', $module) }}
                                        </h4>
                                        <p class="text-xs text-gray-500">
                                            {{ count($actions) }} aksi tersedia
                                        </p>
                                    </div>
                                </div>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           @change="toggleModule('{{ $module }}', $event.target.checked)"
                                           :checked="isModuleSelected('{{ $module }}')"
                                           class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-600">Pilih Semua</span>
                                </label>
                            </div>

                            <div class="space-y-2">
                                @foreach($actions as $action)
                                    <label class="flex items-center p-2 hover:bg-gray-50 rounded-lg transition-colors">
                                        <input type="checkbox"
                                               name="permissions[{{ $module }}][]"
                                               value="{{ $action }}"
                                               x-model="selectedPermissions['{{ $module }}']"
                                               @if(old('permissions'))
                                                   {{ in_array($action, old('permissions')[$module] ?? []) ? 'checked' : '' }}
                                               @else
                                                   {{ isset($userLevel->permissions[$module]) && in_array($action, $userLevel->permissions[$module]) ? 'checked' : '' }}
                                               @endif
                                               class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500">
                                        <div class="ml-3 flex-1">
                                            <span class="text-sm font-medium text-gray-700 capitalize">
                                                {{ $action }}
                                            </span>
                                            <span class="block text-xs text-gray-500">
                                                @switch($action)
                                                    @case('create')
                                                        Dapat menambah data baru
                                                        @break
                                                    @case('read')
                                                        Dapat melihat data
                                                        @break
                                                    @case('update')
                                                        Dapat mengubah data
                                                        @break
                                                    @case('delete')
                                                        Dapat menghapus data
                                                        @break
                                                    @case('approve')
                                                        Dapat menyetujui data
                                                        @break
                                                    @case('export')
                                                        Dapat mengekspor data
                                                        @break
                                                    @case('scan')
                                                        Dapat menggunakan scanner
                                                        @break
                                                    @case('adjust')
                                                        Dapat menyesuaikan stok
                                                        @break
                                                    @default
                                                        Aksi {{ $action }}
                                                @endswitch
                                            </span>
                                        </div>
                                        <i class="fas fa-{{ $action === 'create' ? 'plus' : ($action === 'read' ? 'eye' : ($action === 'update' ? 'edit' : ($action === 'delete' ? 'trash' : ($action === 'approve' ? 'check' : ($action === 'export' ? 'download' : ($action === 'scan' ? 'qrcode' : 'cog')))))) }} text-gray-400"></i>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Permission Summary -->
                <div x-show="getSelectedCount() > 0" class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <h4 class="font-semibold text-blue-900 mb-2 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Ringkasan Permissions
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600" x-text="getSelectedModules()"></div>
                            <div class="text-blue-700">Modul Dipilih</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600" x-text="getSelectedCount()"></div>
                            <div class="text-green-700">Total Permissions</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600" x-text="getReadOnlyCount()"></div>
                            <div class="text-yellow-700">Read Only</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600" x-text="getFullAccessCount()"></div>
                            <div class="text-red-700">Full Access</div>
                        </div>
                    </div>
                </div>

                <!-- Changes Summary -->
                <div x-show="hasChanges()" class="mt-6 p-4 bg-orange-50 rounded-xl border border-orange-200">
                    <h4 class="font-semibold text-orange-900 mb-2 flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Perubahan Terdeteksi
                    </h4>
                    <p class="text-sm text-orange-800">
                        Anda telah melakukan perubahan pada permissions. Pastikan untuk menyimpan perubahan sebelum meninggalkan halaman.
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Templates -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-magic mr-2 text-purple-600"></i>
                    Template Cepat
                </h3>
                <p class="text-sm text-gray-600 mt-1">Gunakan template untuk mengatur permissions dengan cepat</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Admin Template -->
                    <button type="button"
                            @click="applyTemplate('admin')"
                            class="p-4 border-2 border-purple-200 rounded-xl hover:border-purple-400 hover:bg-purple-50 transition-all group">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                                <i class="fas fa-crown text-white text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-1">Admin Penuh</h4>
                            <p class="text-sm text-gray-600">Akses penuh ke semua modul</p>
                        </div>
                    </button>

                    <!-- Manager Template -->
                    <button type="button"
                            @click="applyTemplate('manager')"
                            class="p-4 border-2 border-blue-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                                <i class="fas fa-user-tie text-white text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-1">Manager</h4>
                            <p class="text-sm text-gray-600">Akses ke laporan dan approval</p>
                        </div>
                    </button>

                    <!-- Staff Template -->
                    <button type="button"
                            @click="applyTemplate('staff')"
                            class="p-4 border-2 border-green-200 rounded-xl hover:border-green-400 hover:bg-green-50 transition-all group">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                                <i class="fas fa-user text-white text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-1">Staff</h4>
                            <p class="text-sm text-gray-600">Akses terbatas untuk operasional</p>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Account Timeline -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-clock mr-2 text-green-600"></i>
                    Informasi Level
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-plus text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Level dibuat</p>
                                <p class="text-xs text-gray-500">{{ $userLevel->created_at->format('d M Y H:i') }} ({{ $userLevel->created_at->diffForHumans() }})</p>
                            </div>
                        </div>

                        @if($userLevel->created_at != $userLevel->updated_at)
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-edit text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Terakhir diperbarui</p>
                                    <p class="text-xs text-gray-500">{{ $userLevel->updated_at->format('d M Y H:i') }} ({{ $userLevel->updated_at->diffForHumans() }})</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-users text-purple-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Total Pengguna</p>
                                <p class="text-xs text-gray-500">{{ $userLevel->users_count }} user menggunakan level ini</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-key text-yellow-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Permissions Saat Ini</p>
                                <p class="text-xs text-gray-500">
                                    {{ is_array($userLevel->permissions) ? count($userLevel->permissions) : 0 }} modul dengan akses
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-save"></i>
                    <span>Update Level Pengguna</span>
                </button>

                <a href="{{ route('user-levels.show', $userLevel->user_level_id) }}"
                   class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-eye"></i>
                    <span>Lihat Detail</span>
                </a>

                <a href="{{ route('user-levels.index') }}"
                   class="flex-1 px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>Batal</span>
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function userLevelEdit() {
        return {
            levelName: '{{ $userLevel->level_name }}',
            originalPermissions: @json($userLevel->permissions ?? []),
            selectedPermissions: @json(old('permissions', $userLevel->permissions ?? [])),

            init() {
                // Initialize with current permissions or old values if validation failed
                @if(old('permissions'))
                    this.selectedPermissions = @json(old('permissions'));
                @else
                    this.selectedPermissions = { ...this.originalPermissions };
                @endif
            },

            toggleModule(module, checked) {
                if (checked) {
                    // Select all actions for this module
                    const moduleActions = @json($defaultPermissions);
                    this.selectedPermissions[module] = moduleActions[module] || [];
                } else {
                    // Deselect all actions for this module
                    this.selectedPermissions[module] = [];
                }
            },

            isModuleSelected(module) {
                const moduleActions = @json($defaultPermissions);
                const selectedActions = this.selectedPermissions[module] || [];
                const allActions = moduleActions[module] || [];
                return selectedActions.length === allActions.length && allActions.length > 0;
            },

            selectAllPermissions() {
                const allPermissions = @json($defaultPermissions);
                this.selectedPermissions = { ...allPermissions };
            },

            clearAllPermissions() {
                this.selectedPermissions = {};
            },

            resetToOriginal() {
                this.selectedPermissions = { ...this.originalPermissions };
            },

            hasChanges() {
                return JSON.stringify(this.selectedPermissions) !== JSON.stringify(this.originalPermissions) ||
                       this.levelName !== '{{ $userLevel->level_name }}';
            },

            getSelectedCount() {
                let count = 0;
                Object.values(this.selectedPermissions).forEach(actions => {
                    if (Array.isArray(actions)) {
                        count += actions.length;
                    }
                });
                return count;
            },

            getSelectedModules() {
                return Object.keys(this.selectedPermissions).filter(module =>
                    this.selectedPermissions[module] && this.selectedPermissions[module].length > 0
                ).length;
            },

            getReadOnlyCount() {
                let count = 0;
                Object.entries(this.selectedPermissions).forEach(([module, actions]) => {
                    if (Array.isArray(actions) && actions.length === 1 && actions.includes('read')) {
                        count++;
                    }
                });
                return count;
            },

            getFullAccessCount() {
                const allPermissions = @json($defaultPermissions);
                let count = 0;
                Object.entries(this.selectedPermissions).forEach(([module, actions]) => {
                    if (Array.isArray(actions) && allPermissions[module]) {
                        if (actions.length === allPermissions[module].length) {
                            count++;
                        }
                    }
                });
                return count;
            },

            applyTemplate(template) {
                const templates = {
                    admin: @json($defaultPermissions),
                    manager: {
                        dashboard: ['read'],
                        users: ['read'],
                        user_levels: ['read'],
                        categories: ['create', 'read', 'update'],
                        suppliers: ['create', 'read', 'update'],
                        items: ['create', 'read', 'update'],
                        purchase_orders: ['create', 'read', 'update', 'approve'],
                        goods_receiveds: ['read'],
                        stocks: ['read'],
                        transactions: ['read', 'approve'],
                        reports: ['read', 'export'],
                        activity_logs: ['read'],
                        settings: ['read']
                    },
                    staff: {
                        dashboard: ['read'],
                        items: ['read'],
                        stocks: ['read'],
                        transactions: ['create', 'read'],
                        qr_scanner: ['read', 'scan']
                    }
                };

                this.selectedPermissions = { ...templates[template] };
            }
        }
    }

    // Warn user about unsaved changes
    window.addEventListener('beforeunload', function(e) {
        const alpine = document.querySelector('[x-data]').__x.$data;
        if (alpine.hasChanges()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>
@endpush
