@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-6">
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
        <div class="col-md-6">
            <form action="{{ route('posts.store') }}" method="post" class="row g-3">
                @csrf
                <h4>Update post {{ $post->title }}</h4>
                <div class="col-12">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Title" value="{{ $post->title }}">
                </div>
                <br/>
                <div class="col-12">
                    <label>Content</label>
                    <textarea name="content" class="form-control" placeholder="Content">{{ $post->content }}</textarea>
                </div>
                <br/>
                <div class="col-12">
                    <label>Publish date (Use for schedule publish post)</label><br/>
                    <div class="form-group">
                        <div class='input-group date' id='datetimepicker'>
                            <input type='text' name="publish_date" class="form-control" value="{{ $post->publish_date }}" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-dark">Create</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        $('#datetimepicker').datetimepicker({
            minDate: new Date(),
            format: 'YYYY-MM-DD HH:mm:ss'
        });
    </script>
@endsection
