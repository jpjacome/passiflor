<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud de Consulta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #853720;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .alert {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            border-left: 4px solid #853720;
            margin: 20px 0;
        }
        .highlight {
            color: #853720;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        table td:first-child {
            font-weight: bold;
            width: 30%;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Nueva Solicitud de Consulta</h1>
    </div>
    <div class="content">
        <div class="alert">
            <strong>⏰ Acción requerida:</strong> Un nuevo cliente ha solicitado una consulta inicial.
        </div>
        
        <h2>Información del Cliente</h2>
        
        <table>
            <tr>
                <td>Nombre:</td>
                <td><strong>{{ $consultation->full_name }}</strong></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td>{{ $consultation->email }}</td>
            </tr>
            @if($consultation->phone)
            <tr>
                <td>Teléfono:</td>
                <td>{{ $consultation->phone }}</td>
            </tr>
            @endif
            <tr>
                <td>Tipo de sesión:</td>
                <td><span class="highlight">{{ $consultation->session_type }}</span></td>
            </tr>
            <tr>
                <td>Fecha de solicitud:</td>
                <td>{{ $consultation->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
        
        @if($consultation->message)
        <div class="info-box">
            <h3>Mensaje del cliente:</h3>
            <p>{{ $consultation->message }}</p>
        </div>
        @endif
        
        <p><strong>Próximos pasos:</strong></p>
        <ol>
            <li>Revisar la disponibilidad para una consulta inicial de 15 minutos</li>
            <li>Contactar al cliente dentro de 24-48 horas</li>
            <li>Coordinar fecha y hora para la consulta</li>
        </ol>
        
        <p style="margin-top: 30px;">
            <a href="{{ config('app.url') }}/admin/consultations/{{ $consultation->id }}" 
               style="background-color: #853720; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                Ver Detalles Completos
            </a>
        </p>
    </div>
    <div class="footer">
        <p>© 2025 Passiflor - Panel de Administración</p>
        <p>ID de Consulta: #{{ $consultation->id }}</p>
    </div>
</body>
</html>
