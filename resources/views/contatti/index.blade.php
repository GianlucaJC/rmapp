@extends('layouts.app')

@section('title', 'Contatti - FILLEA CGIL ROMA E LAZIO')

@section('content')
<div class="container">
    <div class="text-center mb-5 page-title">
        <h1>Contatti</h1>
        <p class="lead">I nostri funzionari sul territorio.</p>
    </div>

    <div class="row g-4 justify-content-center">
        @if(empty($funzionariPerZona))
            <div class="col-12">
                <p class="text-center text-white">Nessun contatto da visualizzare al momento.</p>
            </div>
        @else
            @foreach ($funzionariPerZona as $zona => $funzionari)
                <div class="col-12 mt-4 mb-2">
                    <h2 class="border-bottom pb-2 mb-3 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">{{ $zona }}</h2>
                </div>
                @foreach ($funzionari as $funzionario)
                    <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                        <div class="card h-100 w-100 text-center shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">{{ $funzionario->nominativo }}</h5>
                                {{-- per ora disattivato  --}}
                                <!--<h6 class="card-subtitle mb-2 text-muted">{{ $funzionario->incarico }}</h6>
                                !-->
                                <div class="mt-auto pt-3">
                                    @if($funzionario->telefono)
                                        <p class="card-text mb-1">
                                            <i class="bi bi-telephone-fill text-secondary me-2"></i>
                                            <a href="tel:{{ $funzionario->telefono }}" class="text-decoration-none text-dark">{{ $funzionario->telefono }}</a>
                                        </p>
                                    @endif
                                    @if($funzionario->mail)
                                        <p class="card-text mb-0">
                                            <i class="bi bi-envelope-fill text-secondary me-2"></i>
                                            <a href="mailto:{{ $funzionario->mail }}" class="text-decoration-none text-dark text-break">{{ $funzionario->mail }}</a>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        @endif
    </div>

    <div class="text-center mt-5">
        <a href="{{ route('home') }}" class="btn btn-secondary"><i class="bi bi-house-door-fill"></i> Torna alla Home</a>
    </div>
</div>
@endsection