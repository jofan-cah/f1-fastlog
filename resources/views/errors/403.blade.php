<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .gradient-text {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-black to-gray-900 min-h-screen flex items-center justify-center p-4">

    <!-- Background Pattern -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-red-600/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gray-600/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
    </div>

    <!-- Main Container -->
    <div class="relative z-10 max-w-4xl mx-auto text-center">

        <!-- Error Icon -->
        <div class="mb-8 relative">
            <div class="inline-flex items-center justify-center w-32 h-32 md:w-40 md:h-40 rounded-full glass-effect animate-float">
                <i class="fas fa-shield-alt text-6xl md:text-7xl gradient-text"></i>
            </div>
            <div class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 rounded-full animate-pulse-slow flex items-center justify-center">
                <i class="fas fa-exclamation text-white text-sm"></i>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-6">
            <h1 class="text-8xl md:text-9xl font-bold gradient-text mb-2">403</h1>
            <div class="h-1 w-24 bg-gradient-to-r from-red-600 to-red-400 mx-auto rounded-full"></div>
        </div>

        <!-- Error Message -->
        <div class="mb-8 space-y-4">
            <h2 class="text-2xl md:text-3xl font-semibold text-white mb-4">
                Akses Ditolak
            </h2>
            <p class="text-gray-400 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed">
                Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
                Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
            <a href="{{ url('/') }}"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white font-medium rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                <i class="fas fa-home mr-2"></i>
                Kembali ke Beranda
            </a>
            <button onclick="history.back()"
                    class="inline-flex items-center px-6 py-3 bg-gray-800 text-white font-medium rounded-xl hover:bg-gray-700 transition-all duration-300 border border-gray-600 hover:border-gray-500">
                <i class="fas fa-arrow-left mr-2"></i>
                Halaman Sebelumnya
            </button>
        </div>

        <!-- Additional Info -->
        <div class="glass-effect rounded-2xl p-6 max-w-2xl mx-auto">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-600/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-info-circle text-red-400"></i>
                    </div>
                </div>
                <div class="text-left">
                    <h3 class="text-white font-medium mb-2">Mengapa ini terjadi?</h3>
                    <ul class="text-gray-400 text-sm space-y-1">
                        <li>• Anda tidak memiliki permission yang diperlukan</li>
                        <li>• Session login Anda mungkin telah berakhir</li>
                        <li>• Level akses Anda tidak mencukupi untuk halaman ini</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-800">
            <p class="text-gray-500 text-sm">
                Error Code: 403 | {{ config('app.name') }} &copy; {{ date('Y') }}
            </p>
        </div>
    </div>

    <!-- Floating Particles -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-red-500/30 rounded-full animate-ping"></div>
        <div class="absolute top-3/4 right-1/4 w-1 h-1 bg-white/30 rounded-full animate-pulse"></div>
        <div class="absolute top-1/2 left-3/4 w-3 h-3 bg-gray-500/20 rounded-full animate-bounce"></div>
    </div>

</body>
</html>
