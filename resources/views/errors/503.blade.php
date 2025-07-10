<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Layanan Tidak Tersedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        .animate-float {
            animation: float 4s ease-in-out infinite;
        }

        .animate-maintenance {
            animation: maintenance 2s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes maintenance {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-5deg); }
            75% { transform: rotate(5deg); }
        }

        .gradient-text {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .progress-bar {
            animation: progressFill 3s ease-in-out infinite;
        }

        @keyframes progressFill {
            0% { width: 0%; }
            50% { width: 75%; }
            100% { width: 0%; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-yellow-900/20 to-black min-h-screen flex items-center justify-center p-4">

    <!-- Background Pattern -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-yellow-600/15 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-orange-600/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 left-1/3 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Main Container -->
    <div class="relative z-10 max-w-4xl mx-auto text-center">

        <!-- Error Icon -->
        <div class="mb-8 relative">
            <div class="inline-flex items-center justify-center w-32 h-32 md:w-40 md:h-40 rounded-full glass-effect animate-float">
                <i class="fas fa-tools text-6xl md:text-7xl text-yellow-500 animate-maintenance"></i>
            </div>
            <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center animate-pulse">
                <i class="fas fa-wrench text-white text-sm"></i>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-6">
            <h1 class="text-8xl md:text-9xl font-bold gradient-text mb-2">503</h1>
            <div class="h-1 w-24 bg-gradient-to-r from-yellow-600 to-orange-500 mx-auto rounded-full"></div>
        </div>

        <!-- Error Message -->
        <div class="mb-8 space-y-4">
            <h2 class="text-2xl md:text-3xl font-semibold text-white mb-4">
                Layanan Sedang Maintenance
            </h2>
            <p class="text-gray-400 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed">
                Kami sedang melakukan pemeliharaan rutin untuk meningkatkan layanan.
                Sistem akan kembali online dalam waktu singkat. Terima kasih atas kesabaran Anda.
            </p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8 max-w-md mx-auto">
            <div class="glass-effect rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-white font-medium">Progress Maintenance</span>
                    <span class="text-yellow-400 text-sm">Estimasi: 2 jam</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-3 overflow-hidden">
                    <div class="progress-bar h-full bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full"></div>
                </div>
                <div class="mt-3 text-center">
                    <span class="text-gray-400 text-sm">Maintenance akan selesai sekitar {{ date('H:i', strtotime('+2 hours')) }} WIB</span>
                </div>
            </div>
        </div>

        <!-- Status Updates -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
            <div class="glass-effect rounded-xl p-4">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-white text-sm">Database: Online</span>
                </div>
            </div>
            <div class="glass-effect rounded-xl p-4">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                    <span class="text-white text-sm">Web Server: Maintenance</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
            <button onclick="location.reload()"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-yellow-600 to-orange-600 text-white font-medium rounded-xl hover:from-yellow-700 hover:to-orange-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                <i class="fas fa-redo mr-2"></i>
                Cek Status
            </button>
            <a href="#" onclick="subscribeNotification()"
               class="inline-flex items-center px-6 py-3 bg-gray-800 text-white font-medium rounded-xl hover:bg-gray-700 transition-all duration-300 border border-gray-600 hover:border-gray-500">
                <i class="fas fa-bell mr-2"></i>
                Notifikasi Selesai
            </a>
        </div>

        <!-- Maintenance Details -->
        <div class="glass-effect rounded-2xl p-6 max-w-2xl mx-auto mb-8">
            <h3 class="text-white font-medium mb-4 flex items-center">
                <i class="fas fa-info-circle text-yellow-500 mr-2"></i>
                Detail Maintenance
            </h3>
            <div class="space-y-3 text-left">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check text-green-400 mt-1"></i>
                    <span class="text-gray-300 text-sm">Update keamanan sistem - Selesai</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-spinner animate-spin text-yellow-400 mt-1"></i>
                    <span class="text-gray-300 text-sm">Optimasi database - Dalam proses</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-clock text-gray-500 mt-1"></i>
                    <span class="text-gray-400 text-sm">Deployment fitur baru - Menunggu</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-clock text-gray-500 mt-1"></i>
                    <span class="text-gray-400 text-sm">Testing sistem - Menunggu</span>
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="glass-effect rounded-2xl p-6 max-w-2xl mx-auto">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-600/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-headset text-yellow-400"></i>
                    </div>
                </div>
                <div class="text-left">
                    <h3 class="text-white font-medium mb-2">Butuh Bantuan Darurat?</h3>
                    <p class="text-gray-400 text-sm mb-3">
                        Jika ada keperluan mendesak, Anda dapat menghubungi tim support kami.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <a href="mailto:support@company.com" class="text-yellow-400 text-sm hover:text-yellow-300 transition-colors">
                            <i class="fas fa-envelope mr-1"></i>
                            support@company.com
                        </a>
                        <a href="tel:+6281234567890" class="text-yellow-400 text-sm hover:text-yellow-300 transition-colors">
                            <i class="fas fa-phone mr-1"></i>
                            +62 812-3456-7890
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-800">
            <p class="text-gray-500 text-sm">
                Error Code: 503 | Maintenance ID: {{ uniqid() }} | {{ config('app.name') }} &copy; {{ date('Y') }}
            </p>
        </div>
    </div>

    <!-- Floating Maintenance Icons -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-yellow-500/30 rounded-full animate-ping"></div>
        <div class="absolute top-3/4 right-1/4 w-1 h-1 bg-orange-400/30 rounded-full animate-pulse"></div>
        <div class="absolute top-1/2 left-3/4 w-3 h-3 bg-amber-600/20 rounded-full animate-bounce"></div>
        <div class="absolute bottom-1/4 right-1/3 w-2 h-2 bg-yellow-600/40 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <script>
        function subscribeNotification() {
            alert('Notifikasi akan dikirim ke email Anda saat maintenance selesai.');
        }

        // Auto refresh setiap 5 menit
        setTimeout(() => {
            location.reload();
        }, 300000);
    </script>

</body>
</html>
