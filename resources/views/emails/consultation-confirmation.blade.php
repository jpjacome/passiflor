<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Consulta</title>
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
            background-color: #F6F1DE;
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header img {
            max-width: 200px;
            height: auto;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .highlight {
            color: #853720;
            font-weight: bold;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            border-left: 4px solid #853720;
            margin: 20px 0;
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
        <img src="{{ config('app.url') }}/imgs/logo2.png" alt="Passiflor Logo">
    </div>
    <div class="content">
        <h2>¡Hola, {{ $consultation->full_name }}!</h2>
        
        <p>Gracias por contactar a <span class="highlight">Passiflor</span>. Hemos recibido tu solicitud para una consulta inicial de 15 minutos.</p>
        
        <div class="info-box">
            <h3>Detalles de tu solicitud:</h3>
            <p><strong>Tipo de sesión:</strong> {{ $consultation->session_type }}</p>
            <p><strong>Email:</strong> {{ $consultation->email }}</p>
            @if($consultation->phone)
            <p><strong>Teléfono:</strong> {{ $consultation->phone }}</p>
            @endif
            @if($consultation->message)
            <p><strong>Mensaje:</strong><br>{{ $consultation->message }}</p>
            @endif
        </div>
        
        <p>Uno de nuestros psicólogos se pondrá en contacto contigo dentro de las próximas <strong>24-48 horas</strong> para coordinar tu consulta inicial gratuita.</p>
        
        <p>Si tienes alguna pregunta urgente, no dudes en responder a este correo.</p>
        
        <p>Con cariño,<br>
        <span class="highlight">El equipo de Passiflor</span></p>
    </div>
    <div class="footer">
        <p>© 2025 Passiflor. Todos los derechos reservados.</p>
        <p>Este es un correo automático, por favor no respondas directamente a esta dirección.</p>
    </div>
</body>
</html>
