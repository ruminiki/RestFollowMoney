<?php

use \Models\CreditCard as CreditCard;
use \Models\CreditCardInvoice as CreditCardInvoice;
use \Models\Movement as Movement;
use \App\Util\DateUtil as DateUtil;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Illuminate\Database\Capsule\Manager as DB;

$app->get('/creditCards/user/{user}', function (Request $request, Response $response) use ($app){
    $creditCards = CreditCard::where('usuario', $request->getAttribute('user'))->orderBy('descricao')->get();

    $reference = DateUtil::mesReferenciaFromDateString(date('Y-m-d', strtotime(date('Ymd') . "+1 months")));

    foreach ($creditCards as $creditCard) {
        
        $invoice = CreditCardInvoice::whereRaw('cartaoCredito = ? and mesReferencia = ?', [$creditCard->id, $reference])->first();

        $movements = DB::table('movimento')
            ->join('movimentosFatura', 'movimentosFatura.movimento', '=', 'movimento.id')
            ->where('movimentosFatura.fatura', $invoice->id)
            ->select('movimento.operacao', 'movimento.valor')
            ->get();

        $credit = $movements->where('operacao', Movement::CREDIT)->sum('valor');
        $debit = $movements->where('operacao', Movement::DEBIT)->sum('valor');
        
        $creditCard->currentInvoice = $debit - $credit;

    }

    return $creditCards->toJson();
});

$app->get('/creditCards/{id}', function(Request $request, Response $response) use ($app){
    $creditCard = CreditCard::find($request->getAttribute('id'));
    return $creditCard->toJson();
});
 
$app->post('/creditCards', function(Request $request, Response $response) use ($app){
    try{
        $data = json_decode($request->getBody(), false);

        $cc = new CreditCard();
        $cc->descricao      = $data->descricao;
        $cc->limite         = $data->limite;
        $cc->dataFatura     = $data->dataFatura;
        $cc->dataFechamento = $data->dataFechamento;
        $cc->usuario        = $data->usuario;

        $cc->save();

        return $response->withJson($cc, 201);
    }catch(Exception $e){
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }
    
});
 
$app->put('/creditCards/{id}', function(Request $request, Response $response) use ($app){
    try{
        $data = json_decode($request->getBody(), false);

        $cc = CreditCard::find($data->id);
        $cc->descricao      = $data->descricao;
        $cc->limite         = $data->limite;
        $cc->dataFatura     = $data->dataFatura;
        $cc->dataFechamento = $data->dataFechamento;
        $cc->usuario        = $data->usuario;

        $cc->save();

        return $response->withJson($cc, 201);
    }catch(Exception $e){
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }
});

$app->delete('/creditCards/{id}', function(Request $request, Response $response) use ($app){
    return $response->withJson(CreditCard::destroy($request->getAttribute('id')), 201);
});
