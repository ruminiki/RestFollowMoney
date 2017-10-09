<?php

use \Models\BankAccount as BankAccount;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/bankAccounts/user/{user}', function (Request $request, Response $response) use ($app){
    $bankAccounts = BankAccount::where('usuario', $request->getAttribute('user'))->get();
    return $bankAccounts->toJson();
});

$app->get('/bankAccounts/{id}', function(Request $request, Response $response) use ($app){
    $bankAccount = BankAccount::find($request->getAttribute('id'));
    return $bankAccount->toJson();
});
 
$app->post('/bankAccounts', function(Request $request, Response $response) use ($app){
    return $response->withJson(BankAccount::updateOrCreate($request->getBody()), 201);
});
 
$app->put('/bankAccounts/{id}', function(Request $request, Response $response) use ($app){
    return $response->withJson(BankAccount::updateOrCreate($$request->getBody()), 201);
});

$app->delete('/bankAccounts/{id}', function(Request $request, Response $response) use ($app){
    return $response->withJson(BankAccount::destroy($request->getAttribute('id')), 201);
});
