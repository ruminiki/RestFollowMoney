<?php

use \App\Util\DateUtil as DateUtil;
use \Models\CreditCardInvoice as CreditCardInvoice;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/creditCardInvoices/creditCard/{creditCard}', function(Request $request, Response $response) use ($app){
    $invoices = CreditCardInvoice::where('cartaoCredito', $request->getAttribute('creditCard'))
                                 ->with('creditCard')->orderBy('vencimento', 'desc')->get();
    return $invoices->toJson();
});

$app->get('/creditCardInvoices/{id}', function(Request $request, Response $response) use ($app){
    $invoice = CreditCardInvoice::with('creditCard')->find($request->getAttribute('id'));
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
        $data = json_decode($request->getBody(), false);

        $in = CreditCardInvoice::find($data->id);
        $in->paymentForm    = $data->paymentForm;
        $in->valorPagamento = $data->valorPagamento;
        $in->bankAccount    = $data->bankAccount;
        
        $in->pay();

        return $response->withJson($in, 201);
    }catch(Exception $e){
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }
});

$app->put('/creditCardInvoices/unpay/{id}', function(Request $request, Response $response) use ($app){
    $invoice = CreditCardInvoice::find($request->getAttribute('id'));
    $invoice->unpay();
    return $response->withJson($invoice, 201);
});

 