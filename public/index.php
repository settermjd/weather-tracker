<?php

declare(strict_types=1);

use App\Handler\SmsHandler;
use Dotenv\Dotenv;
use Asgrim\MiniMezzio\AppFactory;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$dotenv->required([
    'TWILIO_ACCOUNT_SID',
    'TWILIO_AUTH_TOKEN',
    'WEATHER_API_KEY',
])->notEmpty();

$container = require __DIR__ . '/../config/container.php';
$router = new FastRouteRouter();
$app = AppFactory::create($container, $router);
$app->pipe(new RouteMiddleware($router));
$app->pipe(new DispatchMiddleware());
$app->post('/sms', SmsHandler::class, 'sms');

$app->run();
