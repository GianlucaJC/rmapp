@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">{{ __('Dashboard Amministratore') }}</h4>
                </div>

                <div class="card-body">
                    <h5 class="card-title">Benvenuto, Amministratore!</h5>
                    <p class="card-text">Questa è la tua dashboard. Da qui potrai gestire l'applicazione.</p>
                    
                    <a href="{{ route('admin.logout') }}" class="btn btn-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">@csrf</form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection