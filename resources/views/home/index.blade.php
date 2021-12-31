@extends('layouts.default')

@section('content')
    <div class="row">
        @if(session('success'))
            @component('elements.success')
                {{ session('success') }}
            @endcomponent
        @endif
        @if(!empty($errors))
            @foreach($errors->all() as $error)
                @component('elements.error')
                    {{ $error }}
                @endcomponent
            @endforeach
        @endif
        <h1>Post</h1>
        @if(COUNT($data) > 0)
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Title</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $value)
                    <tr>
                        <td>{{ $value->id }}</td>
                        <td><a href="{{ route('posts.show', $value->id) }}">{{ $value->title }}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p>Not have Data</p>
        @endif
    </div>
@endsection
