@extends('layouts.app')

@section('title', 'Edit User - LogistiK Admin')

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
                <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
                <p class="text-gray-600 mt-1">Ubah informasi user {{ $user->full_name ?? $user->username }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('users.show', $user->user_id) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-eye"></i>
                <span>Lihat Detail</span>
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Edit Informasi User</h3>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                    <span class="text-xs text-gray-500">ID: {{ $user->user_id }}</span>
                </div>
            </div>
        </div>

        <form action="{{ route('users.update', $user->user_id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- User ID (Read Only) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        User ID
                    </label>
                    <input type="text"
                           id="user_id"
                           value="{{ $user->user_id }}"
                           class="w-full py-3 px-4 bg-gray-100 border border-gray-200 rounded-xl text-gray-500"
                           readonly>
                    <p class="mt-1 text-xs text-gray-500">ID tidak dapat diubah</p>
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="username"
                           name="username"
                           value="{{ old('username', $user->username) }}"
                           placeholder="Masukkan username"
                           class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('username') border-red-500 @enderror"
                           required>
                    @error('username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Full Name -->
            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="full_name"
                       name="full_name"
                       value="{{ old('full_name', $user->full_name) }}"
                       placeholder="Masukkan nama lengkap"
                       class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('full_name') border-red-500 @enderror"
                       required>
                @error('full_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email', $user->email) }}"
                       placeholder="user@example.com"
                       class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('email') border-red-500 @enderror"
                       required>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Section -->
            <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-md font-medium text-gray-900">Ubah Password</h4>
                    <label class="flex items-center">
                        <input type="checkbox" id="change_password" class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500">
                        <span class="ml-2 text-sm text-gray-700">Ubah password</span>
                    </label>
                </div>

                <div id="password_fields" class="grid grid-cols-1 md:grid-cols-2 gap-6" style="display: none;">
                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password Baru
                        </label>
                        <div class="relative">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   placeholder="Masukkan password baru"
                                   class="w-full py-3 px-4 pr-12 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('password') border-red-500 @enderror">
                            <button type="button"
                                    onclick="togglePassword('password')"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i id="password-icon" class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah password</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Konfirmasi Password
                        </label>
                        <div class="relative">
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   placeholder="Konfirmasi password baru"
                                   class="w-full py-3 px-4 pr-12 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                            <button type="button"
                                    onclick="togglePassword('password_confirmation')"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i id="password_confirmation-icon" class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Level & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- User Level -->
                <div>
                    <label for="user_level_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Level User <span class="text-red-500">*</span>
                    </label>
                    <select id="user_level_id"
                            name="user_level_id"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all @error('user_level_id') border-red-500 @enderror"
                            required>
                        <option value="">Pilih Level User</option>
                        @foreach($userLevels as $level)
                            <option value="{{ $level->user_level_id }}"
                                    {{ old('user_level_id', $user->user_level_id) == $level->user_level_id ? 'selected' : '' }}>
                                {{ $level->level_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_level_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <div class="flex items-center space-x-6 mt-3">
                        <label class="flex items-center">
                            <input type="radio"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', $user->is_active) == '1' ? 'checked' : '' }}
                                   class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio"
                                   name="is_active"
                                   value="0"
                                   {{ old('is_active', $user->is_active) == '0' ? 'checked' : '' }}
                                   class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Tidak Aktif</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Account Info -->
            <div class="border border-gray-200 rounded-xl p-4 bg-blue-50">
                <h4 class="text-md font-medium text-gray-900 mb-3">Informasi Akun</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-700">Bergabung:</span>
                        <span class="text-gray-600">{{ $user->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Terakhir diubah:</span>
                        <span class="text-gray-600">{{ $user->updated_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                    <i class="fas fa-save"></i>
                    <span>Update User</span>
                </button>

                <a href="{{ route('users.show', $user->user_id) }}"
                   class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-eye"></i>
                    <span>Lihat Detail</span>
                </a>

                <a href="{{ route('users.index') }}"
                   class="px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>Batal</span>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-icon');

        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Toggle password fields
    document.getElementById('change_password').addEventListener('change', function() {
        const passwordFields = document.getElementById('password_fields');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');

        if (this.checked) {
            passwordFields.style.display = 'grid';
            passwordInput.required = true;
            confirmPasswordInput.required = true;
        } else {
            passwordFields.style.display = 'none';
            passwordInput.required = false;
            confirmPasswordInput.required = false;
            passwordInput.value = '';
            confirmPasswordInput.value = '';
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const changePassword = document.getElementById('change_password').checked;

        if (changePassword) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak sama!');
                return false;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password minimal 8 karakter!');
                return false;
            }
        }
    });
</script>
@endpush
