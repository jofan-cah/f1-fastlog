<!-- Header -->
<header class="bg-white/90 backdrop-blur-xl border-b border-gray-200 px-6 py-4 sticky top-0 z-30 shadow-sm">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <!-- Mobile Menu Button -->
            <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-bars text-gray-600"></i>
            </button>

            <!-- Search Bar -->
            <div class="hidden md:flex items-center space-x-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input
                        type="text"
                        placeholder="Cari barang, supplier, atau transaksi..."
                        class="pl-10 pr-4 py-2 w-80 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                    />
                </div>
            </div>
        </div>

        <!-- Right Side -->
        <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <div class="relative">
                <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors relative">
                    <i class="fas fa-bell text-gray-600 text-lg"></i>
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                </button>
            </div>

            <!-- User Profile -->
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-br from-red-600 to-red-700 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-medium">F1</span>
                </div>
                <div class="hidden md:block">
                    <p class="text-sm font-medium text-gray-900">{{ Auth::user()->username ?? 'Admin User' }}</p>
                    <p class="text-xs text-gray-500">{{ Auth::user()->user_level_id->level_name ?? 'Super Admin' }}</p>
                </div>

                <!-- Dropdown Menu -->
                <div class="relative">
                    <button onclick="toggleUserDropdown()" class="p-1 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                    </button>

                    <!-- Dropdown Content -->
                    <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">
                        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-user mr-3 text-gray-400"></i>
                            Profile
                        </a>

                        <hr class="my-2">
                        <form method="POST" action="logout">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-3 text-red-500"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // Toggle User Dropdown
    function toggleUserDropdown() {
        const dropdown = document.getElementById('user-dropdown');
        dropdown.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('user-dropdown');
        const button = event.target.closest('button[onclick="toggleUserDropdown()"]');

        if (!button && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
