<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Consultas - Passiflor Admin</title>
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <x-admin-header />
    
    <div class="dashboard-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="ph ph-calendar-check"></i>
                Gestión de Consultas
            </h1>
        </div>

        <!-- Consultation Statistics -->
        <section class="users-stats-section">
            <div class="stats-container">
                <div class="stat-card">
                    <i class="ph ph-calendar-check stat-icon"></i>
                    <div class="stat-info">
                        <span class="stat-value">{{ $counts['total'] }}</span>
                        <span class="stat-label">Total Consultas</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="ph ph-clock stat-icon"></i>
                    <div class="stat-info">
                        <span class="stat-value">{{ $counts['pending'] }}</span>
                        <span class="stat-label">Pendientes</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="ph ph-check-circle stat-icon"></i>
                    <div class="stat-info">
                        <span class="stat-value">{{ $counts['confirmed'] }}</span>
                        <span class="stat-label">Confirmadas</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="ph ph-check-square stat-icon"></i>
                    <div class="stat-info">
                        <span class="stat-value">{{ $counts['completed'] }}</span>
                        <span class="stat-label">Completadas</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="ph ph-x-circle stat-icon"></i>
                    <div class="stat-info">
                        <span class="stat-value">{{ $counts['cancelled'] }}</span>
                        <span class="stat-label">Canceladas</span>
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

        <!-- Pending Consultations Section -->
        <section class="users-role-section">
            <div class="role-header">
                <h2 class="role-title">
                    <i class="ph ph-clock"></i>
                    Consultas Pendientes
                </h2>
                <span class="role-count">{{ $counts['pending'] }}</span>
            </div>
            <div class="users-grid">
                @forelse($consultations['pending'] as $consultation)
                <div class="user-card consultation-card">
                    <div class="user-card-header">
                        <div class="user-avatar">
                            <i class="ph ph-user"></i>
                        </div>
                        <div class="user-info">
                            <h3 class="user-name">{{ $consultation->full_name }}</h3>
                            <span class="user-role-badge pending">Pendiente</span>
                        </div>
                    </div>
                    <div class="user-card-body">
                        <div class="user-detail">
                            <i class="ph ph-envelope"></i>
                            <span>{{ $consultation->email }}</span>
                        </div>
                        @if($consultation->phone)
                        <div class="user-detail">
                            <i class="ph ph-phone"></i>
                            <span>{{ $consultation->phone }}</span>
                        </div>
                        @endif
                        <div class="user-detail">
                            <i class="ph ph-calendar"></i>
                            <span>{{ $consultation->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="user-detail">
                            <i class="ph ph-tag"></i>
                            <span>{{ ucfirst(str_replace('-', ' ', $consultation->session_type)) }}</span>
                        </div>
                        @if($consultation->message)
                        <div class="user-detail">
                            <i class="ph ph-note"></i>
                            <span class="consultation-message">{{ Str::limit($consultation->message, 80) }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="user-card-actions">
                        <form action="{{ route('admin.consultations.updateStatus', $consultation) }}" method="POST" style="flex: 1;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit" class="btn-action btn-confirm">
                                <i class="ph ph-check"></i>
                                Confirmar
                            </button>
                        </form>
                        <form action="{{ route('admin.consultations.updateStatus', $consultation) }}" method="POST" style="flex: 1;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn-action btn-cancel" onclick="return confirm('¿Estás seguro de cancelar esta consulta?')">
                                <i class="ph ph-x"></i>
                                Cancelar
                            </button>
                        </form>
                        <form action="{{ route('admin.consultations.destroy', $consultation) }}" method="POST" style="flex: 1;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete" onclick="return confirm('¿Estás seguro de eliminar esta consulta?')">
                                <i class="ph ph-trash"></i>
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="no-users-message">No hay consultas pendientes.</p>
                @endforelse
            </div>
            @if($consultations['pending']->hasPages())
            <div class="pagination-wrapper">
                {{ $consultations['pending']->links() }}
            </div>
            @endif
        </section>

        <!-- Confirmed Consultations Section -->
        <section class="users-role-section">
            <div class="role-header">
                <h2 class="role-title">
                    <i class="ph ph-check-circle"></i>
                    Consultas Confirmadas
                </h2>
                <span class="role-count">{{ $counts['confirmed'] }}</span>
            </div>
            <div class="users-grid">
                @forelse($consultations['confirmed'] as $consultation)
                <div class="user-card consultation-card">
                    <div class="user-card-header">
                        <div class="user-avatar">
                            <i class="ph ph-user"></i>
                        </div>
                        <div class="user-info">
                            <h3 class="user-name">{{ $consultation->full_name }}</h3>
                            <span class="user-role-badge confirmed">Confirmada</span>
                        </div>
                    </div>
                    <div class="user-card-body">
                        <div class="user-detail">
                            <i class="ph ph-envelope"></i>
                            <span>{{ $consultation->email }}</span>
                        </div>
                        @if($consultation->phone)
                        <div class="user-detail">
                            <i class="ph ph-phone"></i>
                            <span>{{ $consultation->phone }}</span>
                        </div>
                        @endif
                        <div class="user-detail">
                            <i class="ph ph-calendar"></i>
                            <span>{{ $consultation->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="user-detail">
                            <i class="ph ph-tag"></i>
                            <span>{{ ucfirst(str_replace('-', ' ', $consultation->session_type)) }}</span>
                        </div>
                        @if($consultation->message)
                        <div class="user-detail">
                            <i class="ph ph-note"></i>
                            <span class="consultation-message">{{ Str::limit($consultation->message, 80) }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="user-card-actions">
                        <form action="{{ route('admin.consultations.updateStatus', $consultation) }}" method="POST" style="flex: 1;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn-action btn-complete">
                                <i class="ph ph-check-square"></i>
                                Completar
                            </button>
                        </form>
                        <form action="{{ route('admin.consultations.updateStatus', $consultation) }}" method="POST" style="flex: 1;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn-action btn-cancel" onclick="return confirm('¿Estás seguro de cancelar esta consulta?')">
                                <i class="ph ph-x"></i>
                                Cancelar
                            </button>
                        </form>
                        <form action="{{ route('admin.consultations.destroy', $consultation) }}" method="POST" style="flex: 1;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete" onclick="return confirm('¿Estás seguro de eliminar esta consulta?')">
                                <i class="ph ph-trash"></i>
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="no-users-message">No hay consultas confirmadas.</p>
                @endforelse
            </div>
            @if($consultations['confirmed']->hasPages())
            <div class="pagination-wrapper">
                {{ $consultations['confirmed']->links() }}
            </div>
            @endif
        </section>

        <!-- Completed Consultations Section -->
        <section class="users-role-section">
            <div class="role-header">
                <h2 class="role-title">
                    <i class="ph ph-check-square"></i>
                    Consultas Completadas
                </h2>
                <span class="role-count">{{ $counts['completed'] }}</span>
            </div>
            <div class="table-responsive">
                <table class="consultations-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Mensaje</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consultations['completed'] as $consultation)
                        <tr>
                            <td>{{ $consultation->full_name }}</td>
                            <td>{{ $consultation->email }}</td>
                            <td>{{ $consultation->phone ?? '-' }}</td>
                            <td>{{ $consultation->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ ucfirst(str_replace('-', ' ', $consultation->session_type)) }}</td>
                            <td>{{ Str::limit($consultation->message, 60) }}</td>
                            <td>
                                <form action="{{ route('admin.consultations.destroy', $consultation) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta consulta?')">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="no-users-message">No hay consultas completadas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($consultations['completed']->hasPages())
            <div class="pagination-wrapper">
                {{ $consultations['completed']->links() }}
            </div>
            @endif
        </section>

        <!-- Cancelled Consultations Section -->
        <section class="users-role-section">
            <div class="role-header">
                <h2 class="role-title">
                    <i class="ph ph-x-circle"></i>
                    Consultas Canceladas
                </h2>
                <span class="role-count">{{ $counts['cancelled'] }}</span>
            </div>
            <div class="table-responsive">
                <table class="consultations-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Mensaje</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consultations['cancelled'] as $consultation)
                        <tr>
                            <td>{{ $consultation->full_name }}</td>
                            <td>{{ $consultation->email }}</td>
                            <td>{{ $consultation->phone ?? '-' }}</td>
                            <td>{{ $consultation->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ ucfirst(str_replace('-', ' ', $consultation->session_type)) }}</td>
                            <td>{{ Str::limit($consultation->message, 60) }}</td>
                            <td>
                                <form action="{{ route('admin.consultations.destroy', $consultation) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta consulta?')">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="no-users-message">No hay consultas canceladas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($consultations['cancelled']->hasPages())
            <div class="pagination-wrapper">
                {{ $consultations['cancelled']->links() }}
            </div>
            @endif
        </section>
    </div>
</body>
</html>
