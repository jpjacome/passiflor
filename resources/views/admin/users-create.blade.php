<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - Passiflor Admin</title>
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <x-admin-header />
    
    <div class="dashboard-wrapper">
        <!-- Breadcrumb Navigation -->
        <div class="breadcrumb">
            <a href="{{ route('admin.users') }}" class="breadcrumb-link">
                <i class="ph ph-arrow-left"></i>
                Volver a Usuarios
            </a>
        </div>

        <!-- Create User Form -->
        <section class="form-section">
            <div class="form-header">
                <h1 class="form-title">Crear Nuevo Usuario</h1>
                <p class="form-subtitle">Completa los datos del nuevo usuario</p>
            </div>

            <!-- Validation Errors -->
            @if($errors->any())
            <div class="alert alert-error">
                <i class="ph ph-warning-circle"></i>
                <div>
                    <strong>Por favor corrige los siguientes errores:</strong>
                    <ul class="error-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <form action="{{ route('admin.users.store') }}" method="POST" class="user-form">
                @csrf

                <div class="form-grid">
                    <!-- Name Field -->
                    <div class="form-group full-width">
                        <label for="name" class="form-label">
                            <i class="ph ph-user"></i>
                            Nombre Completo
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-input @error('name') input-error @enderror" 
                            value="{{ old('name') }}" 
                            required
                            placeholder="Ej: María González"
                        >
                        @error('name')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div class="form-group full-width">
                        <label for="email" class="form-label">
                            <i class="ph ph-envelope"></i>
                            Correo Electrónico
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input @error('email') input-error @enderror" 
                            value="{{ old('email') }}" 
                            required
                            placeholder="usuario@ejemplo.com"
                        >
                        @error('email')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Role Field -->
                    <div class="form-group">
                        <label for="role" class="form-label">
                            <i class="ph ph-user-list"></i>
                            Rol de Usuario
                        </label>
                        <select 
                            id="role" 
                            name="role" 
                            class="form-input @error('role') input-error @enderror" 
                            required
                        >
                            <option value="">Selecciona un rol</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                                👑 Administrador
                            </option>
                            <option value="therapist" {{ old('role') === 'therapist' ? 'selected' : '' }}>
                                ⚕️ Terapeuta
                            </option>
                            <option value="patient" {{ old('role') === 'patient' ? 'selected' : '' }}>
                                💚 Paciente
                            </option>
                            <option value="guest" {{ old('role') === 'guest' ? 'selected' : '' }}>
                                👤 Invitado
                            </option>
                        </select>
                        @error('role')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Therapist assignment for patients (shown only when role=patient) -->
                    <div class="form-group" id="therapist-select-wrapper" style="display:none;">
                        <label for="therapist_id" class="form-label">
                            <i class="ph ph-user-circle-gear"></i>
                            Terapeuta asignado
                        </label>
                        <select id="therapist_id" name="therapist_id" class="form-input @error('therapist_id') input-error @enderror">
                            <option value="">Selecciona un terapeuta</option>
                            @foreach($therapists as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} &lt;{{ $t->email }}&gt;</option>
                            @endforeach
                        </select>
                        @error('therapist_id')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Phone Field -->
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <i class="ph ph-phone"></i>
                            Teléfono (Opcional)
                        </label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="form-input @error('phone') input-error @enderror" 
                            value="{{ old('phone') }}" 
                            placeholder="+593 999 999 999"
                        >
                        @error('phone')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password Section -->
                    <div class="form-group full-width">
                        <div class="password-section-header">
                            <label class="form-label">
                                <i class="ph ph-lock"></i>
                                Contraseña
                            </label>
                            <span class="form-hint">La contraseña debe tener al menos 8 caracteres</span>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="ph ph-key"></i>
                            Contraseña
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input @error('password') input-error @enderror" 
                                required
                                placeholder="Mínimo 8 caracteres"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="ph ph-eye" id="password-icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">
                            <i class="ph ph-check-circle"></i>
                            Confirmar Contraseña
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                class="form-input" 
                                required
                                placeholder="Repite la contraseña"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                <i class="ph ph-eye" id="password_confirmation-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('admin.users') }}" class="btn-secondary">
                        <i class="ph ph-x"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="ph ph-user-plus"></i>
                        Crear Usuario
                    </button>
                </div>
            </form>
        </section>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('ph-eye');
                icon.classList.add('ph-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('ph-eye-slash');
                icon.classList.add('ph-eye');
            }
        }
    </script>
    <script>
        // Show therapist select only when role == 'patient'
        const roleSelect = document.getElementById('role');
        const therapistWrapper = document.getElementById('therapist-select-wrapper');
        const therapistSelect = document.getElementById('therapist_id');

        function syncRole() {
            if (roleSelect.value === 'patient') {
                therapistWrapper.style.display = '';
                therapistSelect.required = true;
            } else {
                therapistWrapper.style.display = 'none';
                therapistSelect.required = false;
                therapistSelect.selectedIndex = 0;
            }
        }

        roleSelect.addEventListener('change', syncRole);
        // init on load
        document.addEventListener('DOMContentLoaded', syncRole);
    </script>
</body>
</html>
