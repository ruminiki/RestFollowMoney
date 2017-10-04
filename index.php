<?php

//Autoload
$loader = require 'vendor/autoload.php';

require_once "app/handlers/Error.php";

//$logWriter = new \Slim\LogWriter(fopen('logs/app.log', 'a'));

$config = [
    'settings' => [
        'determineRouteBeforeAppMiddleware' => true, //VERY IMPORTANT for route logging
        'displayErrorDetails'    => true,
        'debug'                  => true,
        'templates.path'         => 'templates',
        'addContentLengthHeader' => true,
  
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
$logger->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());

require_once "app/routes/finalitie_routes.php";
require_once "app/routes/bank_account_routes.php";
require_once "app/routes/payment_form_routes.php";
require_once "app/routes/credit_card_routes.php";
require_once "app/routes/credit_card_invoice_routes.php";
require_once "app/routes/movement_routes.php";


//Rodando aplicação
$app->run();