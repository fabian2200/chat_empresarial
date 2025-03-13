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

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(20, 26, 99, 0.815), rgba(23, 68, 134, 0.815)),
                        url('{{ url('images/photo-1497366754035-f200968a6e72.png') }}') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
            padding: 20px;
        }

    </style>
     <link rel="preload" href="{{ url('images/logo_empresa.png') }}" as="image">
    <link rel="preload" href="{{ url('images/fondo_chat.png') }}" as="image">
    <link rel="preload" href="{{ url('images/photo-1497366754035-f200968a6e72.png') }}" as="image">
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
