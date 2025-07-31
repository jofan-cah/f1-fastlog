{{-- resources/views/components/sidebar.blade.php --}}
<!-- Sidebar -->
<div id="sidebar"
    class="fixed left-0 top-0 h-full w-72 bg-gradient-to-br from-gray-900 via-gray-800 to-black backdrop-blur-xl border-r border-gray-700 transform transition-transform duration-300 ease-in-out z-50 -translate-x-full lg:translate-x-0">
    <!-- Logo -->
    <div class="flex items-center justify-between p-6 border-b border-gray-700">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                <i class="fas fa-box text-white"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">LogistiK</h2>
                <p class="text-xs text-gray-400">Admin Dashboard</p>
            </div>
        </div>
        <button onclick="toggleSidebar()" class="lg:hidden text-gray-400 hover:text-white transition-colors">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="p-4 space-y-2 overflow-y-auto h-[calc(100vh-100px)] custom-scrollbar">

        <!-- Dashboard - No Dropdown -->
        @canAccess('dashboard', 'read')
        <div class="space-y-1">
            <a href="{{ route('dashboard') }}"
                class="sidebar-item w-full flex items-center p-3 rounded-xl transition-all duration-200 group
                      {{ request()->routeIs('dashboard') ? 'bg-red-600/20 text-red-400 border border-red-500/30' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-home group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Dashboard</span>
                </div>
            </a>
        </div>
        @endcanAccess

        <!-- Manajemen User -->
        @canAccess('users', 'read')
        <div class="space-y-1">
            @php
                $userMenuActive = request()->routeIs(['users.*', 'user-levels.*']);
                $userMenuOpen = $userMenuActive ? 'true' : 'false';
            @endphp
            <button onclick="toggleDropdown('users'); setActiveMenu(this);"
                class="sidebar-item w-full flex items-center justify-between p-3 rounded-xl transition-all duration-200 group
                           {{ $userMenuActive ? 'bg-red-600/20 text-red-400 border border-red-500/30' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-users group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Manajemen User</span>
                </div>
                <i id="users-icon"
                    class="fas fa-chevron-down transition-transform {{ $userMenuActive ? 'rotate-180' : '' }}"></i>
            </button>
            <div id="users"
                class="overflow-hidden transition-all duration-300 {{ $userMenuActive ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0' }}">
                <div class="ml-8 space-y-1">
                    @canAccess('users', 'read')
                    <a href="{{ route('users.index') }}"
                        class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('users.*') ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        Pengguna
                    </a>
                    @endcanAccess
                    @canAccess('user_levels', 'read')
                    <a href="{{ route('user-levels.index') }}"
                        class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('user-levels.*') ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        Level Pengguna
                    </a>
                    @endcanAccess
                </div>
            </div>
        </div>
        @endcanAccess

        <!-- Data Master -->
        @if (auth()->user()->hasPermission('categories', 'read') ||
                auth()->user()->hasPermission('items', 'read') ||
                auth()->user()->hasPermission('suppliers', 'read'))
            <div class="space-y-1">
                @php
                    $masterMenuActive = request()->routeIs(['categories.*', 'items.*', 'suppliers.*', 'itemsCode.*']);
                    $masterMenuOpen = $masterMenuActive ? 'true' : 'false';
                @endphp
                <button onclick="toggleDropdown('master'); setActiveMenu(this);"
                    class="sidebar-item w-full flex items-center justify-between p-3 rounded-xl transition-all duration-200 group
                           {{ $masterMenuActive ? 'bg-red-600/20 text-red-400 border border-red-500/30' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-database group-hover:scale-110 transition-transform"></i>
                        <span class="font-medium">Data Barang</span>
                    </div>
                    <i id="master-icon"
                        class="fas fa-chevron-down transition-transform {{ $masterMenuActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="master"
                    class="overflow-hidden transition-all duration-300 {{ $masterMenuActive ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0' }}">
                    <div class="ml-8 space-y-1">
                        @canAccess('categories', 'read')
                        <a href="{{ route('categories.index') }}"
                            class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('categories.*') ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                            Kategori Barang
                        </a>
                        @endcanAccess
                        @canAccess('items', 'read')
                        <a href="{{ route('items.index') }}"
                            class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('items.*') ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                            Jenis Barang
                        </a>
                        @endcanAccess
                        @canAccess('items', 'read')
                        <a href="{{ route('itemsCode.indexCode') }}"
                            class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('itemsCode.*') ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                            Kode Barang
                        </a>
                        @endcanAccess
                        @canAccess('suppliers', 'read')
                        <a href="{{ route('suppliers.index') }}"
                            class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('suppliers.*') ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                            Data Supplier
                        </a>
                        @endcanAccess
                    </div>
                </div>
            </div>
        @endif

        <!-- Pembelian -->
        @if (auth()->user()->hasPermission('purchase_orders', 'read') ||
                auth()->user()->hasPermission('goods_receiveds', 'read'))
            <div class="space-y-1">
                @php
                    $purchaseMenuActive = request()->routeIs(['purchase-orders.*', 'goods-received.*']);
                    $purchaseMenuOpen = $purchaseMenuActive ? 'true' : 'false';
                @endphp
                <button onclick="toggleDropdown('purchase'); setActiveMenu(this);"
                    class="sidebar-item w-full flex items-center justify-between p-3 rounded-xl transition-all duration-200 group
                   {{ $purchaseMenuActive ? 'bg-red-600/20 text-red-400 border border-red-500/30' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-shopping-cart group-hover:scale-110 transition-transform"></i>
                        <span class="font-medium">Pembelian</span>
                    </div>
                    <i id="purchase-icon"
                        class="fas fa-chevron-down transition-transform {{ $purchaseMenuActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="purchase"
                    class="overflow-hidden transition-all duration-300 {{ $purchaseMenuActive ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0' }}">
                    <div class="ml-8 space-y-1">
                        @canAccess('purchase_orders', 'read')
                        <!-- PO Aktif/Proses -->
                        <a href="{{ route('purchase-orders.index', ['filter' => 'active']) }}"
                            class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg group
                      {{ request()->routeIs('purchase-orders.*') && (request('filter') == 'active' || !request('filter')) ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-clock text-xs"></i>
                                    <span>PO Aktif</span>
                                </div>
                                <span class="text-xs bg-orange-500/20 text-orange-400 px-2 py-0.5 rounded-full">
                                    Proses
                                </span>
                            </div>
                        </a>

                        <!-- PO Selesai -->
                        <a href="{{ route('purchase-orders.index', ['filter' => 'completed']) }}"
                            class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg group
                      {{ request()->routeIs('purchase-orders.*') && request('filter') == 'completed' ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-check-circle text-xs"></i>
                                    <span>PO Selesai</span>
                                </div>
                                <span class="text-xs bg-green-500/20 text-green-400 px-2 py-0.5 rounded-full">
                                    Done
                                </span>
                            </div>
                        </a>

                        <!-- Divider -->
                        <div class="border-t border-gray-700/50 my-2"></div>

                        <!-- All POs (Optional - untuk admin yang mau lihat semua) -->
                        @if (auth()->user()->user_level_id === 'LVL001')
                            <a href="{{ route('purchase-orders.index') }}"
                                class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg group
                      {{ request()->routeIs('purchase-orders.*') && !request('filter') ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-list text-xs"></i>
                                    <span>Semua PO</span>
                                </div>
                            </a>
                        @endif
                        @endcanAccess

                        @canAccess('goods_receiveds', 'read')
                        <a href="{{ route('goods-received.index') }}"
                            class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                      {{ request()->routeIs('goods-received.*') ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-box-open text-xs"></i>
                                <span>Penerimaan Barang</span>
                            </div>
                        </a>
                        @endcanAccess
                    </div>
                </div>
            </div>
        @endif

        <!-- Inventori -->
        @canAccess('stocks', 'read')
        <div class="space-y-1">
            @php
                $inventoryMenuActive = request()->routeIs(['stocks.*', 'item-details.*']);
                $inventoryMenuOpen = $inventoryMenuActive ? 'true' : 'false';
            @endphp
            <button onclick="toggleDropdown('inventory'); setActiveMenu(this);"
                class="sidebar-item w-full flex items-center justify-between p-3 rounded-xl transition-all duration-200 group
                           {{ $inventoryMenuActive ? 'bg-red-600/20 text-red-400 border border-red-500/30' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-warehouse group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Inventori</span>
                </div>
                <i id="inventory-icon"
                    class="fas fa-chevron-down transition-transform {{ $inventoryMenuActive ? 'rotate-180' : '' }}"></i>
            </button>
            <div id="inventory"
                class="overflow-hidden transition-all duration-300 {{ $inventoryMenuActive ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0' }}">
                <div class="ml-8 space-y-1">
                    @canAccess('stocks', 'read')
                    <a href="{{ route('stocks.index') }}"
                        class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('stocks.*') ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        Stok Barang
                    </a>
                    @endcanAccess
                    @canAccess('items', 'read')
                    <a href="{{ route('item-details.index') }}"
                        class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('item-details.*') ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        Detail Barang
                    </a>
                    @endcanAccess
                </div>
            </div>
        </div>
        @endcanAccess

        <!-- Transaksi -->
        @canAccess('transactions', 'read')
        <div class="space-y-1">
            @php
                $transactionMenuActive = request()->routeIs([
                    'transactions.*',
                    'requests.*',
                    'transaction-history.*',
                    'approvals.*',
                ]);
                $transactionMenuOpen = $transactionMenuActive ? 'true' : 'false';
            @endphp
            <button onclick="toggleDropdown('transactions'); setActiveMenu(this);"
                class="sidebar-item w-full flex items-center justify-between p-3 rounded-xl transition-all duration-200 group
                           {{ $transactionMenuActive ? 'bg-red-600/20 text-red-400 border border-red-500/30' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-exchange-alt group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Transaksi</span>
                </div>
                <i id="transactions-icon"
                    class="fas fa-chevron-down transition-transform {{ $transactionMenuActive ? 'rotate-180' : '' }}"></i>
            </button>
            <div id="transactions"
                class="overflow-hidden transition-all duration-300 {{ $transactionMenuActive ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0' }}">
                <div class="ml-8 space-y-1">
                    <a href="{{ route('transactions.index') }}"
                        class="block p-2 text-sm mt-2 transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('transactions.*') && request()->get('type') == '' ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-clipboard-list w-4"></i> Semua Data
                    </a>
                    <a href="{{ route('transactions.index', ['type' => 'IN']) }}"
                        class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('transactions.*') && request()->get('type') == 'IN' ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-arrow-down w-4"></i> Barang Masuk
                    </a>
                    <a href="{{ route('transactions.index', ['type' => 'OUT']) }}"
                        class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('transactions.*') && request()->get('type') == 'OUT' ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-arrow-up w-4"></i> Barang Keluar
                    </a>
                    <a href="{{ route('transactions.index', ['type' => 'REPAIR']) }}"
                        class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('transactions.*') && request()->get('type') == 'REPAIR' ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-wrench w-4"></i> Barang Repair
                    </a>
                    <a href="{{ route('transactions.index', ['type' => 'LOST']) }}"
                        class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('transactions.*') && request()->get('type') == 'LOST' ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-exclamation-triangle w-4"></i> Barang Hilang
                    </a>
                    @canAccess('transactions', 'approve')
                    <a href="{{ route('approvals.index') }}"
                        class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('approvals.*') ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <i class="fas fa-check-circle w-4"></i> Approval Transaksi
                    </a>
                    @endcanAccess
                </div>
            </div>
        </div>
        @endcanAccess

        <!-- Pengaturan -->
        @if (auth()->user()->hasPermission('activity_logs', 'read') || auth()->user()->hasPermission('settings', 'read'))
            <div class="space-y-1">
                @php
                    $settingsMenuActive = request()->routeIs(['system-settings.*', 'activity-logs.*', 'backup.*']);
                    $settingsMenuOpen = $settingsMenuActive ? 'true' : 'false';
                @endphp
                <button onclick="toggleDropdown('settings'); setActiveMenu(this);"
                    class="sidebar-item w-full flex items-center justify-between p-3 rounded-xl transition-all duration-200 group
                           {{ $settingsMenuActive ? 'bg-red-600/20 text-red-400 border border-red-500/30' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-cog group-hover:scale-110 transition-transform"></i>
                        <span class="font-medium">Pengaturan</span>
                    </div>
                    <i id="settings-icon"
                        class="fas fa-chevron-down transition-transform {{ $settingsMenuActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div id="settings"
                    class="overflow-hidden transition-all duration-300 {{ $settingsMenuActive ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0' }}">
                    <div class="ml-8 space-y-1">
                        @canAccess('activity_logs', 'read')
                        <a href="{{ route('activity-logs.index') }}"
                            class="block p-2 text-sm transition-all duration-200 hover:translate-x-1 rounded-lg
                              {{ request()->routeIs('activity-logs.*') ? 'text-red-400 bg-red-600/10' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                            Log Aktivitas
                        </a>
                        @endcanAccess
                    </div>
                </div>
            </div>
        @endif

    </nav>
</div>
