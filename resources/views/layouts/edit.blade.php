@extends('layouts.app')

@section('title', 'Modifica News')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Modifica News: {{ $news->title }}</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.news.update', $news) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.news._form', ['news' => $news])
            </form>
        </div>
    </div>
</div>
@endsection