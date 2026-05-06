@extends('layouts.app')

@section('title', 'Informativa sulla Privacy')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header"><h1>Informativa sulla Privacy</h1></div>

                <div class="card-body">
                    <p><strong>Ultimo aggiornamento: {{ date('d/m/Y') }}</strong></p>

                    <p>
                        Gentile utente, la presente informativa descrive le modalità di trattamento dei dati personali da te forniti durante la registrazione e l'utilizzo dei servizi offerti da questa applicazione, in conformità con il Regolamento (UE) 2016/679 (GDPR).
                    </p>

                    <h4>Titolare del Trattamento</h4>
                    <p>Il titolare del trattamento è [Inserire Nome Azienda/Titolare] con sede in [Inserire Indirizzo].</p>

                    <h4>Finalità del Trattamento</h4>
                    <p>I dati personali da te forniti (nome, cognome, email, ecc.) sono raccolti e trattati per le seguenti finalità:</p>
                    <ul>
                        <li>Consentire la registrazione e l'accesso all'area riservata.</li>
                        <li>Erogare i servizi richiesti.</li>
                        <li>Adempiere agli obblighi di legge.</li>
                    </ul>


                </div>
            </div>
        </div>
    </div>
</div>
@endsection