@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid"> {{-- Changed to container-fluid --}}
    <div class="row justify-content-center">
        <div class="col-md-12"> {{-- Changed to col-md-12 for wider content --}}
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        {{ __('Dashboard Amministratore') }}
                        @if(session('admin_user.superadmin'))
                            <span class="badge bg-warning text-dark ms-2">Super Admin</span>
                        @endif
                    </h4>
                    <div>
                        <a href="{{ route('admin.service-requests.trash') }}" class="btn btn-secondary btn-sm me-2">
                            <i class="bi bi-trash"></i> Cestino
                        </a>
                        <a href="{{ route('admin.logout') }}" class="btn btn-danger btn-sm" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">@csrf</form>
                    </div>
                </div>
                

                <div class="card-body p-4 admin-dashboard-card-body">
                    <h5 class="mb-3">Richieste di Servizio</h5>

                    {{-- Filtri Generici --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="filterStatus" class="form-label">Filtra per Stato:</label>
                            <select class="form-select form-select-sm" id="filterStatus">
                                <option value="">Tutti</option>
                                <option value="Inviata">Inviata</option>
                                <option value="Richiesta integrazione">Richiesta integrazione</option>
                                <option value="In attesa documenti">In attesa documenti</option>
                                <option value="Conclusa">Conclusa</option>
                                <option value="Rifiutata">Rifiutata</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterServiceType" class="form-label">Filtra per servizio:</label>
                            <select class="form-select form-select-sm" id="filterServiceType">
                                <option value="">Tutti</option>
                                <option value="Cassa Edile">Cassa Edile</option>
                                <option value="Edilcassa">Edilcassa</option>
                            </select>
                        </div>
                        @if(session('admin_user.superadmin'))
                        <div class="col-md-3">
                            <label for="filterFunzionario" class="form-label">Filtra per Funzionario:</label>
                            <select class="form-select form-select-sm" id="filterFunzionario">
                                <option value="">Tutti</option>
                                @foreach(config('admins.users', []) as $admin)
                                    @if(!$admin['superadmin'])
                                        <option value="{{ $admin['id'] }}">{{ $admin['name'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3 d-flex align-items-end">
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
                                    @if(session('admin_user.superadmin'))
                                    <th>Funzionario Assegnato</th>
                                    @endif
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Passa le variabili PHP a JavaScript
        const isSuperAdmin = {{ session('admin_user.superadmin') ? 'true' : 'false' }};
        const adminUsers = @json(config('admins.users', []));
        const csrfToken = '{{ csrf_token() }}';

        $(document).ready(function() {
            // Definisci la struttura delle colonne in anticipo
            let columns = [
                { data: 'id', name: 'id' },
                { data: 'user_name', name: 'user.name' },
                { data: 'service_name', name: 'service_name' },
                { data: 'service_type', name: 'service_type' },
                { data: 'status', name: 'status' },
            ];

            if (isSuperAdmin) {
                columns.push({
                    data: 'id_funzionario',
                    name: 'id_funzionario',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let select = `<select class="form-select form-select-sm assignment-dropdown" data-id="${row.id}">`;
                        select += `<option value="">Non assegnato</option>`;
                        adminUsers.forEach(function(admin) {
                            // Non mostrare i superadmin come opzione di assegnazione
                            if (!admin.superadmin) {
                                const selected = data == admin.id ? 'selected' : ''; // Usa '==' per confronto non stretto (string vs number)
                                select += `<option value="${admin.id}" ${selected}>${admin.name}</option>`;
                            }
                        });
                        select += `</select>`;
                        return select;
                    }
                });
            }

            columns.push(
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            );

            // Inizializza DataTable
            const dataTable = $('#serviceRequestsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.service-requests.data') }}", // Questa rotta dovrà essere creata
                    data: function (d) {
                        d.status = $('#filterStatus').val();
                        d.service_type = $('#filterServiceType').val();
                        if (isSuperAdmin) {
                            d.id_funzionario = $('#filterFunzionario').val();
                        }
                    }
                },
                columns: columns, // Passa l'array di colonne completo
                columnDefs: [
                    {
                        targets: 1, // Colonna "Utente"
                        render: function(data, type, row) {
                            // Aggiunto controllo per evitare errori se l'utente è stato cancellato
                            if (row.user) {
                                return '<a href="#" class="user-details-link" data-user-id="' + row.user.id + '">' + data + '</a>';
                            }
                            return data; // Ritorna solo il nome (es. 'N/A') se l'utente non esiste
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
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/Italian.json'
                }
            });

            // Applica filtri al click del bottone
            $('#applyFilters').on('click', function() {
                dataTable.ajax.reload();
            });

            // Gestione riassegnazione
            $('#serviceRequestsTable').on('change', '.assignment-dropdown', function() {
                const serviceRequestId = $(this).data('id');
                const newAssigneeId = $(this).val();
                reassignRequest(serviceRequestId, newAssigneeId);
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

            // Gestione cancellazione con SweetAlert
            $('#serviceRequestsTable').on('submit', 'form.delete-form', function(e) {
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


            function reassignRequest(serviceRequestId, funzionarioId) {
                const admin = adminUsers.find(u => u.id == funzionarioId);
                const adminName = admin ? admin.name : 'nessuno';
                Swal.fire({
                    title: 'Sei sicuro?',
                    text: `Vuoi riassegnare questa pratica a ${adminName}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sì, riassegna!',
                    cancelButtonText: 'Annulla'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('admin/service-requests') }}/${serviceRequestId}/assign`,
                            method: 'POST',
                            data: {
                                _token: csrfToken,
                                id_funzionario: funzionarioId
                            },
                            success: (response) => Swal.fire('Riassegnata!', response.message, 'success'),
                            error: (xhr) => Swal.fire('Errore!', xhr.responseJSON.message || 'Si è verificato un errore.', 'error')
                        });
                    }
                });
            }
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