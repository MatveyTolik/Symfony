<?php

namespace App\tests\Service;

use App\Service\WeatherService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Unit tests for the WeatherService class.
 */
class WeatherServiceTest extends TestCase
{
    private $httpClientMock;
    private $loggerMock;
    private $apiKey = 'fake_api_key';
    private $apiPath = 'some_api_path';

    protected function setUp(): void
    {
        // Create mocks for HttpClientInterface and LoggerInterface
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
    }

    /**
     * Tests that getWeatherData correctly parses API response
     * and logs the info message.
     */
    public function testGetWeatherData()
    {
        $city = 'London';

        // Sample JSON response returned by the Weather API
        $apiResponse = json_encode([
          'location' => [
            'name' => 'London',
            'country' => 'UK',
          ],
          'current' => [
            'temp_c' => 20.5,
            'condition' => ['text' => 'Sunny'],
            'humidity' => 60,
            'wind_kph' => 10,
            'last_updated' => '2025-05-21 12:00',
          ],
        ]);

        // Mock the HTTP response to return the sample JSON
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getContent')->willReturn($apiResponse);

        // Configure the HttpClient mock to return the mocked response
        $this->httpClientMock->method('request')->willReturn($responseMock);

        // Expect logger info to be called once with a message containing city and weather details
        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with($this->stringContains('Погода в London: 20.5°C, Sunny'));

        $service = new WeatherService($this->apiKey, $this->apiPath, $this->httpClientMock, $this->loggerMock);

        $result = $service->getWeatherData($city);

        // Assert the parsed data matches the expected structure and values
        $this->assertIsArray($result);
        $this->assertEquals('London', $result['city']);
        $this->assertEquals('UK', $result['country']);
        $this->assertEquals(20.5, $result['temperature']);
        $this->assertEquals('Sunny', $result['condition']);
        $this->assertEquals(60, $result['humidity']);
        $this->assertEquals(10, $result['wind_speed']);
        $this->assertEquals('2025-05-21 12:00', $result['last_updated']);
    }

    /**
     * Tests that fetchData handles exceptions correctly,
     * logs a warning, and returns an error array.
     */
    public function testFetchData()
    {
        $city = 'Paris';

        $exception = $this->createMock(TransportExceptionInterface::class);

        // Mock the HTTP response to return the sample JSON
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getContent')->willThrowException($exception);

        // Configure the HttpClient mock to return the mocked response
        $this->httpClientMock->method('request')->willReturn($responseMock);

        // Expect logger warning to be called once with the error message
        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('HTTP request failed: '));

        $service = new WeatherService($this->apiKey, $this->apiPath, $this->httpClientMock, $this->loggerMock);

        $result = $service->fetchData($city);

        // Assert that the result contains the error message
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
    }
}
