<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GrupoController extends Controller
{
    public function crearGrupo(Request $request)
    {
        $id_crea = $request->id_crea;
        $nombre = $request->nombre;
        $id_amigos = $request->id_amigos;

        $id_amigos = explode(',', $id_amigos);

        $fecha = Carbon::now()->setTimezone('America/Bogota')->format('Y-m-d');
        $hora = Carbon::now()->setTimezone('America/Bogota')->format('g:i:s A');

        $grupo = DB::table('grupos')->insertGetId([
            'id_crea' => $id_crea,
            'nombre' => $nombre,
            'fecha' => $fecha,
            'hora' => $hora
        ]);

        if($grupo){
            DB::table('grupos_usuarios')->insert([
                'id_grupo' => $grupo,
                'id_usuario' => $id_crea
            ]);

            foreach ($id_amigos as $id_amigo) {
                DB::table('grupos_usuarios')->insert([
                    'id_grupo' => $grupo,
                    'id_usuario' => $id_amigo
                ]);
            } 

            return response()->json([
                'success' => true,
                'message' => 'Grupo creado correctamente'
            ]);

        }else{
            return response()->json([
                'success' => false,
                'message' => 'Error al crear grupo'
            ]);
        }       
    }

    public function obtenerGruposUsuario(Request $request)
    {
        $id = $request->id;

        $ids_grupos = DB::table('grupos_usuarios')->where('id_usuario', $id)->orderBy('id_grupo', 'desc')->get();

        foreach ($ids_grupos as $grupo) {
            $grupo->detalle_grupo = DB::table('grupos')->where('id', $grupo->id_grupo)->first();
            $grupo->participantes = DB::table('grupos_usuarios')->where('id_grupo', $grupo->id_grupo)->count();
            $grupo->es_admin = $grupo->detalle_grupo->id_crea == $id ? true : false;
            
            $grupo->detalle_participantes = [];

            $grupo_detalle_participantes = DB::table('grupos_usuarios')->where('id_grupo', $grupo->id_grupo)->get();
            foreach ($grupo_detalle_participantes as $participante) {
                $estado = $participante->estado;
                $participante = DB::table('users')->where('id', $participante->id_usuario)->first();
                $participante->estado = $estado;
                $participante->es_admin = $participante->id == $grupo->detalle_grupo->id_crea ? true : false;
                $grupo->detalle_participantes[] = $participante;
            }

            $grupo->ultimo_mensaje = DB::table('mensajes_grupo')
            ->join('users', 'mensajes_grupo.id_crea', '=', 'users.id')
            ->select('mensajes_grupo.*', 'users.name as nombre_usuario', 'users.empresa as empresa_usuario')
            ->where('id_grupo', $grupo->id_grupo)
            ->orderBy('id', 'desc')
            ->first();
        }

        $ids_grupos = collect($ids_grupos)->sortByDesc(function($grupo) {
            if ($grupo->ultimo_mensaje) {
                return $grupo->ultimo_mensaje->id;
            }
        })->values()->all();


        return response()->json([
            'success' => true,
            'grupos' => $ids_grupos
        ]);
    }

    public function guardarMensaje(Request $request)
    {
        $id = $request->id;
        $grupo_id = $request->grupo_id;
        $mensaje = $request->mensaje;
        $tipo = $request->tipo;

        if($tipo == 'archivo'){
            try {
                $peso_archivo = $this->convertirTamaño($request->archivo->getSize());
                $archivo = $request->archivo;
                $nombre_archivo = time() . '_' . $archivo->getClientOriginalName();
                $ruta_archivo = $archivo->move(public_path('archivos'), $nombre_archivo);
                $mensaje = $nombre_archivo;
                $tipo_archivo = $archivo->getClientOriginalExtension();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar el archivo: ' . $e->getMessage()
                ], 200);
            }
        }
        
        $fecha = Carbon::now()->setTimezone('America/Bogota')->format('Y-m-d');
        $hora = Carbon::now()->setTimezone('America/Bogota')->format('g:i:s A');

        $mensaje = DB::table('mensajes_grupo')->insertGetId([
            'id_crea' => $id,
            'id_grupo' => $grupo_id,
            'mensaje' => $mensaje,
            'tiene_archivo' => $tipo == 'archivo' ? 1 : 0,
            'tipo_archivo' =>  $tipo == 'archivo' ? $tipo_archivo : "no",
            'fecha' => $fecha,
            'hora' => $hora,
            'peso' => $tipo == 'archivo' ? $peso_archivo : 0
        ]);

        if($mensaje){
            return response()->json([
                'success' => true,
                'message' => 'Mensaje guardado correctamente'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar mensaje'
            ]);
        }
    }

    function convertirTamaño($bytes) {
        $kb = 1024;
        $mb = 1024 * $kb;
        $gb = 1024 * $mb;
    
        if ($bytes < $kb) {
            return $bytes . ' B';
        } elseif ($bytes < $mb) {
            return round($bytes / $kb, 2) . ' KB';
        } elseif ($bytes < $gb) {
            return round($bytes / $mb, 2) . ' MB';
        } else {
            return round($bytes / $gb, 2) . ' GB';
        }
    }
    
    public function obtenerMensajesGrupo(Request $request)
    {
        $id = $request->id;
        $grupo_id = $request->grupo_id;

        $mensajes = DB::table('mensajes_grupo')->where('id_grupo', $grupo_id)->orderBy('id', 'asc')->get();

        foreach ($mensajes as $mensaje) {
            $mensaje->fecha_mensaje = Carbon::parse($mensaje->fecha)->format('d/m/Y');
            $mensaje->hora_mensaje = Carbon::parse($mensaje->hora)->format('g:i A');
            $mensaje->is_mine = $mensaje->id_crea == $id ? true : false;

            $usuario = DB::table('users')->where('id', $mensaje->id_crea)->first();
            $mensaje->usuario = $usuario->name . ' - ' . $usuario->empresa;
            $mensaje->avatar = $usuario->avatar;
        }


        return response()->json([
            'success' => true,
            'mensajes' => $mensajes
        ]);
    }
    
    public function eliminarParticipante(Request $request)
    {
        $id_crea = $request->id_crea;
        $id_grupo = $request->id_grupo;
        $id_participante = $request->id_participante;

        $eliminado = DB::table('grupos_usuarios')->where('id_grupo', $id_grupo)
        ->where('id_usuario', $id_participante)
        ->update([
            'estado' => 0
        ]);

        if($eliminado){
            return response()->json([
                'success' => true,
                'message' => 'Participante eliminado correctamente'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar participante'
            ]);
        }
    }

    public function reactivarParticipante(Request $request)
    {
        $id_crea = $request->id_crea;
        $id_grupo = $request->id_grupo;
        $id_participante = $request->id_participante;
        
        $reactivado = DB::table('grupos_usuarios')->where('id_grupo', $id_grupo)
        ->where('id_usuario', $id_participante)
        ->update([
            'estado' => 1
        ]); 

        if($reactivado){
            return response()->json([
                'success' => true,
                'message' => 'Participante reactivado correctamente'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar participante'
            ]);
        }
    }

    public function agregarParticipantes(Request $request)
    {
        $id_crea = $request->id_crea;
        $id_grupo = $request->id_grupo;
        $id_participantes = $request->id_participantes;
        
        $id_participantes = explode(',', $id_participantes);

        foreach ($id_participantes as $id_participante) {
            DB::table('grupos_usuarios')->insert([
                'id_grupo' => $id_grupo,
                'id_usuario' => $id_participante
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Participantes agregados correctamente'
        ]); 
    }

    public function editarGrupo(Request $request)
    {
        $id_grupo = $request->id_grupo;
        $nombre = $request->nombre;
     
        $actualizado = DB::table('grupos')->where('id', $id_grupo)->update([
            'nombre' => $nombre
        ]);

        if($actualizado){
            return response()->json([
                'success' => true,
                'message' => 'Grupo actualizado correctamente'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar grupo'
            ]);
        }
    }
}

