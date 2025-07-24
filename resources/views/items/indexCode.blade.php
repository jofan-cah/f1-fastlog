@extends('layouts.app')

@section('title', 'Items & Categories - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Items & Categories</h1>
            <p class="text-gray-600 mt-1">Lihat item code dan category code dengan export Excel</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('items.export.excel', request()->all()) }}"
               class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-file-excel"></i>
                <span>Export Excel</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-boxes text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Items</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $items->total() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-green-600 to-green-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Items Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $items->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-layer-group text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Categories</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $categories->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-qrcode text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">With Codes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $items->filter(fn($item) => $item->category && $item->category->code_category)->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <form method="GET" action="{{ route('items.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari item code, nama, atau category code..."
                               class="pl-10 pr-4 py-3 w-full bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select name="category"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->category_id }}" {{ request('category') == $category->category_id ? 'selected' : '' }}>
                                [{{ $category->code_category }}] {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status"
                            class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-filter"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('items.index') }}"
                   class="px-6 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center space-x-2">
                    <i class="fas fa-times"></i>
                    <span>Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-list mr-2 text-blue-600"></i>
                    Daftar Items & Categories
                </h3>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Total: {{ $items->total() }} items</span>
                    @if(request()->hasAny(['search', 'category', 'status']))
                        <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full">Filtered</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            No
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Item Code & Name
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category Code & Name
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Unit & Stock
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($items as $index => $item)
                        @php
                            $stockInfo = $item->getStockInfo();
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <!-- Number -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $items->firstItem() + $index }}
                            </td>

                            <!-- Item Code & Name -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-box text-white text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $item->item_name }}</div>
                                        <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-barcode mr-1"></i>
                                            {{ $item->item_code }}
                                        </div>
                                        @if($item->description)
                                            <div class="text-xs text-gray-500 mt-1">{{ Str::limit($item->description, 30) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Category Code & Name -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->category)
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $item->category->category_name }}</div>
                                        <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-tag mr-1"></i>
                                            {{ $item->category->code_category }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $item->getCategoryPath() }}</div>
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500 italic">Tidak ada kategori</div>
                                @endif
                            </td>

                            <!-- Unit & Stock -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $item->unit }}
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium">{{ $stockInfo['available'] }}/{{ $stockInfo['total'] }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            @if($stockInfo['status'] == 'sufficient') bg-green-100 text-green-800
                                            @elseif($stockInfo['status'] == 'low') bg-yellow-100 text-yellow-800
                                            @elseif($stockInfo['status'] == 'empty') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $stockInfo['status_text'] }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">Min: {{ $item->min_stock }}</div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $item->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                    {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button onclick="showDetailModal('{{ $item->item_id }}')"
                                        class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                        title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Detail Modal -->
                        <div id="detailModal{{ $item->item_id }}"
                             class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4"
                             onclick="hideDetailModal(event, '{{ $item->item_id }}')">
                            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                                 onclick="event.stopPropagation()">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-6">
                                        <h3 class="text-xl font-bold text-gray-900 flex items-center">
                                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                                            Detail Item
                                        </h3>
                                        <button onclick="document.getElementById('detailModal{{ $item->item_id }}').classList.add('hidden')"
                                                class="text-gray-400 hover:text-gray-600 p-2">
                                            <i class="fas fa-times text-xl"></i>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Item Information -->
                                        <div class="space-y-4">
                                            <h4 class="font-semibold text-gray-900 border-b pb-2">Informasi Item</h4>

                                            <div class="space-y-3">
                                                <div>
                                                    <label class="text-sm font-medium text-gray-500">Item ID</label>
                                                    <p class="text-sm text-gray-900 font-mono">{{ $item->item_id }}</p>
                                                </div>

                                                <div>
                                                    <label class="text-sm font-medium text-gray-500">Item Code</label>
                                                    <p class="text-sm">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            <i class="fas fa-barcode mr-1"></i>
                                                            {{ $item->item_code }}
                                                        </span>
                                                    </p>
                                                </div>

                                                <div>
                                                    <label class="text-sm font-medium text-gray-500">Item Name</label>
                                                    <p class="text-sm text-gray-900 font-medium">{{ $item->item_name }}</p>
                                                </div>

                                                <div>
                                                    <label class="text-sm font-medium text-gray-500">Unit</label>
                                                    <p class="text-sm">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                            {{ $item->unit }}
                                                        </span>
                                                    </p>
                                                </div>

                                                <div>
                                                    <label class="text-sm font-medium text-gray-500">Min Stock</label>
                                                    <p class="text-sm text-gray-900">{{ $item->min_stock }}</p>
                                                </div>

                                                <div>
                                                    <label class="text-sm font-medium text-gray-500">Status</label>
                                                    <p class="text-sm">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $item->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Category Information -->
                                        <div class="space-y-4">
                                            <h4 class="font-semibold text-gray-900 border-b pb-2">Informasi Kategori</h4>

                                            @if($item->category)
                                                <div class="space-y-3">
                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Category Code</label>
                                                        <p class="text-sm">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                                <i class="fas fa-tag mr-1"></i>
                                                                {{ $item->category->code_category }}
                                                            </span>
                                                        </p>
                                                    </div>

                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Category Name</label>
                                                        <p class="text-sm text-gray-900 font-medium">{{ $item->category->category_name }}</p>
                                                    </div>

                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Category Path</label>
                                                        <p class="text-sm text-gray-900">{{ $item->getCategoryPath() }}</p>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="text-sm text-gray-500 italic">Tidak ada kategori yang ditentukan</p>
                                            @endif

                                            <!-- Stock Information -->
                                            <div class="mt-6">
                                                <h4 class="font-semibold text-gray-900 border-b pb-2">Informasi Stok</h4>
                                                <div class="space-y-3 mt-3">
                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Available Stock</label>
                                                        <p class="text-sm text-gray-900 font-medium">{{ $stockInfo['available'] }}</p>
                                                    </div>

                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Used Stock</label>
                                                        <p class="text-sm text-gray-900">{{ $stockInfo['used'] }}</p>
                                                    </div>

                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Total Stock</label>
                                                        <p class="text-sm text-gray-900 font-medium">{{ $stockInfo['total'] }}</p>
                                                    </div>

                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Stock Status</label>
                                                        <p class="text-sm">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                                @if($stockInfo['status'] == 'sufficient') bg-green-100 text-green-800
                                                                @elseif($stockInfo['status'] == 'low') bg-yellow-100 text-yellow-800
                                                                @elseif($stockInfo['status'] == 'empty') bg-red-100 text-red-800
                                                                @else bg-gray-100 text-gray-800 @endif">
                                                                {{ $stockInfo['status_text'] }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    @if($item->description)
                                        <div class="mt-6">
                                            <h4 class="font-semibold text-gray-900 border-b pb-2">Deskripsi</h4>
                                            <p class="text-sm text-gray-600 mt-3">{{ $item->description }}</p>
                                        </div>
                                    @endif

                                    <div class="flex justify-end mt-6 pt-4 border-t">
                                        <button onclick="document.getElementById('detailModal{{ $item->item_id }}').classList.add('hidden')"
                                                class="px-4 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200">
                                            Tutup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data ditemukan</h3>
                                    <p class="text-gray-500 mb-4">
                                        @if(request()->hasAny(['search', 'category', 'status']))
                                            Tidak ada item yang sesuai dengan filter yang dipilih.
                                        @else
                                            Belum ada data item yang tersedia.
                                        @endif
                                    </p>
                                    @if(request()->hasAny(['search', 'category', 'status']))
                                        <a href="{{ route('items.index') }}"
                                           class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200">
                                            Reset Filter
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($items->hasPages())
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Menampilkan {{ $items->firstItem() }} sampai {{ $items->lastItem() }}
                    dari {{ $items->total() }} hasil
                </div>
                <div class="flex items-center space-x-2">
                    {{ $items->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    @endif

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function showDetailModal(itemId) {
        document.getElementById('detailModal' + itemId).classList.remove('hidden');
        document.getElementById('detailModal' + itemId).classList.add('flex');
    }

    function hideDetailModal(event, itemId) {
        if (event.target === event.currentTarget) {
            document.getElementById('detailModal' + itemId).classList.add('hidden');
            document.getElementById('detailModal' + itemId).classList.remove('flex');
        }
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modals = document.querySelectorAll('[id^="detailModal"]');
            modals.forEach(modal => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        }
    });

    // Auto hide messages after 5 seconds
    setTimeout(() => {
        const messages = document.querySelectorAll('.fixed.top-4.right-4');
        messages.forEach(message => message.remove());
    }, 5000);
</script>
@endpush
