<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- PWA & Mobile Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="LazioAPP">
    <meta name="theme-color" content="#dc3545">

    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}?v={{ time() }}">

    <!-- iOS Icons -->
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'FILLEA CGIL'))</title>

    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23c8102e%22></circle><text x=%2250%25%22 y=%2255%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22sans-serif%22 font-size=%2240%22 font-weight=%22bold%22 fill=%22white%22>CGIL</text></svg>">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Qui potrai aggiungere i tuoi stili CSS personalizzati --}}
    <style>
        /* Stile per i box colorati */
        .feature-box {
            text-decoration: none;
            color: white;
            display: block;
            transition: transform 0.2s ease-in-out;
        }
        .feature-box:hover {
            transform: scale(1.05);
            color: white;
        }
        .card-body {
            min-height: 150px; /* Altezza minima per uniformare i box */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        /* Bootstrap non ha un colore 'rosa', usiamo un colore personalizzato o un'alternativa */
        .bg-pink {
            background-color: #e83e8c; /* Esempio di colore rosa */
        }

        /*
         Stili per rendere il footer delle modali "sticky" in fondo.
         Questo assicura che i pulsanti di azione (Chiudi, Procedi, etc.) siano sempre visibili
         anche quando il contenuto della modale è molto lungo e richiede lo scroll.
        */
        .modal-content {
            max-height: 85vh; /* Limita l'altezza della modale per evitare che esca dallo schermo */
        }
        .modal-body {
            overflow-y: auto; /* Abilita lo scroll verticale solo per il corpo della modale */
        }

        body {
            /* Colore di fallback e base per l'overlay */
            background-color: #c8102e; /* Un rosso CGIL/FILLEA */
            /* Immagine di sfondo con overlay scuro per leggibilità */
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1541888946425-d81bb19240f5?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center center;
            background-attachment: fixed; /* L'immagine sta ferma durante lo scroll */
        }

        /* Stile per i titoli delle pagine, per renderli bianchi e leggibili */
        .page-title h1, .page-title .lead {
            color: #ffffff;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }

        /* Stili per l'effetto flip card */
        .flip-card {
            background-color: transparent;
            perspective: 1000px; /* Effetto 3D */
            min-height: 170px; /* Altezza minima per coerenza */
        }

        .flip-card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }

        /* Applica la rotazione quando la classe .is-flipped è presente */
        .flip-card-inner.is-flipped {
            transform: rotateY(180deg);
        }

        .flip-card-front, .flip-card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-backface-visibility: hidden; /* Safari */
            backface-visibility: hidden;
        }

        .flip-card-back {
            transform: rotateY(180deg);
        }

        /* Stili per nascondere il widget e il banner di Google Translate */
        #google_translate_element {
            display: none;
        }
        .goog-te-banner-frame.skiptranslate {
            display: none !important;
        }
        body {
            top: 0px !important; /* Sovrascrive lo stile inline aggiunto da Google Translate per il banner */
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md shadow-sm fixed-top @if(session('admin_user.superadmin')) navbar-dark bg-danger @else navbar-light bg-white @endif">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/home') }}">
                    {{-- SVG Logo FILLEA CGIL --}}

                {{-- SVG Logo FILLEA CGIL Roma e Lazio --}}
                <svg version="1.1" id="logo-fillea-cgil" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                    viewBox="0 0 1400 136.06" style="height: 30px; width: auto;" xml:space="preserve">
                    
                    <!-- Logo e Testo FILLEA (Rosso) -->
                    <g id="Livello_1_copia_2" data-name="Livello 1 copia 2">
                        <rect x="822.16" y="18.43" width="99.21" height="99.21" style="fill: #ec1c24;"></rect>
                        <g>
                            <path d="m848.17,95.44c6.5,5.85,15.1,9.4,24.52,9.4,20.26,0,36.68-16.42,36.68-36.68s-16.42-36.68-36.68-36.68c-1.12,0-2.22.05-3.32.15l6.7,7.04s.01,0,.02,0c14.93,1.53,26.58,14.14,26.58,29.48,0,16.37-13.27,29.63-29.63,29.63-1.38,0-2.73-.09-4.06-.28-14.45-1.98-24.03-10.34-33.94-24.35,0,0-1.34-1.43-.71,1.96,0,0,.89,5.8,11.51,15.53,0,0,0,1.07.54,1.87,0,0,.54.89,1.34,1.25l.45,1.67Z" style="fill: #fff;"></path>
                            <path d="m864.67,94.91c2.66.69,5.41,1.25,8.3,1.25,15.45,0,27.98-12.53,27.98-27.98s-11.33-26.75-25.73-27.89c-.74-.06-5.68,5.83-5.68,5.83.89-.11,1.8-.17,2.72-.17,11.98,0,21.69,9.71,21.69,21.69s-9.71,21.69-21.69,21.69c-5.09,0-9.77-1.75-13.48-4.69-5.01-3.97-9.65-7.47-10.09-7.91,0,0-2.14-.62-2.32-.09-.18.53.36,1.7,1.07,2.32,0,0,2.14,2.55,2.05,2.91-.07.26-2.1-1.18-3.03-2.01l-2.94-3.12s-2.32-1.25-1.43,2.14c0,0,9.28,12.56,22.57,16.04" style="fill: #fff;"></path>
                            <polygon points="842.44 37.47 842.44 70.84 850.28 64.24 850.28 31.22 842.44 37.47" style="fill: #fff;"></polygon>
                            <path d="m852.96,64.77l30.48,18.74s6.42-1.43,8.38-12.85l-38.85-39.09v33.2Z" style="fill: #fff;"></path>
                            <path d="m851.35,66.38l-7.13,6.25,28.51,14.64s5.35.71,8.38-2.32l-29.76-18.56Z" style="fill: #fff;"></path>
                            <polygon points="898.58 95.65 895.37 98.86 903.93 102.97 906.78 100.47 898.58 95.65" style="fill: #fff;"></polygon>
                            <path d="m906.96,85.65s-3.54,6.67-6.77,8.75l8.02,5v-12.85l-1.25-.89Z" style="fill: #fff;"></path>
                        </g>
                    </g>

                    <g id="Livello_1_copia" data-name="Livello 1 copia">
                        <polygon points="16.73 18.43 83.18 18.43 83.18 46.85 53.05 46.85 53.05 57.11 79.49 57.11 79.49 82.9 53.05 82.9 53.05 117.64 16.73 117.64 16.73 18.43" style="fill: #ec1c24;"></polygon>
                        <rect x="96.01" y="18.43" width="36.05" height="99.21" style="fill: #ec1c24;"></rect>
                        <polygon points="149.28 18.43 185.34 18.43 185.34 85.8 215.6 85.8 215.6 117.64 149.28 117.64 149.28 18.43" style="fill: #ec1c24;"></polygon>
                        <polygon points="225.44 18.43 261.49 18.43 261.49 85.8 291.76 85.8 291.76 117.64 225.44 117.64 225.44 18.43" style="fill: #ec1c24;"></polygon>
                        <polygon points="301.35 18.43 368.06 18.43 368.06 45.53 337.67 45.53 337.67 55.01 365.43 55.01 365.43 80.79 337.67 80.79 337.67 90.53 369.12 90.53 369.12 117.64 301.35 117.64 301.35 18.43" style="fill: #ec1c24;"></polygon>
                        <path d="m414.18,117.64h-38.16l34.48-99.21h39.87l35.39,99.21h-38.16l-3.29-12.37h-27.24l-2.89,12.37Zm23.82-35.92l-4.21-17.5c-1.05-4.21-1.71-8.55-2.37-12.76h-1.32l-6.58,30.27h14.47Z" style="fill: #ec1c24;"></path>
                    </g>

                    <!-- Scritta CGIL (Nero) -->
                    <g id="Livello_2" data-name="Livello 2">
                        <path d="m570.92,58.98c-5.61-5.23-12.62-9.69-20.53-9.69-10.46,0-19,8.29-19,18.75s9.05,18.75,19.51,18.75c8.03,0,14.41-3.44,20.02-8.93l-1.02,35.96c-5.61,2.68-18.62,3.83-24.87,3.83-27.42,0-48.84-21.3-48.84-48.71s22.06-50.5,50.24-50.5c7.91,0,16.07,1.4,23.46,3.95l1.02,36.6Z" style="fill: #231f20;"></path>
                        <path d="m685.22,59.49c-.26,15.68-.26,28.82-11.61,41.06-10.2,11.09-25.89,17.09-40.81,17.09-29.33,0-53.05-18.11-53.05-48.97s23.08-50.24,53.3-50.24c16.71,0,39.66,8.29,47.18,24.74l-32.65,11.99c-2.68-4.85-7.65-7.4-13.26-7.4-12.5,0-19.38,10.71-19.38,22.32,0,10.58,6.63,20.53,17.98,20.53,5.48,0,12.5-2.55,14.16-8.42h-15.81v-22.7h53.94Z" style="fill: #231f20;"></path>
                        <rect x="696.71" y="19.96" width="34.94" height="96.15" style="fill: #231f20;"></rect>
                        <polygon points="747.46 19.96 782.4 19.96 782.4 85.25 811.73 85.25 811.73 116.11 747.46 116.11 747.46 19.96" style="fill: #231f20;"></polygon>
                    </g>

                    <!-- TESTO AGGIUNTO E SPAZIATO -->
                    <text x="950" y="88" style="fill: #231f20; font-family: sans-serif; font-size: 70px; font-weight: bold;">Roma e Lazio</text>

                </svg>

                </a>

                {{-- Contattaci button, always visible --}}
                <ul class="navbar-nav flex-row order-md-last"> {{-- flex-row to keep it horizontal, order-md-last to push it right on larger screens --}}
                    <li class="nav-item me-2"> {{-- me-2 for some spacing --}}
                        <a class="btn btn-warning btn-sm" href="{{ route('contatti.index') }}">
                            <i class="bi bi-telephone-fill me-1"></i> Contattaci
                        </a>
                    </li>

                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        {{-- Potremmo aggiungere link qui in futuro --}}
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto align-items-center">
                        {{-- Check if Admin is logged in --}}
                        @if (session()->has('admin_logged_in') && session()->get('admin_logged_in'))
                            <li class="nav-item dropdown">
                                <a id="adminNavbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-person-check-fill"></i> {{ session('admin_user.name') }}
                                    @if(session('admin_user.superadmin'))
                                        <span class="badge bg-warning text-dark ms-1">Super Admin</span>
                                    @endif
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="adminNavbarDropdown">
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                        <i class="bi bi-people me-2"></i>Gestione Utenti
                                    </a>
                                    @if(session('admin_user.superadmin'))
                                        <a class="dropdown-item" href="{{ route('admin.news.index') }}">
                                            <i class="bi bi-newspaper me-2"></i>Gestione News
                                        </a>
                                    @endif
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </a>
                                    <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">@csrf</form>
                                </div>
                            </li>
                        @else
                            {{-- Admin is NOT logged in, check for regular user --}}
                            @guest
                                @if (Route::has('login'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                    </li>
                                @endif

                                @if (Route::has('register'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('register') }}">{{ __('Registrati') }}</a>
                                    </li>
                                @endif

                                <!-- Admin Login Icon (only if no one is logged in) -->
                                <li class="nav-item border-start ms-2 ps-2">
                                    <a class="nav-link" href="{{ route('admin.login') }}" title="Login Admin">
                                        <i class="bi bi-person-fill-gear fs-5"></i>
                                    </a>
                                </li>
                            @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                    @if(Auth::user()->job_title === 'Funzionario Fillea Cgil')
                                        <span class="badge bg-secondary rounded-pill align-middle ms-1">Funzionario</span>
                                    @elseif(Auth::user()->is_consultant)
                                        <span class="badge bg-info text-dark rounded-pill align-middle ms-1">Consulente</span>
                                    @else
                                        <span class="badge bg-success rounded-pill align-middle ms-1">Lavoratore</span>
                                    @endif
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>

                                    {{-- START: Sezione Notifiche Push (temporaneamente disabilitata) --}}
                                    {{-- <div class="dropdown-divider"></div>
                                    <h6 class="dropdown-header">Notifiche Push</h6>
                                    <a class="dropdown-item" href="#" id="enable-push-notifications" style="display: none;">Abilita Notifiche</a>
                                    <a class="dropdown-item" href="#" id="disable-push-notifications" style="display: none;">Disabilita Notifiche</a>
                                    <span class="dropdown-item-text text-muted" id="push-unsupported-message" style="display: none;">Notifiche non supportate</span> --}}
                                    {{-- END: Sezione Notifiche Push (temporaneamente disabilitata) --}}
                                </div>
                            </li>
                            @endguest
                        @endif
                        {{-- The @endif above closes the @if (session()->has('admin_logged_in')) block --}}
                        {{-- Language Flags --}}
                        <li class="nav-item dropdown ms-2 ps-2 border-start">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownLang" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Seleziona Lingua" >
                                <i class="bi bi-globe"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownLang">
                                <li><a class="dropdown-item lang-switcher" href="#" data-lang="it" title="Italiano">🇮🇹 Italiano</a></li>
                                <li><a class="dropdown-item lang-switcher" href="#" data-lang="fr" title="Français">🇫🇷 Français</a></li>
                                <li><a class="dropdown-item lang-switcher" href="#" data-lang="en" title="English">🇬🇧 English</a></li>
                                <li><a class="dropdown-item lang-switcher" href="#" data-lang="ar" title="العربية">🇸🇦 العربية</a></li>
                                <li><a class="dropdown-item lang-switcher" href="#" data-lang="ro" title="Română">🇷🇴 Română</a></li>
                                <li><a class="dropdown-item lang-switcher" href="#" data-lang="sq" title="Shqip">🇦🇱 Shqip</a></li>
                                <li><a class="dropdown-item lang-switcher" href="#" data-lang="es" title="Español">🇪🇸 Español</a></li>
                            </ul>
                        </li> {{-- This li is outside any authentication checks --}}
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4" style="padding-top: 70px !important;"> {{-- Aggiunto padding-top per compensare la navbar fissa --}}
            <div class="container mt-5">
                <div class="text-center mb-4">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('images/logo_roma.png') }}" alt="Logo Roma" style="height: 130px; background-color: rgba(255, 255, 255, 0.4); border-radius: 8px; padding: 5px; box-sizing: border-box;">
                    </a>
                </div>
            </div>
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    {{-- jQuery è richiesto da DataTables --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        if ('serviceWorker' in navigator) { // Questo blocco di registrazione del Service Worker è corretto e va mantenuto
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('{{ asset('sw.js') }}').then(function(registration) {
                    console.log('Service Worker registrato con successo.');
                }, function(err) {
                    console.error('Registrazione del Service Worker fallita: ', err);
                });
            });
        }
    </script>

    {{-- Il modal e lo script JavaScript per il prompt di installazione PWA --}}
    <!-- PWA Install Prompt Modal -->
    <div class="modal fade" id="pwaInstallModal" tabindex="-1" aria-labelledby="pwaInstallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white" data-bs-theme="dark"> {{-- Aggiunto data-bs-theme="dark" per un pulsante di chiusura bianco --}}
                    <h5 class="modal-title" id="pwaInstallModalLabel">Installa l'App FILLEA CGIL Lazio!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p>Aggiungi la nostra applicazione alla schermata Home del tuo dispositivo per un accesso rapido e un'esperienza migliore!</p>
                    <i class="bi bi-phone-fill fs-1 text-primary my-3"></i>
                    <p class="text-muted">Non perdere le ultime novità e i servizi dedicati.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="dismissPWAButton">No, grazie</button>
                    <button type="button" class="btn btn-primary" id="installPWAButton">Installa Ora</button>
                </div>
            </div>
        </div>
    </div>

    <script> {{-- Questo è il blocco di script che contiene la logica per il prompt di installazione PWA --}}
        let deferredPrompt; // Variabile per memorizzare l'evento beforeinstallprompt

        // Ascolta l'evento beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            // Impedisce alla mini-infobar predefinita del browser di apparire
            e.preventDefault();
            // Memorizza l'evento in modo che possa essere attivato in seguito
            deferredPrompt = e;
            // Mostra il nostro prompt personalizzato dopo un ritardo, solo se non è stato precedentemente ignorato
            if (localStorage.getItem('pwa_install_prompt_dismissed') !== 'true') {
                setTimeout(() => {
                    const installModal = new bootstrap.Modal(document.getElementById('pwaInstallModal'));
                    installModal.show();
                }, 5000); // Ritardo di 5 secondi
            }
        });

        // Listener per il pulsante di installazione personalizzato
        document.getElementById('installPWAButton').addEventListener('click', () => {
            const installModal = bootstrap.Modal.getInstance(document.getElementById('pwaInstallModal'));
            if (installModal) installModal.hide();

            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'dismissed') {
                        localStorage.setItem('pwa_install_prompt_dismissed', 'true'); // Memorizza che l'utente ha ignorato il prompt
                    }
                    deferredPrompt = null; // Pulisce l'evento memorizzato
                });
            }
        });

        // Listener per il pulsante di chiusura personalizzato
        document.getElementById('dismissPWAButton').addEventListener('click', () => {
            localStorage.setItem('pwa_install_prompt_dismissed', 'true'); // Memorizza che l'utente ha ignorato il prompt
        });

        // Opzionale: Ascolta l'evento `appinstalled` per sapere quando l'utente ha installato con successo la PWA
        window.addEventListener('appinstalled', () => {
            console.log('PWA installata con successo!');
            const installModal = bootstrap.Modal.getInstance(document.getElementById('pwaInstallModal'));
            if (installModal) installModal.hide();
            localStorage.removeItem('pwa_install_prompt_dismissed'); // Rimuove il flag di ignorato se l'app è stata installata
        });
    </script>

