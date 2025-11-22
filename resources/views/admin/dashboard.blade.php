<!DOCTYPE html>
<html lang="es">
<x-head title="Admin Dashboard – Passiflor" />
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
</head>
<body>
    <x-admin-header/>
    
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay"></div>
    
    <div class="dashboard-wrapper">
        <div class="dashboard-content fade-in-1">
            <div class="welcome-section">
                <h1 class="welcome-title">Bienvenido, Admin</h1>
            </div>
            
            <!-- Dashboard content will go here -->
        </div>
    </div>

    <script>
        // Loading overlay fade-out
        window.addEventListener('load', function() {
            setTimeout(() => {
                const loadingOverlay = document.getElementById('loadingOverlay');
                if (loadingOverlay) {
                    loadingOverlay.classList.add('fade-out');
                    setTimeout(() => {
                        loadingOverlay.remove();
                    }, 1000);
                }
            }, 500);
        });

        // Mobile menu toggle logic
        const hamburger = document.querySelector('.hamburger-menu');
        const mobileMenu = document.querySelector('.mobile-menu-overlay');
        
        if (hamburger && mobileMenu) {
            hamburger.addEventListener('click', () => {
                if (mobileMenu.classList.contains('menu-inactive')) {
                    mobileMenu.classList.remove('menu-inactive');
                    mobileMenu.classList.add('menu-active');
                } else {
                    mobileMenu.classList.remove('menu-active');
                    mobileMenu.classList.add('menu-inactive');
                }
            });
            
            // Optional: close menu when a link is clicked
            document.querySelectorAll('.mobile-menu-container .navbar-link').forEach(link => {
                link.addEventListener('click', () => {
                    mobileMenu.classList.remove('menu-active');
                    mobileMenu.classList.add('menu-inactive');
                });
            });
        }
    </script>
</body>
</html>
