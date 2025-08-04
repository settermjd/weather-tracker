#!/user/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use App\Command\WeatherNotificationCommand;
use App\Service\DatabaseService;
use App\Service\WeatherService;
use Dotenv\Dotenv;
use Symfony\Component\Console\Application;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required([
    'WEATHER_API_KEY',
])->notEmpty();

$container = require __DIR__ . '/config/container.php';

const CONSOLE_ROOT = __DIR__;
$app = new Application();
$app->add(new WeatherNotificationCommand(
    $container->get(DatabaseService::class),
    $container->get(WeatherService::class),
));
$app->run();
