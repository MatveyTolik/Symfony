<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WeatherController extends AbstractController
{
    private WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    #[Route('/weather/{city}', name: 'weather')]
    public function getWeather(string $city = 'London'): Response
    {
        // Load data from external service.
        $weatherData = $this->weatherService->getWeatherData($city);

        return $this->render('weather/weather.html.twig', [
          'city' => $city,
          'weatherData' => $weatherData,
        ]);
    }
}
