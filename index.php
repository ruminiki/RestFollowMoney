<?php
//Autoload
$loader = require 'vendor/autoload.php';

$config = [
    'settings' => [
        'displayErrorDetails'    => true,
        'templates.path'         => 'templates',
        'addContentLengthHeader' => true,

        'logger' => [
            'name' => 'slim-app',
            'level' => Monolog\Logger::DEBUG,
            'path' => __DIR__ . '/logs/app.log',
        ],
    ],
];

$app = new \Slim\App($config);

require_once "app/routes/finalitie_routes.php";
require_once "app/routes/credit_card_routes.php";
require_once "app/routes/credit_card_invoice_routes.php";


/*
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

//recupera os movimentos da fatura
$app->get('/movements/invoice/:invoice', function($invoice) use ($app){
	(new \controllers\Movement($app))->listByInvoice($invoice);
});

//recupera o extrato da conta bancária
$app->get('/movements/extract/:bankAccount/period/:period', function($bankAccount, $period) use ($app){
	(new \controllers\Movement($app))->listByBankAccount($bankAccount, $period);
});

//saldo geral
$app->get('/movements/previousBalance/user/:user/period/:period', function($user, $period) use ($app){
	(new \controllers\Movement($app))->getPreviousBalance($user, $period);
});

//saldo conta bancária
$app->get('/movements/previousBalance/bankAccount/:bankAccount/period/:period', function($bankAccount, $period) use ($app){
	(new \controllers\Movement($app))->getPreviousBalanceBankAccount($bankAccount, $period);
});*/


//Rodando aplicação
$app->run();