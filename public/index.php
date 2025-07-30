<?php

declare(strict_types=1);

use App\Handler\SmsHandler;
use App\Service\DatabaseService;
use Laminas\ServiceManager\ServiceManager;
use Asgrim\MiniMezzio\AppFactory;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$container = new ServiceManager();
$container->setService(SmsHandler::class, new SmsHandler(new DatabaseService()));
$router = new FastRouteRouter();
$app = AppFactory::create($container, $router);
$app->pipe(new RouteMiddleware($router));
$app->pipe(new DispatchMiddleware());
$app->post('/sms', SmsHandler::class, 'sms');

$app->run();
