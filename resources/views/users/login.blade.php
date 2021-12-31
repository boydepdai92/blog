@extends('layouts.default')

@section('content')
<div class="row">
    <div class="col-md-4 offset-md-4">
        <br/><br/><br/><br/>
    </div>
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
    <div class="col-md-4 offset-md-4">
        <form action="{{ route('do-login') }}" method="post" class="row g-3">
            @csrf
            <h4>Welcome Back</h4>
            <div class="col-12">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Email">
            </div>
            <div class="col-12">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Password">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-dark float-end">Login</button>
            </div>
        </form>
        <hr class="mt-4">
        <div class="col-12">
            <p class="text-center mb-0">Have not account yet? <a href="{{ route('registration') }}">Signup</a></p>
        </div>
    </div>
</div>
@endsection
