<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    function index(Request $request) {

      if ($request->isJson()) {
        $users = User::all();
        return response()->json($users, 200);
      }

      return response()->json(['error' => 'No autorizado'], 401, []);
    }

    function createUser(Request $peticion) {

      if (!$peticion->isJson()) {
          return response()->json(['error' => 'No autorizado'], 401, []);
      } else {

        // Tomo todos los datos de la peticion
        $data = $peticion->json()->all();

        $usuario = User::create([
          'name' => $data['name'],
          'username' => $data['username'],
          'email' => $data['email'],
          'password' => Hash::make($data['password']),
          'api_token' => Str::random(60)
        ]);

        // Si el recurso se creo exitosamente, devuelvo el usuario en formato json y el coddigo 201 (recurso creado)
        return response()->json($data, 201);
      }
    }

    public function getUser(Request $request, $id) {
        if ($request->isJson()) {

            try {
                $user = User::with(['issues_assigned_to_me', 'issues_reported_to_me'])->findOrFail($id);

                return response()->json($user, 200);
            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'No content'], 406);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401, []);
        }
    }

    function getToken(Request $peticion) {

      if (!$peticion->isJson()) {
        return response()->json(['error' => 'No autorizado'], 401, []);
      } else {
        try {
          $data = $peticion->json()->all();

          // Uso el metodo where del modelo User para buscar si existe un usuario con el email proporcionado
          $usuario = User::where('email', $data['email'])->first();

          if ($usuario && Hash::check($data['password'], $usuario->password)) {
            // Devuelvo el usuario completo, incluido el api_token
            return response()->json($usuario, 200);
          } else {
            return response()->json(['error' => 'Acceso incorrecto'], 406);
          }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Acceso incorrecto'], 406);
        }
      }

    }

}
