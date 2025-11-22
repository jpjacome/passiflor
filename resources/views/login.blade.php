<!DOCTYPE html>
<html lang="es">
<x-head title="Passiflor – Iniciar Sesión" />
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <x-header/>
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay"></div>
    
    <div class="login-wrapper">

        <!-- Login Form Section -->
        <div class="login-container fade-in-3">
            <div class="login-card">
                <div class="card-header">
                    <h2>Bienvenido</h2>
                    <p>Inicia sesión</p>
                </div>

                <form id="loginForm" class="login-form">
                    <div class="form-group">
                        <label for="email">
                            <i class="ph ph-envelope"></i>
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="tu@email.com"
                            required
                            autocomplete="email"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="ph ph-lock"></i>
                            Contraseña
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
                                <i class="ph ph-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" id="remember">
                            <span class="checkbox-custom"></span>
                            <span>Recordarme</span>
                        </label>
                        <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="login-btn">
                        <span class="btn-text">Iniciar Sesión</span>
                        <i class="ph ph-arrow-right"></i>
                    </button>
                </form>

                <div class="divider">
                    <span>o</span>
                </div>

                <div class="alternative-actions">
                    <p>¿No tienes una cuenta? <a href="#" class="register-link">Regístrate aquí</a></p>
                </div>
            </div>

            <!-- Decorative circles -->
            <div class="decorative-circles">
                <span class="circle circle-1"></span>
                <span class="circle circle-2"></span>
                <span class="circle circle-3"></span>
                <span class="circle circle-4"></span>
                <span class="circle circle-5"></span>
            </div>
        </div>

        <!-- Border decoration -->
        <div class="border-decoration fade-in-3">
            <img class="border-img" src="{{ asset('imgs/icon4.svg') }}" alt="border decoration">
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

        // Password toggle
        document.querySelector('.toggle-password')?.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('ph-eye');
                icon.classList.add('ph-eye-slash');
                this.setAttribute('aria-label', 'Ocultar contraseña');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('ph-eye-slash');
                icon.classList.add('ph-eye');
                this.setAttribute('aria-label', 'Mostrar contraseña');
            }
        });

        // Form submission with AJAX
        document.getElementById('loginForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = form.querySelector('.login-btn');
            const btnText = submitBtn.querySelector('.btn-text');
            const originalText = btnText.textContent;
            
            // Disable button and show loading state
            submitBtn.disabled = true;
            btnText.textContent = 'Iniciando sesión...';
            
            // Get form data
            const formData = new FormData(form);
            const data = {
                email: formData.get('email'),
                password: formData.get('password'),
                remember: formData.get('remember') ? true : false
            };
            
            try {
                const response = await fetch('{{ route("login.post") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    // Show success message
                    btnText.textContent = '✓ ' + result.message;
                    submitBtn.style.background = 'linear-gradient(135deg, #28a745 0%, #20923b 100%)';
                    
                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 500);
                } else {
                    // Show error message
                    alert(result.message || 'Error al iniciar sesión. Por favor, verifica tus credenciales.');
                    submitBtn.disabled = false;
                    btnText.textContent = originalText;
                }
            } catch (error) {
                console.error('Login error:', error);
                alert('Error de conexión. Por favor, intenta nuevamente.');
                submitBtn.disabled = false;
                btnText.textContent = originalText;
            }
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
