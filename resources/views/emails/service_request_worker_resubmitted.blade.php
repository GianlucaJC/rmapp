<!DOCTYPE html>
<html>
<head>
    <title>Richiesta Servizio Aggiornata dal Lavoratore</title>
</head>
<body>
    <p>Gentile Amministratore,</p>
    <p>Il lavoratore <strong>{{ $serviceRequest->user->name }} {{ $serviceRequest->user->last_name }}</strong> ({{ $serviceRequest->user->email }}) ha aggiornato la sua richiesta di servizio.</p>
    <p>Servizio: <strong>{{ $serviceRequest->service_name }}</strong></p>
    <p>Stato attuale: <strong>{{ $serviceRequest->status }}</strong></p>
    <p>Sono stati caricati nuovi documenti per la pratica.</p>

    <p>Ti invitiamo ad accedere al pannello di amministrazione per visualizzare i dettagli e i documenti caricati:</p>
    <p><a href="{{ route('admin.service-requests.show', $serviceRequest->id) }}">Visualizza Dettagli Richiesta</a></p>
    <p>Grazie,<br>Il sistema automatico FILLEA CGIL</p>
</body>
</html>