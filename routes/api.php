<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GrupoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/search', [UserController::class, 'search']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/logout', [UserController::class, 'logout']);
Route::get('/mi-usuario', [UserController::class, 'obtenerMiUsuario']);

Route::get('/mensajes-chat', [ChatController::class, 'obtenerMensajesChat']);
Route::post('/guardar-mensaje', [ChatController::class, 'guardarMensaje']);
Route::post('/enviar-mensaje-difusion', [ChatController::class, 'enviarMensajeDifusion']);

Route::get('/grupos-mios', [GrupoController::class, 'obtenerGruposUsuario']);
Route::post('/crear-grupo', [GrupoController::class, 'crearGrupo']);
Route::post('/guardar-mensaje-grupo', [GrupoController::class, 'guardarMensaje']);
Route::get('/mensajes-grupo', [GrupoController::class, 'obtenerMensajesGrupo']);
Route::post('/eliminar-participante', [GrupoController::class, 'eliminarParticipante']);
Route::post('/reactivar-participante', [GrupoController::class, 'reactivarParticipante']);
Route::post('/agregar-participantes', [GrupoController::class, 'agregarParticipantes']);
Route::post('/editar-grupo', [GrupoController::class, 'editarGrupo']);
Route::post('/guardar-mensaje-respondiendo', [GrupoController::class, 'guardarMensajeRespondiendo']);

Route::post('/actualizar-perfil', [UserController::class, 'actualizarPerfil']);
Route::get('/mis-datos', [UserController::class, 'misDatos']);
Route::post('/actualizar-datos', [UserController::class, 'actualizarDatos']);
Route::get('/numero-mensajes-recibidos', [UserController::class, 'obtenerNumeroMensajesRecibidos']);
// Rutas de la API de chats
Route::get('/chats-mios', [ChatController::class, 'obtenerChatsUsuario']);
Route::post('/crear-chat', [ChatController::class, 'crearChat']);
Route::get('/cargar-csv', [ChatController::class, 'cargarcsv']);
