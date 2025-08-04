<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DatabaseService;
use App\Service\WeatherService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twilio\Rest\Client;

#[AsCommand(name: 'app:weather:notification', description: 'Send city weather notifications to registered users')]
final class WeatherNotificationCommand extends Command
{
    public function __construct(
        private DatabaseService $databaseService,
        private WeatherService $weatherService,
    ) {
       parent::__construct('app:weather:notification');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $trackedCities = $this->databaseService->getTrackedCities();
        if (count($trackedCities) === 0) {
            $output->writeln('No tracked cities were found in the database.');
            return Command::SUCCESS;
        }

        foreach ($trackedCities as $city) {
            $usersTrackingCities = $this->databaseService->getUsersTrackingCity($city->getCity());
            if (count($usersTrackingCities) === 0) {
                continue;
            }

            foreach ($usersTrackingCities as $userTrackingCity) {
                $weatherData = $this->weatherService->getWeatherForCity($userTrackingCity->getCity());
                $message = <<<'EOF'
Current temperature in %s is %s degrees celsius with a wind chill factor of %s degrees celsius. 

Reply "STOP %1$s" to opt-out of weather alerts for %1$s. 

Reply "STOP" to opt-out of ALL weather alerts.
EOF;
                new Client($_ENV['TWILIO_ACCOUNT_SID'], $_ENV['TWILIO_AUTH_TOKEN'])
                    ->messages
                    ->create(
                        $userTrackingCity->getPhoneNumber(),
                        [
                            "body" => sprintf(
                                $message,
                                $userTrackingCity->getCity(),
                                $weatherData->getCurrentWeather()->getTempInCelcius(),
                                $weatherData->getCurrentWeather()->getWindChillInCelcius(),
                            ),
                            "from" => $_ENV['TWILIO_PHONE_NUMBER'],
                        ]
                    );
            }
        }

        return Command::SUCCESS;
    }
}
