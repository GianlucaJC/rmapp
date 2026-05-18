@extends('layouts.app')

@section('title', 'Cestino Richieste')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Cestino Richieste di Servizio</h4>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light">
                        <i class="bi bi-arrow-left"></i> Torna alla Dashboard
                    </a>
                </div>

                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="trashed-requests-table" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Utente</th>
                                    <th>Tipo Servizio</th>
                                    <th>Nome Servizio</th>
                                    <th>Data Eliminazione</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
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
{{-- Includi le librerie necessarie --}}
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function() {
    $('#trashed-requests-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.service-requests.trash.data') }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'user_name', name: 'user.name' },
            { data: 'service_type', name: 'service_type' },
            { data: 'service_name', name: 'service_name' },
            { data: 'deleted_at', name: 'deleted_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/Italian.json'
        }
    });

    // Gestione cancellazione con SweetAlert
    $('#trashed-requests-table').on('submit', 'form.delete-form', function(e) {
        e.preventDefault();
        var form = this;
        var message = $(form).data('message') || 'Sei sicuro di voler procedere?';

        Swal.fire({
            title: 'Conferma operazione',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sì, procedi!',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush