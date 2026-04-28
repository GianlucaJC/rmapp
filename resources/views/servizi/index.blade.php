@extends('layouts.app')

@section('content')
<div class="container">
    <div class="text-center mb-5 page-title">
        <h1>Servizi Cassa Edile / Edilcassa</h1>
        <p class="lead">Seleziona l'ente di tuo interesse.</p>
    </div>

    <div class="row justify-content-center g-4">

        <!-- Card Cassa Edile -->
        <div class="col-md-6 col-lg-5">
            <a href="{{ route('servizi.cassa-edile') }}" class="feature-box">
                <div class="card bg-success text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-building-check fs-1 mb-3"></i>
                        <h5 class="card-title">PRESTAZIONI CASSA EDILE</h5>
                        <p class="card-text">Consulta l'elenco delle prestazioni per la Cassa Edile di Roma.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card Edilcassa -->
        <div class="col-md-6 col-lg-5">
            <a href="{{ route('servizi.edilcassa') }}" class="feature-box">
                <div class="card bg-success text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-building-add fs-1 mb-3"></i>
                        <h5 class="card-title">PRESTAZIONI EDILCASSA</h5>
                        <p class="card-text">Consulta l'elenco delle prestazioni per l'Edilcassa del Lazio.</p>
                    </div>
                </div>
            </a>
        </div>

    </div>

    <div class="text-center mt-5">
        <a href="{{ route('home') }}" class="btn btn-secondary"><i class="bi bi-house-door-fill"></i> Torna alla Home</a>
    </div>
</div>
@endsection