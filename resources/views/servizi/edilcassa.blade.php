@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="page-title text-center mb-5">
                <h1>Elenco Prestazioni Edilcassa</h1>
                <p class="lead">Prestazioni OPERAI della edilcassa del lazio</p>
            </div>

            <div class="row g-4 justify-content-center">
                @foreach ($prestazioniEdilcassa as $prestazione)
                    <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                        <div class="flip-card w-100" tabindex="0">
                            <div class="flip-card-inner">
                                <div class="flip-card-front">
                                    <div class="card h-100 w-100 d-flex flex-column">
                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                            <i class="bi {{ $prestazione['icona'] }} fs-1 mb-3 text-success"></i>
                                            <h6 class="card-title mb-0">{{ $prestazione['nome'] }}</h6>
                                        </div>
                                        <div class="card-footer bg-transparent border-0 d-flex justify-content-center pb-3">
                                            <a href="#" class="btn btn-sm btn-primary disabled" title="Guida alla presentazione non disponibile">
                                                <i class="bi bi-book"></i>  Guida alla presentazione
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="flip-card-back">
                                    <div class="card bg-dark text-white h-100 w-100">
                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-3">
                                            <p class="small">{{ $prestazione['descrizione'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Service Guide Modal (Copied from cassa-edile.blade.php) -->
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
                    <div id="serviceModalDocsContainer" class="mt-4 border-top pt-3">
                        {{-- I campi per i documenti verranno iniettati qui dal JS --}}
                    </div>
                  </div>
                  <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <a href="#" id="modalProceedBtn" class="btn btn-success">Procedi con la presentazione</a>
                  </div>
                </div>
              </div>
            </div>

            <div class="text-center mt-5">
                <a href="{{ route('servizi.index') }}" class="btn btn-light mt-4"><i class="bi bi-arrow-left"></i> Torna alla selezione</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const serviceModalEl = document.getElementById('serviceModal');
        if (serviceModalEl) {
            const serviceModal = new bootstrap.Modal(serviceModalEl);
            const modalTitleEl = document.getElementById('serviceModalLabel');
            const modalDescriptionEl = document.getElementById('serviceModalDescription');
            const modalDocsContainerEl = document.getElementById('modalServiceDocsContainer'); // Changed ID to avoid conflict if both pages are loaded
            const modalProceedBtn = document.getElementById('modalProceedBtn');

            // Funzione per aggiornare lo stato e gli attributi data del pulsante "Procedi"
            function updateProceedButton(serviceTitle, serviceDescription, serviceType, currentStatus) {
                modalProceedBtn.dataset.serviceTitle = serviceTitle;
                modalProceedBtn.dataset.serviceDescription = serviceDescription;
                modalProceedBtn.dataset.serviceType = serviceType;
                modalProceedBtn.dataset.currentStatus = currentStatus;

                const hasActiveRequest = currentStatus && currentStatus !== 'Concluso';
                if (hasActiveRequest) {
                    modalProceedBtn.classList.add('disabled');
                    modalProceedBtn.removeAttribute('href'); // Rimuove href per prevenire la navigazione
                    modalProceedBtn.textContent = `Richiesta ${currentStatus}`;
                    modalProceedBtn.title = `Hai già una richiesta per questo servizio in stato di "${currentStatus}". Non puoi inviare una nuova richiesta finché quella precedente non è conclusa.`;
                } else {
                    modalProceedBtn.classList.remove('disabled');
                    modalProceedBtn.setAttribute('href', '#'); // Ripristina href
                    modalProceedBtn.textContent = 'Procedi con la presentazione';
                    modalProceedBtn.title = ''; // Pulisce il titolo
                }
            }

            document.querySelectorAll('.btn-show-guide').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const serviceTitle = this.dataset.serviceTitle;
                    const serviceDescription = this.dataset.serviceDescription;
                    const serviceType = this.dataset.serviceType;
                    const currentStatus = this.dataset.currentStatus;
                    const requiredDocs = this.dataset.requiredDocs ? JSON.parse(this.dataset.requiredDocs) : [];

                    modalTitleEl.innerHTML = serviceTitle;
                    modalDescriptionEl.innerHTML = serviceDescription;

                    // Popola dinamicamente il contenitore dei documenti
                    modalDocsContainerEl.innerHTML = ''; // Pulisce il contenuto precedente
                    if (requiredDocs.length > 0) {
                        let docsHtml = '<h6 class="text-muted">Documentazione/Dati Richiesti</h6>';
                        requiredDocs.forEach(group => {
                            if (group.description) {
                                docsHtml += `<p class="small fst-italic">${group.description}</p>`;
                            }
                            // Only render inputs if type is not 'info'
                            if (group.type !== 'info') {
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
                            }
                        });
                        modalDocsContainerEl.innerHTML = docsHtml;
                        modalDocsContainerEl.style.display = 'block';
                    } else {
                        modalDocsContainerEl.style.display = 'none';
                    }

                    updateProceedButton(serviceTitle, serviceDescription, serviceType, currentStatus);
                    serviceModal.show();
                });
            });

            modalProceedBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Impedisce l'azione predefinita del link

                @guest
                    Swal.fire({
                        icon: 'warning',
                        title: 'Accesso Richiesto',
                        html: `Per procedere con la richiesta del servizio è necessario effettuare il <a href="{{ route('login') }}">login</a>.`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#c8102e',
                    });
                @else
                    const serviceTitle = this.dataset.serviceTitle;
                    const serviceDescription = this.dataset.serviceDescription;
                    const serviceType = this.dataset.serviceType;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
                        Swal.fire({ icon: 'warning', title: 'Campi Obbligatori', text: 'Per favore, compila tutti i campi contrassegnati con *', confirmButtonColor: '#c8102e' });
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
                                serviceModal.hide(); // Chiude la modale
                                location.reload(); // Ricarica la pagina per aggiornare lo stato del badge
                            })
                            .catch(error => {
                                Swal.fire({ icon: 'error', title: 'Errore!', text: error.message || 'Si è verificato un errore durante l\'invio della richiesta.', confirmButtonColor: '#c8102e' });
                            });
                        }
                    });
                @endguest
            });
        }
    });
</script>
@endpush