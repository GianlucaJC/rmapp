<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma Richiesta Servizio LazioAPP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-top: 5px solid #c8102e; /* Rosso FILLEA/CGIL */
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eeeeee;
        }
        .header h1 {
            color: #c8102e; /* Rosso FILLEA/CGIL */
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px 0;
        }
        .content p {
            margin-bottom: 10px;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
            font-size: 12px;
            color: #777777;
        }
        .highlight {
            color: #c8102e;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Conferma Richiesta Servizio</h1>
        </div>
        <div class="content">
            <p>Gentile {{ $user->name }},</p>
            <p>Abbiamo ricevuto la tua richiesta per il seguente servizio:</p>
            <ul>
                <li><strong>Servizio Richiesto:</strong> <span class="highlight">{{ $serviceTitle }}</span></li>
                <li><strong>Descrizione Breve:</strong> {!! $serviceDescription !!}</li>
            </ul>
            <p>La tua richiesta è stata presa in carico e sarà elaborata al più presto. Riceverai ulteriori comunicazioni non appena la pratica avanzerà.</p>
            <p>Grazie per aver utilizzato LazioAPP.</p>
            <p>Cordiali saluti,</p>
            <p>Il team di LazioAPP</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} LazioAPP. Tutti i diritti riservati.</p>
        </div>
    </div>
</body>
</html>