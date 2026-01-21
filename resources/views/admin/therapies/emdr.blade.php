<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilateral Stimulation - Asignar (Admin)</title>
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <x-admin-header />

    <div class="dashboard-wrapper">
        <div class="breadcrumb">
            <a href="{{ route('admin.therapies.index') }}" class="breadcrumb-link">
                <i class="ph ph-arrow-left"></i>
                Volver a Plantillas
            </a>
        </div>

        <div class="page-header">
            <h1 class="page-title">
                <i class="ph ph-wave-square"></i>
                Bilateral Stimulation — Asignar Terapeuta / Paciente
            </h1>
        </div>

        @if(session('success'))
            <div class="alert alert-success"><i class="ph ph-check-circle"></i> {{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <i class="ph ph-warning-circle"></i>
                <div>
                    <strong>Errores de validación:</strong>
                    <ul class="error-list">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="form-section therapy-form-section">
            <form action="{{ route('admin.therapies.assign') }}" method="POST" class="therapy-form">
                @csrf

                <div class="form-section-header">
                    <h3 class="form-section-title"><i class="ph ph-users"></i> Asignación de Terapeuta y Paciente</h3>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="ph ph-user-circle-gear"></i>
                            Terapeuta (opcional)
                        </label>
                        <select name="therapist_id" class="form-input">
                            <option value="">-- Seleccionar terapeuta --</option>
                            @foreach($therapists as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} &lt;{{ $t->email }}&gt;</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="patient-select-wrapper">
                        <label class="form-label">
                            <i class="ph ph-users"></i>
                            Paciente (opcional)
                        </label>
                        <select name="patient_id" id="patient_id" class="form-input">
                            <option value="">-- Seleccionar paciente --</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} &lt;{{ $p->email }}&gt;</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="form-divider">

                <div class="form-actions">
                    <a href="{{ route('admin.therapies.index') }}" class="btn-secondary">
                        <i class="ph ph-x"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="ph ph-check"></i>
                        Guardar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
