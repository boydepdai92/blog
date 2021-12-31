@extends('layouts.default')

@section('content')
    <div class="row">
        <div class="col-md-4 offset-md-4">
            <br/><br/>
        </div>
        @if(!empty($errors))
            @foreach($errors->all() as $error)
                @component('elements.error')
                    {{ $error }}
                @endcomponent
            @endforeach
        @endif
        <div class="col-md-4 offset-md-4">
            <form action="{{ route('do-registration') }}" method="post" class="row g-3">
                @csrf
                <h4>Register</h4>
                <div class="col-12">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Name">
                </div>
                <div class="col-12">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Email">
                </div>
                <div class="col-12">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password">
                </div>
                <div class="col-12">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-dark">Register</button>
                </div>
            </form>
        </div>
    </div>
@endsection
