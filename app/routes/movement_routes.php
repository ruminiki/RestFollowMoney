<?php

require_once("models/Movement.php");

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/movements/user/{user}/period/{period}', function (Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    
    return $newResponse->withJson(Movement::listByUserPeriod($request->getAttribute('user'), $request->getAttribute('period')), 201);

});

$app->get('/movements/{id}', function(Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    return $newResponse->withJson(Movement::findByID($request->getAttribute('id')), 201);

});
 
$app->post('/movements', function(Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    $value = json_decode($request->getBody(), false);

    //echo $value;
    return $newResponse->withJson(Movement::insert($value), 201);
});
 
$app->put('/movements/{id}', function(Request $request, Response $response) use ($app){
    global $logger;
    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');


    //$logger->addInfo('Update Movement: ' . $request->getBody());
    $value = json_decode($request->getBody(), false);

    //$logger->addInfo($value->descricao);

    //return 'ok';
    //return $newResponse->withJson($value, 201);

    return $newResponse->withJson(Movement::update($value), 201);

});

$app->delete('/movements/{id}', function(Request $request, Response $response) use ($app){
    
    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    return $newResponse->withJson(Movement::delete($request->getAttribute('id')), 201);
    
});

//recupera os movimentos da fatura
$app->get('/movements/invoice/{invoice}', function(Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    return $newResponse->withJson(Movement::listByInvoice($request->getAttribute('invoice')), 201);

});


$app->get('/movements/extract/{bankAccount}/period/{period}', function(Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    return $newResponse->withJson(Movement::listByBankAccountPeriod($request->getAttribute('bankAccount'), $request->getAttribute('period')), 201);

});


$app->get('/movements/previousBalance/user/{user}/period/{period}', function(Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    return $newResponse->withJson(Movement::getPreviousBalance($request->getAttribute('user'), $request->getAttribute('period')), 201);

});

$app->get('/movements/previousBalance/bankAccount/{bankAccount}/period/{period}', function(Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    return $newResponse->withJson(Movement::getPreviousBalanceBankAccount($request->getAttribute('bankAccount'), $request->getAttribute('period')), 201);

});

