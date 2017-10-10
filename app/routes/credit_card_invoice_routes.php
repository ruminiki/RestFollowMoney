<?php

use \Models\CreditCardInvoice as CreditCardInvoice;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/creditCardInvoices/creditCard/{creditCard}', function(Request $request, Response $response) use ($app){
    $invoices = CreditCardInvoice::where('cartaoCredito', $request->getAttribute('creditCard'))->get();
    return $invoices->toJson();
});

$app->get('/creditCardInvoices/{id}', function(Request $request, Response $response) use ($app){
    $invoice = CreditCardInvoice::find($request->getAttribute('id'));
    return $invoice->toJson();
});

$app->put('/creditCardInvoices/pay/{id}', function(Request $request, Response $response) use ($app){
    $invoice = CreditCardInvoice::find($request->getAttribute('id'));
    $invoice->pay();
    return $newResponse->withJson($invoice, 201);
});

$app->put('/creditCardInvoices/unpay/{id}', function(Request $request, Response $response) use ($app){
    $invoice = CreditCardInvoice::find($request->getAttribute('id'));
    $invoice->unpay();
    return $newResponse->withJson($invoice, 201);
});

 









   
