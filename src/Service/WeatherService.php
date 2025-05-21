<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;

/**
 * Service responsible for fetching weather data from the Weather API.
 *
 * This class handles HTTP requests to the external weather provider and returns
 * either the raw response content or a parsed array.
 */
class WeatherService
{
    /**
     * API key used to authenticate requests to the Weather API.
     */
    private string $apiKey;

    /**
     * API path used to authenticate requests to the Weather API.
     */
    private string $apiPath;

    /**
     * HTTP client for making external API requests.
     */
    private HttpClientInterface $httpClient;

    /**
     * Logger for recording warnings or errors during API communication.
     */
    private LoggerInterface $logger;

    public function __construct(
        string $apiKey,
        string $apiPath,
        HttpClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->apiKey = $apiKey;
        $this->apiPath = $apiPath;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * Gets and parses weather data for a given city.
     *
     * @param string $city The name of the city to query.
     * @return array An array with parsed weather data or an 'error' key.
     */
    public function getWeatherData(string $city): array
    {
        $response = $this->fetchData($city);

        // Return the error response if an error occurred.
        if (!empty($response['error'])) {
            return $response;
        }

        $data = json_decode($response, true);
        $result = [
            'city' => $data['location']['name'],
            'country' => $data['location']['country'],
            'temperature' => $data['current']['temp_c'],
            'condition' => $data['current']['condition']['text'],
            'humidity' => $data['current']['humidity'],
            'wind_speed' => $data['current']['wind_kph'],
            'last_updated' => $data['current']['last_updated'],
        ];

        // Save data to log file.
        $this->logger->info(sprintf(
            'Погода в %s: %.1f°C, %s',
            $result['city'],
            $result['temperature'],
            $result['condition']
        ));

        return $result;
    }

    /**
     * Fetches current weather data for a given city from the Weather API.
     *
     * @param string $city The name of the city to query.
     *
     * @return string|array Returns the raw response content as string, or an array with 'error' key if failed.
     */
    public function fetchData(string $city): array|string
    {
        $url = sprintf(
            '%s?key=%s&q=%s',
            rtrim($this->apiPath, '/'),
            $this->apiKey,
            urlencode($city)
        );

        try {
            $response = $this->httpClient->request('GET', $url, [
              'timeout' => 30,
            ]);

            return $response->getContent();
        } catch (
            TransportExceptionInterface |
            ClientExceptionInterface |
            RedirectionExceptionInterface |
            ServerExceptionInterface $e
        ) {
            $this->logger->warning('HTTP request failed: ' . $e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }
}
