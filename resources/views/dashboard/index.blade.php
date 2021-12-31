@extends('layouts.admin')

@section('content')
    <div class="row">
        @if(session('success'))
            @component('elements.success')
                {{ session('success') }}
            @endcomponent
        @endif
    </div>
@endsection
