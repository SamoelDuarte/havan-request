<?php

namespace App\Services;

use Illuminate\Http\Request;

class HavanAuth
{
    public static function check(Request $request)
    {
        // Pega o token enviado via header Authorization: Bearer <token>
        $token = $request->bearerToken();

        // Compara de forma segura com o token do .env
        if (!$token || !hash_equals(env('HAVAN_TOKEN'), $token)) {
            abort(401, 'Token inválido');
        }

        return true;
    }
}
