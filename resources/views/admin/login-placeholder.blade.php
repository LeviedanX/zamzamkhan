<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Login Admin — PT Zam Zam Khan</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-navy-900 px-5 font-sans">
    <div class="w-full max-w-md rounded-3xl bg-white p-8 text-center shadow-2xl">
        <img src="{{ asset('images/logo-zzk.png') }}" alt="Logo PT Zam Zam Khan" class="mx-auto h-14 w-auto">
        <h1 class="mt-5 font-display text-2xl font-bold text-navy-900">Login Admin</h1>
        <p class="mt-3 text-sm leading-relaxed text-navy-500">
            Halaman login admin akan dikembangkan pada tahap backend.
        </p>
        <a href="{{ route('home') }}" class="btn-primary mt-6 w-full">Kembali ke Beranda</a>
    </div>
</body>
</html>
