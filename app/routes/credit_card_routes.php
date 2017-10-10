<?php

use \Models\CreditCard as CreditCard;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/creditCards/user/{user}', function (Request $request, Response $response) use ($app){
    $creditCards = CreditCard::where('usuario', $request->getAttribute('user'))->get();
    return $creditCards->toJson();
});

$app->get('/creditCards/{id}', function(Request $request, Response $response) use ($app){
    $creditCard = CreditCard::find($request->getAttribute('id'));
    return $creditCard->toJson();
});
 
$app->post('/creditCards', function(Request $request, Response $response) use ($app){
    try{
        $data = json_decode($request->getBody(), false);

        $cc = new CreditCard();
        $cc->descricao      = $data->descricao;
        $cc->limite         = $data->limite;
        $cc->dataFatura     = $data->dataFatura;
        $cc->dataFechamento = $data->dataFechamento;
        $cc->usuario        = $data->usuario;

        $cc->save();

        return $response->withJson($cc, 201);
    }catch(Exception $e){
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }
    
});
 
$app->put('/creditCards/{id}', function(Request $request, Response $response) use ($app){
    try{
        $data = json_decode($request->getBody(), false);

        $cc = CreditCard::find($data->id);
        $cc->descricao      = $data->descricao;
        $cc->limite         = $data->limite;
        $cc->dataFatura     = $data->dataFatura;
        $cc->dataFechamento = $data->dataFechamento;
        $cc->usuario        = $data->usuario;

        $cc->save();

        return $response->withJson($cc, 201);
    }catch(Exception $e){
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }
});

$app->delete('/creditCards/{id}', function(Request $request, Response $response) use ($app){
    return $response->withJson(CreditCard::destroy($request->getAttribute('id')), 201);
});
