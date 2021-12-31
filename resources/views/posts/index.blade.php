@extends('layouts.admin')

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
            <form action="{{ route('posts.index') }}" method="get">
                <div class="col-12">
                    <Select class="form-control" name="status">
                        <option value="">Select status</option>
                        <option value="{{ \App\Models\Post::STATUS_ACTIVE }}">Active</option>
                        <option value="{{ \App\Models\Post::STATUS_INACTIVE }}">Inactive</option>
                    </Select>
                    <br/>
                    <div class="form-group">
                        <div class='input-group date' id='from_time'>
                            <input type='text' name="from_time" class="form-control" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class='input-group date' id='to_time'>
                            <input type='text' name="to_time" class="form-control" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark">Filter</button>
                </div>
            </form>
        <a href="{{ route('posts.create') }}">Create</a>
        @if(COUNT($data) > 0)
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Title</th>
                    <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $value)
                    <tr>
                        <td>{{ $value->id }}</td>
                        <td><a href="{{ route('posts.show', $value->id) }}">{{ $value->title }}</a></td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic example">
                                @if(\Illuminate\Support\Facades\Auth::user()->isSuperAdmin())
                                    @if(\App\Models\Post::STATUS_ACTIVE == $value->status)
                                        <a type="button" class="btn btn-primary" href="{{ route('posts.unpushlish', $value->id) }}">UnPublish</a>
                                    @else
                                        <a type="button" class="btn btn-primary" href="{{ route('posts.publish', $value->id) }}">Publish</a>
                                    @endif
                                @endif
                                @if(\Illuminate\Support\Facades\Auth::user()->can('posts.edit'))
                                    <a type="button" class="btn btn-primary" href="{{ route('posts.edit', $value->id) }}">Edit</a>
                                @endif
                                @if(\Illuminate\Support\Facades\Auth::user()->can('posts.delete'))
                                    <a type="button" class="btn btn-primary" href="{{ route('posts.delete', $value->id) }}">Delete</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p>Not have Data</p>
        @endif
        <script>
            $('#from_time').datepicker({
                format: 'yyyy-mm-dd'
            });
            $('#to_time').datepicker({
                format: 'yyyy-mm-dd'
            });
        </script>
    </div>
@endsection
