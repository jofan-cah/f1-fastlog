@extends('layouts.app')

@section('title', 'Manajemen User - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="userManager()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen User</h1>
            <p class="text-gray-600 mt-1">Kelola pengguna dan akses sistem</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <button onclick="window.location.href='{{ route('users.create') }}'"
                    class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-plus"></i>
                <span>Tambah User</span>
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <form method="GET" action="{{ route('users.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari username, email, atau nama lengkap..."
                               class="pl-10 pr-4 py-3 w-full bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Level Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Level User</label>
                    <select name="level"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        <option value="">Semua Level</option>
                        @foreach($userLevels as $level)
                            <option value="{{ $level->user_level_id }}"
                                    {{ request('level') == $level->user_level_id ? 'selected' : '' }}>
                                {{ $level->level_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-filter"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('users.index') }}"
                   class="px-6 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Daftar Pengguna</h3>
                <span class="text-sm text-gray-600">Total: {{ $users->total() }} user</span>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bergabung</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-all duration-200 hover:scale-[1.01] hover:shadow-md">
                            <!-- User Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-red-600 to-red-700 rounded-full flex items-center justify-center">
                                        <span class="text-white text-sm font-medium">
                                            {{ strtoupper(substr($user->full_name ?? $user->username, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $user->full_name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->username }}</div>
                                        <div class="text-xs text-gray-400">ID: {{ $user->user_id }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                            </td>

                            <!-- Level -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($user->userLevel?->level_name === 'Admin') bg-purple-100 text-purple-800
                                    @elseif($user->userLevel?->level_name === 'Logistik') bg-blue-100 text-blue-800
                                    @elseif($user->userLevel?->level_name === 'Teknisi') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $user->userLevel?->level_name ?? 'N/A' }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                        {{ $user->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                    {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>

                            <!-- Created At -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $user->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $user->created_at->format('H:i') }}</div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <!-- View Button -->
                                    <a href="{{ route('users.show', $user->user_id) }}"
                                       class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                       title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <!-- Edit Button -->
                                    <a href="{{ route('users.edit', $user->user_id) }}"
                                       class="text-yellow-600 hover:text-yellow-900 p-2 hover:bg-yellow-50 rounded-lg transition-all duration-200"
                                       title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- Toggle Status Button -->
                                    <button type="button"
                                            @click="showToggleModal('{{ $user->user_id }}', '{{ addslashes($user->full_name ?? $user->username) }}', {{ $user->is_active ? 'true' : 'false' }})"
                                            class="p-2 rounded-lg transition-all duration-200 {{ $user->is_active ? 'text-orange-600 hover:text-orange-900 hover:bg-orange-50' : 'text-green-600 hover:text-green-900 hover:bg-green-50' }}"
                                            title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} User">
                                        <i class="fas fa-{{ $user->is_active ? 'user-slash' : 'user-check' }}"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button"
                                            @click="showDeleteModal('{{ $user->user_id }}', '{{ addslashes($user->full_name ?? $user->username) }}')"
                                            class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded-lg transition-all duration-200"
                                            title="Hapus User">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data user</h3>
                                    <p class="text-gray-500 mb-4">Belum ada user yang terdaftar dalam sistem.</p>
                                    <a href="{{ route('users.create') }}"
                                       class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200">
                                        Tambah User Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ $users->firstItem() }} sampai {{ $users->lastItem() }}
                        dari {{ $users->total() }} hasil
                    </div>
                    <div class="flex items-center space-x-2">
                        {{ $users->appends(request()->query())->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        @endif
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
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Hapus User</h3>

                <!-- Message -->
                <p class="text-gray-600 text-center mb-6">
                    Apakah Anda yakin ingin menghapus user <span x-text="deleteModal.userName" class="font-semibold text-gray-900"></span>?
                    <br><span class="text-sm text-red-600 mt-2 block">Tindakan ini tidak dapat dibatalkan.</span>
                </p>

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
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-trash"></i>
                        <span>Hapus</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Status Confirmation Modal -->
    <div x-show="toggleModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hideToggleModal()"
         @keydown.escape.window="hideToggleModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="toggleModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <!-- Icon -->
                <div :class="toggleModal.isActive ? 'w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4' : 'w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4'">
                    <i :class="toggleModal.isActive ? 'fas fa-user-slash text-2xl text-orange-600' : 'fas fa-user-check text-2xl text-green-600'"></i>
                </div>

                <!-- Title -->
                <h3 x-text="toggleModal.isActive ? 'Nonaktifkan User' : 'Aktifkan User'" class="text-xl font-bold text-gray-900 text-center mb-2"></h3>

                <!-- Message -->
                <p class="text-gray-600 text-center mb-6">
                    Apakah Anda yakin ingin <span x-text="toggleModal.isActive ? 'menonaktifkan' : 'mengaktifkan'" class="font-semibold"></span> user <span x-text="toggleModal.userName" class="font-semibold text-gray-900"></span>?
                </p>

                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            @click="hideToggleModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="button"
                            @click="confirmToggle()"
                            :class="toggleModal.isActive ? 'flex-1 px-4 py-3 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-xl hover:from-orange-700 hover:to-orange-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl' : 'flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl'">
                        <i :class="toggleModal.isActive ? 'fas fa-user-slash' : 'fas fa-user-check'"></i>
                        <span x-text="toggleModal.isActive ? 'Nonaktifkan' : 'Aktifkan'"></span>
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
    function userManager() {
        return {
            deleteModal: {
                show: false,
                userId: '',
                userName: ''
            },
            toggleModal: {
                show: false,
                userId: '',
                userName: '',
                isActive: false
            },

            showDeleteModal(userId, userName) {
                this.deleteModal = {
                    show: true,
                    userId: userId,
                    userName: userName
                };
            },

            hideDeleteModal() {
                this.deleteModal.show = false;
                setTimeout(() => {
                    this.deleteModal = {
                        show: false,
                        userId: '',
                        userName: ''
                    };
                }, 300);
            },

            confirmDelete() {
                if (this.deleteModal.userId) {
                    // Create and submit delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ route('users.index') }}/${this.deleteModal.userId}`;
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
            },

            showToggleModal(userId, userName, isActive) {
                this.toggleModal = {
                    show: true,
                    userId: userId,
                    userName: userName,
                    isActive: isActive === true || isActive === 'true'
                };
            },

            hideToggleModal() {
                this.toggleModal.show = false;
                setTimeout(() => {
                    this.toggleModal = {
                        show: false,
                        userId: '',
                        userName: '',
                        isActive: false
                    };
                }, 300);
            },

            confirmToggle() {
                if (this.toggleModal.userId) {
                    // Create and submit toggle form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ route('users.index') }}/${this.toggleModal.userId}/toggle-status`;
                    form.style.display = 'none';

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'PATCH';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);

                    this.hideToggleModal();
                    form.submit();
                }
            }
        }
    }
</script>
@endpush
