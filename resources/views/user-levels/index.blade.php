@extends('layouts.app')

@section('title', 'Level Pengguna - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="userLevelManager()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Level Pengguna</h1>
            <p class="text-gray-600 mt-1">Kelola level dan hak akses pengguna sistem</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <button onclick="window.location.href='{{ route('user-levels.create') }}'"
                    class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-plus"></i>
                <span>Tambah Level</span>
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <form method="GET" action="{{ route('user-levels.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari nama level atau deskripsi..."
                               class="pl-10 pr-4 py-3 w-full bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="flex items-end">
                    <div class="flex flex-wrap gap-3">
                        <button type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('user-levels.index') }}"
                           class="px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-times"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- User Levels Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($userLevels as $level)
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 hover:scale-105">
                <!-- Header -->
                <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                                <i class="fas fa-{{ $level->level_name === 'Admin' ? 'crown' : ($level->level_name === 'Logistik' ? 'boxes' : 'tools') }} text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $level->level_name }}</h3>
                                <p class="text-sm text-gray-500">ID: {{ $level->user_level_id }}</p>
                            </div>
                        </div>

                        <!-- Dropdown Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="p-2 hover:bg-gray-200 rounded-lg transition-colors">
                                <i class="fas fa-ellipsis-v text-gray-600"></i>
                            </button>

                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-10"
                                 style="display: none;">
                                <a href="{{ route('user-levels.show', $level->user_level_id) }}"
                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-eye mr-3 text-blue-600"></i>
                                    Lihat Detail
                                </a>
                                <a href="{{ route('user-levels.edit', $level->user_level_id) }}"
                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-edit mr-3 text-yellow-600"></i>
                                    Edit Level
                                </a>
                                <hr class="my-2">
                                <button @click="showDeleteModal('{{ $level->user_level_id }}', '{{ addslashes($level->level_name) }}', {{ $level->users_count }})"
                                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-trash mr-3 text-red-500"></i>
                                    Hapus Level
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <!-- Description -->
                    <div class="mb-4">
                        <p class="text-gray-600 text-sm leading-relaxed">
                            {{ $level->description ?? 'Tidak ada deskripsi' }}
                        </p>
                    </div>

                    <!-- Statistics -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="text-center p-3 bg-blue-50 rounded-xl">
                            <div class="text-2xl font-bold text-blue-600">{{ $level->users_count }}</div>
                            <div class="text-xs text-gray-600">Total User</div>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded-xl">
                            <div class="text-2xl font-bold text-green-600">
                                {{ is_array($level->permissions) ? count($level->permissions) : 0 }}
                            </div>
                            <div class="text-xs text-gray-600">Modul Akses</div>
                        </div>
                    </div>

                    <!-- Permissions Preview -->
                    @if($level->permissions && is_array($level->permissions))
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Akses Modul:</h4>
                            <div class="flex flex-wrap gap-1">
                                @php $count = 0; @endphp
                                @foreach(array_keys($level->permissions) as $module)
                                    @if($count < 3)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst(str_replace('_', ' ', $module)) }}
                                        </span>
                                        @php $count++; @endphp
                                    @else
                                        @break
                                    @endif
                                @endforeach
                                @if(count($level->permissions) > 3)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        +{{ count($level->permissions) - 3 }} lainnya
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex gap-2 pt-4 border-t border-gray-100">
                        <a href="{{ route('user-levels.show', $level->user_level_id) }}"
                           class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 flex items-center justify-center space-x-2 text-sm">
                            <i class="fas fa-eye"></i>
                            <span>Detail</span>
                        </a>
                        <a href="{{ route('user-levels.edit', $level->user_level_id) }}"
                           class="flex-1 px-3 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-all duration-200 flex items-center justify-center space-x-2 text-sm">
                            <i class="fas fa-edit"></i>
                            <span>Edit</span>
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Dibuat: {{ $level->created_at->format('d M Y') }}</span>
                        <span>{{ $level->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-12 text-center">
                    <i class="fas fa-users-cog text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">Tidak ada level pengguna</h3>
                    <p class="text-gray-500 mb-6">Belum ada level pengguna yang terdaftar dalam sistem.</p>
                    <a href="{{ route('user-levels.create') }}"
                       class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 inline-flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Tambah Level Pertama</span>
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($userLevels->hasPages())
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Menampilkan {{ $userLevels->firstItem() }} sampai {{ $userLevels->lastItem() }}
                    dari {{ $userLevels->total() }} hasil
                </div>
                <div class="flex items-center space-x-2">
                    {{ $userLevels->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    @endif

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
                        Apakah Anda yakin ingin menghapus level <span x-text="deleteModal.levelName" class="font-semibold text-gray-900"></span>?
                    </p>

                    <!-- Warning if has users -->
                    <div x-show="deleteModal.userCount > 0" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-center justify-center space-x-2 text-yellow-800">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="text-sm font-medium">
                                Level ini digunakan oleh <span x-text="deleteModal.userCount"></span> user
                            </span>
                        </div>
                        <p class="text-xs text-yellow-700 mt-1">Level tidak dapat dihapus jika masih digunakan</p>
                    </div>

                    <div x-show="deleteModal.userCount === 0" class="text-sm text-red-600 mt-2">
                        Tindakan ini tidak dapat dibatalkan.
                    </div>
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
                            :disabled="deleteModal.userCount > 0"
                            :class="deleteModal.userCount > 0 ? 'flex-1 px-4 py-3 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed flex items-center justify-center space-x-2' : 'flex-1 px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl'">
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
    function userLevelManager() {
        return {
            deleteModal: {
                show: false,
                levelId: '',
                levelName: '',
                userCount: 0
            },

            showDeleteModal(levelId, levelName, userCount) {
                this.deleteModal = {
                    show: true,
                    levelId: levelId,
                    levelName: levelName,
                    userCount: parseInt(userCount)
                };
            },

            hideDeleteModal() {
                this.deleteModal.show = false;
                setTimeout(() => {
                    this.deleteModal = {
                        show: false,
                        levelId: '',
                        levelName: '',
                        userCount: 0
                    };
                }, 300);
            },

            confirmDelete() {
                if (this.deleteModal.levelId && this.deleteModal.userCount === 0) {
                    // Create and submit delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ route('user-levels.index') }}/${this.deleteModal.levelId}`;
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
                }
            }
        }
    }
</script>
@endpush
