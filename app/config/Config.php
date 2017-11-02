<?php

namespace App\Config;

$loader = require '../../vendor/autoload.php';

use \Monolog\Logger as Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Handler\FirePHPHandler;

class Config {

	private static $app = null;
	private static $logger = null;

	private static $config = [
	    'settings' => [
	        'templates.path' => 'templates',
	        'debug' => true,
	        'logger' => [
	            'log.enabled' => true,
	            'name' => 'slim-app',
	            'level' => Logger::DEBUG,
	            'path' => '../../logs/app.log',
	        ],
	        'db' => [
	            'driver' => 'mysql',
	            'host' => 'localhost',
	            'database' => 'fmdb',
	            'username' => 'root',
	            'password' => '',
	            'charset'   => 'latin1',
	            'collation' => 'latin1_swedish_ci',
	            'prefix'    => '',
	        ],
	    ],
	];

	public static function getApp(){
		if ( Config::$app == null ){
			Config::$app = new \Slim\App(Config::$config);
		}
		return Config::$app;
	}

	public static function configureServiceFactoryORM(){
		$container = Config::getApp()->getContainer();
		$capsule = new \Illuminate\Database\Capsule\Manager;
		$capsule->addConnection($container['settings']['db']);
		$capsule->setAsGlobal();
		$capsule->bootEloquent();
	}

	public static function logger(){
		if ( Config::$logger == null ){
			Config::$logger = new Logger('my_logger');
		}
		return Config::$logger;
	}

}

?>