<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SK Generator')</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">SK Generator</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('riwayat.index') }}">Riwayat SK</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('sk-custom.create') }}">SK Custom</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Pengaturan</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('pengaturan.sekolah.edit') }}">Pengaturan Sekolah</a></li>
                            <li><a class="dropdown-item" href="{{ route('pengaturan.penomoran.edit') }}">Penomoran</a></li>
                            <li><a class="dropdown-item" href="{{ route('pengaturan.kop-surat.index') }}">Kop Surat</a></li>
                            <li><a class="dropdown-item" href="{{ route('pengaturan.pejabat.index') }}">Pejabat Penandatangan</a></li>
                            <li><a class="dropdown-item" href="{{ route('pengaturan.jenis-sk.index') }}">Jenis SK</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>