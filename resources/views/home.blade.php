@extends('layouts.app')

@section('title', config('app.name', 'FILLEA CGIL'))
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container">
    <div class="row justify-content-center">
        @auth
            <div class="col-md-12 mb-5"> {{-- La card delle richieste di servizio --}}
                <div class="card">
                    {{-- Header della card, cliccabile per espandere/comprimere --}}
                    <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#serviceRequestsCollapse" aria-expanded="false" aria-controls="serviceRequestsCollapse">
                        <h5 class="mb-0">{{ __('Le tue richieste di servizio') }}</h5>
                        @if (!$serviceRequests->isEmpty())
                            <span class="badge bg-primary rounded-pill">{{ $serviceRequests->count() }}</span>
                        @endif
                    </div>

                    {{-- Contenuto della card, inizialmente nascosto --}}
                    <div id="serviceRequestsCollapse" class="collapse">
                        {{-- Sezione visibile solo agli utenti autenticati per le loro richieste di servizio --}}
                        <div class="card-body">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                            @if ($serviceRequests->isEmpty())
                                <p>Non hai richieste di servizio attive.</p>
                            @else
                                <p class="text-muted text-center mb-2 small"><em>Clicca su una richiesta per visualizzarne i dettagli.</em></p>
                            <div class="list-group">
                                @foreach ($serviceRequests as $request)
                                    <a href="{{ $request->detail_url ?? '#' }}" class="list-group-item list-group-item-action flex-column align-items-start mb-3 text-dark text-decoration-none position-relative">
                                        <div class="pe-4">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1">{{ $request->service_name }} ({{ $request->service_type }})</h5>
                                                <small class="text-muted flex-shrink-0 ps-2">{{ $request->updated_at->format('d/m/Y H:i') }}</small>
                                            </div>
                                            <p class="mb-1">Stato: <strong>{{ $request->status }}</strong></p>
                                            @if ($request->admin_notes)
                                                <div class="alert alert-info mt-2" role="alert">
                                                    <strong>Note del funzionario:</strong>
                                                    <p class="mb-0">{!! nl2br(e($request->admin_notes)) !!}</p>
                                                </div>
                                            @endif
                                        </div>
                                        <i class="bi bi-chevron-right position-absolute top-50 end-0 translate-middle-y me-3 text-secondary fs-4"></i>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
        @endauth

        {{-- Sezione pubblica con i bottoni dei servizi --}}
        <div class="col-md-12">
            <div class="text-center mb-5 page-title">
                <h1>LazioAPP</h1>
                <p class="lead">Seleziona il servizio di tuo interesse.</p>
            </div>

            <div class="row g-4">
                <!-- Sezione CASSA EDILE / EDILCASSA (Verde) -->
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
                        <div class="card bg-warning text-dark h-100">
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
    </div>

    {{-- Modale per messaggi di successo/errore --}}
    <div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responseModalLabel">Messaggio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="responseModalBody">
                    <!-- Il messaggio verrà inserito qui -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {        
        function showModal(title, message) {
            const modal = new bootstrap.Modal(document.getElementById('responseModal'));
            document.getElementById('responseModalLabel').textContent = title;
            document.getElementById('responseModalBody').innerHTML = message;
            modal.show();
        }
    });
</script>
@endpush
