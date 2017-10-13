<?php

use \App\Util\DateUtil as DateUtil;
use \Models\CreditCardInvoice as CreditCardInvoice;
use \Models\Movement as Movement;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Illuminate\Database\Capsule\Manager as DB;

$app->get('/creditCardInvoices/creditCard/{creditCard}', function(Request $request, Response $response) use ($app){
    $invoices = CreditCardInvoice::where('cartaoCredito', $request->getAttribute('creditCard'))
                                 ->with('creditCard')->orderBy('vencimento', 'desc')->get();

    $invoices->each(function ($invoice) {
        $movements = DB::table('movimento')
            ->join('movimentosFatura', 'movimentosFatura.movimento', '=', 'movimento.id')
            ->where('movimentosFatura.fatura', $invoice->id)
            ->select('movimento.operacao', 'movimento.valor')
            ->get();

        $credit = $movements->where('operacao', Movement::CREDIT)->sum('valor');
        $debit = $movements->where('operacao', Movement::DEBIT)->sum('valor');

        $invoice->valor = $debit - $credit;
    });        

    return $invoices->toJson();
});

$app->get('/creditCardInvoices/{id}', function(Request $request, Response $response) use ($app){
    $invoice = CreditCardInvoice::with('creditCard')->find($request->getAttribute('id'));

    $movements = DB::table('movimento')
        ->join('movimentosFatura', 'movimentosFatura.movimento', '=', 'movimento.id')
        ->where('movimentosFatura.fatura', $invoice->id)
        ->select('movimento.operacao', 'movimento.valor')
        ->get();

    $credit = $movements->where('operacao', Movement::CREDIT)->sum('valor');
    $debit = $movements->where('operacao', Movement::DEBIT)->sum('valor');

    $invoice->valor = $debit - $credit;

    return $invoice->toJson();
});

$app->get('/creditCardInvoices/period/{period}', function(Request $request, Response $response) use ($app){
    $mesReferencia = DateUtil::mesReferenciaFromDateString($request->getAttribute('period').'01');
    $invoices = CreditCardInvoice::where('mesReferencia', $mesReferencia)->with('creditCard')
                                ->orderBy('vencimento', 'desc')->get();
    return $invoices->toJson();
});

$app->put('/creditCardInvoices/pay/{id}', function(Request $request, Response $response) use ($app){
    try{

        CreditCardInvoice::getConnectionResolver()->connection()->beginTransaction();
       
        $data = json_decode($request->getBody(), false);

        $in = CreditCardInvoice::find($data->id);
        $in->formaPagamento = isset($data->payment_form) ? $data->payment_form->id : null;
        $in->valorPagamento = isset($data->valorPagamento) ? $data->valorPagamento : null;
        $in->contaBancaria  = isset($data->bank_account) ? $data->bank_account->id : null;
        
        $in->pay();

        CreditCardInvoice::getConnectionResolver()->connection()->commit();

        return $response->withJson($in, 201);
    }catch(Exception $e){
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }
});

$app->put('/creditCardInvoices/unpay/{id}', function(Request $request, Response $response) use ($app){
    try {
        CreditCardInvoice::getConnectionResolver()->connection()->beginTransaction();
       
        $invoice = CreditCardInvoice::find($request->getAttribute('id'));
        $invoice->unpay();
        
        CreditCardInvoice::getConnectionResolver()->connection()->commit();
        return $response->withJson($invoice, 201);

    }
    catch(Exception $e) {
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }
});

 