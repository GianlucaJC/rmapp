@extends('layouts.app')

@section('title', 'Gestione News')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestione News</h1>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Crea Nuova News
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Elenco News</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Titolo</th>
                            <th>Data Inizio</th>
                            <th>Data Fine</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($newsItems as $news)
                            <tr>
                                <td>{{ $news->title }}</td>
                                <td>{{ $news->start_date ? $news->start_date->format('d/m/Y') : 'N/D' }}</td>
                                <td>{{ $news->end_date ? $news->end_date->format('d/m/Y') : 'N/D' }}</td>
                                <td>
                                    @if ($news->is_suspended)
                                        <span class="badge bg-warning text-dark">Sospesa</span>
                                    @else
                                        <span class="badge bg-success">Attiva</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.news.edit', $news) }}" class="btn btn-info btn-sm" title="Modifica">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.news.destroy', $news) }}" method="POST" class="d-inline" onsubmit="return confirm('Sei sicuro di voler eliminare questa news?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Elimina">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Nessuna news trovata.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection