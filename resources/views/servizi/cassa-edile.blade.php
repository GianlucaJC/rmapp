@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="page-title text-center mb-5">
                <h1>Elenco Prestazioni Cassa Edile</h1>
                <p class="lead">Prestazioni OPERAI della Cassa Edile di Roma</p>
            </div>

            <div class="row g-4">
                @foreach ($prestazioniCassaEdile as $prestazione)
                    <div class="col-md-6 col-lg-4 d-flex align-items-stretch" id="{{ $prestazione['slug'] }}">
                        <div class="flip-card w-100" tabindex="0">
                            <div class="flip-card-inner">
                                <div class="flip-card-front">
                                    <div class="card h-100 w-100 d-flex flex-column">
                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                            <i class="bi {{ $prestazione['icona'] }} fs-1 mb-3 text-success"></i>
                                            <h6 class="card-title mb-0">{{ $prestazione['nome'] }}</h6>
                                            @if (isset($prestazione['current_status']))
                                                <span class="badge bg-danger mt-2" style="font-size: 0.85em;">Stato: {{ $prestazione['current_status'] }} il {{ $prestazione['request_date'] }}</span>
                                            @endif
                                        </div>
                                        <div class="card-footer bg-transparent border-0 d-flex justify-content-center pt-0 pb-3">
                                            <a href="#" class="btn btn-sm btn-primary btn-show-guide" title="Dettagli e Guida alla presentazione"
                                               data-service-title="{{ $prestazione['nome'] }}"
                                               data-service-description="{{ $prestazione['descrizione_completa'] }}"
                                               data-service-type="{{ $prestazione['service_type'] }}"
                                               data-current-status="{{ $prestazione['current_status'] ?? '' }}"
                                               data-required-docs="{{ isset($prestazione['documentazione_richiesta']) ? json_encode($prestazione['documentazione_richiesta']) : '' }}"
                                               @if (isset($prestazione['active_request']) && $prestazione['active_request'] && $prestazione['active_request']->id)
                                                   data-active-request="{{ json_encode($prestazione['active_request']) }}"
                                               @endif
                                            >
                                                <i class="bi bi-book"></i> Dettagli
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="flip-card-back">
                                    <div class="card bg-dark text-white h-100 w-100">
                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-3">
                                            <p class="small">{{ $prestazione['descrizione'] }}</p>
                                            <a href="#" class="btn btn-sm btn-light mt-3" onclick="this.closest('.flip-card-inner').style.transform = 'rotateY(0deg)'; return false;">
                                                <i class="bi bi-arrow-left-circle"></i> Torna
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Service Guide Modal -->
            <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="serviceModalLabel">Dettaglio Prestazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div id="serviceModalDescription" style="white-space: pre-wrap;">
                        {{-- La descrizione completa del servizio verrà iniettata qui --}}
                    </div>
                    <div id="serviceModalDocsContainer" class="mt-4 pt-3">
                        {{-- I campi per i documenti verranno iniettati qui dal JS --}}
                    </div>
                    <div id="serviceModalActiveRequestInfo" class="mt-4 border-top pt-3"></div>
                  </div>
                  <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <a href="#" id="modalProceedBtn" class="btn btn-success">Procedi con la presentazione</a>
                  </div>
                </div>
              </div>
            </div>

            <div class="text-center mt-5">
                <a href="{{ route('servizi.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Torna alla selezione</a>
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
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
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
                    modalProceedBtn.removeAttribute('href'); // Rimuove href per prevenire la navigazione
                    modalProceedBtn.textContent = `Richiesta ${currentStatus}`; // Testo per stato attivo
                    modalProceedBtn.title = `Hai già una richiesta per questo servizio in stato di "${currentStatus}".`;
                } else {
                    // Se la richiesta precedente è Conclusa o Rifiutata, si può procedere con una nuova
                    modalProceedBtn.classList.remove('disabled');
                    modalProceedBtn.setAttribute('href', '#'); // Ripristina href
                    if (currentStatus === 'Conclusa' || currentStatus === 'Rifiutata') {
                        modalProceedBtn.textContent = 'Procedi con una nuova presentazione';
                    } else {
                        modalProceedBtn.textContent = 'Procedi con la presentazione';
                    }
                    modalProceedBtn.title = ''; // Pulisce il titolo
                }
            }

            document.querySelectorAll('.btn-show-guide').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const serviceTitle = this.dataset.serviceTitle;
                    const serviceDescription = this.dataset.serviceDescription;
                    const serviceType = this.dataset.serviceType;
                    const currentStatus = this.dataset.currentStatus;
                    const activeRequestData = this.dataset.activeRequest ? JSON.parse(this.dataset.activeRequest) : null;
                    const requiredDocs = this.dataset.requiredDocs ? JSON.parse(this.dataset.requiredDocs) : [];

                    modalTitleEl.innerHTML = serviceTitle;
                    modalDescriptionEl.innerHTML = serviceDescription;

                    // Pulisce i contenitori
                    modalDocsContainerEl.innerHTML = '';
                    serviceModalActiveRequestInfoEl.innerHTML = '';

                    // Gestione della logica per richieste attive vs nuove richieste
                    modalDocsContainerEl.innerHTML = ''; // Pulisce il contenuto precedente
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
                                if (group.type === 'upload') {
                                    docsHtml += `<input type="file" class="form-control form-control-sm" id="doc_${input.name}" name="${input.name}" ${requiredAttr}>`;
                                } else if (group.type === 'form') {
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
                        // Se esiste una richiesta attiva, mostra le sue informazioni
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
                            // Se è richiesta integrazione, mostra il form di upload
                            activeRequestHtml += `
                                <hr>
                                <h6>Area Documenti - Carica i file richiesti</h6>
                                <!-- Container per i file già caricati -->
                                <div id="uploaded-files-list-modal-${activeRequestData.id}" class="list-group mb-3">
                                    ${(activeRequestData.uploaded_documents && Array.isArray(activeRequestData.uploaded_documents) && activeRequestData.uploaded_documents.length > 0) ?
                                        activeRequestData.uploaded_documents.map(doc => `
                                            <div class="list-group-item d-flex justify-content-between align-items-center" data-doc-path="${doc.path}">
                                                <span class="text-break">${doc.original_name || 'Nome file non disponibile'} (${(doc.size / 1024 / 1024).toFixed(2)} MB)</span>
                                                <div class="ms-2 flex-shrink-0">
                                                    <a href="#" class="btn btn-sm btn-outline-secondary disabled me-2" title="Download non ancora disponibile">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger delete-document-btn" data-request-id="${activeRequestData.id}" data-file-path="${doc.path}" title="Elimina documento">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        `).join('') : ''
                                    }
                                </div>

                                <!-- Form per i nuovi upload -->
                                <form id="singleUploadFormModal-${activeRequestData.id}" class="single-upload-form-modal" enctype="multipart/form-data" novalidate>
                                    <input type="hidden" name="service_request_id" value="${activeRequestData.id}">
                                    <div class="input-group mb-3">
                                        <input type="file" class="form-control" name="document" id="document-modal-${activeRequestData.id}" required>
                                        <button class="btn btn-outline-secondary upload-single-btn-modal" type="submit" data-request-id="${activeRequestData.id}">Carica</button>
                                    </div>
                                    <div class="progress mb-2" style="height: 20px; display: none;">
                                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                    </div>
                                    <small class="text-muted">Max 5MB per file (PDF, JPG, PNG)</small>
                                    <div class="form-text" id="single-upload-feedback-modal-${activeRequestData.id}"></div>
                                </form>

                                <!-- Bottone di invio finale -->
                                <div class="d-grid gap-2 mt-3">
                                    <button type="button" class="btn btn-success resubmit-request-btn" data-request-id="${activeRequestData.id}">Invia pratica aggiornata</button>
                                </div>
                            `;
                            modalProceedBtn.style.display = 'none'; // Nasconde il bottone "Procedi" standard
                        } else {
                            modalProceedBtn.style.display = 'block'; // Mostra il bottone "Procedi" standard
                        }
                        serviceModalActiveRequestInfoEl.innerHTML = activeRequestHtml;
                        serviceModalActiveRequestInfoEl.style.display = 'block';
                        modalDocsContainerEl.style.display = 'none'; // Nasconde i campi per nuova richiesta
                    } else {
                        // Logica per nuova richiesta
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
                                    if (group.type === 'upload') {
                                        docsHtml += `<input type="file" class="form-control form-control-sm" id="doc_${input.name}" name="${input.name}" ${requiredAttr}>`;
                                    } else if (group.type === 'form') {
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
                        serviceModalActiveRequestInfoEl.style.display = 'none';
                        modalProceedBtn.style.display = 'block'; // Mostra il bottone "Procedi" standard
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
                    confirmButtonColor: '#c8102e',
                });
                return; // Stop execution for guests
                @endguest

                // Se si arriva qui, l'utente è autenticato.
                // Procedi con la logica per l'utente autenticato.
                const serviceTitle = modalProceedBtn.dataset.serviceTitle; // Get from dataset
                const serviceDescription = modalProceedBtn.dataset.serviceDescription;
                const serviceType = modalProceedBtn.dataset.serviceType;
                const currentStatus = modalProceedBtn.dataset.currentStatus;

                // Validazione client-side
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
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campi Obbligatori',
                        text: 'Per favore, compila tutti i campi contrassegnati con *',
                        confirmButtonColor: '#c8102e'
                    });
                    return;
                }

                // Se la richiesta è in stato di integrazione, non permettere l'invio di una nuova richiesta
                if (currentStatus && currentStatus !== 'Conclusa') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Richiesta Attiva',
                        text: `Hai già una richiesta per questo servizio in stato di "${currentStatus}".`,
                        confirmButtonColor: '#c8102e'
                    });
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
                }).then(result => {
                    if (result.isConfirmed) {
                        // Mostra lo spinner di caricamento
                        Swal.fire({
                            title: 'Invio richiesta...',
                            text: 'Attendere prego',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading() }
                        });

                        // Prepara FormData
                        const formData = new FormData();
                        formData.append('serviceTitle', serviceTitle);
                        formData.append('serviceDescription', serviceDescription);
                        formData.append('serviceType', serviceType);

                        // Aggiunge i campi dinamici
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

                        // Chiamata AJAX con FormData
                        fetch('{{ route('servizi.send-service-request') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(response => {
                            Swal.close(); // Chiude lo spinner
                            if (response.status === 422) { // Errore di validazione dal backend
                                return response.json().then(err => {
                                    const errorMessages = err.errors.map(error => error.message || error).join('<br>'); // Handle validation errors
                                    throw {
                                        message: `Errore di validazione:<br>${errorMessages}`
                                    };
                                });
                            }
                            if (!response.ok) {
                                return response.json().then(err => {
                                    throw err;
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Richiesta Inviata!',
                                text: data.message,
                                confirmButtonColor: '#c8102e'
                            }).then(() => {
                                serviceModal.hide(); // Chiude la modale
                                location.reload(); // Ricarica la pagina per aggiornare lo stato del badge
                            });
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Errore!',
                                html: error.message || 'Si è verificato un errore durante l\'invio della richiesta.',
                                confirmButtonColor: '#c8102e'
                            });
                        });
                    }
                });
            });

            // --- Event Delegation for dynamic content ---
            serviceModalEl.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.delete-document-btn');
                const resubmitBtn = e.target.closest('.resubmit-request-btn');

                if (deleteBtn) {
                    e.preventDefault();
                    const requestId = deleteBtn.dataset.requestId;
                    const filePath = deleteBtn.dataset.filePath;
                    handleDeleteDocument(requestId, filePath, deleteBtn);
                }

                if (resubmitBtn) {
                    e.preventDefault();
                    const requestId = resubmitBtn.dataset.requestId;
                    handleResubmitRequest(requestId);
                }
            });

            serviceModalEl.addEventListener('submit', function(e) {
                if (e.target.matches('.single-upload-form-modal')) {
                    e.preventDefault();
                    const form = e.target;
                    const requestId = form.querySelector('.upload-single-btn-modal').dataset.requestId;
                    handleUploadSingleFile(form, requestId);
                }
            });

            function handleUploadSingleFile(form, requestId) {
                const fileInput = form.querySelector('input[type="file"]');
                const feedbackEl = form.querySelector(`#single-upload-feedback-modal-${requestId}`);
                const progressContainer = form.querySelector('.progress');
                const progressBar = form.querySelector('.progress-bar');

                feedbackEl.innerHTML = '';
                if (fileInput.files.length === 0) {
                    feedbackEl.innerHTML = '<span class="text-danger">Per favore, seleziona un file da caricare.</span>';
                    return;
                }

                const file = fileInput.files[0];
                const formData = new FormData();
                formData.append('document', file);

                progressContainer.style.display = 'block';
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';

                const xhr = new XMLHttpRequest();
                xhr.open('POST', `{{ url('/servizi/upload-single-document') }}/${requestId}`, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressBar.style.width = percentComplete + '%';
                        progressBar.textContent = Math.round(percentComplete) + '%';
                    }
                };

                xhr.onload = function() {
                    setTimeout(() => { progressContainer.style.display = 'none'; }, 1000);
                    const response = JSON.parse(xhr.responseText);

                    if (xhr.status >= 200 && xhr.status < 300) {
                        feedbackEl.innerHTML = `<span class="text-success">${response.message}</span>`;
                        const listContainer = document.getElementById(`uploaded-files-list-modal-${requestId}`);
                        const newFileHtml = `
                            <div class="list-group-item d-flex justify-content-between align-items-center" data-doc-path="${response.document.path}">
                                <span class="text-break">${response.document.original_name} (${(response.document.size / 1024 / 1024).toFixed(2)} MB)</span>
                                <div class="ms-2 flex-shrink-0">
                                    <a href="#" class="btn btn-sm btn-outline-secondary disabled me-2" title="Download non ancora disponibile"><i class="bi bi-download"></i></a>
                                    <button type="button" class="btn btn-sm btn-danger delete-document-btn" data-request-id="${requestId}" data-file-path="${response.document.path}" title="Elimina documento"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>`;
                        listContainer.insertAdjacentHTML('beforeend', newFileHtml);
                        form.reset();
                    } else {
                        let errorMessage = response.message || 'Si è verificato un errore.';
                        if (response.errors && response.errors.document) {
                            errorMessage += '<br>' + response.errors.document.join('<br>');
                        }
                        feedbackEl.innerHTML = `<span class="text-danger">${errorMessage}</span>`;
                    }
                };

                xhr.onerror = function() {
                    progressContainer.style.display = 'none';
                    feedbackEl.innerHTML = '<span class="text-danger">Errore di rete durante l\'upload.</span>';
                };

                xhr.send(formData);
            }

            function handleDeleteDocument(requestId, filePath, button) {
                Swal.fire({ title: 'Sei sicuro?', text: "Il file verrà eliminato definitivamente!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#c8102e', cancelButtonColor: '#6c757d', confirmButtonText: 'Sì, elimina!', cancelButtonText: 'Annulla' }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`{{ url('/servizi/delete-uploaded-document') }}/${requestId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({ file_path: filePath }) })
                            .then(response => response.ok ? response.json() : response.json().then(err => { throw err; }))
                            .then(data => { Swal.fire({ icon: 'success', title: 'Eliminato!', text: data.message, confirmButtonColor: '#c8102e', timer: 1500 }); button.closest('.list-group-item').remove(); })
                            .catch(error => Swal.fire({ icon: 'error', title: 'Errore!', text: error.message || 'Si è verificato un errore durante l\'eliminazione.', confirmButtonColor: '#c8102e' }));
                    }
                });
            }

            function handleResubmitRequest(requestId) {
                Swal.fire({ title: 'Confermi l\'invio?', text: "La pratica verrà inviata al funzionario per la revisione.", icon: 'question', showCancelButton: true, confirmButtonColor: '#c8102e', cancelButtonColor: '#6c757d', confirmButtonText: 'Sì, invia!', cancelButtonText: 'Annulla' }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({ title: 'Invio in corso...', text: 'Attendere prego', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
                        fetch(`{{ url('/servizi/resubmit-request') }}/${requestId}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } })
                            .then(response => { Swal.close(); return response.ok ? response.json() : response.json().then(err => { throw err; }); })
                            .then(data => { Swal.fire({ icon: 'success', title: 'Inviata!', text: data.message, confirmButtonColor: '#c8102e' }).then(() => { serviceModal.hide(); location.reload(); }); })
                            .catch(error => Swal.fire({ icon: 'error', title: 'Errore!', text: error.message || 'Si è verificato un errore.', confirmButtonColor: '#c8102e' }));
                    }
                });
            }
        }
    });
</script>
@endpush