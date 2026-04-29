<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Richiesta Nuovo Servizio da LazioAPP</title>
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
        .button {
            display: inline-block;
            background-color: #c8102e; /* Rosso FILLEA/CGIL */
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
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
            <h1>Richiesta Nuovo Servizio</h1>
        </div>
        <div class="content">
            <p>Gentile Amministratore,</p>
            <p>È stata effettuata una nuova richiesta di servizio tramite LazioAPP.</p>
            <p><strong>Dettagli Richiesta:</strong></p>
            <ul>
                <li><strong>Servizio Richiesto:</strong> <span class="highlight">{{ $serviceTitle }}</span></li>
                <li><strong>Descrizione Breve:</strong> {!! $serviceDescription !!}</li>
                <li><strong>Richiedente:</strong> {{ $user->name }} {{ $user->last_name }} ({{ $user->email }})</li>
                <li><strong>Codice Fiscale:</strong> {{ $user->codice_fiscale }}</li>
                <li><strong>Numero di Telefono:</strong> {{ $user->phone_number }}</li>
            </ul>
            <p>Per visualizzare i dettagli completi e gestire la pratica, si prega di accedere al pannello di amministrazione tramite il seguente link:</p>
            <p style="text-align: center;">
                <a href="{{ $fictitiousUrl }}" class="button">Vai alla Pratica</a>
            </p>
            <p>Cordiali saluti,</p>
            <p>Il team di LazioAPP</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} LazioAPP. Tutti i diritti riservati.</p>
        </div>
    </div>
</body>
</html>