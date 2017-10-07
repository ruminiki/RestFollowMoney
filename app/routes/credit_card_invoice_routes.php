<?php

require_once("models/CreditCardInvoice.php");

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/creditCardInvoices/creditCard/{creditCard}', function(Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    
    return $newResponse->withJson(CreditCardInvoice::listByCreditCard($request->getAttribute('creditCard')), 201);

});

$app->get('/creditCardInvoices/{id}', function(Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    
    return $newResponse->withJson(CreditCardInvoice::findByID($request->getAttribute('id')), 201);

});
   
$app->put('/creditCardInvoices/pay/{id}', function(Request $request, Response $response) use ($app){
    global $logger;
    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

	$value = json_decode($request->getBody(), false);

    return $newResponse->withJson(CreditCardInvoice::payInvoice($value), 201);

});

$app->put('/creditCardInvoices/unpay/{id}', function(Request $request, Response $response) use ($app){
    global $logger;
    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

	$value = json_decode($request->getBody(), false);

    return $newResponse->withJson(CreditCardInvoice::unpayInvoice($value), 201);

});