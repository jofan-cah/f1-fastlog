@extends('layouts.app')

@section('title', 'Detail Supplier - LogistiK Admin')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="space-y-6" x-data="supplierDetail()">
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
                    <a href="{{ route('suppliers.index') }}" class="text-sm font-medium text-gray-700 hover:text-red-600">
                        Supplier
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">{{ $supplier->supplier_name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-br from-red-600 to-red-700 rounded-2xl flex items-center justify-center">
                <i class="fas fa-building text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $supplier->supplier_name }}</h1>
                <p class="text-gray-600 mt-1">{{ $supplier->supplier_code }} • {{ $supplier->getStatusText() }}</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('suppliers.edit', $supplier->supplier_id) }}"
               class="px-4 py-2 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                <i class="fas fa-edit"></i>
                <span>Edit Supplier</span>
            </a>
            <button @click="showToggleModal()"
                    class="px-4 py-2 rounded-xl transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl {{ $supplier->is_active ? 'bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800' : 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' }} text-white">
                <i class="fas fa-{{ $supplier->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                <span>{{ $supplier->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
            </button>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="flex items-center space-x-3">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
            <span class="w-2 h-2 rounded-full mr-2 {{ $supplier->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
            {{ $supplier->getStatusText() }}
        </span>
        <span class="text-sm text-gray-500">
            ID: {{ $supplier->supplier_id }}
        </span>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Informasi Dasar
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kode Supplier</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm font-mono text-gray-900">{{ $supplier->supplier_code }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Supplier</label>
                            <div class="p-3 bg-gray-50 rounded-lg border">
                                <span class="text-sm text-gray-900">{{ $supplier->supplier_name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-address-book mr-2 text-green-600"></i>
                        Informasi Kontak
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        <!-- Contact Person -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kontak Person</label>
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border">
                                <i class="fas fa-user text-gray-400"></i>
                                <span class="text-sm text-gray-900">{{ $supplier->contact_person ?? 'Tidak ada' }}</span>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border">
                                <i class="fas fa-phone text-gray-400"></i>
                                <span class="text-sm text-gray-900">{{ $supplier->getFormattedPhone() }}</span>
                                @if($supplier->phone)
                                    <a href="tel:{{ $supplier->phone }}"
                                       class="ml-auto text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border">
                                <i class="fas fa-envelope text-gray-400"></i>
                                <span class="text-sm text-gray-900">{{ $supplier->email ?? 'Tidak ada' }}</span>
                                @if($supplier->email)
                                    <a href="mailto:{{ $supplier->email }}"
                                       class="ml-auto text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Address -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg border">
                                <i class="fas fa-map-marker-alt text-gray-400 mt-1"></i>
                                <span class="text-sm text-gray-900">{{ $supplier->address ?? 'Tidak ada alamat' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Purchase Orders -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-shopping-cart mr-2 text-purple-600"></i>
                            Purchase Orders Terbaru
                        </h3>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800">
                            Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    @if($supplier->purchaseOrders->count() > 0)
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($supplier->purchaseOrders as $po)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $po->po_number ?? 'PO-' . $po->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $po->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ ucfirst($po->status ?? 'pending') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Rp {{ number_format($po->total_amount ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-8 text-center">
                            <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-4"></i>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Purchase Order</h4>
                            <p class="text-gray-500">Supplier ini belum memiliki riwayat Purchase Order</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Stats & Actions -->
        <div class="space-y-6">
            <!-- Statistics Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-bar mr-2 text-indigo-600"></i>
                        Statistik
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Total PO -->
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-shopping-cart text-white text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Total PO</span>
                            </div>
                            <span class="text-lg font-bold text-blue-600">{{ $stats['total_po'] }}</span>
                        </div>

                        <!-- Active PO -->
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-clock text-white text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">PO Aktif</span>
                            </div>
                            <span class="text-lg font-bold text-yellow-600">{{ $stats['active_po'] }}</span>
                        </div>

                        <!-- Member Since -->
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-calendar text-white text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Terdaftar</span>
                            </div>
                            <span class="text-sm font-medium text-green-600">{{ $supplier->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bolt mr-2 text-yellow-600"></i>
                        Aksi Cepat
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <button class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>Buat Purchase Order</span>
                        </button>

                        <button class="w-full px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-file-alt"></i>
                            <span>Lihat Semua PO</span>
                        </button>

                        @if($supplier->phone)
                            <a href="tel:{{ $supplier->phone }}"
                               class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-phone"></i>
                                <span>Hubungi Supplier</span>
                            </a>
                        @endif

                        @if($supplier->email)
                            <a href="mailto:{{ $supplier->email }}"
                               class="w-full px-4 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-envelope"></i>
                                <span>Kirim Email</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Metadata Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info mr-2 text-gray-600"></i>
                        Metadata
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Dibuat:</span>
                            <span class="text-gray-900">{{ $supplier->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Diupdate:</span>
                            <span class="text-gray-900">{{ $supplier->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID Supplier:</span>
                            <span class="text-gray-900 font-mono">{{ $supplier->supplier_id }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Status Modal -->
    <div x-show="toggleModal.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="hideToggleModal()"
         @keydown.escape.window="hideToggleModal()"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div x-show="toggleModal.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <div class="w-16 h-16 {{ $supplier->is_active ? 'bg-orange-100' : 'bg-green-100' }} rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-{{ $supplier->is_active ? 'toggle-off' : 'toggle-on' }} text-2xl {{ $supplier->is_active ? 'text-orange-600' : 'text-green-600' }}"></i>
                </div>

                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">
                    {{ $supplier->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Supplier
                </h3>

                <p class="text-gray-600 text-center mb-6">
                    Apakah Anda yakin ingin {{ $supplier->is_active ? 'menonaktifkan' : 'mengaktifkan' }} supplier
                    <span class="font-semibold text-gray-900">{{ $supplier->supplier_name }}</span>?
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            @click="hideToggleModal()"
                            class="flex-1 px-4 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="button"
                            @click="confirmToggle()"
                            class="flex-1 px-4 py-3 bg-gradient-to-r {{ $supplier->is_active ? 'from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800' : 'from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' }} text-white rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
                        <i class="fas fa-{{ $supplier->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                        <span>{{ $supplier->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                    </button>
                </div>
            </div>
        </div>
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
    function supplierDetail() {
        return {
            toggleModal: {
                show: false
            },

            // Toggle Status Modal Functions
            showToggleModal() {
                this.toggleModal.show = true;
            },

            hideToggleModal() {
                this.toggleModal.show = false;
            },

            confirmToggle() {
                // Create and submit toggle form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('suppliers.toggle-status', $supplier->supplier_id) }}`;
                form.style.display = 'none';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'PATCH';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);

                this.hideToggleModal();
                form.submit();
            }
        }
    }
</script>
@endpush
