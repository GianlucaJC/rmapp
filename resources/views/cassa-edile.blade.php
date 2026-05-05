@extends('layouts.app')

@section('title', 'Servizi Cassa Edile')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container">
    <h1 class="text-white mb-4">Servizi Cassa Edile</h1>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        @foreach ($prestazioniCassaEdile as $prestazione)
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi {{ $prestazione['icona'] }}"></i> {{ $prestazione['nome'] }}</h5>
                        <p class="card-text">{{ $prestazione['descrizione'] }}</p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#serviceModal"
                                data-service-name="{{ $prestazione['nome'] }}"
                                data-service-description="{{ $prestazione['descrizione_completa'] }}"
                                data-service-type="{{ $prestazione['service_type'] }}"
                                {{-- Pass initial form inputs if any --}}
                                @if (isset($prestazione['documentazione_richiesta']) && !empty($prestazione['documentazione_richiesta']))
                                    data-form-inputs="{{ json_encode($prestazione['documentazione_richiesta']) }}"
                                @endif
                                >
                            Dettagli e Richiedi
                        </button>

                        {{-- Display active request status and upload form if applicable --}}
                        @if (isset($prestazione['active_request']))
                            <hr>
                            <p class="mb-1">Stato della tua richiesta: <strong>{{ $prestazione['active_request']->status }}</strong></p>
                            <small class="text-muted">Ultimo aggiornamento: {{ $prestazione['active_request']->updated_at->format('d/m/Y H:i') }}</small>

                            @if ($prestazione['active_request']->admin_notes)
                                <div class="alert alert-info mt-2" role="alert">
                                    <strong>Note del funzionario:</strong>
                                    <p class="mb-0">{!! nl2br(e($prestazione['active_request']->admin_notes)) !!}</p>
                                </div>
                            @endif

                            @if ($prestazione['active_request']->status === 'Richiesta integrazione')
                                <hr>
                                <h6>Area Documenti - Carica i file richiesti</h6>
                                <form id="uploadForm-{{ $prestazione['active_request']->id }}" class="upload-form" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="service_request_id" value="{{ $prestazione['active_request']->id }}">
                                    <div class="mb-3">
                                        <label for="documents-{{ $prestazione['active_request']->id }}" class="form-label">Seleziona uno o più documenti (PDF, JPG, PNG - max 5MB ciascuno):</label>
                                        <input class="form-control" type="file" name="documents[]" id="documents-{{ $prestazione['active_request']->id }}" multiple required>
                                        <div class="invalid-feedback" id="documents-feedback-{{ $prestazione['active_request']->id }}">
                                            Devi caricare almeno un documento.
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary upload-btn" data-request-id="{{ $prestazione['active_request']->id }}">Invia pratica aggiornata</button>
                                </form>
                            @endif

                            @if (is_array($prestazione['active_request']->uploaded_documents) && !empty($prestazione['active_request']->uploaded_documents))
                                <div class="mt-3">
                                    <h6>Documenti caricati:</h6>
                                    <ul class="list-group">
                                        @foreach ($prestazione['active_request']->uploaded_documents as $doc)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                {{ $doc['original_name'] ?? 'Nome file non disponibile' }}
                                                <a href="#" class="btn btn-sm btn-outline-secondary disabled" title="Download non ancora disponibile">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Modale per i dettagli del servizio e il form di richiesta --}}
<div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="serviceFullDescription"></div>
                <hr>
                <form id="serviceRequestForm">
                    @csrf
                    <input type="hidden" name="serviceTitle" id="modalServiceTitle">
                    <input type="hidden" name="serviceType" id="modalServiceType">
                    <input type="hidden" name="serviceDescription" id="modalServiceDescription">
                    
                    <div id="dynamicFormInputs">
                        {{-- Qui verranno iniettati i campi del form dinamici (es. IBAN) --}}
                    </div>

                    <p class="mt-3">Clicca su "Procedi" per inviare la tua richiesta. Un funzionario valuterà i requisiti e ti contatterà per eventuali integrazioni documentali.</p>
                    <button type="submit" class="btn btn-primary" id="submitServiceRequest">Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica</button>
                </form>
            </div>
        </div>
    </div>
}
</div>

