<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HavanRequestController;
use App\Http\Controllers\ApiMockController;


Route::get('/obter-clientes', [ApiMockController::class, 'obterClientes']);
Route::get('/opcoes-parcelamento', [ApiMockController::class, 'opcoesParcelamento']);
Route::post('/contratar-renegociacao', [ApiMockController::class, 'contratarRenegociacao']);
Route::get('/contratar-renegociacao-tradicional', [ApiMockController::class, 'contratarRenegociacaoTradicional']);
Route::get('/obter-clientes-carteira', [ApiMockController::class, 'obterClientesCarteira']);
Route::get('/obter-opcoes-parcelamento', [ApiMockController::class, 'obterOpcoesParcelamento']);
Route::post('/contratar-renegociacao-cobrancas-externas', [ApiMockController::class, 'contratarRenegociacaoCobrancasExternas']);
Route::post('/obter-status-contato', [ApiMockController::class, 'obterStatusContato']);
Route::post('/gravar-ocorrencia-terceirizadas', [ApiMockController::class, 'gravarOcorrenciaTerceirizadas']);
Route::get('/obter-remocao-clientes', [ApiMockController::class, 'obterRemocaoClientes']);
Route::get('/obter-documentos-quitados', [ApiMockController::class, 'obterDocumentosQuitados']);
Route::get('/obter-documentos-aberto', [ApiMockController::class, 'obterDocumentosAberto']);
Route::get('/obter-boletos-base64', [ApiMockController::class, 'obterBoletosBase64']);
Route::post('/obter-acordos-por-cliente', [ApiMockController::class, 'obterAcordosPorCliente']);
Route::post('/cancelar-renegociacao', [ApiMockController::class, 'cancelarRenegociacao']);
Route::get('/obter-pix-primeira-parcela', [ApiMockController::class, 'obterPixPrimeiraParcela']);
Route::get('/obter-historico-ocorrencia-pessoa', [ApiMockController::class, 'obterHistoricoOcorrenciaPessoa']);
Route::get('/obter-pix-documentos-aberto', [ApiMockController::class, 'obterPixDocumentosAberto']);
Route::post('/cadastrar-operador', [ApiMockController::class, 'cadastrarOperador']);
Route::get('/buscar-operador', [ApiMockController::class, 'buscarOperador']);
Route::put('/atualizar-operador', [ApiMockController::class, 'atualizarOperador']);
Route::put('/atualiza-status', [ApiMockController::class, 'atualizaStatus']);

Route::post('/endpoint', [HavanRequestController::class, 'handle']);
