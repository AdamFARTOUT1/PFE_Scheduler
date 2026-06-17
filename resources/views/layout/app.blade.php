<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titre', 'PFE Scheduler')</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">

    {{-- Styles dynamiques pour les filières lues depuis la base de données --}}
    <style>
        @foreach($_filiereColors ?? [] as $key => $color)
        .badge-{{ $key }} {
            background-color: {{ $color }};
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .cellule-{{ $key }} {
            background-color: {{ $color }}33;
            border-radius: 4px;
            padding: 6px;
        }
        @endforeach
    </style>

    @yield('styles')
</head>

<body>

    <nav class="navbar-custom">
        <div class="navbar-inner">

            <ul class="navbar-liens">
                <li>
                    <a href="{{ route('dashboard.index') }}" class="{{ Request::is('dashboard*') ? 'actif' : '' }}">
                        Tableau de bord
                    </a>
                </li>
                <li>
                    <a href="{{ route('planning.index') }}" class="{{ Request::is('planning*') ? 'actif' : '' }}">
                        Planning
                    </a>
                </li>
                <li>
                    <a href="{{ route('import.index') }}" class="{{ Request::is('import*') ? 'actif' : '' }}">
                        Import
                    </a>
                </li>
                <li>
                    <a href="{{ route('export.index') }}" class="{{ Request::is('export*') ? 'actif' : '' }}">
                        Export
                    </a>
                </li>
                <li>
                    <a href="{{ route('verification.index') }}"
                        class="{{ Request::is('verification*') ? 'actif' : '' }}">
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="{{ asset('js/script.js') }}?v={{ time() }}"></script>
    @yield('scripts')

</body>

</html>