<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Terapias</title>
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
</head>
<body>
    <x-admin-header />

    <div class="dashboard-wrapper">
        <div class="page-header">
            <h1 class="page-title">Terapias</h1>
            <a href="{{ route('admin.therapies.create') }}" class="btn-action btn-confirm">Crear Terapia</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table">
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
                            <a href="{{ $publicUrl }}" target="_blank">{{ $therapy->title }}</a>
                        </td>
                        <td>{{ $therapy->author->name ?? '-' }}</td>
                        <td>
                            @if($therapy->therapist)
                                <a href="{{ route('admin.users.edit', $therapy->therapist) }}">{{ $therapy->therapist->name }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $therapy->assignedPatient->name ?? '-' }}</td>
                        <td>{{ $therapy->published ? 'Sí' : 'No' }}</td>
                        <td>
                            <a href="{{ route('admin.therapies.edit', $therapy) }}" class="btn-action">Editar</a>
                            <form action="{{ route('admin.therapies.destroy', $therapy) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Eliminar esta terapia?')" class="btn-action btn-delete">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">No hay terapias.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">{{ $therapies->links() }}</div>
    </div>
</body>
</html>
