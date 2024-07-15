<?php

declare(strict_types=1);

namespace Tests\Integration\Chat;

class WeatherExample
{
    public string $lastMessage = '';

    /**
     * returns the current weather in the given location. Location is a string containing the name of the city, the state or province and the nation
     * The result contains the description of the weather plus the current temperature in Celsius
     */
    public function currentWeatherForLocation(string $location): string
    {
        $this->lastMessage = "Weather in $location is sunny, temperature is 26 Celsius";

        return $this->lastMessage;
    }
}
