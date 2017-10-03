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
   
