<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-swing {
            animation: swing 2s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }

        @keyframes swing {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(3deg); }
        }

        .gradient-text {
            background: linear-gradient(135deg, #ffffff, #d1d5db);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .search-animation {
            animation: searchPulse 2s ease-in-out infinite;
        }

        @keyframes searchPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-black to-gray-800 min-h-screen flex items-center justify-center p-4">

    <!-- Background Pattern -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gray-600/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 left-1/3 w-64 h-64 bg-red-600/5 rounded-full blur-3xl"></div>
    </div>

    <!-- Main Container -->
    <div class="relative z-10 max-w-4xl mx-auto text-center">

        <!-- Error Icon -->
        <div class="mb-8 relative">
            <div class="inline-flex items-center justify-center w-32 h-32 md:w-40 md:h-40 rounded-full glass-effect animate-float">
                <i class="fas fa-search text-6xl md:text-7xl text-gray-400 search-animation"></i>
            </div>
            <div class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 rounded-full animate-swing flex items-center justify-center">
                <i class="fas fa-question text-white text-sm"></i>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-6">
            <h1 class="text-8xl md:text-9xl font-bold gradient-text mb-2">404</h1>
            <div class="h-1 w-24 bg-gradient-to-r from-gray-600 to-gray-400 mx-auto rounded-full"></div>
        </div>

        <!-- Error Message -->
        <div class="mb-8 space-y-4">
            <h2 class="text-2xl md:text-3xl font-semibold text-white mb-4">
                Halaman Tidak Ditemukan
            </h2>
            <p class="text-gray-400 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed">
                Sepertinya halaman yang Anda cari telah dipindahkan, dihapus, atau mungkin tidak pernah ada.
                Mari kita bantu Anda menemukan jalan kembali.
            </p>
        </div>

        <!-- Search Box -->
        <div class="mb-8 max-w-md mx-auto">
            <div class="relative">
                <input type="text"
                       placeholder="Cari halaman..."
                       class="w-full px-6 py-4 bg-gray-800/50 border border-gray-600 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent backdrop-blur-sm">
                <button class="absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors">
                    <i class="fas fa-search text-gray-300"></i>
                </button>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
            <a href="{{ url('/') }}"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-700 to-gray-800 text-white font-medium rounded-xl hover:from-gray-600 hover:to-gray-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                <i class="fas fa-home mr-2"></i>
                Kembali ke Beranda
            </a>
            <button onclick="history.back()"
                    class="inline-flex items-center px-6 py-3 bg-transparent text-gray-300 font-medium rounded-xl hover:bg-gray-800/50 transition-all duration-300 border border-gray-600 hover:border-gray-500">
                <i class="fas fa-arrow-left mr-2"></i>
                Halaman Sebelumnya
            </button>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <a href="{{ url('/') }}" class="glass-effect rounded-xl p-6 hover:bg-white/10 transition-all duration-300 group">
                <div class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-gray-600 transition-colors">
                    <i class="fas fa-home text-gray-300"></i>
                </div>
                <h3 class="text-white font-medium mb-2">Dashboard</h3>
                <p class="text-gray-400 text-sm">Kembali ke halaman utama</p>
            </a>

            <a href="#" class="glass-effect rounded-xl p-6 hover:bg-white/10 transition-all duration-300 group">
                <div class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-gray-600 transition-colors">
                    <i class="fas fa-headset text-gray-300"></i>
                </div>
                <h3 class="text-white font-medium mb-2">Bantuan</h3>
                <p class="text-gray-400 text-sm">Hubungi tim support</p>
            </a>

            <a href="#" class="glass-effect rounded-xl p-6 hover:bg-white/10 transition-all duration-300 group">
                <div class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-gray-600 transition-colors">
                    <i class="fas fa-map text-gray-300"></i>
                </div>
                <h3 class="text-white font-medium mb-2">Site Map</h3>
                <p class="text-gray-400 text-sm">Jelajahi semua halaman</p>
            </a>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-800">
            <p class="text-gray-500 text-sm">
                Error Code: 404 | {{ config('app.name') }} &copy; {{ date('Y') }}
            </p>
        </div>
    </div>

    <!-- Floating Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-gray-500/30 rounded-full animate-ping"></div>
        <div class="absolute top-3/4 right-1/4 w-1 h-1 bg-white/30 rounded-full animate-pulse"></div>
        <div class="absolute top-1/2 right-1/3 w-3 h-3 bg-gray-600/20 rounded-full animate-bounce" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-1/4 left-1/3 w-2 h-2 bg-red-500/20 rounded-full animate-pulse" style="animation-delay: 2s;"></div>
    </div>

</body>
</html>
