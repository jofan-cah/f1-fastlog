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
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($itemStats['total_items']) }}</p>
                <div class="flex items-center mt-2">
                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                    <span class="text-green-600 text-sm font-medium">{{ $itemStats['active_items'] }} aktif</span>
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
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($utilizationMetrics['available_items']) }}</p>
                <div class="flex items-center mt-2">
                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                    <span class="text-green-600 text-sm font-medium">{{ $utilizationMetrics['availability_rate'] }}% tersedia</span>
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
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $transactionStats['approved_today'] }}</p>
                <div class="flex items-center mt-2">
                    <i class="fas fa-clock text-yellow-500 mr-1"></i>
                    <span class="text-yellow-600 text-sm font-medium">{{ $transactionStats['pending_approvals'] }} pending</span>
                </div>
            </div>
            <div class="w-14 h-14 gradient-gray rounded-2xl flex items-center justify-center">
                <i class="fas fa-exchange-alt text-white text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Nilai Inventori -->
    <div class="bg-white stat-card p-6 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total Inventory</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($utilizationMetrics['total_items']) }}</p>
                <div class="flex items-center mt-2">
                    <i class="fas fa-chart-line text-blue-500 mr-1"></i>
                    <span class="text-blue-600 text-sm font-medium">{{ $utilizationMetrics['utilization_rate'] }}% terpakai</span>
                </div>
            </div>
            <div class="w-14 h-14 bg-gray-800 rounded-2xl flex items-center justify-center">
                <i class="fas fa-chart-pie text-white text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Alert Section -->
@if($lowStockAlerts['total_low_stock'] > 0)
<div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-2xl mb-8">
    <div class="flex items-center">
        <i class="fas fa-exclamation-triangle text-red-500 text-xl mr-3"></i>
        <div>
            <h3 class="text-lg font-semibold text-red-800">⚠️ Peringatan Stok</h3>
            <p class="text-red-700 mt-1">{{ $lowStockAlerts['total_low_stock'] }} barang hampir habis dan memerlukan restock segera</p>
            <a href="{{ route('stocks.index', ['filter' => 'low']) }}" class="inline-block mt-3 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                Lihat Detail
            </a>
        </div>
    </div>
</div>
@endif

@if($stockSummary['out_of_stock_items'] > 0)
<div class="bg-orange-50 border-l-4 border-orange-500 p-6 rounded-2xl mb-8">
    <div class="flex items-center">
        <i class="fas fa-times-circle text-orange-500 text-xl mr-3"></i>
        <div>
            <h3 class="text-lg font-semibold text-orange-800">🚨 Stok Habis</h3>
            <p class="text-orange-700 mt-1">{{ $stockSummary['out_of_stock_items'] }} barang sudah habis dan perlu segera diisi ulang</p>
            <a href="{{ route('stocks.index', ['filter' => 'empty']) }}" class="inline-block mt-3 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                Lihat Detail
            </a>
        </div>
    </div>
</div>
@endif

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mb-8">
    <!-- Scan QR Code -->
    {{-- <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center space-x-4 mb-4">
            <div class="w-12 h-12 gradient-dark rounded-xl flex items-center justify-center">
                <i class="fas fa-qrcode text-white text-lg"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Scan QR Code</h3>
        </div>
        <p class="text-gray-600 mb-4">Scan kode QR untuk tracking barang dengan cepat</p>
        <a href="" class="block w-full py-2 bg-gray-800 text-white text-center rounded-lg hover:bg-gray-900 transition-colors">
            Buka Scanner
        </a>
    </div> --}}

    <!-- Buat PO Baru -->
    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center space-x-4 mb-4">
            <div class="w-12 h-12 gradient-red rounded-xl flex items-center justify-center">
                <i class="fas fa-plus-circle text-white text-lg"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Buat PO Baru</h3>
        </div>
        <p class="text-gray-600 mb-4">Buat purchase order untuk pengadaan barang</p>
        <a href="{{ route('purchase-orders.create') }}" class="block w-full py-2 bg-red-600 text-white text-center rounded-lg hover:bg-red-700 transition-colors">
            Buat PO
        </a>
    </div>

    <!-- Transaksi Baru -->
    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center space-x-4 mb-4">
            <div class="w-12 h-12 gradient-gray rounded-xl flex items-center justify-center">
                <i class="fas fa-exchange-alt text-white text-lg"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Transaksi Baru</h3>
        </div>
        <p class="text-gray-600 mb-4">Buat transaksi barang masuk atau keluar</p>
        <a href="{{ route('transactions.create') }}" class="block w-full py-2 bg-gray-600 text-white text-center rounded-lg hover:bg-gray-700 transition-colors">
            Buat Transaksi
        </a>
    </div>

    <!-- Laporan -->
    {{-- <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300">
        <div class="flex items-center space-x-4 mb-4">
            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-chart-bar text-white text-lg"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Laporan</h3>
        </div>
        <p class="text-gray-600 mb-4">Lihat laporan detail stok dan transaksi</p>
        <a href="" class="block w-full py-2 bg-blue-600 text-white text-center rounded-lg hover:bg-blue-700 transition-colors">
            Lihat Laporan
        </a>
    </div> --}}
