<!DOCTYPE html>
<head>
    <style>
        .chat-messages {
            background: #f1ede6 !important;
            background-image: url('{{ url('images/fondo_chat.png') }}') !important;
            background-size: 70% 70% !important;
            background-position: center;
            will-change: background-image;
            contain: paint;
            background-repeat: no-repeat;
        }
    </style>
    <link rel="preload" href="{{ url('images/fondo_chat.png') }}" as="image">
</head>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Chat - Empresarial</title>
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link rel="icon" href="{{ asset('images/logo.ico') }}">
    </head>
    <body>
        <div id="app"></div>
        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
