<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$laravelAppPath = file_exists(__DIR__.'/laravel_app/bootstrap/app.php')
    ? __DIR__.'/laravel_app'
    : __DIR__.'/../laravel_app';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelAppPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelAppPath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelAppPath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());