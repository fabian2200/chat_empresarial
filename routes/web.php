<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GrupoController;
// Rutas de la API de usuarios
Route::get('/api/users', [UserController::class, 'index']);
Route::get('/api/users/search', [UserController::class, 'search']);
Route::post('/api/register', [UserController::class, 'register']);
Route::post('/api/login', [UserController::class, 'login']);
Route::get('/api/logout', [UserController::class, 'logout']);
Route::get('/api/mensajes-chat', [ChatController::class, 'obtenerMensajesChat']);
Route::post('/api/guardar-mensaje', [ChatController::class, 'guardarMensaje']);
Route::post('/api/enviar-mensaje-difusion', [ChatController::class, 'enviarMensajeDifusion']);

Route::get('/api/grupos-mios', [GrupoController::class, 'obtenerGruposUsuario']);
Route::post('/api/crear-grupo', [GrupoController::class, 'crearGrupo']);
Route::post('/api/guardar-mensaje-grupo', [GrupoController::class, 'guardarMensaje']);
Route::get('/api/mensajes-grupo', [GrupoController::class, 'obtenerMensajesGrupo']);
Route::post('/api/eliminar-participante', [GrupoController::class, 'eliminarParticipante']);
Route::post('/api/reactivar-participante', [GrupoController::class, 'reactivarParticipante']);
Route::post('/api/agregar-participantes', [GrupoController::class, 'agregarParticipantes']);

Route::post('/api/actualizar-perfil', [UserController::class, 'actualizarPerfil']);
Route::get('/api/mis-datos', [UserController::class, 'misDatos']);
// Rutas de la API de chats
Route::get('/api/chats-mios', [ChatController::class, 'obtenerChatsUsuario']);
Route::post('/api/crear-chat', [ChatController::class, 'crearChat']);
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');

