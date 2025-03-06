<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatController;
// Rutas de la API de usuarios
Route::get('/api/users', [UserController::class, 'index']);
Route::get('/api/users/search', [UserController::class, 'search']);
Route::post('/api/register', [UserController::class, 'register']);
Route::post('/api/login', [UserController::class, 'login']);
Route::get('/api/logout', [UserController::class, 'logout']);
Route::get('/api/mensajes-chat', [ChatController::class, 'obtenerMensajesChat']);
Route::post('/api/guardar-mensaje', [ChatController::class, 'guardarMensaje']);
// Rutas de la API de chats
Route::get('/api/chats-mios', [ChatController::class, 'obtenerChatsUsuario']);
Route::post('/api/crear-chat', [ChatController::class, 'crearChat']);
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');

