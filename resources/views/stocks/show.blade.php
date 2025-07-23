@extends('layouts.app')

@section('title', 'Detail Stok - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="stockDetail()">
    <!-- Breadcrumb -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-red-600">
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="{{ route('stocks.index') }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                        Manajemen Stok
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">{{ $stock->item->item_name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl flex items-center justify-center">
                <i class="fas fa-warehouse text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $stock->item->item_name }}</h1>
                <p class="text-gray-600 mt-1">{{ $stock->item->item_code }} • {{ $statusInfo['text'] }}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('stocks.edit', $stock->stock_id) }}"
               class="px-4 py-2 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-edit"></i>
                <span>Edit Stok</span>
            </a>

        </div>
    </div>

    <!-- Status & Progress Badges -->
    <div class="flex items-center space-x-3 flex-wrap">
        <!-- Stock Status Badge -->
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusInfo['class'] }}">
            <span class="w-2 h-2 rounded-full mr-2
                @if($statusInfo['status'] == 'sufficient') bg-green-400
                @elseif($statusInfo['status'] == 'low') bg-yellow-400
                @else bg-red-400 @endif"></span>
            {{ $statusInfo['text'] }}
        </span>

        <!-- Category Badge -->
        @if($stock->item->category)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                <i class="fas fa-tags mr-2"></i>
                {{ $stock->item->category->category_name }}
            </span>
        @endif

        <!-- Stock Percentage -->
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
            <i class="fas fa-percentage mr-2"></i>
            {{ $stock->getStockPercentage() }}% tersedia
        </span>

        <!-- Last Updated -->
        @if($stock->last_updated)
            <span class="text-sm text-gray-500">
                Update: {{ $stock->last_updated->format('d/m/Y H:i') }}
            </span>
        @endif

        <!-- Stock ID -->
        <span class="text-sm text-gray-500">ID: {{ $stock->stock_id }}</span>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Stock Overview Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-bar mr-2 text-green-600"></i>
                        Overview Stok
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Available Stock -->
                        <div class="text-center p-6 bg-blue-50 rounded-xl">
                            <div class="w-16 h-16 bg-blue-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-boxes text-white text-xl"></i>
                            </div>
                            <div class="text-3xl font-bold text-blue-600 mb-2">{{ number_format($stock->quantity_available) }}</div>
                            <div class="text-sm text-gray-600">Stok Tersedia</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $stock->item->unit }}</div>
                        </div>

                        <!-- Used Stock -->
                        <div class="text-center p-6 bg-yellow-50 rounded-xl">
                            <div class="w-16 h-16 bg-yellow-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-shipping-fast text-white text-xl"></i>
                            </div>
                            <div class="text-3xl font-bold text-yellow-600 mb-2">{{ number_format($stock->quantity_used) }}</div>
                            <div class="text-sm text-gray-600">Stok Terpakai</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $stock->item->unit }}</div>
                        </div>

                        <!-- Total Stock -->
                        <div class="text-center p-6 bg-green-50 rounded-xl">
                            <div class="w-16 h-16 bg-green-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-warehouse text-white text-xl"></i>
                            </div>
                            <div class="text-3xl font-bold text-green-600 mb-2">{{ number_format($stock->total_quantity) }}</div>
                            <div class="text-sm text-gray-600">Total Stok</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $stock->item->unit }}</div>
                        </div>
                    </div>

                    <!-- Stock Progress Visualization -->
                    <div class="mt-8">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-700">Utilisasi Stok</span>
                            <span class="text-sm text-gray-500">{{ $stock->getStockPercentage() }}% tersedia</span>
                        </div>

                        @if($stock->total_quantity > 0)
                            <div class="relative">
                                <div class="w-full bg-gray-200 rounded-full h-4">
                                    <div class="bg-gradient-to-r from-blue-600 to-green-600 h-4 rounded-full transition-all duration-500"
                                         style="width: {{ ($stock->quantity_available / $stock->total_quantity) * 100 }}%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-600 mt-2">
                                    <span>Tersedia: {{ number_format($stock->quantity_available) }}</span>
                                    <span>Terpakai: {{ number_format($stock->quantity_used) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="w-full bg-red-200 rounded-full h-4">
                                <div class="bg-red-500 h-4 rounded-full w-full"></div>
                            </div>
                            <p class="text-center text-red-600 text-sm mt-2">Stok Habis</p>
                        @endif
                    </div>

                    <!-- Minimum Stock Warning -->
                    @if($stock->isLowStock())
                        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                <div>
                                    <h4 class="font-medium text-yellow-900">Peringatan Stok Rendah</h4>
                                    <p class="text-sm text-yellow-800">
                                        Stok saat ini ({{ $stock->quantity_available }}) berada di bawah atau sama dengan minimum stock ({{ $stock->item->min_stock }})
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Item Information Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Informasi Barang
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kode Barang</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm font-mono text-gray-900">{{ $stock->item->item_code }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $stock->item->item_name }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">
                                    {{ $stock->item->category ? $stock->item->category->category_name : 'Tidak ada kategori' }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Satuan</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $stock->item->unit }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Stok</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $stock->item->min_stock }} {{ $stock->item->unit }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Barang</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $stock->item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $stock->item->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($stock->item->description)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <p class="text-sm text-gray-900">{{ $stock->item->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Stock Movement History (Placeholder) -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-history mr-2 text-purple-600"></i>
                            Riwayat Pergerakan Stok
                        </h3>
                        <button class="text-sm text-blue-600 hover:text-blue-800">
                            Lihat Semua
                        </button>
                    </div>
                </div>
                <div class="p-8 text-center">
                    <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Riwayat Pergerakan</h4>
                    <p class="text-gray-500 mb-4">
                        Riwayat detail pergerakan stok akan tersedia setelah implementasi StockMovement model
                    </p>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-left">
                        <div class="flex items-center space-x-2 text-blue-800">
                            <i class="fas fa-info-circle"></i>
                            <div class="text-sm">
                                <p class="font-medium">Fitur yang akan datang:</p>
                                <ul class="list-disc list-inside mt-1 space-y-1">
                                    <li>Log semua perubahan stok (in/out/adjustment)</li>
                                    <li>Tracking user yang melakukan perubahan</li>
                                    <li>Alasan dan timestamp setiap pergerakan</li>
                                    <li>Export laporan pergerakan stok</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Actions & Metadata -->

    </div>



    <!-- Success/Error Messages -->
    @if(session('success'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
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

    @if(session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
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
<script>
    function stockDetail() {
        return {
            quickAdjustModal: {
                show: false,
                adjustmentType: 'add',
                quantity: 1,
                reason: 'manual_adjustment'
            },

            // Quick Adjust Modal Functions
            showQuickAdjustModal(type = 'add') {
                this.quickAdjustModal = {
                    show: true,
                    adjustmentType: type,
                    quantity: 1,
                    reason: 'manual_adjustment'
                };
            },

            hideQuickAdjustModal() {
                this.quickAdjustModal.show = false;
                setTimeout(() => {
                    this.quickAdjustModal = {
                        show: false,
                        adjustmentType: 'add',
                        quantity: 1,
                        reason: 'manual_adjustment'
                    };
                }, 300);
            },

            submitQuickAdjust() {
                // Validate quantity
                if (!this.quickAdjustModal.quantity || this.quickAdjustModal.quantity < 1) {
                    alert('Jumlah harus diisi dan minimal 1');
                    return;
                }

                // Check if reduce has enough available stock
                if (this.quickAdjustModal.adjustmentType === 'reduce' &&
                    this.quickAdjustModal.quantity > {{ $stock->quantity_available }}) {
                    alert('Jumlah tidak boleh melebihi stok tersedia ({{ $stock->quantity_available }})');
                    return;
                }

                // Check if return has enough used stock
                if (this.quickAdjustModal.adjustmentType === 'return' &&
                    this.quickAdjustModal.quantity > {{ $stock->quantity_used }}) {
                    alert('Jumlah return tidak boleh melebihi stok terpakai ({{ $stock->quantity_used }})');
                    return;
                }

                // Create and submit form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("stocks.adjustment") }}';
                form.style.display = 'none';

                // CSRF Token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrfToken);

                // Stock ID
                const stockIdInput = document.createElement('input');
                stockIdInput.type = 'hidden';
                stockIdInput.name = 'stock_id';
                stockIdInput.value = '{{ $stock->stock_id }}';
                form.appendChild(stockIdInput);

                // Adjustment Type
                const typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = 'adjustment_type';
                typeInput.value = this.quickAdjustModal.adjustmentType;
                form.appendChild(typeInput);

                // Quantity
                const quantityInput = document.createElement('input');
                quantityInput.type = 'hidden';
                quantityInput.name = 'quantity';
                quantityInput.value = this.quickAdjustModal.quantity;
                form.appendChild(quantityInput);

                // Reason
                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'reason';
                reasonInput.value = this.quickAdjustModal.reason;
                form.appendChild(reasonInput);

                document.body.appendChild(form);
                this.hideQuickAdjustModal();
                form.submit();
            }
        }
    }
</script>
@endpush
