<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Kesalahan Server</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-glitch {
            animation: glitch 2s infinite;
        }

        .animate-spin-slow {
            animation: spin 3s linear infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }

        @keyframes glitch {
            0%, 90%, 100% { transform: translate(0); }
            10% { transform: translate(-2px, -2px); }
            20% { transform: translate(2px, 2px); }
            30% { transform: translate(-2px, 2px); }
            40% { transform: translate(2px, -2px); }
            50% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            70% { transform: translate(-2px, 2px); }
            80% { transform: translate(2px, -2px); }
        }

        .gradient-text {
            background: linear-gradient(135deg, #ef4444, #fca5a5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .code-line {
            animation: typing 3s steps(30) infinite;
            white-space: nowrap;
            overflow: hidden;
            border-right: 2px solid #ef4444;
        }

        @keyframes typing {
            0%, 50% { width: 0; }
            100% { width: 100%; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-red-900/20 to-black min-h-screen flex items-center justify-center p-4">

    <!-- Background Pattern -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-red-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gray-600/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-red-500/5 rounded-full blur-3xl"></div>
    </div>

    <!-- Main Container -->
    <div class="relative z-10 max-w-4xl mx-auto text-center">

        <!-- Error Icon -->
        <div class="mb-8 relative">
            <div class="inline-flex items-center justify-center w-32 h-32 md:w-40 md:h-40 rounded-full glass-effect animate-float">
                <div class="relative">
                    <i class="fas fa-server text-6xl md:text-7xl text-red-400 animate-glitch"></i>
                    <div class="absolute top-0 right-0 w-6 h-6 bg-red-500 rounded-full animate-ping"></div>
                </div>
            </div>
            <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
                <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-red-400 rounded-full animate-bounce" style="animation-delay: 0.1s;"></div>
                    <div class="w-2 h-2 bg-red-300 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                </div>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-6">
            <h1 class="text-8xl md:text-9xl font-bold gradient-text mb-2 animate-glitch">500</h1>
            <div class="h-1 w-24 bg-gradient-to-r from-red-600 to-red-400 mx-auto rounded-full animate-pulse"></div>
        </div>

        <!-- Error Message -->
        <div class="mb-8 space-y-4">
            <h2 class="text-2xl md:text-3xl font-semibold text-white mb-4">
                Kesalahan Server Internal
            </h2>
            <p class="text-gray-400 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed">
                Oops! Terjadi kesalahan pada server kami. Tim teknis sudah diberitahu dan sedang
                bekerja untuk memperbaiki masalah ini. Silakan coba lagi dalam beberapa saat.
            </p>
        </div>

        <!-- Code Display -->
        <div class="mb-8 max-w-2xl mx-auto">
            <div class="glass-effect rounded-xl p-6 text-left">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <span class="text-gray-400 text-sm">server.log</span>
                </div>
                <div class="space-y-2 font-mono text-sm">
                    <div class="text-gray-500">[{{ date('Y-m-d H:i:s') }}] ERROR:</div>
                    <div class="text-red-400 code-line">Internal Server Error - Code 500</div>
                    <div class="text-gray-400">Debugging in progress...</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
            <button onclick="location.reload()"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white font-medium rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                <i class="fas fa-redo mr-2"></i>
                Coba Lagi
            </button>
            <a href="{{ url('/') }}"
               class="inline-flex items-center px-6 py-3 bg-gray-800 text-white font-medium rounded-xl hover:bg-gray-700 transition-all duration-300 border border-gray-600 hover:border-gray-500">
                <i class="fas fa-home mr-2"></i>
                Kembali ke Beranda
            </a>
        </div>

        <!-- Status Info -->
        <div class="glass-effect rounded-2xl p-6 max-w-2xl mx-auto mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-12 h-12 bg-red-600/20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-exclamation-triangle text-red-400"></i>
                    </div>
                    <h3 class="text-white font-medium mb-1">Status</h3>
                    <p class="text-red-400 text-sm">Bermasalah</p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 bg-yellow-600/20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-tools text-yellow-400 animate-spin-slow"></i>
                    </div>
                    <h3 class="text-white font-medium mb-1">Perbaikan</h3>
                    <p class="text-yellow-400 text-sm">Dalam Proses</p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-600/20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-clock text-blue-400"></i>
                    </div>
                    <h3 class="text-white font-medium mb-1">Estimasi</h3>
                    <p class="text-blue-400 text-sm">< 30 menit</p>
                </div>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="glass-effect rounded-2xl p-6 max-w-2xl mx-auto">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gray-700/50 rounded-full flex items-center justify-center">
                        <i class="fas fa-info-circle text-gray-400"></i>
                    </div>
                </div>
                <div class="text-left">
                    <h3 class="text-white font-medium mb-2">Apa yang bisa Anda lakukan?</h3>
                    <ul class="text-gray-400 text-sm space-y-1">
                        <li>• Tunggu beberapa menit dan coba lagi</li>
                        <li>• Periksa koneksi internet Anda</li>
                        <li>• Hubungi administrator jika masalah berlanjut</li>
                        <li>• Simpan pekerjaan Anda untuk menghindari kehilangan data</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-800">
            <p class="text-gray-500 text-sm">
                Error Code: 500 | Error ID: {{ uniqid() }} | {{ config('app.name') }} &copy; {{ date('Y') }}
            </p>
        </div>
    </div>

    <!-- Floating Error Indicators -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-red-500/50 rounded-full animate-ping"></div>
        <div class="absolute top-3/4 right-1/4 w-1 h-1 bg-red-400/30 rounded-full animate-pulse"></div>
        <div class="absolute top-1/2 left-3/4 w-3 h-3 bg-red-600/20 rounded-full animate-bounce"></div>
        <div class="absolute bottom-1/4 right-1/3 w-2 h-2 bg-yellow-500/30 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
    </div>

</body>
</html>
