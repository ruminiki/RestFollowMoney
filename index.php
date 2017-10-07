<?php

//Autoload
$loader = require 'vendor/autoload.php';

require_once "app/handlers/Error.php";

$config = [
    'settings' => [
        'templates.path'         => 'templates',
        'logger' => [
            'log.enabled' => true,
            'name' => 'slim-app',
            'level' => Monolog\Logger::DEBUG,
            'path' => 'logs/app.log',
        ],
    ],
];

$app = new \Slim\App($config);

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

// Create the logger
$logger = new Logger('my_logger');
// Now add some handlers
/*$logger->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());*/

$c = $app->getContainer();

//==========ERROR HANDLING==============

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
    	DB::PDO()->rollback();

	    $newResponse = $response->withHeader('Content-type', 'application/json')
        ->withHeader('Content-Type', 'charset=unicode')
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

  		return $newResponse->withJson($exception->getMessage(), 500);

    };
};

require_once "app/routes/finalitie_routes.php";
require_once "app/routes/bank_account_routes.php";
require_once "app/routes/payment_form_routes.php";
require_once "app/routes/credit_card_routes.php";
require_once "app/routes/credit_card_invoice_routes.php";
require_once "app/routes/movement_routes.php";

//Rodando aplicação
$app->run();