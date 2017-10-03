<?php

require_once("models/CreditCard.php");

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/creditCards/user/{user}', function (Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    
    return $newResponse->withJson(CreditCard::listByUser($request->getAttribute('user')), 201);

});

$app->get('/creditCards/{id}', function(Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    return $newResponse->withJson(CreditCard::findByID($request->getAttribute('id')), 201);

});
 
$app->post('/creditCards/', function(Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    $value = json_decode($request->getBody());

    return $newResponse->withJson(CreditCard::insert($value), 201);
});
 
$app->put('/creditCards/{id}', function(Request $request, Response $response) use ($app){
    
    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    $value = json_decode($request->getBody());

    return $newResponse->withJson(CreditCard::update($value), 201);

});

$app->delete('/creditCards/{id}', function(Request $request, Response $response) use ($app){
    
    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    return $newResponse->withJson(CreditCard::delete($request->getAttribute('id')), 201);
    
});

