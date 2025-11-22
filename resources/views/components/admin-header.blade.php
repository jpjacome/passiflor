
        <!-- Mobile menu overlay for mobile navigation -->
        <div class="mobile-menu-overlay menu-inactive">
                        <div class="mobile-menu-container">
                        <a class="navbar-link" href="/admin/consultations">Consultas</a>
                        <a class="navbar-link" href="/admin/therapies">Terapias</a>
                        <a class="navbar-link" href="/admin/users">Usuarios</a>
                    </div>
        </div>
        <header class="main-navbar">
        <nav class="navbar-menu">
            <a href="/admin/dashboard">
                <img src="{{ asset('imgs/icon5.svg') }}" alt="Passiflor Logo" class="navbar-logo">
            </a>
            <a href="/admin/consultations" class="navbar-link">Consultas</a>
            <a href="/admin/therapies" class="navbar-link">Terapias</a>
            <a href="/admin/users" class="navbar-link">Usuarios</a>
        </nav>
        <h1 class="navbar-title">Passiflor</h1>
        <div class="navbar-actions">
            <div class="navbar-social">
                <a href="/" class="navbar-login-link" aria-label="Ir al sitio público">
                    <i class="ph ph-house"></i>
                </a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="navbar-logout-link" aria-label="Cerrar Sesión">
                        <i class="ph ph-sign-out"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="hamburger-menu">
        <span></span>
        <span></span>
        <span></span>
        </div>
        </header>
