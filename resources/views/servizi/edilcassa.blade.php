@extends('layouts.app')

@section('title', 'Prestazioni Edilcassa')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container">
    <h1 class="text-white mb-4">Servizi Edilcassa</h1>

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
        @foreach ($prestazioniEdilcassa as $prestazione)
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi {{ $prestazione['icona'] }}"></i> {{ $prestazione['nome'] }}</h5>
                        <p class="card-text">{{ $prestazione['descrizione'] }}</p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#serviceModal"
                                data-service-name="{{ $prestazione['nome'] }}"
                                data-service-description="{{ $prestazione['descrizione_completa'] }}"
                                data-service-type="{{ $prestazione['service_type'] }}"
                                @if (isset($prestazione['active_request']))
                                    data-active-request="{{ json_encode($prestazione['active_request']) }}"
                                @endif
                                {{-- Pass initial form inputs if any --}}
                                @if (isset($prestazione['documentazione_richiesta']) && !empty($prestazione['documentazione_richiesta']))
                                    data-form-inputs="{{ json_encode($prestazione['documentazione_richiesta']) }}"
                                @endif
                                >
                            Dettagli e Richiedi
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Modale per i dettagli del servizio e il form di richiesta --}}
<div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="serviceModalLabel">Dettaglio Prestazione</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="serviceFullDescription" style="white-space: pre-wrap;">
            {{-- La descrizione completa del servizio verrà iniettata qui --}}
        </div>
                    <div id="serviceModalDocsContainer" class="mt-4 border-top pt-3">
            {{-- I campi per i documenti verranno iniettati qui dal JS --}}
        </div>
        <div id="serviceModalActiveRequestInfo" class="mt-4 border-top pt-3"></div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
        <a href="#" id="modalProceedBtn" class="btn btn-success">Procedi con la presentazione</a>
      </div>
    </div>
  </div>
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
        const serviceModalEl = document.getElementById('serviceModal');
        if (serviceModalEl) {
            const serviceModal = new bootstrap.Modal(serviceModalEl);
            const modalTitleEl = document.getElementById('serviceModalLabel');
            const modalDescriptionEl = document.getElementById('serviceModalDescription');
            const modalDocsContainerEl = document.getElementById('serviceModalDocsContainer');
            const serviceModalActiveRequestInfoEl = document.getElementById('serviceModalActiveRequestInfo');
            const modalProceedBtn = document.getElementById('modalProceedBtn');

            // Funzione per aggiornare lo stato e gli attributi data del pulsante "Procedi"
            function updateProceedButton(serviceTitle, serviceDescription, serviceType, currentStatus, requestId = null) {
                modalProceedBtn.dataset.serviceTitle = serviceTitle;
                modalProceedBtn.dataset.serviceDescription = serviceDescription;
                modalProceedBtn.dataset.serviceType = serviceType;
                modalProceedBtn.dataset.currentStatus = currentStatus;
                modalProceedBtn.dataset.requestId = requestId; // Aggiungi l'ID della richiesta
                
                // Una richiesta è considerata "attiva" se non è né Conclusa né Rifiutata
                const hasActiveRequest = currentStatus && currentStatus !== 'Conclusa' && currentStatus !== 'Rifiutata';
                if (hasActiveRequest) {
                    modalProceedBtn.classList.add('disabled');
                    modalProceedBtn.removeAttribute('href');
                    modalProceedBtn.textContent = `Richiesta ${currentStatus}`;
                    modalProceedBtn.title = `Hai già una richiesta per questo servizio in stato di "${currentStatus}".`;
                } else {
                    // Se la richiesta precedente è Conclusa o Rifiutata, si può procedere con una nuova
                    modalProceedBtn.classList.remove('disabled');
                    modalProceedBtn.setAttribute('href', '#');
                    if (currentStatus === 'Conclusa' || currentStatus === 'Rifiutata') {
                        modalProceedBtn.textContent = 'Procedi con una nuova presentazione';
                    } else {
                        modalProceedBtn.textContent = 'Procedi con la presentazione';
                    }
                    modalProceedBtn.title = '';
                }
            }

            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const serviceTitle = this.dataset.serviceName;
                    const serviceDescription = this.dataset.serviceDescription;
                    const serviceType = this.dataset.serviceType;
                    const currentStatus = this.dataset.currentStatus || '';
                    const activeRequestData = this.dataset.activeRequest ? JSON.parse(this.dataset.activeRequest) : null;
                    const requiredDocs = this.dataset.formInputs ? JSON.parse(this.dataset.formInputs) : [];

                    modalTitleEl.innerHTML = serviceTitle;
                    modalDescriptionEl.innerHTML = serviceDescription;

                    modalDocsContainerEl.innerHTML = '';
                    serviceModalActiveRequestInfoEl.innerHTML = '';

                    if (requiredDocs.length > 0) {
                        let docsHtml = '<h6 class="text-muted">Documentazione/Dati Richiesti</h6>';
                        requiredDocs.forEach(group => {
                            if (group.description) {
                                docsHtml += `<p class="small fst-italic">${group.description}</p>`;
                            }
                            group.inputs.forEach(input => {
                                const requiredStar = input.required ? ' <span class="text-danger">*</span>' : '';
                                const requiredAttr = input.required ? 'required' : '';
                                docsHtml += '<div class="mb-3">';
                                docsHtml += `<label for="doc_${input.name}" class="form-label small">${input.label}${requiredStar}</label>`;
                                if (group.type === 'form') {
                                    docsHtml += `<input type="${input.type}" class="form-control form-control-sm" id="doc_${input.name}" name="${input.name}" placeholder="${input.placeholder || ''}" ${requiredAttr}>`;
                                }
                                docsHtml += '</div>';
                            });
                        });
                        modalDocsContainerEl.innerHTML = docsHtml;
                        modalDocsContainerEl.style.display = 'block';
                    } else {
                        modalDocsContainerEl.style.display = 'none';
                    }

                    if (activeRequestData) {
                        let activeRequestHtml = `
                            <hr>
                            <p class="mb-1">Stato della tua richiesta: <strong>${activeRequestData.status}</strong></p>
                            <small class="text-muted">Ultimo aggiornamento: ${new Date(activeRequestData.updated_at).toLocaleDateString('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</small>
                        `;
                        if (activeRequestData.admin_notes) {
                            activeRequestHtml += `
                                <div class="alert alert-info mt-2" role="alert">
                                    <strong>Note del funzionario:</strong>
                                    <p class="mb-0">${activeRequestData.admin_notes.replace(/\n/g, '<br>')}</p>
                                </div>
                            `;
                        }

                        if (activeRequestData.status === 'Richiesta integrazione') {
                            activeRequestHtml += `
                                <hr>
                                <h6>Area Documenti - Carica i file richiesti</h6>
                                <form id="uploadFormModal-${activeRequestData.id}" class="upload-form-modal" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="service_request_id" value="${activeRequestData.id}">
                                    <div class="mb-3">
                                        <label for="documents-modal-${activeRequestData.id}" class="form-label">Seleziona uno o più documenti (PDF, JPG, PNG - max 5MB ciascuno):</label>
                                        <input class="form-control" type="file" name="documents[]" id="documents-modal-${activeRequestData.id}" multiple required>
                                        <div class="invalid-feedback" id="documents-feedback-modal-${activeRequestData.id}">
                                            Devi caricare almeno un documento.
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary upload-btn-modal" data-request-id="${activeRequestData.id}">Invia pratica aggiornata</button>
                                </form>
                            `;
                            modalProceedBtn.style.display = 'none';
                        } else {
                            modalProceedBtn.style.display = 'block';
                        }

                        if (activeRequestData.uploaded_documents && activeRequestData.uploaded_documents.length > 0) {
                            activeRequestHtml += `<div class="mt-3"><h6>Documenti caricati:</h6><ul class="list-group">`;
                            activeRequestData.uploaded_documents.forEach(doc => {
                                activeRequestHtml += `<li class="list-group-item d-flex justify-content-between align-items-center">${doc.original_name || 'Nome file non disponibile'} <a href="#" class="btn btn-sm btn-outline-secondary disabled" title="Download non ancora disponibile"><i class="bi bi-download"></i></a></li>`;
                            });
                            activeRequestHtml += `</ul></div>`;
                        }
                        serviceModalActiveRequestInfoEl.innerHTML = activeRequestHtml;
                        serviceModalActiveRequestInfoEl.style.display = 'block';
                        modalDocsContainerEl.style.display = 'none';
                    } else {
                        serviceModalActiveRequestInfoEl.style.display = 'none';
                        modalProceedBtn.style.display = 'block';
                    }

                    updateProceedButton(serviceTitle, serviceDescription, serviceType, currentStatus, activeRequestData ? activeRequestData.id : null);
                    serviceModal.show();
                });
            });

            modalProceedBtn.addEventListener('click', function(e) {
                e.preventDefault();

                @guest
                    Swal.fire({
                        icon: 'warning',
                        title: 'Accesso Richiesto',
                        html: `Per procedere con la richiesta del servizio è necessario effettuare il <a href="{{ route('login') }}">login</a>.`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#c8102e',
                    });
                @else // User is authenticated
                    const serviceDescription = modalProceedBtn.dataset.serviceDescription;
                    const serviceType = modalProceedBtn.dataset.serviceType;
                    const currentStatus = modalProceedBtn.dataset.currentStatus;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const form = serviceModalEl.querySelector('.modal-body');
                    const requiredInputs = form.querySelectorAll('[required]');
                    let isValid = true;
                    requiredInputs.forEach(input => {
                        input.classList.remove('is-invalid');
                        if (!input.value) {
                            if (input.type === 'file' && input.files.length === 0) {
                                isValid = false;
                                input.classList.add('is-invalid');
                            } else if (input.type !== 'file') {
                                isValid = false;
                                input.classList.add('is-invalid');
                            }
                        }
                    });

                    if (!isValid) {
                        Swal.fire({ icon: 'warning', title: 'Campi Obbligatori', text: 'Per favore, compila tutti i campi contrassegnati con *', confirmButtonColor: '#c8102e' });
                        return;
                    }

                    if (currentStatus && currentStatus !== 'Conclusa') {
                        Swal.fire({ icon: 'warning', title: 'Richiesta Attiva', text: `Hai già una richiesta per questo servizio in stato di "${currentStatus}".`, confirmButtonColor: '#c8102e' });
                        return;
                    }

                    Swal.fire({
                        title: 'Confermi la richiesta?',
                        html: `Stai per richiedere il servizio: <strong>${serviceTitle}</strong>. Vuoi procedere?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#c8102e',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sì, procedi!',
                        cancelButtonText: 'Annulla'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Invio richiesta...',
                                text: 'Attendere prego',
                                allowOutsideClick: false,
                                didOpen: () => { Swal.showLoading() }
                            });

                            const formData = new FormData();
                            formData.append('serviceTitle', serviceTitle);
                            formData.append('serviceDescription', serviceDescription);
                            formData.append('serviceType', serviceType);

                            const docInputs = modalDocsContainerEl.querySelectorAll('input');
                            docInputs.forEach(input => {
                                if (input.type === 'file') {
                                    if (input.files.length > 0) {
                                        formData.append(input.name, input.files[0]);
                                    }
                                } else {
                                    formData.append(input.name, input.value);
                                }
                            });

                            fetch('{{ route('servizi.send-service-request') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: formData
                            })
                            .then(response => {
                                Swal.close();
                                if (response.status === 422) {
                                    return response.json().then(err => {
                                        const errorMessages = err.errors.join('<br>');
                                        throw { message: `Errore di validazione:<br>${errorMessages}` };
                                    });
                                }
                                if (!response.ok) {
                                    return response.json().then(err => { throw err; });
                                }
                                return response.json();
                            })
                            .then(data => {
                                Swal.fire({ icon: 'success', title: 'Richiesta Inviata!', text: data.message, confirmButtonColor: '#c8102e' });
                                serviceModal.hide();
                                location.reload();
                            })
                            .catch(error => {
                                Swal.fire({ icon: 'error', title: 'Errore!', text: error.message || 'Si è verificato un errore durante l\'invio della richiesta.', confirmButtonColor: '#c8102e' });
                            });
                        }
                    });
                @endguest
            });
        }

        function showModal(title, message) {
            const modal = new bootstrap.Modal(document.getElementById('responseModal'));
            document.getElementById('responseModalLabel').textContent = title;
            document.getElementById('responseModalBody').innerHTML = message;
            modal.show();
        }
    });
</script>
@endpush