{{--    @auth --}} {{-- START: Blocco JavaScript Notifiche Push (temporaneamente disabilitato) --}}
    <script>
        // Logica per le notifiche Push
        const vapidPublicKey = '{{ config('webpush.vapid.public_key') }}';
        const enablePushBtn = document.getElementById('enable-push-notifications');
        const disablePushBtn = document.getElementById('disable-push-notifications');
        const unsupportedMsg = document.getElementById('push-unsupported-message');

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        function updateUI(isSubscribed) {
            if (isSubscribed) {
                enablePushBtn.style.display = 'none';
                disablePushBtn.style.display = 'inline-block';
            } else {
                enablePushBtn.style.display = 'inline-block';
                disablePushBtn.style.display = 'none';
            }
        }

        async function checkSubscription() {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            updateUI(!!subscription);
            return subscription;
        }

        async function subscribeUser() {
            const registration = await navigator.serviceWorker.ready;
            try {
                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
                });

                // Invia la sottoscrizione al backend
                await fetch('{{ route('push.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(subscription)
                });
                updateUI(true);
                Swal.fire('Successo', 'Notifiche abilitate!', 'success');
            } catch (err) {
                console.error('Failed to subscribe the user: ', err);
                Swal.fire('Errore', 'Impossibile abilitare le notifiche.', 'error');
            }
        }

        async function unsubscribeUser() {
            const subscription = await checkSubscription();
            if (subscription) {
                await subscription.unsubscribe();
                // Invia la richiesta di cancellazione al backend
                await fetch('{{ route('push.destroy') }}', {
                    method: 'POST', // Usiamo POST con _method per il DELETE
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ endpoint: subscription.endpoint })
                });
                updateUI(false);
                Swal.fire('Disabilitate', 'Le notifiche sono state disabilitate.', 'info');
            }
        }

        if ('serviceWorker' in navigator && 'PushManager' in window) {
            if (enablePushBtn && disablePushBtn) {
                enablePushBtn.addEventListener('click', subscribeUser);
                disablePushBtn.addEventListener('click', unsubscribeUser);
                checkSubscription();
            }
        } else {
            if (unsupportedMsg) {
                unsupportedMsg.style.display = 'block';
            }
        }
    </script>
{{--    @endauth --}} {{-- END: Blocco JavaScript Notifiche Push (temporaneamente disabilitato) --}}

    @stack('scripts')

    <!-- Elemento per Google Translate, reso invisibile -->
    <div id="google_translate_element" style="display:none;"></div>

    <script type="text/javascript">
        // Funzione di callback per l'API di Google Translate
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'it', // Lingua originale del sito
                autoDisplay: false
            }, 'google_translate_element');
        }

        // Funzione per cambiare la lingua tramite cookie e ricaricamento
        function triggerGoogleTranslate(lang) {
            const googleCookie = document.cookie.match(/googtrans=([^;]+)/);
            const currentTranslation = googleCookie && googleCookie[1] ? googleCookie[1].split('/')[2] : 'it';

            if (lang === currentTranslation) {
                return; // Già nella lingua desiderata, non fare nulla
            }

            if (lang === 'it') {
                // Rimuove il cookie di traduzione per tornare all'originale
                document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.' + window.location.hostname;
            } else {
                // Imposta il cookie per la lingua desiderata
                document.cookie = `googtrans=/it/${lang}; path=/`;
            }
            // Ricarica la pagina per applicare la traduzione
            location.reload();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const htmlTag = document.documentElement;
            const savedLocale = localStorage.getItem('app_locale');

            // Applica la lingua salvata al caricamento della pagina
            if (savedLocale) {
                htmlTag.lang = savedLocale;
            }

            // Gestisce il click sulle bandierine per cambiare lingua
            document.querySelectorAll('.lang-switcher').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const lang = this.dataset.lang;
                    
                    // Salva la preferenza prima di avviare il ricaricamento
                    localStorage.setItem('app_locale', lang);
                    
                    // Attiva la traduzione
                    triggerGoogleTranslate(lang);
                });
            });

            // Gestisce l'apertura automatica di una modale se l'URL contiene un hash
            if (window.location.hash) {
                // Un piccolo ritardo per dare tempo alla pagina di renderizzare e allo scroll di completarsi
                setTimeout(() => {
                    try {
                        console.log(`[AutoModal] Rilevato hash: ${window.location.hash}`);
                        const elementId = window.location.hash.substring(1);
                        const containerElement = document.getElementById(elementId);

                        if (!containerElement) {
                            console.error(`[AutoModal] ERRORE: Elemento contenitore con ID '${elementId}' non trovato.`);
                            return;
                        }
                        console.log(`[AutoModal] Trovato elemento contenitore:`, containerElement);

                        // Trova il pulsante "Dettagli" all'interno del contenitore
                        const detailButton = containerElement.querySelector('.btn-show-guide');

                        if (detailButton) {
                            console.log(`[AutoModal] Trovato pulsante 'Dettagli'. Simulo il click...`, detailButton);
                            detailButton.click();
                            console.log(`[AutoModal] Click simulato. La modale dovrebbe aprirsi.`);
                        } else {
                            console.error(`[AutoModal] ERRORE: Pulsante '.btn-show-guide' non trovato all'interno di #${elementId}.`);
                        }

                    } catch (e) {
                        console.error("[AutoModal] ERRORE CRITICO nel tentativo di aprire la modale dall'hash dell'URL:", e);
                    }
                }, 500);
            }
        });
    </script>
    <!-- Script di Google Translate, caricato alla fine -->
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
