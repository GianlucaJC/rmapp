@extends('layouts.admin') {{-- Assumi un layout admin esistente --}}

@section('title', 'Dettagli Richiesta di Servizio #' . $serviceRequest->id)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Dettagli Richiesta di Servizio #{{ $serviceRequest->id }}</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Torna alla Dashboard</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Informazioni Richiesta</div>
        <div class="card-body">
            <p><strong>Utente:</strong> {{ $serviceRequest->user->name }} ({{ $serviceRequest->user->email }})</p>
            <p><strong>Servizio:</strong> {{ $serviceRequest->service_name }} ({{ $serviceRequest->service_type }})</p>
            <p><strong>Descrizione:</strong> {{ $serviceRequest->service_description }}</p>
            <p><strong>Data Richiesta:</strong> {{ $serviceRequest->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Ultimo Aggiornamento:</strong> {{ $serviceRequest->updated_at->format('d/m/Y H:i') }}</p>
            @if ($serviceRequest->additional_data)
                <p><strong>Dati Aggiuntivi:</strong></p>
                <ul>
                    @foreach ($serviceRequest->additional_data as $key => $value)
                        <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Documenti Caricati</div>
        <div class="card-body">
            @if ($serviceRequest->uploaded_documents && count($serviceRequest->uploaded_documents) > 0)
                <ul class="list-group">
                    @foreach ($serviceRequest->uploaded_documents as $doc)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $doc['original_name'] ?? 'Nome file non disponibile' }} ({{ round(($doc['size'] ?? 0) / 1024 / 1024, 2) }} MB)</span>
                            <a href="{{ route('admin.service-requests.download-document', ['serviceRequest' => $serviceRequest->id, 'filePath' => $doc['path']]) }}" class="btn btn-sm btn-primary" title="Scarica documento">
                                <i class="bi bi-download"></i> Scarica
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p>Nessun documento caricato per questa richiesta.</p>
            @endif
        </div>
    </div>

    {{-- Form per aggiornare stato e note del funzionario --}}
    <div class="card">
        <div class="card-header">Gestione Richiesta</div>
        <div class="card-body">
            <form action="{{ route('admin.service-requests.update', $serviceRequest->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="status" class="form-label">Stato</label>
                    <select name="status" id="status" class="form-control">
                        <option value="Inviata" @if($serviceRequest->status === 'Inviata') selected @endif>Inviata</option>
                        <option value="Richiesta integrazione" @if($serviceRequest->status === 'Richiesta integrazione') selected @endif>Richiesta integrazione</option>
                        <option value="In attesa documenti" @if($serviceRequest->status === 'In attesa documenti') selected @endif>In attesa documenti</option>
                        <option value="Conclusa" @if($serviceRequest->status === 'Conclusa') selected @endif>Conclusa</option>
                        <option value="Rifiutata" @if($serviceRequest->status === 'Rifiutata') selected @endif>Rifiutata</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="admin_notes" class="form-label">Note Funzionario</label>
                    <textarea name="admin_notes" id="admin_notes" class="form-control" rows="5">{{ $serviceRequest->admin_notes }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Aggiorna Stato e Note</button>
            </form>
        </div>
    </div>
</div>
@endsection