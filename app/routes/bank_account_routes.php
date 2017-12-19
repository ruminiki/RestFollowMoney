<?php

use \Models\BankAccount as BankAccount;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Illuminate\Database\Capsule\Manager as DB;

$app->get('/bankAccounts/user/{user}', function (Request $request, Response $response) use ($app){
    $user   = $request->getAttribute('user');
    $periodo = date('Ym');

    $bankAccounts = BankAccount::where('usuario', $user)
                         			 ->orderBy('situacao', 'asc')
    								 ->orderBy('descricao', 'asc')
                         			 ->get();

	foreach ($bankAccounts as $account) {
		$contaBancaria = $account->id;
		$sql = "select 
				(select 
	                sum(valor) 
	            from movimento m 
	            inner join contaBancaria c on c.id = m.contaBancaria 
	            where m.contaBancaria = $contaBancaria and 
	            SUBSTRING(m.vencimento, 1, 6) <= $periodo and 
	            m.operacao = 'CREDITO' and 
	            m.status = 'PAGO') as credit,
	            
	            (select 
	                sum(valor) as debit
	            from movimento m 
	            inner join contaBancaria c on c.id = m.contaBancaria 
	            where m.contaBancaria = $contaBancaria and 
	            SUBSTRING(m.vencimento, 1, 6) <= $periodo and 
	            m.operacao = 'DEBITO' and
	            m.status = 'PAGO') as debit";

	    $resume = collect(DB::select($sql))->first();
	    $account->balance = $resume->credit - $resume->debit;
	}

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
		$ba->tipo      = $data->tipo;
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
		$ba->tipo      = $data->tipo;
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
