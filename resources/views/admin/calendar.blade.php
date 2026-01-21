<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Calendario - Passiflor Admin</title>
    <link rel="stylesheet" href="{{ asset('css/general.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
    
    <style>
        .calendar-container {
            background: var(--color-1);
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 4px 24px rgba(133, 55, 32, 0.07);
            margin-bottom: 2rem;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .calendar-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .fc {
            font-family: 'Quicksand', sans-serif;
        }
        
        .fc .fc-button {
            background: var(--color-3);
            border: none;
            color: var(--color-4);
            text-transform: capitalize;
            font-family: 'Quicksand', sans-serif;
        }
        
        .fc .fc-button:hover {
            background: var(--color-2);
            color: var(--color-1);
        }
        
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background: var(--color-2);
            color: var(--color-1);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background: var(--color-1);
            margin: 8% auto;
            overflow-y: scroll;
            padding: 2rem;
            border-radius: 18px;
            width: 90%;
            max-height: 80vh;
            max-width: 600px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .modal-title {
            font-family: 'Hedvig Letters Serif', serif;
            font-size: 1.5rem;
            color: var(--color-4);
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--color-4);
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--color-4);
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--color-3);
            border-radius: 8px;
            font-family: 'Quicksand', sans-serif;
            background: var(--color-1);
            color: var(--color-4);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }
        
        .feed-url-container {
            background: var(--color-3);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .feed-url {
            word-break: break-all;
            font-family: monospace;
            font-size: 0.9rem;
            color: var(--color-4);
        }
    </style>
</head>
<body>
    <x-admin-header />
    
    <div class="dashboard-wrapper">
        <div class="page-header">
            <h1 class="page-title">
                <i class="ph ph-calendar"></i>
                Calendario de Citas
            </h1>
            <div class="calendar-actions">
                @if(auth()->user()->calendar_token)
                <button type="button" class="btn-action" onclick="showFeedUrl()">
                    <i class="ph ph-link"></i>
                    Suscribir Calendario
                </button>
                @endif
                <button type="button" class="btn-action btn-confirm" onclick="openCreateModal()">
                    <i class="ph ph-plus"></i>
                    Nueva Cita
                </button>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success">
            <i class="ph ph-check-circle"></i>
            {{ session('success') }}
        </div>
        @endif

        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Create/Edit Appointment Modal -->
    <div id="appointmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Nueva Cita</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <form id="appointmentForm">
                <input type="hidden" id="appointmentId">
                
                <div class="form-group">
                    <label>Terapeuta</label>
                    <select id="therapist_id" name="therapist_id" required {{ auth()->user()->actingAsTherapist() ? 'disabled' : '' }}>
                        <option value="">-- Seleccionar terapeuta --</option>
                        @foreach($therapists as $t)
                            <option value="{{ $t->id }}" {{ auth()->user()->actingAsTherapist() && $t->id === auth()->id() ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Paciente</label>
                    <select id="patient_id" name="patient_id">
                        <option value="">-- Seleccionar paciente --</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Título</label>
                    <input type="text" id="title" name="title" required placeholder="Ej: Sesión con Juan Pérez">
                </div>
                
                <div class="form-group">
                    <label>Fecha y Hora de Inicio</label>
                    <input type="datetime-local" id="start_time" name="start_time" required>
                </div>
                
                <div class="form-group">
                    <label>Fecha y Hora de Fin</label>
                    <input type="datetime-local" id="end_time" name="end_time" required>
                </div>
                
                <div class="form-group">
                    <label>Tipo</label>
                    <select id="type" name="type" required>
                        <option value="session">Sesión</option>
                        <option value="consultation">Consulta</option>
                        <option value="evaluation">Evaluación</option>
                        <option value="follow-up">Seguimiento</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Estado</label>
                    <select id="status" name="status" required>
                        <option value="scheduled">Programada</option>
                        <option value="confirmed">Confirmada</option>
                        <option value="completed">Completada</option>
                        <option value="cancelled">Cancelada</option>
                        <option value="no-show">No asistió</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Color</label>
                    <input type="color" id="color" name="color" value="#A1966B">
                </div>
                
                <div class="form-group">
                    <label>Notas</label>
                    <textarea id="notes" name="notes" placeholder="Notas adicionales..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-action btn-cancel" onclick="closeModal()">Cancelar</button>
                    <button type="button" class="btn-action btn-delete" id="deleteBtn" onclick="deleteAppointment()" style="display:none;">Eliminar</button>
                    <button type="submit" class="btn-action btn-confirm">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Feed URL Modal -->
    <div id="feedModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Suscribir a tu Calendario</h2>
                <button class="close-modal" onclick="closeFeedModal()">&times;</button>
            </div>
            <p style="color: var(--color-4); margin-bottom: 1rem;">
                Copia esta URL y agrégala a Google Calendar, Outlook, o Apple Calendar como "Suscripción de calendario" o "Calendario por URL".
            </p>
            <div class="feed-url-container">
                <div class="feed-url">{{ route('calendar.feed', auth()->user()->calendar_token ?? 'token') }}</div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-action btn-confirm" onclick="copyFeedUrl()">
                    <i class="ph ph-copy"></i>
                    Copiar URL
                </button>
                <button type="button" class="btn-action" onclick="closeFeedModal()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    
    <script>
        let calendar;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día'
                },
                events: '{{ route('admin.calendar.appointments') }}',
                editable: true,
                selectable: true,
                select: function(info) {
                    openCreateModal(info.startStr, info.endStr);
                },
                dateClick: function(info) {
                    // Single click on a date - only set start time
                    // Format: YYYY-MM-DDTHH:mm for datetime-local input
                    const clickedDate = new Date(info.date);
                    const year = clickedDate.getFullYear();
                    const month = String(clickedDate.getMonth() + 1).padStart(2, '0');
                    const day = String(clickedDate.getDate()).padStart(2, '0');
                    const hours = String(clickedDate.getHours()).padStart(2, '0');
                    const minutes = String(clickedDate.getMinutes()).padStart(2, '0');
                    const start = `${year}-${month}-${day}T${hours}:${minutes}`;
                    openCreateModal(start, null);
                },
                eventClick: function(info) {
                    openEditModal(info.event);
                },
                eventDrop: function(info) {
                    updateAppointmentTime(info.event);
                },
                eventResize: function(info) {
                    updateAppointmentTime(info.event);
                }
            });
            
            calendar.render();
        });
        
        function openCreateModal(start = null, end = null) {
            document.getElementById('modalTitle').textContent = 'Nueva Cita';
            document.getElementById('appointmentForm').reset();
            document.getElementById('appointmentId').value = '';
            document.getElementById('deleteBtn').style.display = 'none';
            
            if (start) {
                document.getElementById('start_time').value = start.slice(0, 16);
                // Only auto-fill end time if explicitly provided (from drag selection)
                if (end && start !== end) {
                    document.getElementById('end_time').value = end.slice(0, 16);
                }
            }
            
            // Pre-select therapist if in therapist mode
            @if(auth()->user()->actingAsTherapist())
                document.getElementById('therapist_id').value = '{{ auth()->id() }}';
            @endif
            
            document.getElementById('appointmentModal').style.display = 'block';
        }
        
        function openEditModal(event) {
            document.getElementById('modalTitle').textContent = 'Editar Cita';
            document.getElementById('appointmentId').value = event.id;
            document.getElementById('title').value = event.title;
            document.getElementById('start_time').value = event.startStr.slice(0, 16);
            document.getElementById('end_time').value = event.endStr.slice(0, 16);
            document.getElementById('type').value = event.extendedProps.type;
            document.getElementById('status').value = event.extendedProps.status;
            document.getElementById('notes').value = event.extendedProps.notes || '';
            document.getElementById('color').value = event.backgroundColor;
            document.getElementById('deleteBtn').style.display = 'block';
            
            document.getElementById('appointmentModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('appointmentModal').style.display = 'none';
        }
        
        function showFeedUrl() {
            document.getElementById('feedModal').style.display = 'block';
        }
        
        function closeFeedModal() {
            document.getElementById('feedModal').style.display = 'none';
        }
        
        function copyFeedUrl() {
            const url = document.querySelector('.feed-url').textContent;
            navigator.clipboard.writeText(url).then(() => {
                alert('URL copiada al portapapeles');
            });
        }
        
        document.getElementById('appointmentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                therapist_id: document.getElementById('therapist_id').value,
                patient_id: document.getElementById('patient_id').value || null,
                start_time: document.getElementById('start_time').value,
                end_time: document.getElementById('end_time').value,
                title: document.getElementById('title').value,
                type: document.getElementById('type').value,
                status: document.getElementById('status').value,
                notes: document.getElementById('notes').value,
                color: document.getElementById('color').value,
            };
            
            const appointmentId = document.getElementById('appointmentId').value;
            const url = appointmentId 
                ? `/admin/calendar/appointments/${appointmentId}`
                : '{{ route('admin.calendar.store') }}';
            const method = appointmentId ? 'PUT' : 'POST';
            
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(formData)
                });
                
                if (!response.ok) {
                    const text = await response.text();
                    console.error('Server response:', text);
                    alert('Error al guardar la cita. Revisa la consola para más detalles.');
                    return;
                }
                
                const data = await response.json();
                
                if (data.success) {
                    closeModal();
                    calendar.refetchEvents();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo guardar la cita'));
                }
            } catch (error) {
                alert('Error al guardar la cita');
                console.error(error);
            }
        });
        
        async function deleteAppointment() {
            if (!confirm('¿Estás seguro de eliminar esta cita?')) return;
            
            const appointmentId = document.getElementById('appointmentId').value;
            
            try {
                const response = await fetch(`/admin/calendar/appointments/${appointmentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    closeModal();
                    calendar.refetchEvents();
                }
            } catch (error) {
                alert('Error al eliminar la cita');
                console.error(error);
            }
        }
        
        async function updateAppointmentTime(event) {
            const formData = {
                therapist_id: event.extendedProps.therapist_id,
                patient_id: event.extendedProps.patient_id,
                start_time: event.startStr,
                end_time: event.endStr,
                title: event.title,
                type: event.extendedProps.type,
                status: event.extendedProps.status,
                notes: event.extendedProps.notes,
                color: event.backgroundColor,
            };
            
            try {
                await fetch(`/admin/calendar/appointments/${event.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(formData)
                });
            } catch (error) {
                console.error('Error updating appointment:', error);
                event.revert();
            }
        }
    </script>
</body>
</html>
