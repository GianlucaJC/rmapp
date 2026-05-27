@extends('layouts.app')

@section('title', 'Contatti - FILLEA CGIL ROMA E LAZIO')

@section('content')
<div class="container">
    <div class="text-center mb-5 page-title">
        <h1>Contatti</h1>
        <p class="lead">Clicca per selezionare la zona o provincia di tuo interesse.</p>
    </div>

    <div class="accordion" id="accordionContatti">
        @forelse ($funzionariPerZona as $zona => $funzionari)
            @php
                // Crea un ID univoco per l'elemento collassabile
                $collapseId = 'collapse-' . \Illuminate\Support\Str::slug($zona);
            @endphp
            <div class="accordion-item mb-3 shadow-sm">
                <h2 class="accordion-header" id="heading-{{ $collapseId }}">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                        <i class="bi bi-geo-alt-fill me-2"></i> {{ $zona }}
                    </button>
                </h2>
                <div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $collapseId }}" data-bs-parent="#accordionContatti">
                    <div class="accordion-body">
                        <div class="row g-4">
                            @foreach ($funzionari as $funzionario)
                                <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                                    <div class="card h-100 w-100 text-center shadow-sm">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title">{{ $funzionario->nominativo }}</h5>
                                            {{-- per ora disattivato  --}}
                                            <!--<h6 class="card-subtitle mb-2 text-muted">{{ $funzionario->incarico }}</h6>
                                            !-->
                                            <div class="mt-auto pt-3 d-grid gap-2">
                                                @if($funzionario->telefono)
                                                    <a href="tel:{{ preg_replace('/\s+/', '', $funzionario->telefono) }}" class="btn btn-outline-success">
                                                        <i class="bi bi-telephone-fill me-2"></i>{{ $funzionario->telefono }}
                                                    </a>
                                                @endif
                                                @if($funzionario->mail)
                                                    <a href="mailto:{{ $funzionario->mail }}" class="btn btn-outline-primary text-break">
                                                        <i class="bi bi-envelope-fill me-2"></i>{{ $funzionario->mail }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info text-center">
                <p class="mb-0">Nessun contatto da visualizzare al momento.</p>
            </div>
        @endforelse
    </div>

    <div class="text-center mt-5">
        <a href="{{ route('home') }}" class="btn btn-secondary"><i class="bi bi-house-door-fill"></i> Torna alla Home</a>
    </div>
</div>
@endsection