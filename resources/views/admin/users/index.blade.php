@extends('layouts.app')

@section('title', 'Gestione Utenti')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Gestione Utenti</h4>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light">
                        <i class="bi bi-arrow-left"></i> Torna alla Dashboard
                    </a>
                </div>

                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table id="users-table" class="table table-bordered table-striped table-hover w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Cognome</th>
                                    <th>Email</th>
                                    <th>Codice Fiscale</th>
                                    <th>Telefono</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- I dati verranno caricati tramite DataTables --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(function() {
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.users.data') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'last_name', name: 'last_name' },
                    { data: 'email', name: 'email' },
                    { data: 'codice_fiscale', name: 'codice_fiscale' },
                    { data: 'phone_number', name: 'phone_number' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/Italian.json'
                }
            });
        });
    </script>
@endpush