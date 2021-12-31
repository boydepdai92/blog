@extends('layouts.admin')

@section('content')
    <div class="row">
        <h1 class="display-6">{{ $post->title }}</h1>
        <p>{{ \Illuminate\Support\Str::markdown($post->content) }}</p>
    </div>
@endsection
