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

//========FATURA CARTÃO CREDITO=====//
$app->get('/creditCardInvoices/creditCard/:creditCard', function($creditCard) use ($app){
	(new \controllers\CreditCardInvoice($app))->list($creditCard);
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

//========FORMA PAGAMENTO=====//
$app->get('/paymentForms/user/:user', function($user) use ($app){
	(new \controllers\PaymentForm($app))->list($user);
});
 
$app->get('/paymentForms/:id', function($id) use ($app){
	(new \controllers\PaymentForm($app))->get($id);
});
 
$app->post('/paymentForms/', function() use ($app){
	(new \controllers\PaymentForm($app))->new();
});
 
$app->put('/paymentForms/:id', function($id) use ($app){
	(new \controllers\PaymentForm($app))->edit($id);
});
 
$app->delete('/paymentForms/:id', function($id) use ($app){
	(new \controllers\PaymentForm($app))->delete($id);
});


//========MOVIMENTOS=====//
$app->get('/movements/user/:user/period/:period', function($user, $period) use ($app){
	(new \controllers\Movement($app))->list($user, $period);
});
 
$app->get('/movements/:id', function($id) use ($app){
	(new \controllers\Movement($app))->get($id);
});
 
$app->post('/movements/', function() use ($app){
	(new \controllers\Movement($app))->new();
});
 
$app->put('/movements/:id', function($id) use ($app){
	(new \controllers\Movement($app))->edit($id);
});
 
$app->delete('/movements/:id', function($id) use ($app){
	(new \controllers\Movement($app))->delete($id);
});

//Rodando aplicação
$app->run();