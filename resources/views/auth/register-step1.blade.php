@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card page-title">
                <div class="card-header"><h1>{{ __('Registrazione - Step 1 di 2') }}</h1></div>

                <div class="card-body">
                    <form method="GET" action="{{ route('register') }}">
                        <input type="hidden" name="from" value="contracts">

                        <div class="row mb-4">
                            <label for="account_type" class="col-md-4 col-form-label text-md-end">Per quale profilo ti registri?</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <select id="account_type" class="form-select" name="type" required>
                                        <option value="" disabled selected>Scegli il tipo di account...</option>
                                        <option value="worker">Lavoratore</option>
                                        <option value="consultant">Consulente / Azienda</option>
                                        <option value="funzionario">Funzionario Fillea Cgil</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    Prosegui <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection