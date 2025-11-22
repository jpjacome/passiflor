<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Terapia</title>
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
</head>
<body>
    <x-admin-header />

    <div class="dashboard-wrapper">
        <div class="page-header">
            <h1 class="page-title">Crear Terapia</h1>
        </div>

        @if($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.therapies.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" value="" required>
            </div>
            <div class="form-group">
                <label>Título (hero)</label>
                <input type="text" name="title" value="" required>
            </div>
            <div class="form-group">
                <label>Subtítulo (hero)</label>
                <input type="text" name="subtitle" value="">
            </div>
            <div class="form-group">
                <label>Descripción (hero)</label>
                <textarea name="description"></textarea>
            </div>
            {{-- assigned patient will be chosen after selecting a therapist --}}
            <div class="form-group">
                <label>Publicado</label>
                <input type="checkbox" name="published" value="1">
            </div>

            <hr>
            <h3>Asignar terapeuta</h3>
            <div class="form-group">
                <label>Terapeuta</label>
                <select name="therapist_id">
                    <option value="">-- Seleccionar terapeuta --</option>
                    @foreach($therapists as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} &lt;{{ $t->email }}&gt;</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" id="patient-select-wrapper" style="display:none;">
                <label>Paciente (asignar al terapeuta seleccionado)</label>
                <select name="assigned_patient_id" id="assigned_patient_id" disabled>
                    <option value="">-- Seleccionar paciente --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} &lt;{{ $p->email }}&gt;</option>
                    @endforeach
                </select>
            </div>

            <hr>
            <h3>Páginas adicionales (tipos permitidos: Step, Info)</h3>
            <div id="pages-container"></div>

            <button type="button" id="add-page" class="btn-action">Agregar página</button>

            <div style="margin-top:20px;">
                <button type="submit" class="btn-action btn-confirm">Guardar</button>
                <a href="{{ route('admin.therapies.index') }}" class="btn-action btn-cancel">Cancelar</a>
            </div>
        </form>
    </div>

    <template id="page-template">
        <div class="page-item" style="border:1px solid #ddd;padding:10px;margin-bottom:8px;">
            <div>
                <label>Tipo</label>
                <select name="pages[][type]">
                    <option value="step" selected>Step</option>
                    <option value="info">Info</option>
                </select>
                <label>Numero (step)</label>
                <input type="number" name="pages[][number]" style="width:80px">
            </div>
            <div>
                <label>Título</label>
                <input type="text" name="pages[][title]">
            </div>
            <div>
                <label>Subtítulo</label>
                <input type="text" name="pages[][subtitle]">
            </div>
            <div>
                <label>Texto (o líneas)</label>
                <textarea name="pages[][body]"></textarea>
            </div>
            <div>
                <label>Nota</label>
                <input type="text" name="pages[][note]">
            </div>
            <div>
                <button type="button" class="remove-page btn-action btn-delete">Eliminar página</button>
            </div>
        </div>
    </template>

    <script>
        const addPageBtn = document.getElementById('add-page');
        const pagesContainer = document.getElementById('pages-container');
        const template = document.getElementById('page-template');

        addPageBtn.addEventListener('click', () => {
            const clone = template.content.cloneNode(true);
            const removeBtn = clone.querySelector('.remove-page');
            removeBtn.addEventListener('click', (e) => {
                e.target.closest('.page-item').remove();
            });
            pagesContainer.appendChild(clone);
        });

        // Show patient select only after therapist is chosen
        const therapistSelect = document.querySelector('select[name="therapist_id"]');
        const patientWrapper = document.getElementById('patient-select-wrapper');
        const patientSelect = document.getElementById('assigned_patient_id');

        therapistSelect.addEventListener('change', (e) => {
            const val = e.target.value;
            if (val) {
                patientWrapper.style.display = '';
                patientSelect.disabled = false;
            } else {
                patientSelect.selectedIndex = 0;
                patientSelect.disabled = true;
                patientWrapper.style.display = 'none';
            }
        });
    </script>
</body>
</html>
