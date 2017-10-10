<?php

use \Models\Movement as Movement;
use \Models\CreditCardInvoice as CreditCardInvoice;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/movements/user/{user}/period/{period}', function (Request $request, Response $response) use ($app){
    $movements = Movement::whereRaw("usuario = ? and SUBSTRING(vencimento, 1, 6) = ? and hashTransferencia = '' and fatura is null", 
                                    [$request->getAttribute('user'), $request->getAttribute('period')])
                         ->with('bankAccount')
                         ->with('creditCard')
                         ->with('finality')
                         ->with('invoice')
                         ->get();
    return $movements->toJson();
});

$app->get('/movements/{id}', function(Request $request, Response $response) use ($app){

    $movement = Movement::whereRaw('id = ?', $request->getAttribute('id'))
                 ->with('bankAccount')
                 ->with('creditCard')
                 ->with('finality')
                 ->with('invoice')
                 ->get();
                 
    return $movement->toJson();
});
 
$app->post('/movements', function(Request $request, Response $response) use ($app){

    $data = json_decode($request->getBody(), true);
    $keys = array_keys($data);

    $movement = new Movement();
    foreach ($dados as $key => $value) {
        if ( is_array($value) ){
            $value = $value['id'];
        }
        $movement[$key] = $value;
    }
    $movement->save();

    if ( $movement->creditCard != null && $movement->creditCard->id > 0 ){
        $movement->addToInvoice();
    }

    return $movement->toJson();
});
 
$app->put('/movements/{id}', function(Request $request, Response $response) use ($app){
    global $logger;

    $data = json_decode($request->getBody(), false);
    $keys = array_keys($data);

    $movement = Movement::find($data->id);
    
    foreach ($dados as $key => $value) {
        if ( is_array($value) ){
            $value = $value['id'];
        }
        $movement[$key] = $value;
    }

    $movement->validateUpdateDelete();

    if ( $movement->isInOpenInvoice() ){
        if ( ($movementOld->creditCard->id != $this->creditCard->id) ||
             ($movementOld->vencimento != $this->vencimento) ){
            $logger->addInfo('Movement Update: removendo movimento da fatura anterior.' );
            $movementInvoice = MovementsInvoice::where('movimento', $this->id)->first();
            $movementInvoice->destroy();
            //adiciona a nova invoice
            $movement->addToInvoice();
        }
    }else{
        if ( $movement->creditCard != null && $movement->creditCard->id > 0 ){
            $movement->addToInvoice();
        }
    }

    $movement->update($data);

    return $movement->toJson();

});

$app->delete('/movements/{id}', function(Request $request, Response $response) use ($app){
    
    $movement = Movement::find($request->getAttribute('id'));

    $movement->validateUpdateDelete();

    if ( $movement->isInOpenInvoice() ){
        $movementInvoice = MovementsInvoice::where('movimento', $movement->id)->first();
        $movementInvoice->destroy();
    }
        
    $movement->destroy();

    return $movement->toJson();
    
});

//recupera os movimentos da fatura
$app->get('/movements/invoice/{invoice}', function(Request $request, Response $response) use ($app){
    $movements = CreditCardInvoice::find($request->getAttribute('invoice'))->movements;
    return $movements->toJson();
});

//extrato conta bancária
$app->get('/movements/bankAccount/{bankAccount}/period/{period}', function(Request $request, Response $response) use ($app){

    $bank_account_id = $request->getAttribute('bankAccount');
    $period = $request->getAttribute('period');
    $movements = Movement::whereRaw("contaBancaria = ? and SUBSTRING(vencimento, 1, 6) = ?", [$bank_account_id, $period])
                         ->with(['bankAccount','creditCard','finality','invoice'])->get();
    return $movements->toJson();

});

//saldo anterior
$app->get('/movements/previousBalance/user/{user}/period/{period}', function(Request $request, Response $response) use ($app){

    $user = $request->getAttribute('user');
    $period = $request->getAttribute('period');

    $movements = Movement::whereRaw("usuario = ? and SUBSTRING(vencimento, 1, 6) < ? and hashTransferencia = '' and fatura is null", [$user, $period])->get();
    $credit = $movements->where('operacao', Movement::CREDIT)->sum('valor');
    $debit = $movements->where('operacao', Movement::DEBIT)->sum('valor');

    return $response->withJson($credit - $debit);

});

$app->get('/movements/previousBalance/bankAccount/{bankAccount}/period/{period}', function(Request $request, Response $response) use ($app){

    $bank_account_id = $request->getAttribute('bankAccount');
    $period = $request->getAttribute('period');

    $movements = Movement::whereRaw("usuario = ? and contaBancaria < ? and hashTransferencia = '' and fatura is null", [$user, $bank_account_id])->get();
    $credit = $movements->where('operacao', Movement::CREDIT)->sum('valor');
    $debit = $movements->where('operacao', Movement::DEBIT)->sum('valor');

    return $response->withJson($credit - $debit);

});

