@extends('layouts.app')

@section('title', $title)

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="h3 mb-4">{{ $title }}</h1>
            @include('placeholders.notice')
        </div>
    </section>
@endsection
