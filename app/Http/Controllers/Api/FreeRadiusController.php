<?php

namespace App\Http\Controllers\Api;

use App\Database;
use App\Helpers\Databases;
use DB;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Radcheck;

class FreeRadiusController  extends Controller
{
    //
    public function getUsers(Request $request) {

        $database = Database::where([
            'host' => $request->host,
            'database' => $request->database,
            ])->first();

        if (!$database) {
            return response()->json([
                'message' => 'No se encontro la base de datos: ' . $request->database . ' en el host: ' . $request->host,
            ], 404);
        }

        Databases::setConnection($database);
        
        $users = Radcheck::all();
        return response()->json([
            'message' => 'OK',
            'data' => $users,
        ]);
    }

    public function getUser(Request $request, $username)
    {
        $database = Database::where([
            'host' => $request->host,
            'database' => $request->database,
            ])->first();

        if (!$database) {
            return response()->json([
                'message' => 'No se encontro la base de datos: ' . $request->database . ' en el host: ' . $request->host,
            ], 404);
        }

        Databases::setConnection($database);

        $user = Radcheck::where('username', $username)->first();

        return response()->json([
            'message' => $user ? 'OK' : 'El suauario no existe!',
            'data' => $user
        ], $user ? 200 : 404);
    }

    public function addUser(Request $request) {

        $database = Database::where([
            'host' => $request->host,
            'database' => $request->database,
            ])->first();

        if (!$database) {
            return response()->json([
                'message' => 'No se encontro la base de datos: ' . $request->database . ' en el host: ' . $request->host,
            ], 404);
        }

        Databases::setConnection($database);

        DB::beginTransaction();

        try {
            $newUser = Radcheck::create([
                'username' => $request->username,
                'attribute' => 'User-Password',
                'opt' => ':=',
                'value' => $request->value, // str_random(10),
            ]);

            $emailTo = '';
            $data = [
                'message' => '<p>usuario: ' . $request->username . '</p><p>clave: ' . $request->value . '</p>',
            ];

            DB::commit();

            return response()->json([
                'message' => 'OK',
                'data' => $newUser
            ], 201);
        } catch (Exception $exception) {

            DB::rollBack();

            return response()->json([
                'message' => $exception->getMessage(),
                'data' => [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ]
            ]);
        }
    }

    public function removeUser(Request $request, $username)
    {
        $database = Database::where([
            'host' => $request->host,
            'database' => $request->database,
            ])->first();

        if (!$database) {
            return response()->json([
                'message' => 'No se encontro la base de datos: ' . $request->database . ' en el host: ' . $request->host,
            ], 404);
        }

        Databases::setConnection($database);

        $user = Radcheck::where('username',  $username)->first();

        if ($user) {
            DB::beginTransaction();

            try {

                $user->delete();

                DB::commit();

                return response()->json([
                    'message' => 'Usuario eliminado!',
                ]);

            } catch (Exception $exception) {

                DB::rollBack();
                return response()->json([
                    'message' => $exception->getMessage(),
                    'data' => [
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                    ]
                ]);
            }
        }

        return response()->json([
            'message' => 'El usuario no existe!',
            'data' => null
        ], 404);
    }
}
