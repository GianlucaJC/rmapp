@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid"> {{-- Changed to container-fluid --}}
    <div class="row justify-content-center">
        <div class="col-md-12"> {{-- Changed to col-md-12 for wider content --}}
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('Dashboard Amministratore') }}</h4>
                    {{-- Il pulsante di logout è stato spostato qui per coerenza con la navbar --}}
                    <a href="{{ route('admin.logout') }}" class="btn btn-danger btn-sm" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">@csrf</form>
                </div>
                

                <div class="card-body p-4 admin-dashboard-card-body">
                    <h5 class="mb-3">Richieste di Servizio</h5>

                    {{-- Filtri Generici --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="filterStatus" class="form-label">Filtra per Stato:</label>
                            <select class="form-select form-select-sm" id="filterStatus">
                                <option value="">Tutti</option>
                                <option value="Inviata">Inviata</option>
                                <option value="In Lavorazione">In Lavorazione</option>
                                <option value="Approvata">Approvata</option>
                                <option value="Rifiutata">Rifiutata</option>
                                <option value="Concluso">Concluso</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filterServiceType" class="form-label">Filtra per servizio:</label>
                            <select class="form-select form-select-sm" id="filterServiceType">
                                <option value="">Tutti</option>
                                <option value="Cassa Edile">Cassa Edile</option>
                                <option value="Edilcassa">Edilcassa</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button id="applyFilters" class="btn btn-primary btn-sm w-100">Applica Filtri</button>
                        </div>
                    </div>

                    {{-- Tabella DataTable --}}
                    <div class="table-responsive">
                        <table id="serviceRequestsTable" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Utente</th>
                                    <th>Servizio</th>
                                    <th>Tipo</th>
                                    <th>Stato</th>
                                    <th>Data Richiesta</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- I dati verranno caricati qui tramite AJAX da DataTables --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDetailsModalLabel">Dettagli Utente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modal-user-details-content">
                    <p><strong>Nome:</strong> <span id="modal-user-name"></span></p>
                    <p><strong>Cognome:</strong> <span id="modal-user-last-name"></span></p>
                    <p><strong>Email:</strong> <span id="modal-user-email"></span></p>
                    <p><strong>Codice Fiscale:</strong> <span id="modal-user-codice-fiscale"></span></p>
                    <p><strong>Numero di Telefono:</strong> <span id="modal-user-phone-number"></span></p>
                    <p><strong>Tipo Contratto:</strong> <span id="modal-user-contract-type"></span></p>
                    <p><strong>Qualifica:</strong> <span id="modal-user-job-title"></span></p>
                    <p><strong>Registrato il:</strong> <span id="modal-user-created-at"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Includi le librerie DataTables --}}
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inizializza DataTable
            $('#serviceRequestsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.service-requests.data') }}", // Questa rotta dovrà essere creata
                    data: function (d) {
                        d.status = $('#filterStatus').val();
                        d.service_type = $('#filterServiceType').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'user_name', name: 'user.name' }, // 'user.name' per accedere alla relazione
                    { data: 'service_name', name: 'service_name' },
                    { data: 'service_type', name: 'service_type' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
                columnDefs: [
                    {
                        targets: 1, // Colonna "Utente"
                        render: function(data, type, row) {
                            return '<a href="#" class="user-details-link" data-user-id="' + row.user.id + '">' + data + '</a>';
                        }
                    },
                    {
                        targets: 2, // Colonna "Servizio"
                        render: function(data, type, row) {
                            var detailUrl = '{{ route('admin.service-requests.show', ':id') }}';
                            detailUrl = detailUrl.replace(':id', row.id);
                            return '<a href="' + detailUrl + '" class="service-details-link">' + data + '</a>';
                        }
                    }
                ],
                language: {
                    url: '{{ asset('js/datatables/i18n/Italian.json') }}'
                }
            });

            // Applica filtri al click del bottone
            $('#applyFilters').on('click', function() {
                $('#serviceRequestsTable').DataTable().ajax.reload();
            });

            // Handle click on user name to show modal
            $('#serviceRequestsTable').on('click', '.user-details-link', function(e) {
                e.preventDefault();
                var userId = $(this).data('user-id');
                
                // Clear previous data
                $('#modal-user-details-content span').text('');

                $.ajax({
                    url: '{{ url('admin/users') }}/' + userId + '/details-modal', // New route
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            var user = response.user;
                            $('#modal-user-name').text(user.name);
                            $('#modal-user-last-name').text(user.last_name);
                            $('#modal-user-email').text(user.email);
                            $('#modal-user-codice-fiscale').text(user.codice_fiscale);
                            $('#modal-user-phone-number').text(user.phone_number);
                            $('#modal-user-contract-type').text(user.contract_type);
                            $('#modal-user-job-title').text(user.job_title);
                            $('#modal-user-created-at').text(user.created_at);
                            
                            var userDetailsModal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
                            userDetailsModal.show();
                        } else {
                            Swal.fire('Errore', response.message || 'Impossibile caricare i dettagli utente.', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Errore', 'Si è verificato un errore durante il recupero dei dati utente.', 'error');
                        console.error(xhr.responseText);
                    }
                });
            });

        });
    </script>
    <style>
        /* Override per il card-body della dashboard admin */
        .admin-dashboard-card-body {
            align-items: stretch !important; /* Permette al contenuto di occupare tutta la larghezza disponibile */
            /* Se vuoi che il contenuto inizi dall'alto invece che centrato verticalmente */
            justify-content: flex-start !important;
        }
    </script>
@endpush