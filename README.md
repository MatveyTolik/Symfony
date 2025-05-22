# Guide to running an existing Symfony 6.4 project from Git using DDEV

## Prerequisites
Make sure you have the following installed:
- Docker Desktop
- DDEV
- Composer
- Git

## Step 1: Clone the Project from Git

```
git clone https://github.com/MatveyTolik/Symfony.git
cd Symfony
```

## Step 2: Start the DDEV Environment

```
ddev start
```

## Step 4: Install PHP Dependencies

```
ddev composer install
```
This reads composer.json and installs all required Symfony packages.

## Step 5: Open the Project in a Browser

Open the URL shown in ddev start, for example:
👉 https://symfony.ddev.site/weather/Kyiv

You should now see your Symfony app running


# A brief explanation of your implementation decisions

The WeatherController is responsible for handling requests to the `/weather/{city}` route, where `{city}` is an optional parameter. This controller calls the `getWeatherData` method from the `weatherService`.

The `getWeatherData` method then invokes the `fetchData` method, passing the `$city` parameter received from the controller. The `fetchData` method retrieves the API endpoint and key from the `.env` file using the `WEATHER_API_PATH` and `WEATHER_API_KEY` variables. It sends a request to a third-party weather service, passing the `apiKey` and `$city` as parameters to the `WEATHER_API_PATH`. It also handles exceptions in case the request fails and logs the error.

If an error occurs, the `getWeatherData` method captures the error message and returns it to the controller, where it is displayed using a Twig template.

If the request is successful, the response data is stored in the array within the `getWeatherData` method. A log entry is also written to the `weather_log.txt` file. The data is then returned to the controller and rendered via the `weather.html.twig` template.
