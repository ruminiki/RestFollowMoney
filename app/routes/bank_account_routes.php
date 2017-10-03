<?php

require_once("models/BankAccount.php");

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/bankAccounts/user/{user}', function (Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    
    return $newResponse->withJson(BankAccount::listByUser($request->getAttribute('user')), 201);

});

$app->get('/bankAccounts/{id}', function(Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    return $newResponse->withJson(BankAccount::findByID($request->getAttribute('id')), 201);

});
 
$app->post('/bankAccounts', function(Request $request, Response $response) use ($app){

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    $value = json_decode($request->getBody());

    //echo $value;
    return $newResponse->withJson(BankAccount::insert($value), 201);
});
 
$app->put('/bankAccounts/{id}', function(Request $request, Response $response) use ($app){
    
    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    $value = json_decode($request->getBody());

    return $newResponse->withJson(BankAccount::update($value), 201);

});

$app->delete('/bankAccounts/{id}', function(Request $request, Response $response) use ($app){
    
    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    return $newResponse->withJson(BankAccount::delete($request->getAttribute('id')), 201);
    
});
