<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Warehouse - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        .logistics-bg {
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600" fill="none"><defs><linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:%234f46e5;stop-opacity:1" /><stop offset="100%" style="stop-color:%237c3aed;stop-opacity:1" /></linearGradient></defs><rect width="800" height="600" fill="url(%23grad1)"/><g opacity="0.1"><path d="M100 150h150v80H100z" fill="white"/><path d="M280 150h150v80H280z" fill="white"/><path d="M460 150h150v80H460z" fill="white"/><path d="M100 260h150v80H100z" fill="white"/><path d="M280 260h150v80H280z" fill="white"/><path d="M460 260h150v80H460z" fill="white"/></g><g opacity="0.2"><circle cx="150" cy="450" r="30" fill="white"/><circle cx="350" cy="450" r="30" fill="white"/><circle cx="550" cy="450" r="30" fill="white"/><path d="M120 420h460v60H120z" fill="white"/></g></svg>');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Left Side - Logistics Image -->
        <div class="hidden lg:flex lg:w-1/2 logistics-bg relative">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-purple-700 opacity-90"></div>
            <div class="relative z-10 flex flex-col justify-center items-center text-white p-12">
                <!-- Logistics Icons -->
                <div class="mb-8">
                    <div class="flex space-x-6 mb-6">
                        <div class="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-truck text-2xl text-white"></i>
                        </div>
                        <div class="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-warehouse text-2xl text-white"></i>
                        </div>
                        <div class="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-boxes text-2xl text-white"></i>
                        </div>
                    </div>
                </div>

                <!-- Title & Description -->
                <div class="text-center">
                    <h1 class="text-4xl font-bold mb-4">F1 Warehouse</h1>
                    <p class="text-xl mb-6 opacity-90">Sistem Manajemen Gudang Terintegrasi</p>
                    <div class="text-lg opacity-80">
                        <div class="flex items-center justify-center mb-2">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>Manajemen Inventory Real-time</span>
                        </div>
                        <div class="flex items-center justify-center mb-2">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>Tracking & Monitoring</span>
                        </div>
                        <div class="flex items-center justify-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>Laporan Analitik</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <!-- Mobile Logo (only visible on mobile) -->
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-xl mb-4">
                        <i class="fas fa-warehouse text-2xl text-white"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">F1 Warehouse</h1>
                </div>

                <!-- Login Card -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Masuk</h2>
                        <p class="text-gray-600">Silakan masuk ke akun Anda</p>
                    </div>

                    <form action="{{ route('login.submit') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Display Validation Errors -->
                        @if ($errors->any())
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex">
                                    <i class="fas fa-exclamation-circle text-red-400 mr-2 mt-0.5"></i>
                                    <div class="text-sm text-red-700">
                                        @foreach ($errors->all() as $error)
                                            <p>{{ $error }}</p>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Success Message -->
                        @if (session('success'))
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex">
                                    <i class="fas fa-check-circle text-green-400 mr-2 mt-0.5"></i>
                                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Username Input -->
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                Username
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    value="{{ old('username') }}"
                                    required
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('username') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                    placeholder="Masukkan username"
                                >
                            </div>
                            @error('username')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    required
                                    class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('password') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                    placeholder="Masukkan password"
                                >
                                <button
                                    type="button"
                                    onclick="togglePassword()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="toggleIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input
                                    id="remember"
                                    name="remember"
                                    type="checkbox"
                                    {{ old('remember') ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                >
                                <label for="remember" class="ml-2 block text-sm text-gray-700">
                                    Ingat saya
                                </label>
                            </div>
                            <a href="#" class="text-sm text-indigo-600 hover:text-indigo-500">
                                Lupa password?
                            </a>
                        </div>

                        <!-- Login Button -->
                        <button
                            type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            id="loginButton"
                        >
                            <span id="buttonText">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Masuk
                            </span>
                        </button>
                    </form>

                    <!-- Footer -->
                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-600">
                            Belum punya akun?
                            <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">
                                Hubungi Administrator
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-500">
                        © 2024 F1 Warehouse System. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        function handleLogin(event) {
            event.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const button = document.querySelector('button[type="submit"]');
            const buttonText = document.getElementById('buttonText');

            // Show loading state
            buttonText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            button.disabled = true;


        }
    </script>
</body>
</html>
