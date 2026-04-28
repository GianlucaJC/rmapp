@extends('layouts.app')

@section('content')
<div class="container">
    <div class="text-center mb-5 page-title">
        <h1>Benvenuto nell'App FILLEA</h1>
        <p class="lead">Seleziona il servizio di tuo interesse.</p>
        {{-- Qui potremmo inserire il selettore della lingua --}}
    </div>

    <div class="row g-4">

        <!-- Sezione CASSA EDILE/EDILCASSA (Verde) -->
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('servizi.index') }}" class="feature-box">
                <div class="card bg-success text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-building fs-1 mb-3"></i>
                        <h5 class="card-title">CASSA EDILE / EDILCASSA</h5>
                        <p class="card-text">Prestazioni, link e informazioni utili.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Sezione PRESTAZIONI SANEDIL (Rosa) -->
        <div class="col-md-6 col-lg-4">
            <a href="#" class="feature-box">
                {{-- Ho usato una classe custom 'bg-pink' definita nel layout --}}
                <div class="card bg-pink text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-heart-pulse fs-1 mb-3"></i>
                        <h5 class="card-title">PRESTAZIONI SANEDIL</h5>
                        <p class="card-text">Scopri le prestazioni del fondo sanitario.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Sezione FONDI PENSIONISTICI (Blu) -->
        <div class="col-md-6 col-lg-4">
            <a href="#" class="feature-box">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-piggy-bank fs-1 mb-3"></i>
                        <h5 class="card-title">FONDI PENSIONISTICI</h5>
                        <p class="card-text">PREVEDI, FONDAPI e altro.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Sezione SERVIZI CGIL (Rosso) -->
        <div class="col-md-6 col-lg-4">
            <a href="#" class="feature-box">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-shield-check fs-1 mb-3"></i>
                        <h5 class="card-title">SERVIZI CGIL</h5>
                        <p class="card-text">Assistenza fiscale, patronato e vertenze.</p>
                    </div>
                </div>
            </a>
        </div>
        
        <!-- Sezione HAI PERSO IL LAVORO? (Rosso) -->
        <div class="col-md-6 col-lg-4">
            <a href="#" class="feature-box">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-question-circle fs-1 mb-3"></i>
                        <h5 class="card-title">HAI PERSO IL LAVORO?</h5>
                        <p class="card-text">Informazioni e supporto.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Sezione CONTATTI (Giallo) -->
        <div class="col-md-6 col-lg-4">
            <a href="#" class="feature-box">
                <div class="card bg-warning text-dark h-100"> {{-- Testo scuro per leggibilità su giallo --}}
                    <div class="card-body text-center">
                        <i class="bi bi-telephone-fill fs-1 mb-3"></i>
                        <h5 class="card-title">CONTATTI</h5>
                        <p class="card-text">Contattaci per ogni esigenza.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Altri link principali -->
    <div class="row justify-content-center mt-5 gy-3">
        <div class="col-md-4">
            <a href="#" class="btn btn-light w-100 py-3 fw-bold">FILLEA SUL TERRITORIO</a>
        </div>
        <div class="col-md-4">
            <a href="http://www.costruire.net" target="_blank" class="btn btn-light w-100 py-3 fw-bold">COSTRUIRE.NET</a>
        </div>
        <div class="col-md-4">
            <a href="#" class="btn btn-light w-100 py-3 fw-bold">NEWS E INIZIATIVE</a>
        </div>
    </div>
</div>
@endsection
