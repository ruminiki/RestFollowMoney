<?php

//Autoload
$loader = require 'vendor/autoload.php';

require_once "app/handlers/Error.php";

$config = [
    'settings' => [
        'templates.path' => 'templates',
        'debug' => true,
        'logger' => [
            'log.enabled' => true,
            'name' => 'slim-app',
            'level' => Monolog\Logger::DEBUG,
            'path' => 'logs/app.log',
        ],
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'fmdb',
            'username' => 'root',
            'password' => 'dust258',
            'charset'   => 'latin1',
            'collation' => 'latin1_swedish_ci',
            'prefix'    => '',
        ],
    ],
];

$app = new \Slim\App($config);

//==========SERVICE FACTORY FOR ORM=====================

$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

//======================================================


//==========LOG=========================================
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

// Create the logger
$logger = new Logger('my_logger');
// Now add some handlers
/*$logger->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());*/


//==========ERROR HANDLING==============

$c = $app->getContainer();

$c['phpErrorHandler'] = function ($c) {
    return function ($request, $response, $error) use ($c) {

    	global $logger;
    	$logger->addInfo($error->getMessage());

        $newResponse = $response->withHeader('Content-type', 'application/json')
        ->withHeader('Content-Type', 'charset=unicode')
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

  		return $newResponse->withJson($error->getMessage(), 500);

    };
};

$c['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {

    	global $logger;
    	$logger->addWarning($exception->getMessage());
    	//$logger->addInfo('Rollback transaction...');
    	//DB::rollback();

	    $newResponse = $response->withHeader('Content-type', 'application/json')
        ->withHeader('Content-Type', 'charset=unicode')
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

  		return $newResponse->withJson($exception->getMessage(), 500);

    };
};

//==========ROUTES==========================

require_once "app/routes/finality_routes.php";
require_once "app/routes/bank_account_routes.php";
require_once "app/routes/payment_form_routes.php";
require_once "app/routes/credit_card_routes.php";
require_once "app/routes/credit_card_invoice_routes.php";
require_once "app/routes/movement_routes.php";


//==========TOKEN AUTHENTICATION=============
/*
use Slim\Middleware\TokenAuthentication;
use Models\User as User;
$authenticator = function($request, TokenAuthentication $tokenAuth){
    # Search for token on header, parameter, cookie or attribute
    $token = $tokenAuth->findToken($request);
    $logger->addInfo('Authentication:token ' . $token);
    # Your method to make token validation
    $user = User::where('token', $token);
    $logger->addInfo('Authentication:user ' . $user->nome);
    # If occured ok authentication continue to route
    # before end you can storage the user informations or whatever
};*/

/*$app->add(new TokenAuthentication([
    'secure' => false,
    'path' => '/',
    'authenticator' => $authenticator
]));*/

//==========================================

//Rodando aplicação
$app->run();