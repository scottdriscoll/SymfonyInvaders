<?php

use App\Kernel;

require dirname(__DIR__).'/vendor/autoload.php';

$env = $_SERVER['APP_ENV'] ?? 'dev';
$debug = ($_SERVER['APP_DEBUG'] ?? ('prod' !== $env)) && '0' !== ($_SERVER['APP_DEBUG'] ?? null);

$kernel = new Kernel($env, (bool) $debug);
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
