<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiMockController extends Controller
{
    public function digital(): JsonResponse { return response()->json(['endpoint' => 'digital']); }
    public function obterClientes(): JsonResponse { return response()->json(['endpoint' => 'obter-clientes']); }
    public function opcoesParcelamento(): JsonResponse { return response()->json(['endpoint' => 'opcoes-parcelamento']); }
    public function contratarRenegociacao(): JsonResponse { return response()->json(['endpoint' => 'contratar-renegociacao']); }
    public function contratarRenegociacaoTradicional(): JsonResponse { return response()->json(['endpoint' => 'contratar-renegociacao-tradicional']); }
    public function obterClientesCarteira(): JsonResponse { return response()->json(['endpoint' => 'obter-clientes-carteira']); }
    public function obterOpcoesParcelamento(): JsonResponse { return response()->json(['endpoint' => 'obter-opcoes-parcelamento']); }
    public function contratarRenegociacaoCobrancasExternas(): JsonResponse { return response()->json(['endpoint' => 'contratar-renegociacao-cobrancas-externas']); }
    public function obterStatusContato(): JsonResponse { return response()->json(['endpoint' => 'obter-status-contato']); }
    public function gravarOcorrenciaTerceirizadas(): JsonResponse { return response()->json(['endpoint' => 'gravar-ocorrencia-terceirizadas']); }
    public function obterRemocaoClientes(): JsonResponse { return response()->json(['endpoint' => 'obter-remocao-clientes']); }
    public function obterDocumentosQuitados(): JsonResponse { return response()->json(['endpoint' => 'obter-documentos-quitados']); }
    public function obterDocumentosAberto(): JsonResponse { return response()->json(['endpoint' => 'obter-documentos-aberto']); }
    public function obterBoletosBase64(): JsonResponse { return response()->json(['endpoint' => 'obter-boletos-base64']); }
    public function obterAcordosPorCliente(): JsonResponse { return response()->json(['endpoint' => 'obter-acordos-por-cliente']); }
    public function cancelarRenegociacao(): JsonResponse { return response()->json(['endpoint' => 'cancelar-renegociacao']); }
    public function obterPixPrimeiraParcela(): JsonResponse { return response()->json(['endpoint' => 'obter-pix-primeira-parcela']); }
    public function obterHistoricoOcorrenciaPessoa(): JsonResponse { return response()->json(['endpoint' => 'obter-historico-ocorrencia-pessoa']); }
    public function obterPixDocumentosAberto(): JsonResponse { return response()->json(['endpoint' => 'obter-pix-documentos-aberto']); }
    public function cobrancaExternaOperador(): JsonResponse { return response()->json(['endpoint' => 'cobranca-externa-operador']); }
    public function cadastrarOperador(): JsonResponse { return response()->json(['endpoint' => 'cadastrar-operador']); }
    public function buscarOperador(): JsonResponse { return response()->json(['endpoint' => 'buscar-operador']); }
    public function atualizarOperador(): JsonResponse { return response()->json(['endpoint' => 'atualizar-operador']); }
    public function atualizaStatus(): JsonResponse { return response()->json(['endpoint' => 'atualiza-status']); }
}
