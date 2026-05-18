@extends('layouts.app')

@section('title', config('app.name', 'FILLEA CGIL'))
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container">
    <div class="row justify-content-center">
        @auth
            <div class="col-md-12 mb-4"> {{-- Sezione News --}}
                <div class="card">
                    {{-- Header della card, cliccabile per espandere/comprimere --}}
                    <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#newsCollapse" aria-expanded="false" aria-controls="newsCollapse">
                        <h5 class="mb-0"><i class="bi bi-newspaper me-2"></i>{{ __('News e Aggiornamenti') }}</h5>
                        @if (isset($newsItems) && !$newsItems->isEmpty())
                            <span class="badge bg-danger rounded-pill">{{ $newsItems->count() }}</span>
                        @endif
                    </div>

                    {{-- Contenuto della card, inizialmente nascosto --}}
                    <div id="newsCollapse" class="collapse">
                        <div class="card-body">
                            @forelse ($newsItems ?? [] as $news)
                                <div class="news-item mb-4 pb-3 @if(!$loop->last) border-bottom @endif">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 fw-bold">{{ $news->title }}</h6>
                                        <small class="text-muted flex-shrink-0 ps-2">{{ $news->created_at->format('d/m/Y') }}</small>
                                    </div>
                                    <div class="news-content mt-2 small">
                                        {!! $news->content !!}
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-muted mb-0">Non ci sono news al momento.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mb-5"> {{-- La card delle richieste di servizio --}}
                <div class="card">
                    {{-- Header della card, cliccabile per espandere/comprimere --}}
                    <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#serviceRequestsCollapse" aria-expanded="false" aria-controls="serviceRequestsCollapse">
                        <h5 class="mb-0">{{ __('Le tue richieste di servizio') }}</h5>
                        @if (!$serviceRequests->isEmpty())
                            <span class="badge bg-primary rounded-pill">{{ $serviceRequests->count() }}</span>
                        @endif
                    </div>

                    {{-- Contenuto della card, inizialmente nascosto --}}
                    <div id="serviceRequestsCollapse" class="collapse">
                        {{-- Sezione visibile solo agli utenti autenticati per le loro richieste di servizio --}}
                        <div class="card-body">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                            @if ($serviceRequests->isEmpty())
                                <p>Non hai richieste di servizio attive.</p>
                            @else
                                <p class="text-muted text-center mb-2 small"><em>Clicca su una richiesta per visualizzarne i dettagli.</em></p>
                            <div class="list-group">
                                @foreach ($serviceRequests as $request)
                                    <a href="{{ $request->detail_url ?? '#' }}" class="list-group-item list-group-item-action flex-column align-items-start mb-3 text-dark text-decoration-none position-relative">
                                        <div class="pe-4">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1">{{ $request->service_name }} ({{ $request->service_type }})</h5>
                                                <small class="text-muted flex-shrink-0 ps-2">{{ $request->updated_at->format('d/m/Y H:i') }}</small>
                                            </div>
                                            <p class="mb-1">Stato: <strong>{{ $request->status }}</strong></p>
                                            @if ($request->admin_notes)
                                                <div class="alert alert-info mt-2" role="alert">
                                                    <strong>Note del funzionario:</strong>
                                                    <p class="mb-0">{!! nl2br(e($request->admin_notes)) !!}</p>
                                                </div>
                                            @endif
                                        </div>
                                        <i class="bi bi-chevron-right position-absolute top-50 end-0 translate-middle-y me-3 text-secondary fs-4"></i>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
        @endauth

        {{-- Sezione pubblica con i bottoni dei servizi --}}
        <div class="col-md-12">
            <div class="text-center mb-5 page-title">
                <h1>LazioAPP</h1>
                <p class="lead">Seleziona il servizio di tuo interesse.</p>
            </div>

            <div class="row g-4">
                <!-- Sezione CASSA EDILE / EDILCASSA (Verde) -->
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('servizi.index') }}" class="feature-box">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-building fs-1 mb-3"></i>
                                <h5 class="card-title">CASSA EDILE / EDILCASSA</h5>
                                <p class="card-text">Prestazioni, link e informazioni utili.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Sezione PRESTAZIONI SANEDIL (Rosa) -->
                <div class="col-md-6 col-lg-4">
                    <a href="#" class="feature-box" data-bs-toggle="modal" data-bs-target="#sanedilModal">
                        <div class="card bg-pink text-white h-100" style="cursor: pointer;">
                            <div class="card-body text-center">
                                <i class="bi bi-heart-pulse fs-1 mb-3"></i>
                                <h5 class="card-title">PRESTAZIONI SANEDIL</h5>
                                <p class="card-text">Scopri le prestazioni del fondo sanitario.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Sezione FONDI PENSIONISTICI (Blu) -->
                <div class="col-md-6 col-lg-4">
                    <a href="#" class="feature-box" data-bs-toggle="modal" data-bs-target="#fondiPensionisticiModal">
                        <div class="card bg-primary text-white h-100" style="cursor: pointer;">
                            <div class="card-body text-center">
                                <i class="bi bi-piggy-bank fs-1 mb-3"></i>
                                <h5 class="card-title">FONDI PENSIONISTICI</h5>
                                <p class="card-text">PREVEDI, FONDAPI e altro.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Sezione SERVIZI CGIL (Rosso) -->
                <div class="col-md-6 col-lg-4">
                    <a href="#" class="feature-box" data-bs-toggle="modal" data-bs-target="#serviziCgilModal">
                        <div class="card bg-danger text-white h-100" style="cursor: pointer;">
                            <div class="card-body text-center">
                                <i class="bi bi-shield-check fs-1 mb-3"></i>
                                <h5 class="card-title">SERVIZI CGIL</h5>
                                <p class="card-text">Assistenza fiscale, patronato e vertenze.</p>
                            </div>
                        </div>
                    </a>
                </div>
                
                <!-- Sezione HAI PERSO IL LAVORO? (Rosso) -->
                <div class="col-md-6 col-lg-4">
                    <a href="#" class="feature-box" data-bs-toggle="modal" data-bs-target="#uvlModal">
                        <div class="card bg-danger text-white h-100" style="cursor: pointer;">
                            <div class="card-body text-center">
                                <i class="bi bi-question-circle fs-1 mb-3"></i>
                                <h5 class="card-title">HAI PERSO IL LAVORO?</h5>
                                <p class="card-text">Informazioni e supporto.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Sezione CONTATTI (Giallo) -->
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('contatti.index') }}" class="feature-box">
                        <div class="card bg-warning text-dark h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-telephone-fill fs-1 mb-3"></i>
                                <h5 class="card-title">CONTATTI -  
                                FILLEA SUL TERRITORIO</h5>
                                <p class="card-text">Contattaci per ogni esigenza.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Altri link principali -->
            <div class="row justify-content-center mt-5 gy-3">
                <div class="col-md-4">
                    @auth
                        @php
                            // Cerca una richiesta di "Analisi Busta Paga" per l'utente loggato.
                            $bustaPagaRequest = $serviceRequests->firstWhere('service_name', 'Analisi Busta Paga');
                        @endphp

                        @if ($bustaPagaRequest)
                            @php
                                // Imposta classe e testo del badge in base allo stato della richiesta.
                                $statusClass = 'bg-secondary'; // default
                                $statusText = $bustaPagaRequest->status;

                                switch ($bustaPagaRequest->status) {
                                    case 'Richiesta integrazione':
                                        $statusClass = 'bg-warning text-dark';
                                        $statusText = 'Azione richiesta';
                                        break;
                                    case 'Inviata':
                                        $statusClass = 'bg-info';
                                        break;
                                    case 'In attesa documenti':
                                        $statusClass = 'bg-primary';
                                        $statusText = 'In revisione';
                                        break;
                                    case 'Conclusa':
                                        $statusClass = 'bg-success';
                                        break;
                                    case 'Rifiutata':
                                        $statusClass = 'bg-danger';
                                        break;
                                }
                            @endphp
                            {{-- Pulsante con stato per utente loggato con richiesta esistente --}}
                            <a href="#" class="btn btn-light w-100 py-3 fw-bold position-relative" data-bs-toggle="modal" data-bs-target="#uvlModal" data-source="busta-paga">
                                ANALISI BUSTA PAGA
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill {{ $statusClass }}">{{ $statusText }}</span>
                            </a>
                        @else
                            {{-- Pulsante di default per utente loggato senza richiesta --}}
                            <a href="#" class="btn btn-info w-100 py-3 fw-bold text-white" data-bs-toggle="modal" data-bs-target="#uvlModal" data-source="busta-paga">INVIACI LA TUA BUSTA PAGA</a>
                        @endif
                    @else
                        {{-- Pulsante di default per utente non loggato --}}
                        <a href="#" class="btn btn-info w-100 py-3 fw-bold text-white" data-bs-toggle="modal" data-bs-target="#uvlModal" data-source="busta-paga">INVIACI LA TUA BUSTA PAGA</a>
                    @endauth
                </div>
                <div class="col-md-4">
                    <a href="https://www.costruire.net" target="_blank" class="btn btn-light w-100 py-3 fw-bold">COSTRUIRE.NET</a>
                </div>
                <div class="col-md-4">
                    <a href="#" id="contracts-link" class="btn btn-light w-100 py-3 fw-bold">CONTRATTI E TABELLE PAGA</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale Fondi Pensionistici -->
    <div class="modal fade" id="fondiPensionisticiModal" tabindex="-1" aria-labelledby="fondiPensionisticiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="fondiPensionisticiModalLabel"><i class="bi bi-piggy-bank me-2"></i>Fondi Pensionistici</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-grid gap-3 d-md-flex justify-content-md-center mb-4">
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#prevediCollapse" aria-expanded="false" aria-controls="prevediCollapse">PREVEDI</button>
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#fondapiCollapse" aria-expanded="false" aria-controls="fondapiCollapse">FONDAPI</button>
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#previdenzaCooperativaCollapse" aria-expanded="false" aria-controls="previdenzaCooperativaCollapse">PREVIDENZA COOPERATIVA</button>
                    </div>

                    <div class="accordion" id="fondiPensionisticiAccordion">
                        <!-- Cos'è Prevedi -->
                        <div class="accordion-item border-0">
                            <div id="prevediCollapse" class="accordion-collapse collapse" data-bs-parent="#fondiPensionisticiAccordion">
                                <div class="accordion-body p-0">
                                    <hr>
                                    <h4 class="text-primary mb-3">PREVEDI</h4>
                                    <p>Ogni lavoratore edile soggetto al <strong>Contratto nazionale Edili-industria</strong> o <strong>Edili-artigianato</strong> riceve, dal suo datore di lavoro, un accantonamento mensile gratuito nel Fondo Prevedi, per alimentare una liquidazione integrativa a favore del lavoratore.</p>
                                    <p>Questo accantonamento gratuito si chiama "contributo contrattuale".</p>
                                    <p>Il lavoratore, inoltre, può ricevere dall'azienda l'1% in più della retribuzione mensile, in aggiunta al contributo contrattuale, per aumentare la sua posizione previdenziale integrativa nel Fondo Prevedi: per ricevere dal datore di lavoro questo contributo aggiuntivo dall'azienda il lavoratore deve inviare a Prevedi il <strong>modulo di integrazione/variazione contributiva</strong>, barrando la lettera A. modulo di integrazione contributiva.</p>
                                    <p>Le contribuzioni versate a Prevedi vengono dedotte dalle imposte sui redditi fino a <strong>5.164,57 euro annui</strong>, con conseguente risparmio fiscale per il lavoratore.</p>
                                    <p>Il funzionamento del Fondo Prevedi è regolato dal D.Lgs. n. 252 del 5 dicembre 2005 ("Disciplina delle forme pensionistiche complementari") ed è sottoposto al controllo della <strong>Commissione di Vigilanza sui Fondi Pensione (COVIP)</strong>.</p>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('contatti.index') }}" class="btn btn-outline-success"><i class="bi bi-telephone-fill me-1"></i> Contattaci per info</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FONDAPI -->
                        <div class="accordion-item border-0">
                            <div id="fondapiCollapse" class="accordion-collapse collapse" data-bs-parent="#fondiPensionisticiAccordion">
                                <div class="accordion-body p-0">
                                    <hr>
                                    <h4 class="text-primary mb-3">FONDAPI</h4>
                                    <p>Il fondo per i lavoratori delle piccole e medie imprese.</p>
                                    <p>Da oltre vent'anni <strong>Fondapi</strong> è il Fondo Pensione Complementare negoziale di categoria destinato ai lavoratori e alle piccole-medie imprese; ente non-profit pensato per offrirti una sicurezza economica nel futuro.</p>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('contatti.index') }}" class="btn btn-outline-success"><i class="bi bi-telephone-fill me-1"></i> Contattaci per info</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PREVIDENZA COOPERATIVA -->
                        <div class="accordion-item border-0">
                            <div id="previdenzaCooperativaCollapse" class="accordion-collapse collapse" data-bs-parent="#fondiPensionisticiAccordion">
                                <div class="accordion-body p-0">
                                    <hr>
                                    <h4 class="text-primary mb-3">PREVIDENZA COOPERATIVA</h4>
                                    <p>Siamo il fondo pensione delle lavoratrici e dei lavoratori delle cooperative e, attraverso i nostri servizi, tutti i soci e i dipendenti di ogni livello possono realizzare concretamente il proprio diritto costituzionale alla previdenza complementare.</p>
                                    <p>Forniamo un fondamentale servizio di welfare contrattuale alle cooperative e sostegno economico ai dipendenti, ai soci e alle loro famiglie, riuscendo a offrire loro condizioni agevolate, grazie alla forza negoziale della nostra unione.</p>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('contatti.index') }}" class="btn btn-outline-success"><i class="bi bi-telephone-fill me-1"></i> Contattaci per info</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale Prestazioni Sanedil -->
    <div class="modal fade" id="sanedilModal" tabindex="-1" aria-labelledby="sanedilModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-pink text-white">
                    <h5 class="modal-title" id="sanedilModalLabel"><i class="bi bi-heart-pulse me-2"></i>Prestazioni Sanedil</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Per conoscere nel dettaglio le prestazioni offerte, consulta la guida ufficiale del fondo sanitario.</p>

                    <div class="text-center my-3">
                        <a href="https://www.fondosanedil.it/wp-content/uploads/2025/12/Sanedil-Guida-al-Piano-Sanitario-UniSalute-2026.pdf" target="_blank" class="btn btn-primary">
                            <i class="bi bi-file-earmark-pdf-fill me-2"></i>
                            Sanedil-Guida-al-Piano-Sanitario-UniSalute-2026.pdf
                        </a>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Per le prestazioni del piano sanitario vedi pag 4-5.
                    </div>
                    <hr>
                    <div class="text-center mt-3">
                        <p class="mb-2">Per maggiori informazioni o assistenza:</p>
                        <a href="{{ route('contatti.index') }}" class="btn btn-success"><i class="bi bi-telephone-fill me-1"></i> Contattaci per info</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale Servizi CGIL -->
    <div class="modal fade" id="serviziCgilModal" tabindex="-1" aria-labelledby="serviziCgilModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="serviziCgilModalLabel"><i class="bi bi-shield-check me-2"></i>Servizi CGIL</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-grid gap-3 d-md-flex justify-content-md-center mb-4">
                        <button class="btn btn-danger" type="button" data-bs-toggle="collapse" data-bs-target="#caafCgilCollapse" aria-expanded="false" aria-controls="caafCgilCollapse">CAAF CGIL</button>
                        <button class="btn btn-danger" type="button" data-bs-toggle="collapse" data-bs-target="#patronatoIncaCollapse" aria-expanded="false" aria-controls="patronatoIncaCollapse">PATRONATO INCA</button>
                        <button class="btn btn-danger" type="button" data-bs-toggle="collapse" data-bs-target="#uffVertenzeCollapse" aria-expanded="false" aria-controls="uffVertenzeCollapse">UFF. VERTENZE</button>
                    </div>

                    <div class="accordion" id="cgilServicesAccordion">
                        <!-- CAAF CGIL -->
                        <div class="accordion-item border-0">
                            <div id="caafCgilCollapse" class="accordion-collapse collapse" data-bs-parent="#cgilServicesAccordion">
                                <div class="accordion-body p-0">
                                    <hr>
                                    <h4 class="text-danger mb-3">CAAF CGIL - ASSISTENZA FISCALE</h4>
                                    <p>Il CAAF offre alle persone fisiche: assistenza alla compilazione annuale della dichiarazione dei redditi (modello 730 o modello REDDITI) o delle dichiarazioni correttive e/o integrative; assistenza in caso di comunicazioni di irregolarità, avvisi di accertamento o di liquidazione, cartelle esattoriali calcolo delle imposte comunali sugli immobili e servizi (IMU/TASI); assistenza ai titolari di partiva IVA che non si avvalgono di dipendenti; compilazione e trasmissione dei modelli reddituali finalizzati all’ottenimento di prestazioni sociali legate al reddito (modelli RED-INPS); compilazione dei modelli dichiarativi relativi a indennità di frequenza, pensione o assegno sociale; compilazione della dichiarazione sostitutiva unica (DSU) al fine di ottenere l’ISEE ordinario, Minorenni, Socio sanitario, Socio sanitario residenziale o Universitario; compilazione della domanda di assegno maternità o assegno ai nucleo con tre figli minori, di esenzione o riduzione delle tariffe comunali; assistenza alla compilazione della dichiarazione di successione e delle volture catastali; assistenza negli adempimenti previsti dal contratto di lavoro per assistenti familiari.</p>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('contatti.index') }}" class="btn btn-outline-success"><i class="bi bi-telephone-fill me-1"></i> CONTATTACI PER ASSISTENZA FISCALE</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PATRONATO INCA CGIL -->
                        <div class="accordion-item border-0">
                            <div id="patronatoIncaCollapse" class="accordion-collapse collapse" data-bs-parent="#cgilServicesAccordion">
                                <div class="accordion-body p-0">
                                    <hr>
                                    <h4 class="text-danger mb-3">PATRONATO INCA CGIL</h4>
                                    <p>INCA CGIL è il Patronato promosso dalla CGIL che assiste e tutela lavoratrici e lavoratori, pensionate e pensionati, cittadine e cittadini in Italia e all'estero, rendendo esigibili i loro diritti. Grazie a una straordinaria rete di prossimità, con circa 900 sedi in Italia e all’estero, le sindacaliste ed i sindacalisti della tutela individuale di INCA CGIL ti accompagnano nelle pratiche che riguardano:</p>
                                    <ul>
                                        <li>pensioni e previdenza</li>
                                        <li>disoccupazione e sostegno al reddito</li>
                                        <li>danni da lavoro (infortuni e malattie professionali)</li>
                                        <li>genitorialità</li>
                                        <li>malattia</li>
                                        <li>disabilità</li>
                                        <li>migranti in Italia</li>
                                        <li>italiane e italiani all’estero</li>
                                    </ul>
                                    <p>L’INCA CGIL è da sempre il primo patronato in Italia e all’estero per volume di attività: ogni anno assiste oltre 5 milioni di persone in Italia e più di 600.000 connazionali residenti all’estero.</p>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('contatti.index') }}" class="btn btn-outline-success"><i class="bi bi-telephone-fill me-1"></i> CONTATTACI PER IL PATRONATO INCA</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- UFF. VERTENZE CGIL FILLEA -->
                        <div class="accordion-item border-0">
                            <div id="uffVertenzeCollapse" class="accordion-collapse collapse" data-bs-parent="#cgilServicesAccordion">
                                <div class="accordion-body p-0">
                                    <hr>
                                    <h4 class="text-danger mb-3">UFF. VERTENZE CGIL FILLEA</h4>
                                    <p>La CGIL offre ai propri associati una serie di tutele collettive ed individuali, riguardanti lo svolgimento del rapporto di lavoro subordinato o atipico (collaborazioni, lavoro in somministrazione, associati in partecipazione). L’obiettivo che la CGIL intende perseguire è fornire al lavoratore e alla lavoratrice informazione adeguata e trasparente delle regole che determinano il rapporto che si instaura tra CGIL, Ufficio vertenze e legale di riferimento. Per specifiche norme di legge il servizio è rivolto esclusivamente agli iscritti alla CGIL. Qualora un lavoratore non sia iscritto ma intenda utilizzare questo servizio deve regolarizzare la propria posizione iscrivendosi prima dell’apertura della pratica.</p>
                                    <p>Nell’ambito delle tutela vertenziale e legale, la CGIL si avvale del lavoro svolto attraverso interventi di propri funzionari esperti e di avvocati. I servizi offerti dai nostri Ufficio vertenze e legale riguardano licenziamenti individuali, contestazioni – provvedimenti disciplinari, lavoro nero e irregolare, controllo contratto, busta paga e Tfr, recupero crediti da lavoro, trasferimenti individuali, modifica mansioni, fallimenti e procedure concorsuali, consulenza legale, infortuni, mobbing e integrità psico-fisica.</p>
                                    <div class="text-center mt-4 p-3 bg-light rounded">
                                        <h6 class="fw-bold">CHIAMACI O SCRIVICI</h6>
                                        <a href="tel:0646206631" class="btn btn-outline-success me-2 my-1"><i class="bi bi-telephone-fill me-1"></i> 06 46206631</a>
                                        <a href="tel:0646206689" class="btn btn-outline-success me-2 my-1"><i class="bi bi-telephone-fill me-1"></i> 06 46206689</a>
                                        <a href="mailto:uffvertfilleacgilrmlz@pec.it" class="btn btn-outline-primary my-1"><i class="bi bi-envelope-fill me-1"></i> uffvertfilleacgilrmlz@pec.it</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale UVL - Hai perso il lavoro? -->
    <div class="modal fade" id="uvlModal" tabindex="-1" aria-labelledby="uvlModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="uvlModalLabel"><i class="bi bi-question-circle me-2"></i>Hai perso il lavoro?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Sezione per l'upload della busta paga, visibile solo se si clicca sul bottone apposito --}}
                    <div id="bustaPagaUploadSection" style="display: none;">
                        <h4 class="text-center text-info">Analisi Busta Paga</h4>

                        @guest
                            <div class="alert alert-warning text-center">
                                <i class="bi bi-lock-fill me-2"></i>
                                Per inviare la tua busta paga, devi prima <a href="{{ route('login') }}">accedere</a> o <a href="{{ route('register') }}">registrarti</a>.
                                <br><br>
                                In alternativa, contatta i nostri uffici per ricevere assistenza.
                            </div>
                        @endguest

                        @auth
                            @if(auth()->user()->is_consultant)
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    Come consulente, puoi visualizzare le informazioni ma non puoi inviare richieste di analisi.
                                </div>
                            @else
                                @php
                                    // Cerca una richiesta specifica per 'Analisi Busta Paga'
                                    $bustaPagaRequest = $serviceRequests->firstWhere('service_name', 'Analisi Busta Paga');
                                @endphp

                                @if ($bustaPagaRequest && $bustaPagaRequest->status === 'Richiesta integrazione')
                                    {{-- L'utente è autorizzato a caricare, mostra il form attivo --}}
                                    <p class="text-center">Usa questo modulo per inviarci la tua busta paga per un'analisi. Un nostro funzionario la esaminerà e ti contatterà al più presto.</p>
                                    <form id="bustaPagaForm" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="bustaPagaFile" class="form-label">Carica la tua busta paga (PDF, JPG, PNG - max 5MB)</label>
                                            <input class="form-control" type="file" id="bustaPagaFile" name="busta_paga_file" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="bustaPagaNotes" class="form-label">Note aggiuntive (opzionale)</label>
                                            <textarea class="form-control" id="bustaPagaNotes" name="notes" rows="3" placeholder="Scrivi qui eventuali dubbi o domande sulla tua busta paga..."></textarea>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">Invia per analisi</button>
                                        </div>
                                    </form>
                                @elseif ($bustaPagaRequest)
                                    {{-- Esiste già una richiesta ma non è in stato di integrazione, quindi mostra lo stato --}}
                                    <div class="alert alert-info text-center">
                                        <p class="mb-1">Hai già una richiesta di "Analisi Busta Paga" in corso.</p>
                                        <p class="mb-1">Stato attuale: <strong>{{ $bustaPagaRequest->status }}</strong></p>
                                        <small class="text-muted">Ultimo aggiornamento: {{ $bustaPagaRequest->updated_at->format('d/m/Y H:i') }}</small>
                                        @if ($bustaPagaRequest->admin_notes)
                                            <div class="alert alert-light mt-2 text-start">
                                                <strong>Note del funzionario:</strong>
                                                <p class="mb-0">{!! nl2br(e($bustaPagaRequest->admin_notes)) !!}</p>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    {{-- Nessuna richiesta esistente, l'utente deve contattare l'admin. Mostra il form disabilitato. --}}
                                    <form id="requestBustaPagaForm">
                                        <div class="alert alert-info text-center">
                                            <p>Non hai una richiesta di analisi busta paga attiva.</p>
                                            <p>Clicca il pulsante qui sotto per inviare una richiesta di analisi. Un funzionario la prenderà in carico e abiliterà l'upload dei documenti necessari.</p>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-send-check me-2"></i>
                                                Richiedi analisi busta paga
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            @endif
                        @endauth
                        <hr class="my-4">
                    </div>

                    <p class="lead">L’<strong>Ufficio Vertenze Legali (UVL)</strong> della Fillea Cgil ti fornisce informazioni e assistenza su:</p>
                    <ul>
                        <li>Dimissioni telematiche e risoluzioni consensuali del rapporto di lavoro;</li>
                        <li>Verifica buste paga e TFR;</li>
                        <li>Verifica corretto inquadramento contrattuale;</li>
                        <li>Verifica versamenti Cassa Edile ed Edilcassa;</li>
                        <li>Consulenza legale e contrattuale.</li>
                    </ul>
                    <hr>
                    <p class="lead">Ti assiste, anche in sede legale, per ogni tipo di vertenza:</p>
                    <ul>
                        <li>Impugnativa risoluzione del rapporto di lavoro, mancata assunzione e messa a disposizione energie lavorative;</li>
                        <li>Gestione contestazioni disciplinari;</li>
                        <li>Lavoro nero;</li>
                        <li>Lavoro autonomo e parasubordinato;</li>
                        <li>Recupero stipendi arretrati;</li>
                        <li>Recupero mancati versamenti al fondo di previdenza complementare.</li>
                    </ul>
                    <hr>
                    <div class="text-center mt-4 p-3 bg-light rounded">
                        <h6 class="fw-bold">CHIAMACI O SCRIVICI</h6>
                        <a href="tel:0646206631" class="btn btn-outline-success me-2 my-1"><i class="bi bi-telephone-fill me-1"></i> 06 46206631</a>
                        <a href="tel:0646206689" class="btn btn-outline-success me-2 my-1"><i class="bi bi-telephone-fill me-1"></i> 06 46206689</a>
                        <a href="mailto:uffvertfilleacgilrmlz@pec.it" class="btn btn-outline-primary my-1"><i class="bi bi-envelope-fill me-1"></i> uffvertfilleacgilrmlz@pec.it</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modale per messaggi di successo/errore --}}
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

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function showModal(title, message) {
            const modal = new bootstrap.Modal(document.getElementById('responseModal'));
            document.getElementById('responseModalLabel').textContent = title;
            document.getElementById('responseModalBody').innerHTML = message;
            modal.show();
        }

        // Resetta lo stato della modale Servizi CGIL quando viene chiusa
        const serviziCgilModalEl = document.getElementById('serviziCgilModal');
        if (serviziCgilModalEl) {
            serviziCgilModalEl.addEventListener('hidden.bs.modal', function (event) {
                // Trova tutti gli elementi collassabili all'interno di questa modale che sono attualmente aperti
                const collapseElements = serviziCgilModalEl.querySelectorAll('.accordion-collapse.collapse.show');
                
                // Nascondi ogni elemento che è attualmente visibile usando l'API di Bootstrap
                collapseElements.forEach(function(el) {
                    const collapseInstance = bootstrap.Collapse.getInstance(el) || new bootstrap.Collapse(el);
                    collapseInstance.hide();
                });
            });
        }

        // Resetta lo stato della modale Fondi Pensionistici quando viene chiusa
        const fondiPensionisticiModalEl = document.getElementById('fondiPensionisticiModal');
        if (fondiPensionisticiModalEl) {
            fondiPensionisticiModalEl.addEventListener('hidden.bs.modal', function (event) {
                // Trova tutti gli elementi collassabili all'interno di questa modale che sono attualmente aperti
                const collapseElements = fondiPensionisticiModalEl.querySelectorAll('.accordion-collapse.collapse.show');
                
                // Nascondi ogni elemento che è attualmente visibile usando l'API di Bootstrap
                collapseElements.forEach(function(el) {
                    const collapseInstance = bootstrap.Collapse.getInstance(el) || new bootstrap.Collapse(el);
                    collapseInstance.hide();
                });
            });
        }

        // Logica per la modale UVL e l'upload della busta paga
        const uvlModalEl = document.getElementById('uvlModal');
        if (uvlModalEl) {
            const bustaPagaUploadSection = document.getElementById('bustaPagaUploadSection');

            uvlModalEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button && button.dataset.source === 'busta-paga') {
                    bustaPagaUploadSection.style.display = 'block';
                }
            });

            uvlModalEl.addEventListener('hidden.bs.modal', function (event) {
                bustaPagaUploadSection.style.display = 'none';
                const bustaPagaForm = document.getElementById('bustaPagaForm');
                if (bustaPagaForm) {
                    bustaPagaForm.reset();
                }
            });
        }

        @auth
        const bustaPagaForm = document.getElementById('bustaPagaForm');
        if (bustaPagaForm) {
            bustaPagaForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const fileInput = document.getElementById('bustaPagaFile');

                if (fileInput.files.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'File Mancante',
                        text: 'Per favore, seleziona un file da caricare.',
                        confirmButtonColor: '#c8102e'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Invio in corso...',
                    text: 'Attendere prego',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading() }
                });

                fetch('{{ route("servizi.send-busta-paga") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Inviata!',
                            text: data.message,
                            confirmButtonColor: '#c8102e'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Errore!',
                            html: data.message || 'Si è verificato un errore.',
                            confirmButtonColor: '#c8102e'
                        });
                    }
                })
                .catch(error => {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore!',
                        html: error.message || 'Si è verificato un problema di comunicazione.',
                        confirmButtonColor: '#c8102e'
                    });
                });
            });
        }

        // Gestione per la richiesta iniziale di analisi busta paga (senza file)
        const requestBustaPagaForm = document.getElementById('requestBustaPagaForm');
        if(requestBustaPagaForm) {
            requestBustaPagaForm.addEventListener('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Confermi la richiesta?',
                    text: "Stai per inviare una richiesta per l'analisi della busta paga. Un funzionario la esaminerà e abiliterà l'upload.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#c8102e',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sì, invia richiesta!',
                    cancelButtonText: 'Annulla'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Invio in corso...',
                            text: 'Attendere prego',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading() }
                        });

                        fetch('{{ route("servizi.request-busta-paga-analysis") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            }
                        })
                        .then(response => response.ok ? response.json() : response.json().then(err => { throw err; }))
                        .then(data => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Richiesta Inviata!',
                                text: data.message,
                                confirmButtonColor: '#c8102e'
                            }).then(() => {
                                location.reload();
                            });
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Errore!',
                                html: error.message || 'Si è verificato un problema durante l\'invio della richiesta.',
                                confirmButtonColor: '#c8102e'
                            });
                        });
                    }
                });
            });
        }
        @endauth

        // Gestione click su "Contratti e Tabelle Paga"
        const contractsLink = document.getElementById('contracts-link');
        if (contractsLink) {
            contractsLink.addEventListener('click', function(e) {
                e.preventDefault();
                const contractsUrl = 'https://www.costruire.net/?page_id=830';

                @guest
                    Swal.fire({
                        title: 'Accesso Richiesto',
                        text: "Per accedere a questa sezione è necessario essere registrati come Consulente/Azienda.",
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Accedi',
                        cancelButtonText: 'Registrati',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route('login') }}';
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            window.location.href = '{{ route('register') }}?from=contracts';
                        }
                    });
                @endguest

                @auth
                    const isConsultant = {{ auth()->user()->is_consultant ? 'true' : 'false' }};
                    if (isConsultant) {
                        window.open(contractsUrl, '_blank');
                    } else {
                        Swal.fire({ title: 'Accesso non consentito', text: 'Questa sezione è riservata ai soli Consulenti/Aziende.', icon: 'warning', confirmButtonColor: '#c8102e' });
                    }
                @endauth
            });
        }
    });
</script>
@endpush
