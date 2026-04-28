@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="page-title text-center mb-5">
                <h1>Elenco Prestazioni Cassa Edile</h1>
                <p class="lead">Prestazioni OPERAI della Cassa Edile di Roma</p>
            </div>

            <div class="row g-4">
                @foreach ($prestazioniCassaEdile as $prestazione)
                    <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                        <div class="flip-card w-100" tabindex="0">
                            <div class="flip-card-inner">
                                <div class="flip-card-front">
                                    <div class="card h-100 w-100 d-flex flex-column">
                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                            <i class="bi {{ $prestazione['icona'] }} fs-1 mb-3 text-success"></i>
                                            <h6 class="card-title mb-0">{{ $prestazione['nome'] }}</h6>
                                        </div>
                                        <div class="card-footer bg-transparent border-0 d-flex justify-content-center pt-0 pb-3">
                                            <a href="#" class="btn btn-sm btn-primary btn-show-guide" title="Dettagli e Guida alla presentazione"
                                               data-service-title="{{ $prestazione['nome'] }}"
                                               data-service-description="{{ $prestazione['descrizione_completa'] }}"
                                               >
                                                <i class="bi bi-book"></i> Dettagli
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="flip-card-back">
                                    <div class="card bg-dark text-white h-100 w-100">
                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-3">
                                            <p class="small">{{ $prestazione['descrizione'] }}</p>
                                            <a href="#" class="btn btn-sm btn-light mt-3" onclick="this.closest('.flip-card-inner').style.transform = 'rotateY(0deg)'; return false;">
                                                <i class="bi bi-arrow-left-circle"></i> Torna
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Service Guide Modal -->
            <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="serviceModalLabel">Dettaglio Prestazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body" id="serviceModalBody" style="white-space: pre-wrap;">
                    ...
                  </div>
                  <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    {{-- Il link href verrà aggiunto in futuro per puntare al modulo corretto --}}
                    <a href="#" id="modalProceedBtn" class="btn btn-success">Procedi con la presentazione</a>
                  </div>
                </div>
              </div>
            </div>

            <div class="text-center mt-5">
                <a href="{{ route('servizi.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Torna alla selezione</a>
            </div>
        </div>
    </div>
</div>
@endsection