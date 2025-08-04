<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\DatabaseService;
use Laminas\Diactoros\Response\XmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twilio\TwiML\MessagingResponse;

final readonly class SmsHandler implements RequestHandlerInterface
{
    const string PATTERN_TRACK_CITY = '/track (?<city>.*)/';
    const string PATTERN_STOP_TRACKING_CITY = '/stop tracking (?<city>.*)/';

    public function __construct(private DatabaseService $databaseService)
    {
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $from = $request->getParsedBody()['From'];
        $body = strtolower(trim($request->getParsedBody()['Body']));

        $response = new MessagingResponse();

        // Handle requests to start tracking a phone number
        if (
            str_starts_with($body, 'track')
            && preg_match(self::PATTERN_TRACK_CITY, $body, $matches) === 1
        ) {
            // Add the user to the tracking list
            $this->databaseService->startTracking($from, $matches['city']);

            // Send them a response
            $response->message(
                "We're now tracking the daily weather for {$matches['city']} for you."
            );
            return new XmlResponse($response->asXML());
        }

        if (strtolower(trim($body)) === "stop tracking") {
            $this->databaseService->stopAllCityTrackingForUser($from);
            $response->message(
                "We're no longer tracking the daily weather for you."
            );
            return new XmlResponse($response->asXML());
        }

        // Handle requests to stop tracking a phone number
        if (
            str_starts_with($body, 'stop tracking')
            && preg_match(self::PATTERN_STOP_TRACKING_CITY, $body, $matches) === 1
        ) {
            // Remove the user from the tracking list
            $this->databaseService->stopTracking($from, $matches['city']);

            // Send them a response
            $response->message(
                "We're no longer tracking the daily weather for {$matches['city']} for you."
            );
            return new XmlResponse($response->asXML());
        }

        $defaultMessage =<<<EOF
Sorry, but we're not sure what you want us to do. 

Do you want to track a city's weather? Then send an SMS starting with "Track " followed by a city name, e.g., "Track Bundaberg". 
Otherwise, to stop tracking a city's weather, start the SMS with "Stop tracking " followed by the city's name.
EOF;

        $response->message($defaultMessage);
        return new XmlResponse($response->asXML());
    }
}
