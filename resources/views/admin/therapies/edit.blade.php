<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Plantilla - Passiflor Admin</title>
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <x-admin-header />

    <div class="dashboard-wrapper">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="{{ route('admin.therapies.index') }}" class="breadcrumb-link">
                <i class="ph ph-arrow-left"></i>
                Volver a Plantillas
            </a>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="ph ph-pencil"></i>
                Editar Plantilla
            </h1>
            @php $publicUrl = $therapy->slug ? route('therapy.show', $therapy->slug) : route('therapy.show'); @endphp
            <a href="{{ $publicUrl }}" target="_blank" class="btn-create btn-view">
                <i class="ph ph-eye"></i>
                Ver Página Pública
            </a>
        </div>

        <!-- Error Alert -->
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

        @php $hero = $therapy->pages->where('type', 'hero')->first(); @endphp
        
        <!-- Form Section -->
        <div class="form-section therapy-form-section">
            <form action="{{ route('admin.therapies.update', $therapy) }}" method="POST" enctype="multipart/form-data" class="therapy-form">
            <form action="{{ route('admin.therapies.update', $therapy) }}" method="POST" enctype="multipart/form-data" class="therapy-form">
                @csrf
                @method('PATCH')
                
                <!-- Basic Info Section -->
                <div class="form-section-header">
                    <h3 class="form-section-title"><i class="ph ph-link"></i> Información Básica</h3>
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">
                        <i class="ph ph-link"></i>
                        Slug (URL de la terapia)
                    </label>
                    <input type="text" name="slug" value="{{ old('slug', $therapy->slug) }}" class="form-input" required>
                    <span class="form-hint">Este será parte de la URL pública de la terapia</span>
                </div>

                <hr class="form-divider">

                <!-- Assignment Section -->
                <div class="form-section-header">
                    <h3 class="form-section-title"><i class="ph ph-users"></i> Asignación de Terapeuta y Paciente</h3>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="ph ph-user-circle-gear"></i>
                            Terapeuta (opcional)
                        </label>
                        <select name="therapist_id" class="form-input" {{ auth()->user()->actingAsTherapist() ? 'disabled' : '' }}>
                            <option value="">-- Seleccionar terapeuta --</option>
                            @foreach($therapists as $t)
                                <option value="{{ $t->id }}" {{ ($therapy->therapist_id == $t->id) ? 'selected' : '' }}>
                                    {{ $t->name }} &lt;{{ $t->email }}&gt;
                                </option>
                            @endforeach
                        </select>
                        @if(auth()->user()->actingAsTherapist())
                            <input type="hidden" name="therapist_id" value="{{ $therapy->therapist_id }}">
                            <span class="form-hint">En modo terapeuta, solo puedes editar tus propias plantillas.</span>
                        @endif
                    </div>

                    <div class="form-group" id="patient-select-wrapper">
                        <label class="form-label">
                            <i class="ph ph-users"></i>
                            Paciente (opcional)
                        </label>
                        <select name="assigned_patient_id" id="assigned_patient_id" class="form-input">
                            <option value="">-- Sin paciente --</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}" {{ (old('assigned_patient_id', $therapy->assigned_patient_id) == $p->id) ? 'selected' : '' }}>
                                    {{ $p->name }} &lt;{{ $p->email }}&gt;
                                </option>
                            @endforeach
                        </select>
                        @if(auth()->user()->actingAsTherapist())
                            <span class="form-hint">Solo se muestran tus pacientes asignados.</span>
                        @endif
                    </div> 
                </div>

                <hr class="form-divider">

                <!-- Hero Content Section -->
                <div class="form-section-header">
                    <h3 class="form-section-title"><i class="ph ph-text-align-left"></i> Contenido del Hero</h3>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="ph ph-text-t"></i>
                            Título de la plantilla
                        </label>
                        <input type="text" name="title" value="{{ old('title', $hero->title ?? $therapy->title) }}" class="form-input" required placeholder="Ej: Entrenamiento para ir al baño">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="ph ph-text-align-center"></i>
                            Subtítulo (opcional)
                        </label>
                        <input type="text" name="subtitle" value="{{ old('subtitle', $hero->subtitle ?? '') }}" class="form-input" placeholder="Ej: Planificación terapéutica">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label checkbox-label">
                        <input type="checkbox" name="published" value="1" {{ old('published', $therapy->published) ? 'checked' : '' }}>
                        <span><i class="ph ph-eye"></i> Publicar plantilla (visible para pacientes)</span>
                    </label>
                </div>

                <hr class="form-divider">

                <!-- Pages Section -->
                <div class="form-section-header">
                    <h3 class="form-section-title"><i class="ph ph-files"></i> Páginas Adicionales</h3>
                    <p class="form-section-description">Edita las páginas existentes o agrega nuevas de tipo Step (listas con viñetas) o Info (párrafos de texto).</p>
                </div>

                <div id="pages-container" class="pages-container">
                    @foreach($therapy->pages->where('type', '!=', 'hero') as $page)
                    <div class="page-item" data-page-type="{{ $page->type }}">
                        <input type="hidden" name="pages[][id]" value="{{ $page->id }}">
                        
                        <div class="page-item-header">
                            <h4 class="page-item-title"><i class="ph ph-file-text"></i> Página #{{ $page->number }}</h4>
                            <button type="button" class="remove-page btn-action btn-delete btn-icon" style="flex:0">
                                <i class="ph ph-trash"style="padding: 1rem;"></i>
                            </button>
                        </div>
                        
                        <div class="page-item-body">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Tipo de página</label>
                                    <select name="pages[][type]" class="page-type-select form-input">
                                        <option value="step" {{ $page->type === 'step' ? 'selected' : '' }}>Step (lista con bullets)</option>
                                        <option value="info" {{ $page->type === 'info' ? 'selected' : '' }}>Info (párrafo)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Número de orden</label>
                                    <input type="number" name="pages[][number]" class="form-input" value="{{ $page->number }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Título</label>
                                <input type="text" name="pages[][title]" class="form-input" value="{{ $page->title }}">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Subtítulo (opcional)</label>
                                <input type="text" name="pages[][subtitle]" class="form-input" value="{{ $page->subtitle }}">
                            </div>
                            
                            <!-- Content for INFO type (paragraph) -->
                            <div class="info-content" style="display:{{ $page->type === 'info' ? '' : 'none' }};">
                                <div class="form-group">
                                    <label class="form-label">Texto (párrafo)</label>
                                    <textarea name="pages[][body]" rows="5" class="form-input" placeholder="Escribe el texto del párrafo aquí...">{{ $page->type === 'info' ? $page->body : '' }}</textarea>
                                </div>
                            </div>
                            
                            <!-- Content for STEP type (list items) -->
                            <div class="step-content" style="display:{{ $page->type === 'step' ? '' : 'none' }};">
                                <label class="form-label">Items de la lista</label>
                                <div class="list-items-container">
                                    @if($page->type === 'step' && $page->list_items && is_array($page->list_items))
                                        @foreach($page->list_items as $idx => $item)
                                        <div class="list-item-wrapper">
                                            <input type="text" name="pages[][list_items][]" class="form-input" placeholder="Item {{ $idx + 1 }}" value="{{ $item }}">
                                            <button type="button" class="remove-list-item btn-action btn-delete btn-sm">
                                                <i class="ph ph-x"></i>
                                            </button>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="list-item-wrapper">
                                            <input type="text" name="pages[][list_items][]" class="form-input" placeholder="Item 1">
                                            <button type="button" class="remove-list-item btn-action btn-delete btn-sm">
                                                <i class="ph ph-x"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="add-list-item btn-action btn-add-item">
                                    <i class="ph ph-plus"></i>
                                    Agregar item
                                </button>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Nota (opcional)</label>
                                <input type="text" name="pages[][note]" class="form-input" value="{{ $page->note }}" placeholder="Ej: *Basado en...">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="button" id="add-page" class="btn-action btn-add-page">
                    <i class="ph ph-plus-circle"></i>
                    Agregar Página
                </button>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('admin.therapies.index') }}" class="btn-secondary">
                        <i class="ph ph-x"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="ph ph-check"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <template id="page-template">
        <div class="page-item">
            <div class="page-item-header">
                <h4 class="page-item-title"><i class="ph ph-file-text"></i> Nueva Página</h4>
                <button type="button" class="remove-page btn-action btn-delete btn-icon" style="flex:0">
                    <i class="ph ph-trash" style="padding: 1rem;"></i>
                </button>
            </div>
            
            <div class="page-item-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Tipo de página</label>
                        <select name="pages[][type]" class="page-type-select form-input">
                            <option value="step" selected>Step (lista con bullets)</option>
                            <option value="info">Info (párrafo)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Número de orden</label>
                        <input type="number" name="pages[][number]" class="form-input" placeholder="1">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Título</label>
                    <input type="text" name="pages[][title]" class="form-input" placeholder="Título de la página">
                </div>

                <div class="form-group">
                    <label class="form-label">Subtítulo (opcional)</label>
                    <input type="text" name="pages[][subtitle]" class="form-input" placeholder="Subtítulo descriptivo">
                </div>
                
                <!-- Content for INFO type (paragraph) -->
                <div class="info-content" style="display:none;">
                    <div class="form-group">
                        <label class="form-label">Texto (párrafo)</label>
                        <textarea name="pages[][body]" rows="5" class="form-input" placeholder="Escribe el texto del párrafo aquí..."></textarea>
                    </div>
                </div>
                
                <!-- Content for STEP type (list items) -->
                <div class="step-content">
                    <label class="form-label">Items de la lista</label>
                    <div class="list-items-container">
                        <div class="list-item-wrapper">
                            <input type="text" name="pages[][list_items][]" class="form-input" placeholder="Item 1">
                            <button type="button" class="remove-list-item btn-action btn-delete btn-sm">
                                <i class="ph ph-x"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="add-list-item btn-action btn-add-item">
                        <i class="ph ph-plus"></i>
                        Agregar item
                    </button>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nota (opcional)</label>
                    <input type="text" name="pages[][note]" class="form-input" placeholder="Ej: *Basado en...">
                </div>
            </div>
        </div>
    </template>

    <script>
        const addPageBtn = document.getElementById('add-page');
        const pagesContainer = document.getElementById('pages-container');
        const template = document.getElementById('page-template');
        let pageIndex = document.querySelectorAll('.page-item').length;

        function setupPageTypeToggle(pageItem, idx) {
            const typeSelect = pageItem.querySelector('.page-type-select');
            const infoContent = pageItem.querySelector('.info-content');
            const stepContent = pageItem.querySelector('.step-content');
            
            typeSelect.addEventListener('change', (e) => {
                if (e.target.value === 'info') {
                    infoContent.style.display = '';
                    stepContent.style.display = 'none';
                    // Clear list items when switching to info
                    stepContent.querySelectorAll('input[name*="list_items"]').forEach(input => input.value = '');
                } else {
                    infoContent.style.display = 'none';
                    stepContent.style.display = '';
                    // Clear body when switching to step
                    infoContent.querySelector('textarea').value = '';
                }
            });
        }

        function setupListItemControls(pageItem, idx) {
            const addItemBtn = pageItem.querySelector('.add-list-item');
            const itemsContainer = pageItem.querySelector('.list-items-container');

            // Normalize existing list item names to include the page index
            itemsContainer.querySelectorAll('input').forEach((input, i) => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/pages\[(?:\d*)\]\[([^\]]+)\]/, `pages[${idx}][$1]`).replace(/pages\[\]\[([^\]]+)\]/, `pages[${idx}][$1]`));
                }
            });
            
            addItemBtn.addEventListener('click', () => {
                const itemCount = itemsContainer.children.length + 1;
                const newItem = document.createElement('div');
                newItem.className = 'list-item-wrapper';
                newItem.style.marginBottom = '5px';
                newItem.innerHTML = `
                    <input type="text" name="pages[${idx}][list_items][]" placeholder="Item ${itemCount}" style="width:calc(100% - 80px)">
                    <button type="button" class="remove-list-item btn-action btn-delete" style="width:70px;">Eliminar</button>
                `;
                
                newItem.querySelector('.remove-list-item').addEventListener('click', (e) => {
                    const wrapper = e.target.closest('.list-item-wrapper');
                    if (itemsContainer.children.length > 1) {
                        wrapper.remove();
                    } else {
                        wrapper.querySelector('input').value = '';
                    }
                });
                
                itemsContainer.appendChild(newItem);
            });
            
            // Setup remove for all existing items
            itemsContainer.querySelectorAll('.remove-list-item').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const wrapper = e.target.closest('.list-item-wrapper');
                    if (itemsContainer.children.length > 1) {
                        wrapper.remove();
                    } else {
                        wrapper.querySelector('input').value = '';
                    }
                });
            });
        }

        function reindexPage(pageItem, idx) {
            pageItem.querySelectorAll('select, input, textarea').forEach(input => {
                const name = input.getAttribute('name');
                if (!name) return;
                const newName = name.replace(/pages\[(?:\d*)\]\[([^\]]+)\]/, `pages[${idx}][$1]`).replace(/pages\[\]\[([^\]]+)\]/, `pages[${idx}][$1]`);
                input.setAttribute('name', newName);
            });
        }

        function reindexPages() {
            document.querySelectorAll('.page-item').forEach((pageItem, i) => {
                reindexPage(pageItem, i);
            });
            pageIndex = document.querySelectorAll('.page-item').length;
        }

        // Setup existing pages
        document.querySelectorAll('.page-item').forEach((pageItem, idx) => {
            reindexPage(pageItem, idx);
            setupPageTypeToggle(pageItem, idx);
            setupListItemControls(pageItem, idx);
            
            const removeBtn = pageItem.querySelector('.remove-page');
            removeBtn.addEventListener('click', (e) => {
                if (confirm('¿Eliminar esta página?')) {
                    pageItem.remove();
                    reindexPages();
                }
            });
        });

        // Add new page
        addPageBtn.addEventListener('click', () => {
            const clone = template.content.cloneNode(true);
            const pageItem = clone.querySelector('.page-item');
            
            // Update all input names with the current index
            clone.querySelectorAll('select, input, textarea').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/pages\[\]\[([^\]]+)\]/, `pages[${pageIndex}][$1]`));
                }
            });
            
            pagesContainer.appendChild(clone);
            
            // Get the actual DOM element after appending
            const addedPage = pagesContainer.lastElementChild;
            const idx = Array.from(pagesContainer.children).indexOf(addedPage);

            setupPageTypeToggle(addedPage, idx);
            setupListItemControls(addedPage, idx);
            
            const removeBtn = addedPage.querySelector('.remove-page');
            removeBtn.addEventListener('click', (e) => {
                if (confirm('¿Eliminar esta página?')) {
                    addedPage.remove();
                    reindexPages();
                }
            });
            
            reindexPages();
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
