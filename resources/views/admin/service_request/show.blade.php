@extends('layouts.app')

@section('title', 'Dettagli Richiesta Servizio')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">{{ __('Dettagli Richiesta Servizio') }} #{{ $serviceRequest->id }}</h4>
                </div>

                <div class="card-body p-4">
                    <h5 class="card-title">Servizio: {{ $serviceRequest->service_name }} ({{ $serviceRequest->service_type }})</h5>
                    <p><strong>Richiesto da:</strong> {{ $serviceRequest->user->name }} ({{ $serviceRequest->user->email }})</p>
                    <p><strong>Stato:</strong> {{ $serviceRequest->status }}</p>
                    <p><strong>Data Richiesta:</strong> {{ $serviceRequest->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Descrizione:</strong> {{ $serviceRequest->service_description }}</p>
                    {{-- Qui verranno mostrati i dati aggiuntivi e i file caricati --}}
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary mt-3">Torna alla Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection