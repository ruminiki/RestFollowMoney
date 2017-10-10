<?php

use \Models\Finality as Finality;
use Illuminate\Database\Capsule\Manager as DB;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/finalities/user/{user}', function (Request $request, Response $response) use ($app){
    $finalities = Finality::where('usuario', $request->getAttribute('user'))->get();
    return $finalities->toJson();
});

$app->get('/finalities/{id}', function(Request $request, Response $response) use ($app){
    $finalities = Finality::find($request->getAttribute('id'));
    return $finalities->toJson();
});

$app->get('/finalities/user/{user}/fill/{letters}', function (Request $request, Response $response) use ($app){
     try{
        $user = $request->getAttribute('user'); 
        $letters = $request->getAttribute('letters');
        $finalities = DB::select(DB::raw("SELECT * FROM finalidade 
                                          WHERE usuario = $user 
                                          AND descricao RLIKE '^[$letters]'"));
        return $response->withJson($finalities, 201);
    }catch(Exception $e){
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }
});

 
$app->post('/finalities', function(Request $request, Response $response) use ($app){
    try{
        $data = json_decode($request->getBody(), false);

        $fn = new Finality();
        $fn->descricao = $data->descricao;
        $fn->usuario   = $data->usuario;

        $fn->save();

        return $response->withJson($fn, 201);
    }catch(Exception $e){
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }
    
});
 
$app->put('/finalities/{id}', function(Request $request, Response $response) use ($app){
    try{
        $data = json_decode($request->getBody(), false);

        $fn = Finality::find($data->id);
        $fn->descricao = $data->descricao;
        $fn->usuario   = $data->usuario;

        $fn->save();

        return $response->withJson($fn, 201);
    }catch(Exception $e){
        throw new Exception("Error Processing Request: " . $e->getMessage(), 1);
    }
    
});

$app->delete('/finalities/{id}', function(Request $request, Response $response) use ($app){
    return $response->withJson(Finality::destroy($request->getAttribute('id')), 201);
});


