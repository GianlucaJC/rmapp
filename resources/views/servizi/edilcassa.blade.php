@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="page-title text-center mb-5">
                <h1>Elenco Prestazioni Edilcassa</h1>
                <p class="lead">Prestazioni OPERAI della edilcassa del lazio</p>
            </div>

            <div class="row g-4 justify-content-center">
                @foreach ($prestazioniEdilcassa as $prestazione)
                    <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                        <div class="flip-card w-100" tabindex="0">
                            <div class="flip-card-inner">
                                <div class="flip-card-front">
                                    <div class="card h-100 w-100 d-flex flex-column">
                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                            <i class="bi {{ $prestazione['icona'] }} fs-1 mb-3 text-success"></i>
                                            <h6 class="card-title mb-0">{{ $prestazione['nome'] }}</h6>
                                        </div>
                                        <div class="card-footer bg-transparent border-0 d-flex justify-content-center pb-3">
                                            <a href="#" class="btn btn-sm btn-primary disabled" title="Guida alla presentazione non disponibile">
                                                <i class="bi bi-book"></i>  Guida alla presentazione
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="flip-card-back">
                                    <div class="card bg-dark text-white h-100 w-100">
                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-3">
                                            <p class="small">{{ $prestazione['descrizione'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-5">
                <a href="{{ route('servizi.index') }}" class="btn btn-light mt-4"><i class="bi bi-arrow-left"></i> Torna alla selezione</a>
            </div>
        </div>
    </div>
</div>
@endsection