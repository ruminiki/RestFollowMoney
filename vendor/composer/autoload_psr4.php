<?php

// autoload_psr4.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'controllers\\' => array($baseDir . '/controllers'),
    'Symfony\\Polyfill\\Mbstring\\' => array($vendorDir . '/symfony/polyfill-mbstring'),
    'Symfony\\Component\\Translation\\' => array($vendorDir . '/symfony/translation'),
    'Slim\\PDO\\' => array($vendorDir . '/slim/pdo/src/PDO'),
    'Slim\\Middleware\\' => array($vendorDir . '/dyorg/slim-token-authentication/src'),
    'Slim\\' => array($vendorDir . '/slim/slim/Slim'),
    'Psr\\SimpleCache\\' => array($vendorDir . '/psr/simple-cache/src'),
    'Psr\\Log\\' => array($vendorDir . '/psr/log/Psr/Log'),
    'Psr\\Http\\Message\\' => array($vendorDir . '/psr/http-message/src'),
    'Psr\\Container\\' => array($vendorDir . '/psr/container/src'),
    'Monolog\\' => array($vendorDir . '/monolog/monolog/src/Monolog'),
    'Models\\' => array($baseDir . '/models'),
    'Interop\\Container\\' => array($vendorDir . '/container-interop/container-interop/src/Interop/Container'),
    'Illuminate\\Support\\' => array($vendorDir . '/illuminate/support'),
    'Illuminate\\Database\\' => array($vendorDir . '/illuminate/database'),
    'Illuminate\\Contracts\\' => array($vendorDir . '/illuminate/contracts'),
    'Illuminate\\Container\\' => array($vendorDir . '/illuminate/container'),
    'FastRoute\\' => array($vendorDir . '/nikic/fast-route/src'),
    'Doctrine\\Common\\Inflector\\' => array($vendorDir . '/doctrine/inflector/lib/Doctrine/Common/Inflector'),
    'Carbon\\' => array($vendorDir . '/nesbot/carbon/src/Carbon'),
    'App\\Handlers\\' => array($baseDir . '/app/handlers'),
    'App\\Exceptions\\' => array($baseDir . '/app/exceptions'),
    'App\\Auth\\' => array($baseDir . '/app/auth'),
);
