<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = DB::table('users')
                ->select('id', 'name', 'email', 'avatar', 'last_seen', 'empresa', 'online')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->avatar ?? 'https://i.pravatar.cc/150?img=' . $user->id,
                        'online' => $this->isOnline($user->online),
                        'lastSeen' => $this->formatLastSeen($user->last_seen),
                        'empresa' => $user->empresa,
                        'email' => $user->email
                    ];
                });

            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener usuarios'], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $query = $request->input('query');
            
            $users = DB::table('users')
                ->select('id', 'name', 'email', 'avatar', 'last_seen', 'online', 'empresa')
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->avatar ?? 'https://i.pravatar.cc/150?img=' . $user->id,
                        'online' => $this->isOnline($user->online),
                        'lastSeen' => $this->formatLastSeen($user->last_seen),
                        'empresa' => $user->empresa,
                        'email' => $user->email
                    ];
                });

            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al buscar usuarios'], 500);
        }
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

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'empresa' => 'required|string|max:255',
            'avatar' => 'required|string|in:masculino,femenino,otro',
            'password' => 'required|string|min:8',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El correo electrónico debe ser válido',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'empresa.required' => 'La empresa es obligatoria',
            'avatar.required' => 'El género es obligatorio',
            'avatar.in' => 'El género seleccionado no es válido',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            
            $user = DB::table('users')->insert([
                'name' => $request->name,
                'email' => $request->email,
                'empresa' => $request->empresa,
                'avatar' => $request->avatar,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => '¡Usuario registrado exitosamente!'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar el usuario. Por favor, intente nuevamente.'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ], [
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El correo electrónico debe ser válido',
            'password.required' => 'La contraseña es obligatoria'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $user = DB::table('users')
                ->where('email', $request->email)
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Credenciales inválidas'
                ], 401);
            }

            // Actualizar last_seen
            DB::table('users')
                ->where('id', $user->id)
                ->update(['last_seen' => now(), 'online' => 1]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login exitoso',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'empresa' => $user->empresa,
                    'avatar' => $user->avatar ?? 'https://i.pravatar.cc/150?img=' . $user->id,
                    'primera_vez' => $user->primera_vez
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al iniciar sesión. Por favor, intente nuevamente.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $id = $request->id;
        DB::table('users')
            ->where('id', $id)
            ->update(['last_seen' => now(), 'online' => 0]);

        try {
            // Limpiar la sesión
            $request->session()->flush();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Sesión cerrada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cerrar sesión. Por favor, intente nuevamente.'
            ], 500);
        }
    }

    public function actualizarPerfil(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'empresa' => 'required|string|max:255',
            'avatar' => 'required|string|in:masculino,femenino,otro',
        ], [
            'id.required' => 'El ID es obligatorio',
            'id.integer' => 'El ID debe ser un número entero',
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El correo electrónico debe ser válido',
            'empresa.required' => 'La empresa es obligatoria',
            'avatar.required' => 'El género es obligatorio',
            'avatar.in' => 'El género seleccionado no es válido',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 200);    
        }

        try {

            if($request->password){
                if(strlen($request->password) < 8){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'La contraseña debe tener al menos 8 caracteres'
                    ], 200);
                }  

                $password = Hash::make($request->password);
                $data = [
                    'name' => $request->name,   
                    'email' => $request->email,         
                    'empresa' => $request->empresa,
                    'password' => $password,
                    'avatar' => $request->avatar,
                ];
            }else{
                $data = [
                    'name' => $request->name,   
                    'email' => $request->email,         
                    'empresa' => $request->empresa,
                    'avatar' => $request->avatar,
                ];
            }

            $user = DB::table('users')
                ->where('id', $request->id)
                ->update($data);

            if($user){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Perfil actualizado exitosamente, puede cerrar la ventana.'
                ], 200);
            }else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al actualizar el perfil. Por favor, intente nuevamente.'
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el perfil. Por favor, intente nuevamente.'
            ], 200);
        }
    }

    public function misDatos(Request $request)
    {
        $id = $request->id;
        $user = DB::table('users')->where('id', $id)->first();

        $user = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'empresa' => $user->empresa,
            'avatar' => $user->avatar,
            'primera_vez' => $user->primera_vez
        ];
        
        if($user){
            return response()->json([
                'status' => 'success',
                'data' => $user
            ], 200);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los datos del usuario. Por favor, intente nuevamente.'
            ], 500);
        }
    }

    public function actualizarDatos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'password' => 'required|string|min:8',
            'empresa' => 'required|string|max:255', 
            'avatar' => 'required|string|in:masculino,femenino,otro',
        ], [
            'id.required' => 'El ID es obligatorio',
            'id.integer' => 'El ID debe ser un número entero',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'empresa.required' => 'La empresa es obligatoria',
            'avatar.required' => 'El género es obligatorio',
            'avatar.in' => 'El género seleccionado no es válido',
        ]);

        if ($validator->fails()) {
            return response()->json([   
                'status' => 'error',    
                'message' => $validator->errors()->first()
            ], 200);
        }

        try {   
            $user = DB::table('users')
                ->where('id', $request->id)
                ->update([
                    'password' => Hash::make($request->password),
                    'empresa' => $request->empresa,
                    'avatar' => $request->avatar,
                    'primera_vez' => 1
                ]);

            if($user){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Datos actualizados exitosamente, puede cerrar la ventana.'
                ], 200);
            }else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al actualizar los datos. Por favor, intente nuevamente.'
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar los datos. Por favor, intente nuevamente.'
            ], 200);
        }
    }
} 
