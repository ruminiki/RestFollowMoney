<?php
//Autoload
$loader = require 'vendor/autoload.php';
 
//Instanciando objeto
$app = new \Slim\Slim(array(
    'templates.path' => 'templates'
));

//=========FINALIDADE=======//
$app->get('/finalities/user/:user', function($user) use ($app){
	(new \controllers\Finality($app))->list($user);
});
 
$app->get('/finalities/:id', function($id) use ($app){
	(new \controllers\Finality($app))->get($id);
});
 
$app->post('/finalities/', function() use ($app){
	(new \controllers\Finality($app))->new();
});
 
$app->put('/finalities/:id', function($id) use ($app){
	(new \controllers\Finality($app))->edit($id);
});
 
$app->delete('/finalities/:id', function($id) use ($app){
	(new \controllers\Finality($app))->delete($id);
});
 
//========CARTÃO DE CRÉDITO=====//
$app->get('/creditCards/user/:user', function($user) use ($app){
	(new \controllers\CreditCard($app))->list($user);
});
 
$app->get('/creditCards/:id', function($id) use ($app){
	(new \controllers\CreditCard($app))->get($id);
});
 
$app->post('/creditCards/', function() use ($app){
	(new \controllers\CreditCard($app))->new();
});
 
$app->put('/creditCards/:id', function($id) use ($app){
	(new \controllers\CreditCard($app))->edit($id);
});
 
$app->delete('/creditCards/:id', function($id) use ($app){
	(new \controllers\CreditCard($app))->delete($id);
});

//========CONTA BANCÁRIA=====//
$app->get('/bankAccounts/user/:user', function($user) use ($app){
	(new \controllers\BankAccount($app))->list($user);
});
 
$app->get('/bankAccounts/:id', function($id) use ($app){
	(new \controllers\BankAccount($app))->get($id);
});
 
$app->post('/bankAccounts/', function() use ($app){
	(new \controllers\BankAccount($app))->new();
});
 
$app->put('/bankAccounts/:id', function($id) use ($app){
	(new \controllers\BankAccount($app))->edit($id);
});
 
$app->delete('/bankAccounts/:id', function($id) use ($app){
	(new \controllers\BankAccount($app))->delete($id);
});

//Rodando aplicação
$app->run();