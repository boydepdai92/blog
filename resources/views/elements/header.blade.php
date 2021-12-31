<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="javascript:void(0)">Blog</a>
        </div>
        <ul class="nav navbar-nav">
            <li class="active"><a href="{{ route('posts.index') }}">Post</a></li>
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0)">
                    {{ \Illuminate\Support\Facades\Auth::user()->name }}
                    <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('logout') }}">Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
