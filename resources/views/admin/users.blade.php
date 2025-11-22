<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Passiflor Admin</title>
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <x-admin-header />
    
    <div class="dashboard-wrapper">
        <!-- Page Header with Action Button -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="ph ph-users"></i>
                Gestión de Usuarios
            </h1>
            <a href="{{ route('admin.users.create') }}" class="btn-create">
                <i class="ph ph-user-plus"></i>
                Crear Usuario
            </a>
        </div>

        <!-- User Statistics -->
        <section class="users-stats-section">
            <div class="stats-container">
                <div class="stat-card">
                    <i class="ph ph-users stat-icon"></i>
                    <div class="stat-info">
                        <span class="stat-value">{{ $counts['total'] }}</span>
                        <span class="stat-label">Total Usuarios</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="ph ph-crown stat-icon"></i>
                    <div class="stat-info">
                        <span class="stat-value">{{ $counts['admins'] }}</span>
                        <span class="stat-label">Administradores</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="ph ph-user-circle-gear stat-icon"></i>
                    <div class="stat-info">
                        <span class="stat-value">{{ $counts['therapists'] }}</span>
                        <span class="stat-label">Terapeutas</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="ph ph-heart stat-icon"></i>
                    <div class="stat-info">
                        <span class="stat-value">{{ $counts['patients'] }}</span>
                        <span class="stat-label">Pacientes</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="ph ph-user stat-icon"></i>
                    <div class="stat-info">
                        <span class="stat-value">{{ $counts['guests'] }}</span>
                        <span class="stat-label">Invitados</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="alert alert-success">
            <i class="ph ph-check-circle"></i>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            <i class="ph ph-warning-circle"></i>
            {{ session('error') }}
        </div>
        @endif

        <!-- Administrators Section -->
        <section class="users-role-section">
            <div class="role-header">
                <h2 class="role-title">
                    <i class="ph ph-crown"></i>
                    Administradores
                </h2>
                <span class="role-count">{{ $counts['admins'] }}</span>
            </div>
            <div class="users-grid">
                @forelse($users['admins'] as $user)
                <div class="user-card">
                    <div class="user-card-header">
                        <div class="user-avatar">
                            <i class="ph ph-crown"></i>
                        </div>
                        <div class="user-info">
                            <h3 class="user-name">{{ $user->name }}</h3>
                            <span class="user-role-badge admin">Admin</span>
                        </div>
                    </div>
                    <div class="user-card-body">
                        <div class="user-detail">
                            <i class="ph ph-envelope"></i>
                            <span>{{ $user->email }}</span>
                        </div>
                        @if($user->phone)
                        <div class="user-detail">
                            <i class="ph ph-phone"></i>
                            <span>{{ $user->phone }}</span>
                        </div>
                        @endif
                        {{-- (no therapist info for administrators) --}}
                        <div class="user-detail">
                            <i class="ph ph-calendar"></i>
                            <span>Creado: {{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    <div class="user-card-actions">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-action btn-edit">
                            <i class="ph ph-pencil-simple"></i>
                            Editar
                        </a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                <i class="ph ph-trash"></i>
                                Eliminar
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <p class="no-users-message">No hay administradores registrados.</p>
                @endforelse
            </div>
            @if($users['admins']->hasPages())
            <div class="pagination-wrapper">
                {{ $users['admins']->links() }}
            </div>
            @endif
        </section>

        <!-- Therapists Section -->
        <section class="users-role-section">
            <div class="role-header">
                <h2 class="role-title">
                    <i class="ph ph-user-circle-gear"></i>
                    Terapeutas
                </h2>
                <span class="role-count">{{ $counts['therapists'] }}</span>
            </div>
            <div class="users-grid">
                @forelse($users['therapists'] as $user)
                <div class="user-card">
                    <div class="user-card-header">
                        <div class="user-avatar">
                            <i class="ph ph-user-circle-gear"></i>
                        </div>
                        <div class="user-info">
                            <h3 class="user-name">{{ $user->name }}</h3>
                            <span class="user-role-badge therapist">Terapeuta</span>
                        </div>
                    </div>
                    <div class="user-card-body">
                        <div class="user-detail">
                            <i class="ph ph-envelope"></i>
                            <span>{{ $user->email }}</span>
                        </div>
                        @if($user->phone)
                        <div class="user-detail">
                            <i class="ph ph-phone"></i>
                            <span>{{ $user->phone }}</span>
                        </div>
                        @endif
                        {{-- therapists should not display an assigned therapist here --}}
                        <div class="user-detail">
                            <i class="ph ph-calendar"></i>
                            <span>Creado: {{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    <div class="user-card-actions">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-action btn-edit">
                            <i class="ph ph-pencil-simple"></i>
                            Editar
                        </a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                <i class="ph ph-trash"></i>
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="no-users-message">No hay terapeutas registrados.</p>
                @endforelse
            </div>
            @if($users['therapists']->hasPages())
            <div class="pagination-wrapper">
                {{ $users['therapists']->links() }}
            </div>
            @endif
        </section>

        <!-- Patients Section -->
        <section class="users-role-section">
            <div class="role-header">
                <h2 class="role-title">
                    <i class="ph ph-heart"></i>
                    Pacientes
                </h2>
                <span class="role-count">{{ $counts['patients'] }}</span>
            </div>
            <div class="users-grid">
                @forelse($users['patients'] as $user)
                <div class="user-card">
                    <div class="user-card-header">
                        <div class="user-avatar">
                            <i class="ph ph-heart"></i>
                        </div>
                        <div class="user-info">
                            <h3 class="user-name">{{ $user->name }}</h3>
                            <span class="user-role-badge patient">Paciente</span>
                        </div>
                    </div>
                    <div class="user-card-body">
                        <div class="user-detail">
                            <i class="ph ph-envelope"></i>
                            <span>{{ $user->email }}</span>
                        </div>
                        @if($user->phone)
                        <div class="user-detail">
                            <i class="ph ph-phone"></i>
                            <span>{{ $user->phone }}</span>
                        </div>
                        @endif
                        @if($user->therapist)
                        <div class="user-detail">
                            <i class="ph ph-user"></i>
                            <span>Terapeuta: {{ $user->therapist->name }} &lt;{{ $user->therapist->email }}&gt;</span>
                        </div>
                        @endif
                        <div class="user-detail">
                            <i class="ph ph-calendar"></i>
                            <span>Creado: {{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    <div class="user-card-actions">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-action btn-edit">
                            <i class="ph ph-pencil-simple"></i>
                            Editar
                        </a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                <i class="ph ph-trash"></i>
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="no-users-message">No hay pacientes registrados.</p>
                @endforelse
            </div>
            @if($users['patients']->hasPages())
            <div class="pagination-wrapper">
                {{ $users['patients']->links() }}
            </div>
            @endif
        </section>

        <!-- Guests Section -->
        <section class="users-role-section">
            <div class="role-header">
                <h2 class="role-title">
                    <i class="ph ph-user"></i>
                    Invitados
                </h2>
                <span class="role-count">{{ $counts['guests'] }}</span>
            </div>
            <div class="users-grid">
                @forelse($users['guests'] as $user)
                <div class="user-card">
                    <div class="user-card-header">
                        <div class="user-avatar">
                            <i class="ph ph-user"></i>
                        </div>
                        <div class="user-info">
                            <h3 class="user-name">{{ $user->name }}</h3>
                            <span class="user-role-badge guest">Invitado</span>
                        </div>
                    </div>
                    <div class="user-card-body">
                        <div class="user-detail">
                            <i class="ph ph-envelope"></i>
                            <span>{{ $user->email }}</span>
                        </div>
                        @if($user->phone)
                        <div class="user-detail">
                            <i class="ph ph-phone"></i>
                            <span>{{ $user->phone }}</span>
                        </div>
                        @endif
                        <div class="user-detail">
                            <i class="ph ph-calendar"></i>
                            <span>Creado: {{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    <div class="user-card-actions">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-action btn-edit">
                            <i class="ph ph-pencil-simple"></i>
                            Editar
                        </a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                <i class="ph ph-trash"></i>
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="no-users-message">No hay invitados registrados.</p>
                @endforelse
            </div>
            @if($users['guests']->hasPages())
            <div class="pagination-wrapper">
                {{ $users['guests']->links() }}
            </div>
            @endif
        </section>
    </div>
</body>
</html>
