<?php

use App\Handler\SmsHandler;
use App\Service\DatabaseService;
use App\Service\WeatherService;
use GuzzleHttp\Client;
use Laminas\ServiceManager\ServiceManager;
use WeatherApi\RequestFactory;
use WeatherApi\WeatherProvider;

$databaseService = new DatabaseService();
$weatherProvider = new WeatherProvider(
    $_ENV['WEATHER_API_KEY'],
    new Client(['timeout'  => 2.0,]),
    new RequestFactory(),
);

$container = new ServiceManager();
$container->setService(SmsHandler::class, new SmsHandler($databaseService));
$container->setService(DatabaseService::class, $databaseService);
$container->setService(WeatherService::class, new WeatherService($weatherProvider));

return $container;
