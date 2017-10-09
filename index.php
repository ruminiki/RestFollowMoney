<?php

//Autoload
$loader = require 'vendor/autoload.php';

require_once "app/handlers/Error.php";
use Models\User as User;

$config = [
    'settings' => [
        'templates.path' => 'templates',
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
    	$logger->addInfo('Rollback transaction...');
    	DB::rollback();

	    $newResponse = $response->withHeader('Content-type', 'application/json')
        ->withHeader('Content-Type', 'charset=unicode')
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

  		return $newResponse->withJson($exception->getMessage(), 500);

    };
};

//==========ROUTES==========================

require_once "app/routes/finalitie_routes.php";
require_once "app/routes/bank_account_routes.php";
require_once "app/routes/payment_form_routes.php";
require_once "app/routes/credit_card_routes.php";
require_once "app/routes/credit_card_invoice_routes.php";
require_once "app/routes/movement_routes.php";


//==========TOKEN AUTHENTICATION=============
$app->add(new App\Auth\TokenAuth());

/*$app->get('/users/{user}', function($request, $response) {

    $newResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    
    global $logger;
    $token = User::find($request->getAttribute('user'))->token;
    
    if ( !$token ){
        $logger->addWarning('User not authenticated!');
    }else{
        $logger->addInfo('Token authentication: ' . $token->token);
    }

    return $response->getBody()->write($token->toJson());

});*/
 


//Rodando aplicação
$app->run();