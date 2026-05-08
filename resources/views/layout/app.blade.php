<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titre', 'PFE Scheduler')</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('styles')
</head>
<body>

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ url('/dashboard') }}"> PFE Scheduler</a>
        </div>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="nav navbar-nav">
                <li class="{{ Request::is('dashboard*') ? 'active' : '' }}">
                    <a href="{{ url('/dashboard') }}"> Dashboard</a>
                </li>
                <li class="{{ Request::is('planning*') ? 'active' : '' }}">
                    <a href="{{ url('/planning') }}"> Planning</a>
                </li>
                <li class="{{ Request::is('verification*') ? 'active' : '' }}">
                    <a href="{{ url('/verification') }}"> Vérification</a>
                </li>
                <li class="{{ Request::is('import*') ? 'active' : '' }}">
                    <a href="{{ url('/import') }}"> Import</a>
                </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="{{ url('/export') }}">⬇ Exporter</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="contenu-principal">

    @if(session('success'))
    <div class="container-fluid" style="margin-top:10px">
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
             {{ session('success') }}
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="container-fluid" style="margin-top:10px">
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
             {{ session('error') }}
        </div>
    </div>
    @endif

    @yield('contenu')

</div>

<footer class="pied-page">
    <div class="container-fluid text-center">
        <p>🎓 <strong>PFE Scheduler</strong> — ENSA Al Hoceima | Département MI</p>
    </div>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="{{ asset('js/script.js') }}"></script>
@yield('scripts')

</body>
</html>