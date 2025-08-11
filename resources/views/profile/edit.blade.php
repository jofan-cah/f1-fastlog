@extends('layouts.app')

@section('title', 'Edit Profile - LogistiK Admin')

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-red-600">
                        <i class="fas fa-home mr-2"></i>
                        Dashboard
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-sm font-medium text-gray-500">Edit Profile</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-user-edit text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>
                    <p class="text-gray-600 mt-1">Update informasi profile Anda</p>
                    <div class="flex items-center mt-2 text-sm">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                            {{ $user->getLevelName() }}
                        </span>
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('dashboard') }}"
                    class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <!-- Profile Edit Form -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-user mr-2 text-blue-600"></i>
                        Informasi Profile
                    </h3>
                </div>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="p-6 space-y-6">
                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="username" name="username"
                                value="{{ old('username', $user->username) }}"
                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('username') border-red-500 @enderror"
                                placeholder="Masukkan username">
                            @error('username')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email"
                                value="{{ old('email', $user->email) }}"
                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('email') border-red-500 @enderror"
                                placeholder="Masukkan email">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Full Name -->
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="full_name" name="full_name"
                                value="{{ old('full_name', $user->full_name) }}"
                                class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('full_name') border-red-500 @enderror"
                                placeholder="Masukkan nama lengkap">
                            @error('full_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Section -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">
                                <i class="fas fa-key text-yellow-600 mr-2"></i>
                                Ubah Password (Opsional)
                            </h4>
                            <p class="text-sm text-gray-600 mb-4">Kosongkan jika tidak ingin mengubah password</p>

                            <!-- New Password -->
                            <div class="mb-4">
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Password Baru
                                </label>
                                <input type="password" id="password" name="password"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('password') border-red-500 @enderror"
                                    placeholder="Masukkan password baru (minimal 6 karakter)">
                                @error('password')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Konfirmasi Password Baru
                                </label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    placeholder="Ulangi password baru">
                            </div>
                        </div>

                        <!-- Info Card -->
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                            <div class="flex items-start">
                                <div class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fas fa-info text-blue-600 text-sm"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-blue-900 mb-1">Informasi</h4>
                                    <div class="text-sm text-blue-800 space-y-1">
                                        <p>• Username hanya boleh huruf, angka, underscore (_), dan hyphen (-)</p>
                                        <p>• Email harus valid dan unik</p>
                                        <p>• Password minimal 6 karakter (jika diubah)</p>
                                        <p>• User Level tidak dapat diubah sendiri</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                            <a href="{{ route('dashboard') }}"
                                class="px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                                <i class="fas fa-times"></i>
                                <span>Batal</span>
                            </a>
                            <button type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                                <i class="fas fa-save"></i>
                                <span>Update Profile</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
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

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
