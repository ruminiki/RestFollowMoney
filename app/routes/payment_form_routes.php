<?php

use \Models\PaymentForm as PaymentForm;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/paymentForms/user/{user}', function (Request $request, Response $response) use ($app){
    $paymentForms = PaymentForm::where('usuario', $request->getAttribute('user'))->get();
    return $paymentForms->toJson();
});

$app->get('/paymentForms/{id}', function(Request $request, Response $response) use ($app){
    $paymentForm = PaymentForm::find($request->getAttribute('id'));
    return $paymentForm->toJson();
});
 
$app->post('/paymentForms', function(Request $request, Response $response) use ($app){
    try{
        $data = json_decode($request->getBody(), false);

        $pf = new PaymentForm();
        $pf->descricao = $data->descricao;
        $pf->sigla     = $data->numero;
        $pf->usuario   = $data->usuario;

        $pf->save();

        return $response->withJson($pf, 201);
    }catch(Exception $e){
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }
    
});
 
$app->put('/paymentForms/{id}', function(Request $request, Response $response) use ($app){
    try{
        $data = json_decode($request->getBody(), false);

        $pf = PaymentForm::find($data->id);
        $pf->descricao = $data->descricao;
        $pf->sigla     = $data->numero;
        $pf->usuario   = $data->usuario;

        $pf->save();
        return $response->withJson($pf, 201);
    }catch(Exception $e){
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }
    
});

$app->delete('/paymentForms/{id}', function(Request $request, Response $response) use ($app){
    return $response->withJson(PaymentForm::destroy($request->getAttribute('id')), 201);
});
