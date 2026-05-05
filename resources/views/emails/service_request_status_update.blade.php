<!DOCTYPE html>
<html>
<head>
    <title>Aggiornamento Stato Richiesta Servizio</title>
</head>
<body>
    <p>Gentile {{ $serviceRequest->user->name }} {{ $serviceRequest->user->last_name }},</p>
    <p>La tua richiesta per il servizio "<strong>{{ $serviceRequest->service_name }}</strong>" è stata aggiornata.</p>
    <p>Il nuovo stato della tua pratica è: <strong>{{ $serviceRequest->status }}</strong></p>

    @if ($serviceRequest->admin_notes)
        <p><strong>Note del funzionario:</strong></p>
        <p>{!! nl2br(e($serviceRequest->admin_notes)) !!}</p>
    @endif

    @if ($serviceRequest->status === 'Richiesta integrazione')
        <p>Ti invitiamo ad accedere al portale per visualizzare i dettagli e caricare la documentazione richiesta.</p>
        <p><a href="{{ url('/home') }}">Accedi al portale</a></p>
    @endif

    <p>Grazie,<br>Il team FILLEA CGIL</p>
</body>
</html>