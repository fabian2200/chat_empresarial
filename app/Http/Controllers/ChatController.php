<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function crearChat(Request $request)
    {
        try {
        
            $chatExistente = DB::table('chats')
            ->where(function($query) use ($request) {
                $query->where('id_crea', $request->id_crea)
                        ->where('id_amigo', $request->id_amigo);
            })
            ->orWhere(function($query) use ($request) {
                $query->where('id_crea', $request->id_amigo)
                        ->where('id_amigo', $request->id_crea);
            })
            ->first();

            if ($chatExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un chat entre estos usuarios, ¿desea abrirlo?',
                    'tipo' => 'existe',
                    'chat_id' => $chatExistente->id
                ], 200);
            }

            // Crear el chat usando DB::table
            $chatId = DB::table('chats')->insertGetId([
                'id_crea' => $request->id_crea,
                'id_amigo' => $request->id_amigo,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Chat creado exitosamente',
                'chat_id' => $chatId
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el chat',
                'error' => $e->getMessage()
            ], 200);
        }
    }

    public function obtenerChatsUsuario(Request $request)
    {
        $id = $request->id;

        try {
            $chats = DB::table('chats')
            ->where('id_crea', $id)
            ->orWhere('id_amigo', $id)
            ->get();

            foreach ($chats as $chat) {
                if ($chat->id_crea == $id) {
                    $chat->tipo = 'creador';
                    $usuario = DB::table('users')->where('id', $chat->id_amigo)->first();
                    $chat->nombre = $usuario->name;
                    $chat->avatar = $usuario->avatar;
                    $chat->empresa = $usuario->empresa;
                    $chat->last_seen = $this->formatLastSeen($usuario->last_seen);    
                    $chat->created_at = $chat->created_at;
                    $chat->fecha_chat = Carbon::parse($chat->created_at)->format('d/m/Y');
                    $chat->hora_chat = Carbon::parse($chat->created_at)
                        ->setTimezone('America/Bogota')
                        ->format('g:i A');
                    $chat->online = $this->isOnline($usuario->online);
                } else {
                    $chat->tipo = 'amigo';
                    $usuario = DB::table('users')->where('id', $chat->id_crea)->first();
                    $chat->nombre = $usuario->name;
                    $chat->avatar = $usuario->avatar;
                    $chat->empresa = $usuario->empresa;
                    $chat->last_seen = $this->formatLastSeen($usuario->last_seen);
                    $chat->created_at = $chat->created_at;
                    $chat->fecha_chat = Carbon::parse($chat->created_at)->format('d/m/Y');
                    $chat->hora_chat = Carbon::parse($chat->created_at)
                        ->setTimezone('America/Bogota')
                        ->format('g:i A');
                    $chat->online = $this->isOnline($usuario->online);
                }

                $chat->mensajes_sin_leer = $this->obtenerMensajesSinLeer($chat->id, $id);
                $chat->ultimo_mensaje = $this->ultimoMensajeChat($chat->id);
            }

            $chats = collect($chats)->sortByDesc(function($chat) {
                if ($chat->ultimo_mensaje) {
                    return $chat->ultimo_mensaje->fecha . ' ' . $chat->ultimo_mensaje->hora;
                }
                return $chat->created_at;
            })->values()->all();

            return response()->json([
                'success' => true,
                'message' => 'Chats obtenidos exitosamente',
                'chats' => $chats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los chats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function ultimoMensajeChat($chatId)
    {
        $ultimoMensaje = DB::table('mensajes')->where('id_chat', $chatId)->orderBy('id', 'desc')->first();
        return $ultimoMensaje;
    }

    private function obtenerMensajesSinLeer($chatId, $id)
    {
        $mensajes_sin_leer = DB::table('mensajes')->where('id_chat', $chatId)
        ->where('id_crea', '!=', $id)->where('estado', 0)
        ->count();

        return $mensajes_sin_leer;
    }

    private function marcarMensajesComoLeidos($chatId, $id)
    {
        DB::table('mensajes')->where('id_chat', $chatId)
        ->where('id_crea', '!=', $id)->where('estado', 0)
        ->update(['estado' => 1]);
    }
    
    public function guardarMensaje(Request $request)
    {
        $id = $request->id;
        $chatId = $request->chat_id;
        $mensaje = $request->mensaje;
        $tipo = $request->tipo;

        if ($tipo == 'archivo') {
            $archivo = $request->archivo;
            $nombreArchivo = $archivo->getClientOriginalName();
            
            // Asegurarse que la carpeta existe
            if (!file_exists(public_path('archivos'))) {
                mkdir(public_path('archivos'), 0777, true);
            }
            
            $archivo->move(public_path('archivos'), $nombreArchivo);
            $rutaArchivo = 'archivos/' . $nombreArchivo;
            $tipoArchivo = $archivo->getClientOriginalExtension();
            $mensaje = $nombreArchivo;
        }

        $fecha = Carbon::now('America/Bogota')->format('Y-m-d');
        $hora = Carbon::now('America/Bogota')->format('g:i:s A');

        $mensaje = DB::table('mensajes')->insert([
            'id_chat' => $chatId,
            'id_crea' => $id,
            'mensaje' => $mensaje,
            'tiene_archivo' => $tipo == 'archivo' ? 1 : 0,
            'fecha' => $fecha,
            'hora' => $hora,
            'tipo_archivo' => $tipo == 'archivo' ? $tipoArchivo : 'no'
        ]);

        if ($mensaje) {
            return response()->json([
                'success' => true,
                'message' => 'Mensaje guardado exitosamente'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el mensaje'
            ], 200);
        }
        
    }

    public function obtenerMensajesChat(Request $request)
    {
        $id = $request->id;
        $chatId = $request->chat_id;

        $mensajes = DB::table('mensajes')->where('id_chat', $chatId)->get();

        foreach ($mensajes as $mensaje) {
            $mensaje->is_mine = $mensaje->id_crea == $id ? true : false;
            $mensaje->fecha_mensaje = Carbon::parse($mensaje->fecha)->format('d/m/Y');
            $mensaje->hora_mensaje = Carbon::parse($mensaje->hora)
                ->setTimezone('America/Bogota')
                ->format('g:i A');
        }

        $this->marcarMensajesComoLeidos($chatId, $id);

        return response()->json([
            'success' => true,
            'message' => 'Mensajes obtenidos exitosamente',
            'mensajes' => $mensajes
        ], 200);

    }

    private function isOnline($online)
    {
        if ($online == 1) {
            return true;
        } else {
            return false;
        }
    }

    private function formatLastSeen($lastSeen)
    {
        if ($lastSeen == null) {
            return "Nunca";
        }
        
        $diff = now()->diff($lastSeen);
        
        if ($diff->days > 0) {
            return "hace {$diff->days} días";
        } elseif ($diff->h > 0) {
            return "hace {$diff->h} horas";
        } elseif ($diff->i > 0) {
            return "hace {$diff->i} minutos";
        } else {
            return "hace un momento";
        }
    }

    public function enviarMensajeDifusion(Request $request)
    {
        try {
            $id_amigos_seleccionados = explode(',', $request->id_amigos_seleccionados);
            $mensaje = $request->mensaje;
            $id_crea = $request->id_crea;
            $archivo = $request->archivo;

            // Si hay archivo, guardarlo una sola vez
            if (isset($archivo)) {
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                
                if (!file_exists(public_path('archivos'))) {
                    mkdir(public_path('archivos'), 0777, true);
                }
                
                // Guardar el archivo una sola vez
                $archivo->move(public_path('archivos'), $nombreArchivo);
            }

            $ids_chats = [];
            foreach ($id_amigos_seleccionados as $id_amigo) {
                $ids_chats[] = $this->crearChatDifusion($id_crea, $id_amigo);
            }

            foreach ($ids_chats as $id_chat) {
                $this->guardarMensajeDifusion($id_crea, $id_chat, $mensaje);
                
                // Si hay archivo, crear el mensaje con la referencia al archivo
                if (isset($archivo)) {
                    DB::table('mensajes')->insert([
                        'id_chat' => $id_chat,
                        'id_crea' => $id_crea,
                        'mensaje' => $nombreArchivo,
                        'tiene_archivo' => 1,
                        'fecha' => Carbon::now('America/Bogota')->format('Y-m-d'),
                        'hora' => Carbon::now('America/Bogota')->format('g:i:s A'),
                        'tipo_archivo' => $archivo->getClientOriginalExtension()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Mensaje difundido exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al difundir el mensaje: ' . $e->getMessage()
            ], 500);
        }
    }

    public function crearChatDifusion($id_crea, $id_amigo)
    {
      $chatExistente = DB::table('chats')
      ->where(function($query) use ($id_crea, $id_amigo) {
        $query->where('id_crea', $id_crea)
              ->where('id_amigo', $id_amigo);
      })
      ->orWhere(function($query) use ($id_crea, $id_amigo) {
        $query->where('id_crea', $id_amigo)
              ->where('id_amigo', $id_crea);
      })
      ->first();

      if ($chatExistente) {
        return $chatExistente->id;
      } else {
        $chatId = DB::table('chats')->insertGetId([
            'id_crea' => $id_crea,  
            'id_amigo' => $id_amigo,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $chatId;
      } 
        
    }

    public function guardarMensajeDifusion($id_crea, $id_chat, $mensaje)
    {
        $fecha = Carbon::now('America/Bogota')->format('Y-m-d');
        $hora = Carbon::now('America/Bogota')->format('g:i:s A');
        
        $mensaje = DB::table('mensajes')->insert([
            'id_chat' => $id_chat,
            'id_crea' => $id_crea,
            'mensaje' => $mensaje,
            'tiene_archivo' => 0,
            'fecha' => $fecha,
            'hora' => $hora,
            'tipo_archivo' => 'no'
        ]);

        return $mensaje;
    }


    public function cargarcsv()
    {
        $path = public_path('libro1.csv');

        $file = fopen($path, 'r');
        while (($line = fgetcsv($file, 1000, ">")) !== FALSE) {
            $line = explode(";", $line[0]);
            $data[] = $line;
        }

        $data = array_slice($data, 1);
        fclose($file);

        
        foreach ($data as $key) {
            DB::table('users')->insert([
                'name' => $key[0],
                'email' => $key[1],
                'empresa' => $key[2],
                'password' => $key[3],
                'last_seen' => $key[4],
                'avatar' => $key[5],
                'genero' => $key[6],
                'online' => $key[7],
                'primera_vez' => $key[8]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuarios cargados exitosamente'
        ], 200);
    }
}