</div>

<!-- Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Recent Transactions -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Transaksi Terbaru</h3>
            <a href="{{ route('transactions.index') }}" class="text-red-600 hover:text-red-700 font-medium">
                Lihat Semua
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-medium text-gray-600">No. Transaksi</th>
                        <th class="text-left py-3 px-4 font-medium text-gray-600">Tipe</th>
                        <th class="text-left py-3 px-4 font-medium text-gray-600">Barang</th>
                        <th class="text-left py-3 px-4 font-medium text-gray-600">Tanggal</th>
                        <th class="text-left py-3 px-4 font-medium text-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(array_slice($recentActivities, 0, 10) as $activity)
                    @if($activity['type'] === 'transaction')
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $activity['title'] }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $activity['status']['class'] }}">
                                {{ $activity['status']['text'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-700">{{ $activity['description'] }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700">{{ $activity['time']->format('d/m H:i') }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700">{{ $activity['user'] }}</td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                            <p>Belum ada transaksi hari ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="space-y-6">
        <!-- Pending Approvals -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Pending Approval</h3>
                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ count($pendingApprovals) }}
                </span>
            </div>
            <div class="space-y-3">
                @forelse($pendingApprovals as $approval)
                <div class="border-l-4 border-yellow-400 pl-3 py-2">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900">{{ $approval['number'] }}</p>
                        <span class="text-xs text-gray-500">{{ $approval['age_hours'] }}h</span>
                    </div>
                    <p class="text-sm text-gray-600">{{ $approval['item_name'] }}</p>
                    <p class="text-xs text-gray-500">oleh {{ $approval['created_by'] }}</p>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-500">Tidak ada pending approval</p>
                </div>
                @endforelse
            </div>
            @if(count($pendingApprovals) > 0)
            <div class="mt-4 pt-4 border-t">
                <a href="{{ route('transactions.index', ['status' => 'pending']) }}" class="block w-full py-2 bg-yellow-500 text-white text-center rounded-lg hover:bg-yellow-600 transition-colors">
                    Review Semua
                </a>
            </div>
            @endif
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Stok Rendah</h3>
                <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ $lowStockAlerts['total_low_stock'] }}
                </span>
            </div>
            <div class="space-y-3">
                @forelse(array_slice($lowStockAlerts['items'], 0, 5) as $item)
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ Str::limit($item['item_name'], 20) }}</p>
                        <p class="text-xs text-gray-500">{{ $item['item_code'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-red-600">{{ $item['current_available'] }}</p>
                        <p class="text-xs text-gray-500">min: {{ $item['min_stock'] }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-500">Semua stok mencukupi</p>
                </div>
                @endforelse
            </div>
            @if($lowStockAlerts['total_low_stock'] > 0)
            <div class="mt-4 pt-4 border-t">
                <a href="{{ route('stocks.index', ['filter' => 'low']) }}" class="block w-full py-2 bg-red-500 text-white text-center rounded-lg hover:bg-red-600 transition-colors">
                    Lihat Semua
                </a>
            </div>
            @endif
        </div>

        <!-- Quick Stats -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Barang</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Tersedia</span>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ number_format($utilizationMetrics['available_items']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Terpakai</span>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ number_format($utilizationMetrics['used_items']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Repair</span>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ number_format($utilizationMetrics['repair_items']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Hilang</span>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ number_format($utilizationMetrics['lost_items']) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Performance -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-semibold text-gray-900">Performa Kategori</h3>
        <a href="{{ route('categories.index') }}" class="text-red-600 hover:text-red-700 font-medium">Lihat Semua</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($categoryStats as $category)
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">{{ $category['category_name'] }}</h4>
                <span class="text-sm text-gray-500">{{ $category['total_items'] }} items</span>
            </div>
            <div class="mb-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Stock</span>
                    <span class="font-medium">{{ number_format($category['total_stock']) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Tersedia</span>
                    <span class="font-medium text-green-600">{{ number_format($category['available_stock']) }}</span>
                </div>
            </div>
            <div class="flex items-center">
                <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $category['utilization_rate'] }}%"></div>
                </div>
                <span class="text-sm font-medium">{{ $category['utilization_rate'] }}%</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('styles')
<style>
    .gradient-red {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .gradient-dark {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    }

    .gradient-gray {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    }

    .stat-card {
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto refresh dashboard setiap 5 menit
    setInterval(function() {
        location.reload();
    }, 300000);

    // Real-time counter updates
    setInterval(function() {
        fetch('/dashboard/quick-stats')
            .then(response => response.json())
            .then(data => {
                // Update stats if needed
                console.log('Stats updated:', data);
            })
            .catch(error => console.error('Error updating stats:', error));
    }, 60000); // Update every minute
</script>
@endpush
