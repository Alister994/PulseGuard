<?php

<<<<<<< HEAD
=======
use Illuminate\Foundation\Application;
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
<<<<<<< HEAD
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
=======
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
