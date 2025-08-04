<?php

namespace App\Service;


use WeatherApi\Entity\WeatherData;
use WeatherApi\Hydrator\WeatherDataHydrator;
use WeatherApi\WeatherProvider;

final readonly class WeatherService
{
    public function __construct(private WeatherProvider $weatherProvider)
    {
    }

    public function getWeatherForCity(string $city): WeatherData|null
    {
        $weatherData = json_decode(
            $this->weatherProvider->getWeather($city),
            associative: true,
            flags: JSON_OBJECT_AS_ARRAY);

        return new WeatherDataHydrator()->hydrate($weatherData);
    }
}
