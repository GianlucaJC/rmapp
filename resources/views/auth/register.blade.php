@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        {{-- Nome --}}
                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Il tuo nome">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Cognome --}}
                        <div class="row mb-3">
                            <label for="last_name" class="col-md-4 col-form-label text-md-end">{{ __('Cognome') }}</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                    <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" placeholder="Il tuo cognome">
                                    @error('last_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Codice Fiscale --}}
                        <div class="row mb-3">
                            <label for="codice_fiscale" class="col-md-4 col-form-label text-md-end">{{ __('Codice Fiscale') }}</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                    <input id="codice_fiscale" type="text" class="form-control @error('codice_fiscale') is-invalid @enderror" name="codice_fiscale" value="{{ old('codice_fiscale') }}" required autocomplete="off" maxlength="16" placeholder="Es. RSSMRA80A01H501Z">
                                    @error('codice_fiscale')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Numero di Telefono --}}
                        <div class="row mb-3">
                            <label for="phone_number" class="col-md-4 col-form-label text-md-end">{{ __('Numero di Telefono') }}</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                    <input id="phone_number" type="tel" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" value="{{ old('phone_number') }}" required autocomplete="tel" placeholder="Es. +39 333 1234567">
                                    @error('phone_number')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Contratto di Inquadramento --}}
                        <div class="row mb-3">
                            <label for="contract_type" class="col-md-4 col-form-label text-md-end">{{ __('Contratto di Inquadramento') }}</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                                    <select id="contract_type" class="form-select @error('contract_type') is-invalid @enderror" name="contract_type" required>
                                        <option value="">Seleziona un contratto</option>
                                        <option value="edilizia" {{ old('contract_type') == 'edilizia' ? 'selected' : '' }}>Edilizia: Costruzioni, manutenzione, restauro.</option>
                                        <option value="legno_arredo" {{ old('contract_type') == 'legno_arredo' ? 'selected' : '' }}>Legno e Arredo: Produzione mobili, lavorazione legno.</option>
                                        <option value="cemento_calce_gesso" {{ old('contract_type') == 'cemento_calce_gesso' ? 'selected' : '' }}>Cemento, Calce e Gesso: Produzione materiali cementizi.</option>
                                        <option value="lapidei_escavazione_sabbia" {{ old('contract_type') == 'lapidei_escavazione_sabbia' ? 'selected' : '' }}>Lapidei ed Escavazione Sabbia: Estrazione marmi, sabbia.</option>
                                        <option value="laterizi_manufatti" {{ old('contract_type') == 'laterizi_manufatti' ? 'selected' : '' }}>Laterizi e Manufatti: Produzione mattoni, tegole, manufatti.</option>
                                        <option value="restauro_beni_culturali" {{ old('contract_type') == 'restauro_beni_culturali' ? 'selected' : '' }}>Restauro e Beni Culturali: Tutela e restauro beni vincolati.</option>
                                    </select>
                                    @error('contract_type')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Mansione Lavorativa --}}
                        <div class="row mb-3">
                            <label for="job_title" class="col-md-4 col-form-label text-md-end">{{ __('Mansione Lavorativa') }}</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                    <input id="job_title" type="text" class="form-control @error('job_title') is-invalid @enderror" name="job_title" value="{{ old('job_title') }}" required autocomplete="organization-title" placeholder="Es. Muratore, Carpentiere">
                                    @error('job_title')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Email Address --}}
                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="La tua email">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Min. 8 caratteri, maiuscola, minuscola, numero, simbolo">
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Confirm Password --}}
                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Conferma la password">
                                </div>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                            <div class="col-md-6 offset-md-4 mt-3">
                                <a href="{{ route('home') }}" class="btn btn-link">{{ __('Torna alla Home') }}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
