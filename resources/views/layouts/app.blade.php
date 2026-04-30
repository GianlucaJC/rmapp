<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Colore della barra degli indirizzi su mobile -->
    <meta name="theme-color" content="#cc0000">

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
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/home') }}">
                    {{-- SVG Logo FILLEA CGIL --}}
                    <svg version="1.1" id="logo-fillea-cgil" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        viewBox="0 0 935.43 136.06" style="height: 30px; width: auto;" xml:space="preserve">
                        <g id="Livello_1_copia_2" data-name="Livello 1 copia 2">
                            <g>
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
                        </g>
                        <g id="Livello_1_copia" data-name="Livello 1 copia">
                            <g>
                            <polygon points="16.73 18.43 83.18 18.43 83.18 46.85 53.05 46.85 53.05 57.11 79.49 57.11 79.49 82.9 53.05 82.9 53.05 117.64 16.73 117.64 16.73 18.43" style="fill: #ec1c24;"></polygon>
                            <rect x="96.01" y="18.43" width="36.05" height="99.21" style="fill: #ec1c24;"></rect>
                            <polygon points="149.28 18.43 185.34 18.43 185.34 85.8 215.6 85.8 215.6 117.64 149.28 117.64 149.28 18.43" style="fill: #ec1c24;"></polygon>
                            <polygon points="225.44 18.43 261.49 18.43 261.49 85.8 291.76 85.8 291.76 117.64 225.44 117.64 225.44 18.43" style="fill: #ec1c24;"></polygon>
                            <polygon points="301.35 18.43 368.06 18.43 368.06 45.53 337.67 45.53 337.67 55.01 365.43 55.01 365.43 80.79 337.67 80.79 337.67 90.53 369.12 90.53 369.12 117.64 301.35 117.64 301.35 18.43" style="fill: #ec1c24;"></polygon>
                            <path d="m414.18,117.64h-38.16l34.48-99.21h39.87l35.39,99.21h-38.16l-3.29-12.37h-27.24l-2.89,12.37Zm23.82-35.92l-4.21-17.5c-1.05-4.21-1.71-8.55-2.37-12.76h-1.32l-6.58,30.27h14.47Z" style="fill: #ec1c24;"></path>
                            </g>
                        </g>
                        <g id="Livello_2" data-name="Livello 2">
                            <g>
                            <path d="m570.92,58.98c-5.61-5.23-12.62-9.69-20.53-9.69-10.46,0-19,8.29-19,18.75s9.05,18.75,19.51,18.75c8.03,0,14.41-3.44,20.02-8.93l-1.02,35.96c-5.61,2.68-18.62,3.83-24.87,3.83-27.42,0-48.84-21.3-48.84-48.71s22.06-50.5,50.24-50.5c7.91,0,16.07,1.4,23.46,3.95l1.02,36.6Z" style="fill: #231f20;"></path>
                            <path d="m685.22,59.49c-.26,15.68-.26,28.82-11.61,41.06-10.2,11.09-25.89,17.09-40.81,17.09-29.33,0-53.05-18.11-53.05-48.97s23.08-50.24,53.3-50.24c16.71,0,39.66,8.29,47.18,24.74l-32.65,11.99c-2.68-4.85-7.65-7.4-13.26-7.4-12.5,0-19.38,10.71-19.38,22.32,0,10.58,6.63,20.53,17.98,20.53,5.48,0,12.5-2.55,14.16-8.42h-15.81v-22.7h53.94Z" style="fill: #231f20;"></path>
                            <rect x="696.71" y="19.96" width="34.94" height="96.15" style="fill: #231f20;"></rect>
                            <polygon points="747.46 19.96 782.4 19.96 782.4 85.25 811.73 85.25 811.73 116.11 747.46 116.11 747.46 19.96" style="fill: #231f20;"></polygon>
                            </g>
                        </g>
                    </svg>
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
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}" title="Dashboard Admin">
                                    <i class="bi bi-speedometer2 fs-5"></i> Dashboard Admin
                                </a>
                            </li>
                            <li class="nav-item border-start ms-2 ps-2">
                                <a class="nav-link" href="{{ route('admin.logout') }}" title="Logout Admin"
                                   onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                                    <i class="bi bi-box-arrow-right fs-5"></i>
                                </a>
                                <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">@csrf</form>
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
                                </div>
                            </li>
                            @endguest
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/logo_roma.png') }}" alt="Logo Roma" style="height: 80px;">
                </div>
            </div>
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    {{-- jQuery è richiesto da DataTables --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>


  <g id="bianco">
    <rect width="935.43" height="136.06" style="fill: #fff;"></rect>
  </g>
  <g id="Livello_1_copia_2" data-name="Livello 1 copia 2">
    <g>
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
  </g>
  <g id="Livello_1_copia" data-name="Livello 1 copia">
    <g>
      <polygon points="16.73 18.43 83.18 18.43 83.18 46.85 53.05 46.85 53.05 57.11 79.49 57.11 79.49 82.9 53.05 82.9 53.05 117.64 16.73 117.64 16.73 18.43" style="fill: #ec1c24;"></polygon>
      <rect x="96.01" y="18.43" width="36.05" height="99.21" style="fill: #ec1c24;"></rect>
      <polygon points="149.28 18.43 185.34 18.43 185.34 85.8 215.6 85.8 215.6 117.64 149.28 117.64 149.28 18.43" style="fill: #ec1c24;"></polygon>
      <polygon points="225.44 18.43 261.49 18.43 261.49 85.8 291.76 85.8 291.76 117.64 225.44 117.64 225.44 18.43" style="fill: #ec1c24;"></polygon>
      <polygon points="301.35 18.43 368.06 18.43 368.06 45.53 337.67 45.53 337.67 55.01 365.43 55.01 365.43 80.79 337.67 80.79 337.67 90.53 369.12 90.53 369.12 117.64 301.35 117.64 301.35 18.43" style="fill: #ec1c24;"></polygon>
      <path d="m414.18,117.64h-38.16l34.48-99.21h39.87l35.39,99.21h-38.16l-3.29-12.37h-27.24l-2.89,12.37Zm23.82-35.92l-4.21-17.5c-1.05-4.21-1.71-8.55-2.37-12.76h-1.32l-6.58,30.27h14.47Z" style="fill: #ec1c24;"></path>
    </g>
  </g>
  <g id="Livello_2" data-name="Livello 2">
    <g>
      <path d="m570.92,58.98c-5.61-5.23-12.62-9.69-20.53-9.69-10.46,0-19,8.29-19,18.75s9.05,18.75,19.51,18.75c8.03,0,14.41-3.44,20.02-8.93l-1.02,35.96c-5.61,2.68-18.62,3.83-24.87,3.83-27.42,0-48.84-21.3-48.84-48.71s22.06-50.5,50.24-50.5c7.91,0,16.07,1.4,23.46,3.95l1.02,36.6Z" style="fill: #231f20;"></path>
      <path d="m685.22,59.49c-.26,15.68-.26,28.82-11.61,41.06-10.2,11.09-25.89,17.09-40.81,17.09-29.33,0-53.05-18.11-53.05-48.97s23.08-50.24,53.3-50.24c16.71,0,39.66,8.29,47.18,24.74l-32.65,11.99c-2.68-4.85-7.65-7.4-13.26-7.4-12.5,0-19.38,10.71-19.38,22.32,0,10.58,6.63,20.53,17.98,20.53,5.48,0,12.5-2.55,14.16-8.42h-15.81v-22.7h53.94Z" style="fill: #231f20;"></path>
      <rect x="696.71" y="19.96" width="34.94" height="96.15" style="fill: #231f20;"></rect>
      <polygon points="747.46 19.96 782.4 19.96 782.4 85.25 811.73 85.25 811.73 116.11 747.46 116.11 747.46 19.96" style="fill: #231f20;"></polygon>
    </g>
  </g>
