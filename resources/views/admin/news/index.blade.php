@extends('layouts.app')

@section('title', 'Gestione News')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestione News</h1>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Crea Nuova News
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Elenco News</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="newsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Titolo</th>
                            <th>Stato</th>
                            <th>Inizio Pubblicazione</th>
                            <th>Fine Pubblicazione</th>
                            <th>Creata il</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($newsItems as $news)
                            <tr>
                                <td>{{ $news->title }}</td>
                                <td>
                                    @php
                                        $today = \Carbon\Carbon::today();
                                        if ($news->is_suspended) {
                                            echo '<span class="badge bg-warning text-dark">Sospesa</span>';
                                        } elseif ($news->start_date && $news->start_date > $today) {
                                            echo '<span class="badge bg-info">Programmata</span>';
                                        } elseif ($news->end_date && $news->end_date < $today) {
                                            echo '<span class="badge bg-secondary">Scaduta</span>';
                                        } else {
                                            echo '<span class="badge bg-success">Attiva</span>';
                                        }
                                    @endphp
                                </td>
                                <td>{{ $news->start_date ? $news->start_date->format('d/m/Y') : 'N/D' }}</td>
                                <td>{{ $news->end_date ? $news->end_date->format('d/m/Y') : 'N/D' }}</td>
                                <td>{{ $news->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.news.edit', $news) }}" class="btn btn-info btn-sm" title="Modifica">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <form action="{{ route('admin.news.destroy', $news) }}" method="POST" class="d-inline delete-form" data-message="Sei sicuro di voler eliminare questa news?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Elimina">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Nessuna news trovata.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Confirmation for delete forms
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const message = form.getAttribute('data-message') || 'Sei sicuro di voler procedere?';
            if (confirm(message)) {
                form.submit();
            }
        });
    });
});
</script>
@endpush