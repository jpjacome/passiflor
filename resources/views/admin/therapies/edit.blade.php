<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Terapia</title>
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
</head>
<body>
    <x-admin-header />

    <div class="dashboard-wrapper">
        <div class="page-header">
            <h1 class="page-title">Editar Terapia</h1>
        </div>

        <div style="margin-bottom:12px;">
            @php $publicUrl = $therapy->slug ? route('therapy.show', $therapy->slug) : route('therapy.show'); @endphp
            <a href="{{ $publicUrl }}" target="_blank" class="btn-action btn-view">Ver página pública</a>
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

        @php $hero = $therapy->pages->where('type', 'hero')->first(); @endphp
        <form action="{{ route('admin.therapies.update', $therapy) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" value="{{ $therapy->slug }}" required>
            </div>
            <div class="form-group">
                <label>Título (hero)</label>
                <input type="text" name="title" value="{{ old('title', $hero->title ?? $therapy->title) }}" required>
            </div>
            <div class="form-group">
                <label>Subtítulo (hero)</label>
                <input type="text" name="subtitle" value="{{ old('subtitle', $hero->subtitle ?? '') }}">
            </div>
            <div class="form-group">
                <label>Descripción (hero)</label>
                <textarea name="description">{{ old('description', $hero->body ?? '') }}</textarea>
            </div>
            <div class="form-group">
                <label>Imagen de portada (max 3MB)</label>
                {{-- images ignored for now --}}
            </div>

            <div class="form-group">
                <label>Publicado</label>
                <input type="checkbox" name="published" value="1" {{ $therapy->published ? 'checked' : '' }}>
            </div>

            <hr>
            <h3>Asignar terapeuta</h3>
            <div class="form-group">
                <label>Terapeuta</label>
                <select name="therapist_id">
                    <option value="">-- Seleccionar terapeuta --</option>
                    @foreach($therapists as $t)
                        <option value="{{ $t->id }}" {{ ($therapy->therapist_id == $t->id) ? 'selected' : '' }}>{{ $t->name }} &lt;{{ $t->email }}&gt;</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" id="patient-select-wrapper" style="display:{{ $therapy->therapist_id ? '' : 'none' }};">
                <label>Paciente (asignar al terapeuta seleccionado)</label>
                <select name="assigned_patient_id" id="assigned_patient_id">
                    <option value="">-- Seleccionar paciente --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ ($therapy->assigned_patient_id == $p->id) ? 'selected' : '' }}>{{ $p->name }} &lt;{{ $p->email }}&gt;</option>
                    @endforeach
                </select>
            </div>

            <hr>
            <h3>Páginas adicionales (Step / Info)</h3>
            <div id="pages-container">
                @foreach($therapy->pages->where('type', '!=', 'hero') as $page)
                <div class="page-item" style="border:1px solid #ddd;padding:10px;margin-bottom:8px;">
                    <input type="hidden" name="pages[][id]" value="{{ $page->id }}">
                    <div>
                        <label>Tipo</label>
                        <select name="pages[][type]">
                            <option value="step" {{ $page->type === 'step' ? 'selected' : '' }}>Step</option>
                            <option value="info" {{ $page->type === 'info' ? 'selected' : '' }}>Info</option>
                        </select>
                        <label>Numero (step)</label>
                        <input type="number" name="pages[][number]" style="width:80px" value="{{ $page->number }}">
                    </div>
                    <div>
                        <label>Título</label>
                        <input type="text" name="pages[][title]" value="{{ $page->title }}">
                    </div>
                    <div>
                        <label>Subtítulo</label>
                        <input type="text" name="pages[][subtitle]" value="{{ $page->subtitle }}">
                    </div>
                    <div>
                        <label>Texto (o líneas)</label>
                        <textarea name="pages[][body]">{{ $page->body }}</textarea>
                    </div>
                    <div>
                        <label>Nota</label>
                        <input type="text" name="pages[][note]" value="{{ $page->note }}">
                    </div>
                    <div>
                        {{-- images ignored for now --}}
                    </div>
                    <div>
                        <button type="button" class="remove-page btn-action btn-delete">Eliminar página</button>
                    </div>
                </div>
                @endforeach
            </div>

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

        document.querySelectorAll('.remove-page').forEach(btn => {
            btn.addEventListener('click', (e) => e.target.closest('.page-item').remove());
        });

        // Show patient select only after therapist is chosen (same behavior as create)
        const therapistSelect = document.querySelector('select[name="therapist_id"]');
        const patientWrapper = document.getElementById('patient-select-wrapper');
        const patientSelect = document.getElementById('assigned_patient_id');

        if (therapistSelect) {
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
        }
    </script>
</body>
</html>
