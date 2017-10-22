<?php

use \Models\Movement as Movement;
use \Models\CreditCardInvoice as CreditCardInvoice;
use \Models\MovementsInvoice as MovementsInvoice;
use \Illuminate\Database\Capsule\Manager as DB;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/movements/user/{user}/period/{period}', function (Request $request, Response $response) use ($app){
    $movements = Movement::whereRaw("usuario = ? and SUBSTRING(vencimento, 1, 6) = ? and hashTransferencia = '' and fatura is null", 
                                    [$request->getAttribute('user'), $request->getAttribute('period')])
                         ->with('bankAccount')
                         ->with('creditCard')
                         ->with('finality')
                         ->with('invoice')
                         ->orderBy('vencimento', 'desc')
                         ->orderBy('emissao', 'desc')
                         ->orderBy('descricao')
                         ->get();

    return $movements->toJson();
});

$app->get('/movements/{id}', function(Request $request, Response $response) use ($app){

    $movement = Movement::whereRaw('id = ?', $request->getAttribute('id'))
                 ->with('bankAccount')
                 ->with('creditCard')
                 ->with('finality')
                 ->with('invoice')
                 ->orderBy('vencimento', 'desc')
                 ->orderBy('emissao')
                 ->orderBy('descricao')
                 ->first();
                 
    return $movement->toJson();
});
 
$app->post('/movements', function(Request $request, Response $response) use ($app){

    global $logger;

    $data = json_decode($request->getBody(), false);

    $movement = new Movement();
    $movement->emissao       = $data->emissao;
    $movement->vencimento    = $data->vencimento;
    $movement->finalidade    = isset($data->finality) ? $data->finality->id : null;
    $movement->contaBancaria = isset($data->bank_account) ? $data->bank_account->id : null;
    $movement->cartaoCredito = isset($data->credit_card) ? $data->credit_card->id : null;
    $movement->valor         = $data->valor;
    $movement->descricao     = $data->descricao;
    $movement->status        = $data->status;
    $movement->operacao      = $data->operacao;
    $movement->usuario       = $data->usuario;


    try {

        Movement::getConnectionResolver()->connection()->beginTransaction();
        
        $movement->save();

        if ( $movement->creditCard != null && $movement->creditCard->id > 0 ){
            $movement->addToInvoice();
        }

        $logger->addInfo('Movement Save: saving movement.' );
        $movement->save();

        Movement::getConnectionResolver()->connection()->commit();

        return $movement->toJson();

    }
    catch(Exception $e) {
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }

});
 
$app->put('/movements/{id}', function(Request $request, Response $response) use ($app){
    global $logger;

    $data = json_decode($request->getBody(), false);

    $movement = Movement::find($data->id);
    $movement->emissao       = $data->emissao;
    $movement->vencimento    = $data->vencimento;
    $movement->finalidade    = isset($data->finality) ? $data->finality->id : null;
    $movement->contaBancaria = isset($data->bank_account) ? $data->bank_account->id : null;
    $movement->cartaoCredito = isset($data->credit_card) ? $data->credit_card->id : null;
    $movement->valor         = $data->valor;
    $movement->descricao     = $data->descricao;
    $movement->status        = $data->status;

    $logger->addInfo('Movement Cartao Credito: ' . $movement->cartaoCredito );

    $movement->validateUpdateDelete('U');

    try {
        Movement::getConnectionResolver()->connection()->beginTransaction();

        if ( $movement->isInOpenInvoice() ){
            $movementOld = Movement::find($data->id);
            if ( ($movementOld->cartaoCredito != $movement->cartaoCredito) ||
                 ($movementOld->vencimento != $movement->vencimento) ){

                $logger->addInfo('Movement Update: removendo movimento da fatura anterior.' );
                $movementInvoice = MovementsInvoice::where('movimento', $movement->id)->first();
                $logger->addInfo($movementInvoice->fatura . ' ' . $movementInvoice->movimento);
                $movementInvoice->where('movimento', $movement->id)->delete();
                //adiciona a nova invoice
                $logger->addInfo('Movement Update: adicionando movimento a nova fatura.' );
                $movement->addToInvoice();

            }
        }else{
            if ( $movement->cartaoCredito > 0 ){
                $movement->addToInvoice();
            }
        }

        $logger->addInfo('Movement Update: saving movement.' );
        $movement->save();

        Movement::getConnectionResolver()->connection()->commit();

        return $movement->toJson();

    }
    catch(Exception $e) {
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }
    

});

