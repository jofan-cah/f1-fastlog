<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LogistiK - Admin Dashboard')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        .glassmorphism {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-item:hover {
            transform: translateX(5px);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .gradient-red {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        }

        .gradient-dark {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        }

        .gradient-gray {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>

    <!-- Additional Styles -->
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Mobile overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

    <!-- Main Content -->
    <div class="lg:ml-72 min-h-screen">
        <!-- Header -->
        @include('layouts.header')

        <!-- Page Content -->
        <main class="p-6">
            @yield('content')
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // Mobile Menu Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');

            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Close sidebar when clicking overlay
        document.getElementById('mobile-overlay').addEventListener('click', function() {
            toggleSidebar();
        });

        // Dropdown Menu Toggle
        function toggleDropdown(menuId) {
            const menu = document.getElementById(menuId);
            const icon = document.getElementById(menuId + '-icon');

            menu.classList.toggle('max-h-0');
            menu.classList.toggle('max-h-96');
            menu.classList.toggle('opacity-0');
            menu.classList.toggle('opacity-100');

            icon.classList.toggle('rotate-180');
        }

        // Set active menu
        function setActiveMenu(element) {
            // Remove active class from all menu items
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.classList.remove('bg-red-600', 'text-white');
                item.classList.add('text-gray-300', 'hover:text-white', 'hover:bg-white/10');
            });

            // Add active class to clicked item
            element.classList.add('bg-red-600', 'text-white');
            element.classList.remove('text-gray-300', 'hover:text-white', 'hover:bg-white/10');
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Set active menu based on current route
            const currentPath = window.location.pathname;
            const menuItems = document.querySelectorAll('.sidebar-item');

            menuItems.forEach(item => {
                const links = item.parentElement.querySelectorAll('a');
                links.forEach(link => {
                    if (link.getAttribute('href') === currentPath) {
                        setActiveMenu(item);
                        // Open parent dropdown if item is inside one
                        const parentDropdown = link.closest('[id]');
                        if (parentDropdown && parentDropdown.id !== 'sidebar') {
                            toggleDropdown(parentDropdown.id);
                        }
                    }
                });
            });
        });
    </script>

    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>
