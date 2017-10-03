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
require_once "app/routes/bank_account_routes.php";
require_once "app/routes/payment_form_routes.php";
require_once "app/routes/credit_card_routes.php";
require_once "app/routes/credit_card_invoice_routes.php";
require_once "app/routes/movement_routes.php";

//Rodando aplicação
$app->run();