{{-- Modale per messaggi di successo/errore (per upload) --}}
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

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // JavaScript per il modale di richiesta servizio (già esistente)
        var serviceModal = document.getElementById('serviceModal');
        serviceModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var serviceName = button.getAttribute('data-service-name');
            var serviceDescription = button.getAttribute('data-service-description');
            var serviceType = button.getAttribute('data-service-type');
            var formInputsData = button.getAttribute('data-form-inputs');

            var modalTitle = serviceModal.querySelector('.modal-title');
            var modalBodyDescription = serviceModal.querySelector('#serviceFullDescription');
            var modalServiceTitle = serviceModal.querySelector('#modalServiceTitle');
            var modalServiceType = serviceModal.querySelector('#modalServiceType');
            var modalServiceDescription = serviceModal.querySelector('#modalServiceDescription');
            var dynamicFormInputsDiv = serviceModal.querySelector('#dynamicFormInputs');

            modalTitle.textContent = serviceName;
            modalBodyDescription.innerHTML = serviceDescription;
            modalServiceTitle.value = serviceName;
            modalServiceType.value = serviceType;
            modalServiceDescription.value = serviceDescription;

            // Clear previous dynamic inputs
            dynamicFormInputsDiv.innerHTML = '';

            if (formInputsData) {
                var formInputs = JSON.parse(formInputsData);
                formInputs.forEach(group => {
                    if (group.type === 'form' && group.inputs) {
                        group.inputs.forEach(input => {
                            var inputHtml = `
                                <div class="mb-3">
                                    <label for="${input.name}" class="form-label">${input.label}</label>
                                    <input type="${input.type}" class="form-control" id="${input.name}" name="${input.name}" ${input.required ? 'required' : ''} placeholder="${input.placeholder || ''}">
                                </div>
                            `;
                            dynamicFormInputsDiv.innerHTML += inputHtml;
                        });
                    }
                });
            }
        });

        // JavaScript per l'invio della richiesta servizio (già esistente)
        document.getElementById('serviceRequestForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitButton = document.getElementById('submitServiceRequest');
            submitButton.disabled = true;
            submitButton.textContent = 'Invio...';

            fetch('{{ route('servizi.send-service-request') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || 'Errore durante l\'invio della richiesta.');
                    });
                }
                return response.json();
            })
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Richiesta Inviata!',
                    text: data.message,
                    confirmButtonText: 'Ok'
                }).then(() => {
                    location.reload(); // Ricarica la pagina per aggiornare lo stato
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Errore!',
                    text: error.message || 'Si è verificato un errore inaspettato.',
                    confirmButtonText: 'Ok'
                });
                submitButton.disabled = false;
                submitButton.textContent = 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica';
            });
        });

        // JavaScript per l'upload dei documenti (nuovo)
        document.querySelectorAll('.upload-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const requestId = this.querySelector('input[name="service_request_id"]').value;
                const filesInput = this.querySelector('input[name="documents[]"]');
                const feedbackDiv = document.getElementById(`documents-feedback-${requestId}`);
                const uploadButton = this.querySelector('.upload-btn');

                // Client-side validation for at least one file
                if (filesInput.files.length === 0) {
                    filesInput.classList.add('is-invalid');
                    feedbackDiv.style.display = 'block';
                    return;
                } else {
                    filesInput.classList.remove('is-invalid');
                    feedbackDiv.style.display = 'none';
                }

                const formData = new FormData(this);
                // No need to append _method 'POST' here, as the route is already POST and FormData handles it.
                // If the route was PUT, you'd need to simulate it with _method=PUT in FormData.
                // But for file uploads, POST is standard.

                uploadButton.disabled = true;
                uploadButton.textContent = 'Caricamento...';

                fetch(`/servizi/upload-documents/${requestId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || 'Errore durante il caricamento dei documenti.');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    showModal('Successo', data.message || 'Documenti caricati con successo!');
                    location.reload(); // Ricarica la pagina per mostrare lo stato aggiornato e i documenti
                })
                .catch(error => {
                    console.error('Errore:', error);
                    showModal('Errore', error.message || 'Si è verificato un errore inaspettato.');
                    uploadButton.disabled = false;
                    uploadButton.textContent = 'Invia pratica aggiornata';
                });
            });
        });

        function showModal(title, message) {
            const modal = new bootstrap.Modal(document.getElementById('responseModal'));
            document.getElementById('responseModalLabel').textContent = title;
            document.getElementById('responseModalBody').innerHTML = message;
            modal.show();
        }
    });
</script>
@endpush