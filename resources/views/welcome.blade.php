<!DOCTYPE html>
{{-- resources\views\welcome.blade.php --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet"/>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body class="font-sans antialiased dark:bg-black dark:text-white/50">
<div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
    <div class="container">
        <h1 class="text-center mt-5">Bootstrap está funcionando</h1>
        <button class="btn btn-primary">Botón de Bootstrap</button>
    </div>
</div>

<script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
