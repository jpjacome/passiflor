<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Plantillas - Passiflor Admin</title>
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
                <i class="ph ph-article"></i>
                Gestión de Plantillas
            </h1>
            <a href="{{ route('admin.therapies.create') }}" class="btn-create">
                <i class="ph ph-plus-circle"></i>
                Crear Plantilla
            </a>
            <a href="{{ route('admin.therapies.emdr') }}" class="btn-create btn-secondary" style="margin-left:12px;">
                <i class="ph ph-wave-square"></i>
                Bilateral Stimulation
            </a>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="ph ph-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Therapies Table -->
        <div class="table-responsive">
            <table class="therapies-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Terapeuta</th>
                        <th>Asignada a</th>
                        <th>Publicado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($therapies as $therapy)
                    <tr>
                        <td>
                            @php $publicUrl = $therapy->slug ? route('therapy.show', $therapy->slug) : route('therapy.show'); @endphp
                            <a href="{{ $publicUrl }}" target="_blank" class="therapy-link">{{ $therapy->title }}</a>
                        </td>
                        <td>{{ $therapy->author->name ?? '-' }}</td>
                        <td>
                            @if($therapy->therapist)
                                <a href="{{ route('admin.users.edit', $therapy->therapist) }}" class="therapist-link">{{ $therapy->therapist->name }}</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $therapy->assignedPatient->name ?? '-' }}</td>
                        <td>
                            @if($therapy->published)
                                <span class="badge-published"><i class="ph ph-check-circle"></i> Sí</span>
                            @else
                                <span class="badge-draft"><i class="ph ph-clock"></i> No</span>
                            @endif
                        </td>
                        <td class="actions-cell">
                            <a href="{{ route('admin.therapies.edit', $therapy) }}" class="btn-action btn-edit" title="Editar">
                                <i class="ph ph-pencil"></i>
                            </a>
                            <form action="{{ route('admin.therapies.destroy', $therapy) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('¿Eliminar esta plantilla?')" class="btn-action btn-delete" title="Eliminar">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="no-users-message">No hay plantillas disponibles.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($therapies->hasPages())
            <div class="pagination-wrapper">{{ $therapies->links() }}</div>
        @endif
    </div>
</body>
</html>
