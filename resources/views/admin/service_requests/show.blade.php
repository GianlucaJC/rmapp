@extends('layouts.app')

@section('title', 'Dettaglio Richiesta #' . $serviceRequest->id)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Dettaglio Richiesta #{{ $serviceRequest->id }}</h4>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Torna alla Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        {{-- La colonna principale si allarga se non c'è la colonna di gestione --}}
        <div class="@if(session('admin_user.superadmin')) col-lg-12 @else col-lg-8 @endif">
            <!-- Request Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Informazioni Richiesta</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Servizio</dt>
                        <dd class="col-sm-8">{{ $serviceRequest->service_name }}</dd>

                        <dt class="col-sm-4">Tipo</dt>
                        <dd class="col-sm-8">{{ $serviceRequest->service_type }}</dd>

                        <dt class="col-sm-4">Data Richiesta</dt>
                        <dd class="col-sm-8">{{ $serviceRequest->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Ultimo Aggiornamento</dt>
                        <dd class="col-sm-8">{{ $serviceRequest->updated_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Stato Attuale</dt>
                        <dd class="col-sm-8"><span class="badge bg-primary">{{ $serviceRequest->status }}</span></dd>

                        @if($serviceRequest->admin_notes)
                        <dt class="col-sm-4">Note Amministratore</dt>
                        <dd class="col-sm-8">{!! nl2br(e($serviceRequest->admin_notes)) !!}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- User Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Dettagli Utente</h5>
                </div>
                <div class="card-body">
                    @if($serviceRequest->user)
                    <dl class="row">
                        <dt class="col-sm-4">Nome</dt>
                        <dd class="col-sm-8">{{ $serviceRequest->user->name }} {{ $serviceRequest->user->last_name }}</dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $serviceRequest->user->email }}</dd>

                        <dt class="col-sm-4">Codice Fiscale</dt>
                        <dd class="col-sm-8">{{ $serviceRequest->user->codice_fiscale }}</dd>

                        <dt class="col-sm-4">Telefono</dt>
                        <dd class="col-sm-8">{{ $serviceRequest->user->phone_number }}</dd>
                    </dl>
                    @else
                    <p class="text-danger">Utente non trovato o cancellato.</p>
                    @endif
                </div>
            </div>

            <!-- Uploaded Documents Card -->
            @if($serviceRequest->uploaded_documents && count($serviceRequest->uploaded_documents) > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Documenti Caricati dall'Utente</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($serviceRequest->uploaded_documents as $document)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-file-earmark-text me-2"></i>
                                {{ $document['original_name'] }}
                            </span>
                            <a href="{{ route('admin.service-requests.download-document', ['serviceRequest' => $serviceRequest->id, 'filePath' => $document['path']]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i> Scarica
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

        </div>

        {{-- La colonna di gestione è visibile solo ai funzionari (non superadmin) --}}
        @if(!session('admin_user.superadmin'))
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Gestione Richiesta</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.service-requests.update', $serviceRequest->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold">Cambia Stato</label>
                            <select class="form-select" id="status" name="status">
                                @foreach(['Inviata', 'Richiesta integrazione', 'In attesa documenti', 'Conclusa', 'Rifiutata'] as $status)
                                    <option value="{{ $status }}" @if($serviceRequest->status == $status) selected @endif>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="admin_notes" class="form-label fw-bold">Note per l'utente</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="5" placeholder="Inserisci qui eventuali note o richieste di integrazione per l'utente. Saranno visibili nella sua area personale e inviate via email se lo stato cambia in 'Richiesta integrazione'.">{{ old('admin_notes', $serviceRequest->admin_notes) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle"></i> Aggiorna Stato
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection