@extends('layouts.app')

@section('title', 'Dashboard - LogistiK Admin')

@section('content')
<!-- Welcome Section -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Selamat Datang, {{ Auth::user()->name ?? 'Admin' }}! 👋</h1>
    <p class="text-gray-600">Berikut adalah ringkasan sistem logistik Anda hari ini</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Barang -->
    <div class="bg-white stat-card p-6 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total Barang</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalBarang ?? '2,847' }}</p>
                <div class="flex items-center mt-2">
                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                    <span class="text-green-600 text-sm font-medium">+12% dari bulan lalu</span>
                </div>
            </div>
            <div class="w-14 h-14 gradient-red rounded-2xl flex items-center justify-center">
                <i class="fas fa-box text-white text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Stok Tersedia -->
    <div class="bg-white stat-card p-6 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Stok Tersedia</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stokTersedia ?? '1,234' }}</p>
                <div class="flex items-center mt-2">
                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                    <span class="text-green-600 text-sm font-medium">+5% dari minggu lalu</span>
                </div>
            </div>
            <div class="w-14 h-14 gradient-dark rounded-2xl flex items-center justify-center">
                <i class="fas fa-warehouse text-white text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Transaksi Hari Ini -->
    <div class="bg-white stat-card p-6 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Transaksi Hari Ini</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $transaksiHariIni ?? '156' }}</p>
                <div class="flex items-center mt-2">
                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                    <span class="text-green-600 text-sm font-medium">+8% dari kemarin</span>
                </div>
            </div>
            <div class="w-14 h-14 gradient-gray rounded-2xl flex items-center justify-center">
                <i class="fas fa-bolt text-white text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Nilai Inventori -->
    <div class="bg-white stat-card p-6 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Nilai Inventori</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $nilaiInventori ?? 'Rp 2.4M' }}</p>
                <div class="flex items-center mt-2">
                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                    <span class="text-green-600 text-sm font-medium">+15% dari bulan lalu</span>
                </div>
            </div>
            <div class="w-14 h-14 bg-gray-800 rounded-2xl flex items-center justify-center">
                <i class="fas fa-dollar-sign text-white text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Alert Section -->
@if($alertStok ?? true)
<div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-2xl mb-8">
    <div class="flex items-center">
        <i class="fas fa-exclamation-triangle text-red-500 text-xl mr-3"></i>
        <div>
            <h3 class="text-lg font-semibold text-red-800">⚠️ Peringatan Stok</h3>
            <p class="text-red-700 mt-1">{{ $jumlahStokHabis ?? '5' }} barang hampir habis dan memerlukan restock segera</p>
            <a href="stock.index" class="inline-block mt-3 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                Lihat Detail
            </a>
        </div>
    </div>
</div>
@endif

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Scan QR Code -->
    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center space-x-4 mb-4">
            <div class="w-12 h-12 gradient-dark rounded-xl flex items-center justify-center">
                <i class="fas fa-qrcode text-white text-lg"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Scan QR Code</h3>
        </div>
        <p class="text-gray-600 mb-4">Scan kode QR untuk tracking barang dengan cepat</p>
        <a href="scan-qr.index" class="block w-full py-2 bg-gray-800 text-white text-center rounded-lg hover:bg-gray-900 transition-colors">
            Buka Scanner
        </a>
    </div>

    <!-- Buat PO Baru -->
    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center space-x-4 mb-4">
            <div class="w-12 h-12 gradient-red rounded-xl flex items-center justify-center">
                <i class="fas fa-plus-circle text-white text-lg"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Buat PO Baru</h3>
        </div>
        <p class="text-gray-600 mb-4">Buat purchase order untuk pengadaan barang</p>
        <a href="purchase-orders.create" class="block w-full py-2 bg-red-600 text-white text-center rounded-lg hover:bg-red-700 transition-colors">
            Buat PO
        </a>
    </div>

    <!-- Laporan Stok -->
    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center space-x-4 mb-4">
            <div class="w-12 h-12 gradient-gray rounded-xl flex items-center justify-center">
                <i class="fas fa-chart-bar text-white text-lg"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Laporan Stok</h3>
        </div>
        <p class="text-gray-600 mb-4">Lihat laporan detail stok barang terkini</p>
        <a href="stock-reports.index" class="block w-full py-2 bg-gray-600 text-white text-center rounded-lg hover:bg-gray-700 transition-colors">
            Lihat Laporan
        </a>
    </div>
</div>

<!-- Recent Transactions -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-semibold text-gray-900">Transaksi Terbaru</h3>
        <a href="transaction-history.index" class="text-red-600 hover:text-red-700 font-medium">
            Lihat Semua
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-4 font-medium text-gray-600">ID Transaksi</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">Barang</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">Tipe</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">Jumlah</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">Tanggal</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions ?? [] as $transaction)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-4 text-sm font-medium text-gray-900">#{{ $transaction['id'] }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">{{ $transaction['item'] }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $transaction['type'] === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $transaction['type'] === 'in' ? 'Masuk' : 'Keluar' }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-700">{{ $transaction['quantity'] }}</td>
                    <td class="py-3 px-4 text-sm text-gray-700">{{ $transaction['date'] }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                            {{ $transaction['status'] }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                        <p>Belum ada transaksi hari ini</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto refresh dashboard setiap 5 menit
    setInterval(function() {
        location.reload();
    }, 300000);
</script>
@endpush
