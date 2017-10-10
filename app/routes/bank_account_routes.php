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
	try{
		$data = json_decode($request->getBody(), false);

		$ba = new BankAccount();
		$ba->descricao = $data->descricao;
		$ba->numero    = $data->numero;
		$ba->digito    = $data->digito;
		$ba->situacao  = $data->situacao;
		$ba->usuario   = $data->usuario;

		$ba->save();

	    return $response->withJson($ba, 201);
	}catch(Exception $e){
		throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
	}
	
});
 
$app->put('/bankAccounts/{id}', function(Request $request, Response $response) use ($app){
	try{
		$data = json_decode($request->getBody(), false);

		$ba = BankAccount::find($data->id);
		$ba->descricao = $data->descricao;
		$ba->numero    = $data->numero;
		$ba->digito    = $data->digito;
		$ba->situacao  = $data->situacao;
		$ba->usuario   = $data->usuario;

		$ba->save();
		return $response->withJson($ba, 201);
	}catch(Exception $e){
		throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
	}
    
});

$app->delete('/bankAccounts/{id}', function(Request $request, Response $response) use ($app){
    return $response->withJson(BankAccount::destroy($request->getAttribute('id')), 201);
});
