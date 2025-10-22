<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HavanAuth;

class HavanRequestController extends Controller
{
    public function __construct(Request $request)
    {
        // Toda requisição que entrar neste controller precisa de token válido
        HavanAuth::check($request);
    }

    public function handle(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Token válido, request aceita!'. $this->gerarToken(),
            'data' => $request->all()
        ]);
    }

    function gerarToken()
    {
        // Inicializa a sessão cURL
        $curl = curl_init();

        // Configurações da requisição cURL
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://cobrancaexternaauthapi.apps.havan.com.br/token', // URL do endpoint de autenticação
            CURLOPT_RETURNTRANSFER => true, // Retorna a resposta como string
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST', // Tipo da requisição
            CURLOPT_POSTFIELDS => 'grant_type=password'
                . '&client_id=' . env('HAVAN_CLIENT_ID')
                . '&username=' . env('HAVAN_USERNAME')
                . '&password=' . env('HAVAN_PASSWORD'), // Parâmetros do POST
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded' // Tipo de conteúdo
            ),
        ));

        // Executa a requisição e captura a resposta
        $response = curl_exec($curl);

        // Verifica se ocorreu erro na requisição cURL
        if ($response === false) {
            echo 'Erro cURL: ' . curl_error($curl);
            return null;
        }

        // Fecha a sessão cURL
        curl_close($curl);

        // Converte a resposta JSON para um array PHP
        $responseData = json_decode($response, true);
        // Verifica se a resposta contém o token
        if (isset($responseData['access_token'])) {
            return $responseData['access_token'];
        } else {
            echo 'Erro ao obter o token: ' . $responseData;
            return null;
        }
    }
}
