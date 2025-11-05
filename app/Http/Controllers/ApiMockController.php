<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class ApiMockController extends Controller
{
    public function obterClientes(): JsonResponse
    {
        return response()->json(['endpoint' => 'obter-clientes']);
    }
    public function opcoesParcelamento(): JsonResponse
    {
        return response()->json(['endpoint' => 'opcoes-parcelamento']);
    }
    public function contratarRenegociacao(): JsonResponse
    {
        $request = request();
        $codigoCarteiraCobranca = $request->input('codigoCarteiraCobranca');
        $codigoUsuarioCarteiraCobranca = $request->input('codigoUsuarioCarteiraCobranca');
        $gerarBoleto = $request->input('gerarBoleto');
        $chave = env('HAVAN_API_PASSWORD');
        $hash = $request->input('hash');
        $token = $this->gerarToken();

        if (!is_numeric($codigoCarteiraCobranca) || intval($codigoCarteiraCobranca) <= 0) {
            return response()->json([
                'error' => 'O parâmetro "codigoCarteiraCobranca" deve ser um inteiro válido maior que zero.'
            ], 400);
        }
        if (!is_numeric($codigoUsuarioCarteiraCobranca) || intval($codigoUsuarioCarteiraCobranca) <= 0) {
            return response()->json([
                'error' => 'O parâmetro "codigoUsuarioCarteiraCobranca" deve ser um inteiro válido maior que zero.'
            ], 400);
        }
        if ($gerarBoleto === null) {
            return response()->json([
                'error' => 'O parâmetro "gerarBoleto" é obrigatório.'
            ], 400);
        }
        if (!$chave) {
            return response()->json([
                'error' => 'Chave não encontrada nas variáveis de ambiente.'
            ], 400);
        }
        if (!$hash) {
            return response()->json([
                'error' => 'O parâmetro "hash" é obrigatório.'
            ], 400);
        }
        if (!$token) {
            return response()->json([
                'error' => 'Token de autenticação não gerado.'
            ], 401);
        }

        $body = [
            'codigoCarteiraCobranca' => (int) $codigoCarteiraCobranca,
            'codigoUsuarioCarteiraCobranca' => (int) $codigoUsuarioCarteiraCobranca,
            'gerarBoleto' => $gerarBoleto,
            'chave' => $chave,
            'hash' => $hash
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->post('https://cobrancaexternaapi.apps.havan.com.br/api/v3/CobrancaExternaDigital/ContratarRenegociacao', [
                'headers' => [
                    'Accept' => 'text/plain',
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ],
                'json' => $body
            ]);
            $data = json_decode($res->getBody()->getContents(), true);
            return response()->json($data, $res->getStatusCode());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = $response ? $response->getBody()->getContents() : null;
            return response()->json([
                'error' => 'Erro ao consultar API externa',
                'message' => $e->getMessage(),
                'api_response' => $body
            ], $response ? $response->getStatusCode() : 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao consultar API externa',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function obterClientesCarteira(): JsonResponse
    {
        return response()->json(['endpoint' => 'obter-clientes-carteira']);
    }
    public function contratarRenegociacaoTradicional(): JsonResponse
    {
        return response()->json(['endpoint' => 'contratar-renegociacao-tradicional']);
    }
    public function obterOpcoesParcelamento(Request $request): JsonResponse
    {
        try {
            $token = $this->gerarToken();
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao obter token de autenticação'
                ], 401);
            }

            // Preparar dados da requisição
            $requestData = $request->all();
            
            // Adicionar chave se não estiver presente
            if (!isset($requestData['chave'])) {
                $requestData['chave'] = env('HAVAN_API_PASSWORD');
            }

            // Validações obrigatórias
            if (empty($requestData['codigoUsuarioCarteiraCobranca']) || empty($requestData['codigoCarteiraCobranca']) || 
                empty($requestData['pessoaCodigo']) || empty($requestData['dataPrimeiraParcela'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parâmetros obrigatórios faltando: codigoUsuarioCarteiraCobranca, codigoCarteiraCobranca, pessoaCodigo, dataPrimeiraParcela'
                ], 400);
            }

            // Converter pessoaCodigo para string (remove aspas simples se houver)
            $requestData['pessoaCodigo'] = (string) str_replace("'", "", $requestData['pessoaCodigo']);
            $requestData['codigoUsuarioCarteiraCobranca'] = (int) $requestData['codigoUsuarioCarteiraCobranca'];
            $requestData['codigoCarteiraCobranca'] = (int) $requestData['codigoCarteiraCobranca'];

            // Adicionar valorEntrada se não estiver presente (valor padrão 0)
            if (!isset($requestData['valorEntrada'])) {
                $requestData['valorEntrada'] = 0;
            }

            // Adicionar renegociaSomenteDocumentosEmAtraso se não estiver presente
            if (!isset($requestData['renegociaSomenteDocumentosEmAtraso'])) {
                $requestData['renegociaSomenteDocumentosEmAtraso'] = false;
            }

            // Fazer requisição para a API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])
            ->timeout(60)
            ->post('https://cobrancaexternaapi.apps.havan.com.br/api/v3/CobrancaExternaTradicional/ObterOpcoesParcelamento', $requestData);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Se há múltiplas alçadas, pegar sempre a mais barata (menor valor à vista)
                if (is_array($responseData) && count($responseData) > 1) {
                    $alcadaMaisBarata = null;
                    $menorValor = PHP_FLOAT_MAX;
                    
                    // Encontrar a alçada com menor valor à vista (1 parcela)
                    foreach ($responseData as $alcada) {
                        if (isset($alcada['parcelamento'][0]['valorTotal'])) {
                            $valorAvista = $alcada['parcelamento'][0]['valorTotal'];
                            if ($valorAvista < $menorValor) {
                                $menorValor = $valorAvista;
                                $alcadaMaisBarata = $alcada;
                            }
                        }
                    }
                    
                    // Se encontrou a mais barata, usar ela; senão usar a última
                    $alcadaSelecionada = $alcadaMaisBarata ?? end($responseData);
                    $responseData = [$alcadaSelecionada];
                }
                
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erro na API da Havan',
                'details' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error_details' => $e->getMessage()
            ], 500);
        }
    }
    function gerarToken()
    {
        // Inicializa a sessão cURL
        $curl = curl_init();

        // Configurações da requisição cURL
        $clientId = env('HAVAN_CLIENT_ID');
        $username = env('HAVAN_API_USERNAME');
        $password = env('HAVAN_API_PASSWORD');
        $postFields = 'grant_type=password&client_id=' . $clientId . '&username=' . $username . '&password=' . $password;
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://cobrancaexternaauthapi.apps.havan.com.br/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
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
            echo 'Erro ao obter o token: ' . json_encode($responseData);
            return null;
        }
    }
    public function obterStatusContato(Request $request): JsonResponse
    {
        $codigoCarteiraCobranca = $request->input('codigoCarteiraCobranca');
        $usuario = env('HAVAN_API_USERNAME');
        $chave = env('HAVAN_API_PASSWORD');
        $token = $this->gerarToken();

        if (!is_numeric($codigoCarteiraCobranca) || intval($codigoCarteiraCobranca) <= 0) {
            return response()->json([
                'error' => 'O parâmetro "codigoCarteiraCobranca" deve ser um inteiro válido maior que zero.'
            ], 400);
        }
        if (!$codigoCarteiraCobranca) {
            return response()->json([
                'error' => 'O parâmetro "codigoCarteiraCobranca" é obrigatório.'
            ], 400);
        }
        if (!$token) {
            return response()->json([
                'error' => 'Token de autenticação não gerado.'
            ], 401);
        }

        $body = [
            'codigoCarteiraCobranca' => (int) $codigoCarteiraCobranca,
            'usuario' => $usuario,
            'chave' => $chave
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->post('https://cobrancaexternaapi.apps.havan.com.br/api/v3/CobrancaExterna/ObterStatusDeContato', [
                'headers' => [
                    'Accept' => 'text/plain',
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ],
                'json' => $body
            ]);
            $data = json_decode($res->getBody()->getContents(), true);
            return response()->json($data, $res->getStatusCode());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = $response ? $response->getBody()->getContents() : null;
            return response()->json([
                'error' => 'Erro ao consultar API externa',
                'message' => $e->getMessage(),
                'api_response' => $body
            ], $response ? $response->getStatusCode() : 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao consultar API externa',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function gravarOcorrenciaTerceirizadas(): JsonResponse
    {
        return response()->json(['endpoint' => 'gravar-ocorrencia-terceirizadas']);
    }
    public function obterRemocaoClientes(): JsonResponse
    {
        return response()->json(['endpoint' => 'obter-remocao-clientes']);
    }
    public function obterDocumentosQuitados(): JsonResponse
    {
        return response()->json(['endpoint' => 'obter-documentos-quitados']);
    }
    public function obterDocumentosAberto(Request $request): JsonResponse
    {
        $codigoCliente = $request->input('codigoCliente');
        $cpf = $request->input('cpf');
        $codigoCarteiraCobranca = $request->input('codigoCarteiraCobranca');
        $chave = env('HAVAN_API_PASSWORD');
        $token = $this->gerarToken();

        if (!$cpf && !$codigoCliente) {
            return response()->json([
                'error' => 'Informe o CPF ou o código da pessoa.'
            ], 400);
        }
        if (!$token) {
            return response()->json([
                'error' => 'Token de autenticação não gerado.'
            ], 401);
        }

        $body = [
            'pessoaCodigo' => $codigoCliente,
            'cpf' => $cpf,
            'codigoCarteiraCobranca' => $codigoCarteiraCobranca,
            'chave' => $chave
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->post('https://cobrancaexternaapi.apps.havan.com.br/api/v3/CobrancaExterna/ObterBoletosDocumentosEmAberto', [
                'headers' => [
                    'Accept' => 'text/plain',
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ],
                'json' => $body
            ]);
            $data = json_decode($res->getBody()->getContents(), true);
            // Se a resposta for o erro esperado, retorna JSON padronizado
            if (is_array($data) && isset($data[0]['text']) && $data[0]['text'] === 'Nenhum boleto encontrado.') {
                return response()->json(['mensagem' => 'nenhum boleto encontrado']);
            }
            return response()->json($data, $res->getStatusCode());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = $response ? $response->getBody()->getContents() : null;
            $data = json_decode($body, true);
            if (is_array($data) && isset($data[0]['text']) && $data[0]['text'] === 'Nenhum boleto encontrado.') {
                return response()->json(['mensagem' => 'nenhum boleto encontrado']);
            }
            return response()->json([
                'error' => 'Erro ao consultar API externa',
                'message' => $e->getMessage(),
                'api_response' => $body
            ], $response ? $response->getStatusCode() : 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao consultar API externa',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function obterBoletosBase64(Request $request): JsonResponse
    {
        $urlParam = $request->input('url');
        $token = $this->gerarToken();

        if (!$urlParam) {
            return response()->json([
                'error' => 'O parâmetro "url" é obrigatório.'
            ], 400);
        }
        if (!$token) {
            return response()->json([
                'error' => 'Token de autenticação não gerado.'
            ], 401);
        }

        $url = 'https://cobrancaexternaapi.apps.havan.com.br/api/v3/CobrancaExterna/ObterBoletoBase64?url=' . urlencode($urlParam);

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->get($url, [
                'headers' => [
                    'Accept' => 'text/plain',
                    'Authorization' => 'Bearer ' . $token,
                ]
            ]);
            $data = $res->getBody()->getContents();
            // Se a resposta for uma string base64, retorna como campo base64
            return response()->json(['base64' => $data], $res->getStatusCode());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = $response ? $response->getBody()->getContents() : null;
            return response()->json([
                'error' => 'Erro ao consultar API externa',
                'message' => $e->getMessage(),
                'api_response' => $body
            ], $response ? $response->getStatusCode() : 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao consultar API externa',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function obterAcordosPorCliente(Request $request)
    {
        $codigoCliente = $request->input('codigoCliente');
        $chave = env('HAVAN_API_PASSWORD');
        $token = $this->gerarToken();
        if (!is_numeric($codigoCliente) || intval($codigoCliente) <= 0) {
            return response()->json([
                'error' => 'O parâmetro "codigoCliente" deve ser um inteiro válido maior que zero.'
            ], 400);
        }
        if (!$token) {
            return response()->json([
                'error' => 'Token de autenticação não gerado.'
            ], 401);
        }

        $url = 'https://cobrancaexternaapi.apps.havan.com.br/api/v3/CobrancaExterna/ObterAcordosPorCliente?codigoCliente=' . intval($codigoCliente) . '&chave=' . urlencode($chave);

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->get($url, [
                'headers' => [
                    'Accept' => 'text/plain',
                    'Authorization' => 'Bearer ' . $token,
                ]
            ]);
            $data = json_decode($res->getBody()->getContents(), true);
            // Se a resposta for o erro esperado, retorna JSON padronizado
            if (is_array($data) && isset($data[0]['text']) && $data[0]['text'] === 'Nenhum acordo encontrado.') {
                return response()->json(['mensagem' => 'nenhumacordo encontrado']);
            }
            return response()->json($data, $res->getStatusCode());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = $response ? $response->getBody()->getContents() : null;
            $data = json_decode($body, true);
            if (is_array($data) && isset($data[0]['text']) && $data[0]['text'] === 'Nenhum acordo encontrado.') {
                return response()->json(['mensagem' => 'nenhumacordo encontrado']);
            }
            return response()->json([
                'error' => 'Erro ao consultar API externa',
                'message' => $e->getMessage(),
                'api_response' => $body
            ], $response ? $response->getStatusCode() : 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao consultar API externa',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function cancelarRenegociacao(): JsonResponse
    {
        return response()->json(['endpoint' => 'cancelar-renegociacao']);
    }
    public function obterPixPrimeiraParcela(): JsonResponse
    {
        return response()->json(['endpoint' => 'obter-pix-primeira-parcela']);
    }
    public function obterHistoricoOcorrenciaPessoa(): JsonResponse
    {
        return response()->json(['endpoint' => 'obter-historico-ocorrencia-pessoa']);
    }
    public function obterPixDocumentosAberto(): JsonResponse
    {
        return response()->json(['endpoint' => 'obter-pix-documentos-aberto']);
    }
    public function cobrancaExternaOperador(): JsonResponse
    {
        return response()->json(['endpoint' => 'cobranca-externa-operador']);
    }
    public function cadastrarOperador(): JsonResponse
    {
        return response()->json(['endpoint' => 'cadastrar-operador']);
    }
    public function buscarOperador(): JsonResponse
    {
        return response()->json(['endpoint' => 'buscar-operador']);
    }
    public function atualizarOperador(): JsonResponse
    {
        return response()->json(['endpoint' => 'atualizar-operador']);
    }
    public function atualizaStatus(): JsonResponse
    {
        return response()->json(['endpoint' => 'atualiza-status']);
    }
}