$app->delete('/movements/{id}', function(Request $request, Response $response) use ($app){
    global $logger; 
    $movement = Movement::find($request->getAttribute('id'));

    $movement->validateUpdateDelete('D');

    if ( $movement->isInOpenInvoice() ){
        $movementInvoice = MovementsInvoice::where('movimento', $movement->id)->first();
        $movementInvoice->where('movimento', $movimento->id)->delete();
    }

    if ( !empty($movement->hashTransferencia) ){
	$logger->addInfo('Transferência: ' . $movement->hashTransferencia);
        Movement::where('hashTransferencia', $movement->hashTransferencia)->delete();
    }
        
    $movement->delete();

    return $movement->toJson();
    
});

//recupera os movimentos da fatura
$app->get('/movements/invoice/{invoice}', function(Request $request, Response $response) use ($app){
    $movements = CreditCardInvoice::find($request->getAttribute('invoice'))->movementsInvoice;
    return $movements->toJson();
});

//extrato conta bancária
$app->get('/movements/bankAccount/{bankAccount}/period/{period}', function(Request $request, Response $response) use ($app){

    $bank_account_id = $request->getAttribute('bankAccount');
    $period = $request->getAttribute('period');
    $movements = Movement::whereRaw("contaBancaria = ? and SUBSTRING(vencimento, 1, 6) = ?", [$bank_account_id, $period])
                         ->with(['bankAccount','creditCard','finality','invoice'])
                         ->orderBy('vencimento', 'desc')
                         ->orderBy('emissao', 'desc')
                         ->orderBy('descricao')->get();
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

    $movements = Movement::whereRaw("contaBancaria = ? and SUBSTRING(vencimento, 1, 6) < ?", [$bank_account_id, $period])->get();
    $credit = $movements->where('operacao', Movement::CREDIT)->sum('valor');
    $debit = $movements->where('operacao', Movement::DEBIT)->sum('valor');

    return $response->withJson($credit - $debit);

});

//bank account transfers
$app->post('/movements/accountTransfer', function(Request $request, Response $response) use ($app){

    global $logger;

    $data = json_decode($request->getBody(), false);
    $codeTransfer = md5(date('Ymd H:i:s:u'));

    $logger->addInfo('Origem> ' . $data->contaBancariaDestino->descricao);

    try {

        Movement::getConnectionResolver()->connection()->beginTransaction();
        
        $logger->addInfo('Movement transfer: Saving debit...' );
        $debit = new Movement();
        $debit->prepareTransfer($data, $codeTransfer, Movement::DEBIT);
        $debit->save();
        
        $logger->addInfo('Movement transfer: Saving credit...' );
        $credit = new Movement();
        $credit->prepareTransfer($data, $codeTransfer, Movement::CREDIT);
        $credit->save();

        Movement::getConnectionResolver()->connection()->commit();

        return $response->withJson($data);

    }
    catch(Exception $e) {
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }

});

//chart finalities spending
$app->get('/movements/finalitiesChart/user/{user}/period/{period}', function(Request $request, Response $response) use ($app){

    global $logger;

    $logger->addInfo('Movement finality chart: getting data to chart...' );

    $usuario  = $request->getAttribute('user');
    $periodo  = $request->getAttribute('period');
    $operacao = Movement::DEBIT;

    $sql = "select 
                sum(valor) as value, 
                ((sum(valor) / 

                (select 
                    sum(valor) 
                from movimento 
                where usuario = $usuario and SUBSTRING(vencimento, 1, 6) = $periodo and operacao = '$operacao'
                and hashTransferencia = '' and fatura is null)

                ) * 100) as percent, 
                f.descricao as label
            from movimento m 
            inner join finalidade f on f.id = m.finalidade 
            where m.usuario = $usuario and SUBSTRING(m.vencimento, 1, 6) = $periodo and m.operacao = '$operacao' 
            and hashTransferencia = '' and fatura is null group by f.descricao order by 1 asc";

    $resume = DB::select($sql);

    return $response->withJson($resume);

});
