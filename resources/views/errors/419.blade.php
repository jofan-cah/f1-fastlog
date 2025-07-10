<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Session Expired</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-clock {
            animation: clockTick 2s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @keyframes clockTick {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(10deg); }
        }

        .gradient-text {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .timer-circle {
            animation: timerRotate 1s linear infinite;
        }

        @keyframes timerRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-blue-900/20 to-black min-h-screen flex items-center justify-center p-4">

    <!-- Background Pattern -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-600/15 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-600/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 left-1/3 w-64 h-64 bg-cyan-500/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Main Container -->
    <div class="relative z-10 max-w-4xl mx-auto text-center">

        <!-- Error Icon -->
        <div class="mb-8 relative">
            <div class="inline-flex items-center justify-center w-32 h-32 md:w-40 md:h-40 rounded-full glass-effect animate-float">
                <i class="fas fa-clock text-6xl md:text-7xl text-blue-400 animate-clock"></i>
            </div>
            <div class="absolute -top-2 -right-2 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <i class="fas fa-hourglass-end text-white text-sm timer-circle"></i>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-6">
            <h1 class="text-8xl md:text-9xl font-bold gradient-text mb-2">419</h1>
            <div class="h-1 w-24 bg-gradient-to-r from-blue-600 to-indigo-500 mx-auto rounded-full"></div>
        </div>

        <!-- Error Message -->
        <div class="mb-8 space-y-4">
            <h2 class="text-2xl md:text-3xl font-semibold text-white mb-4">
                Session Telah Berakhir
            </h2>
            <p class="text-gray-400 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed">
                Sesi login Anda telah habis untuk menjaga keamanan akun.
                Silakan login kembali untuk melanjutkan aktivitas Anda.
            </p>
        </div>

        <!-- Session Info -->
        <div class="mb-8 max-w-md mx-auto">
            <div class="glass-effect rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-white font-medium">Informasi Session</span>
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Durasi Session:</span>
                        <span class="text-blue-400">2 jam</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Berakhir pada:</span>
                        <span class="text-blue-400">{{ date('H:i') }} WIB</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Alasan:</span>
                        <span class="text-blue-400">Timeout</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
            <a href="{{ route('login') }}"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Login Kembali
            </a>
            <a href="{{ url('/') }}"
               class="inline-flex items-center px-6 py-3 bg-gray-800 text-white font-medium rounded-xl hover:bg-gray-700 transition-all duration-300 border border-gray-600 hover:border-gray-500">
                <i class="fas fa-home mr-2"></i>
                Ke Beranda
            </a>
        </div>

        <!-- Security Features -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="glass-effect rounded-xl p-6 hover:bg-white/10 transition-all duration-300">
                <div class="w-12 h-12 bg-blue-600/20 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-shield-alt text-blue-400"></i>
                </div>
                <h3 class="text-white font-medium mb-2">Keamanan Tinggi</h3>
                <p class="text-gray-400 text-sm">Session timeout otomatis untuk perlindungan akun</p>
            </div>

            <div class="glass-effect rounded-xl p-6 hover:bg-white/10 transition-all duration-300">
                <div class="w-12 h-12 bg-blue-600/20 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-key text-blue-400"></i>
                </div>
                <h3 class="text-white font-medium mb-2">Login Aman</h3>
                <p class="text-gray-400 text-sm">Enkripsi data dan autentikasi berlapis</p>
            </div>

            <div class="glass-effect rounded-xl p-6 hover:bg-white/10 transition-all duration-300">
                <div class="w-12 h-12 bg-blue-600/20 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-clock text-blue-400"></i>
                </div>
                <h3 class="text-white font-medium mb-2">Auto Logout</h3>
                <p class="text-gray-400 text-sm">Logout otomatis saat tidak aktif</p>
            </div>
        </div>

        <!-- Tips -->
        <div class="glass-effect rounded-2xl p-6 max-w-2xl mx-auto">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-600/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-lightbulb text-blue-400"></i>
                    </div>
                </div>
                <div class="text-left">
                    <h3 class="text-white font-medium mb-2">Tips Keamanan</h3>
                    <ul class="text-gray-400 text-sm space-y-1">
                        <li>• Jangan tinggalkan komputer dalam keadaan login</li>
                        <li>• Selalu logout setelah selesai menggunakan sistem</li>
                        <li>• Gunakan password yang kuat dan unik</li>
                        <li>• Aktifkan "Remember Me" hanya di perangkat pribadi</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-800">
            <p class="text-gray-500 text-sm">
                Error Code: 419 | Session ID: {{ session()->getId() ?? 'N/A' }} | {{ config('app.name') }} &copy; {{ date('Y') }}
            </p>
        </div>
    </div>

    <!-- Floating Time Icons -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-blue-500/30 rounded-full animate-ping"></div>
        <div class="absolute top-3/4 right-1/4 w-1 h-1 bg-indigo-400/30 rounded-full animate-pulse"></div>
        <div class="absolute top-1/2 left-3/4 w-3 h-3 bg-cyan-600/20 rounded-full animate-bounce"></div>
        <div class="absolute bottom-1/4 right-1/3 w-2 h-2 bg-blue-600/40 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
    </div>

</body>
</html>
