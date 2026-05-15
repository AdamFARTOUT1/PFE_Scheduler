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

<nav class="navbar-custom">
    <div class="navbar-inner">

        <a href="{{ url('/dashboard') }}" class="navbar-logo">
             PFE Scheduler
        </a>

        <ul class="navbar-liens">
            <li>
                <a href="{{ url('/dashboard') }}" class="{{ Request::is('dashboard*') ? 'actif' : '' }}">
                     Dashboard
                </a>
            </li>
            <li>
                <a href="{{ url('/planning') }}" class="{{ Request::is('planning*') ? 'actif' : '' }}">
                    Planning
                </a>
            </li>
            <li>
                <a href="{{ url('/import') }}" class="{{ Request::is('import*') ? 'actif' : '' }}">
                     Import
                </a>
            </li>
            <li>
                <a href="{{ url('/export') }}" class="{{ Request::is('export*') ? 'actif' : '' }}">
                    Export
                </a>
            </li>
            <li>
                <a href="{{ url('/verification') }}" class="{{ Request::is('verification*') ? 'actif' : '' }}">
                     Vérification
                </a>
            </li>
        </ul>

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