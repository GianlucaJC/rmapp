@extends('layouts.app')

@section('title', 'Crea Nuova News')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Crea Nuova News</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.news.store') }}" method="POST">
                @csrf
                @include('admin.news._form', ['news' => new \App\Models\News()])
            </form>
        </div>
    </div>
</div>
@endsection
