<?php

namespace App\Controller;

use App\Repository\MeasurementRepository;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherController extends AbstractController
{
    #[Route('/weather/{id}', name: 'app_weather_id', requirements: ['id' => '\d+'])]
    public function weatherById(int $id, LocationRepository $locationRepository, MeasurementRepository $repository): Response
    {
        $location = $locationRepository->find($id);
        if (!$location) {
            throw $this->createNotFoundException(sprintf('Location with ID "%d" not found.', $id));
        }
        $measurements = $repository->findByLocation($location);
        return $this->render('weather/city.html.twig', [
            'location' => $location,
            'measurements' => $measurements,
        ]);
    }

    #[Route('/weather/{city}', name: 'app_weather', requirements: ['city' => '.+'])]
    public function city(string $city, LocationRepository $locationRepository, MeasurementRepository $repository): Response
    {
        $parts = explode(',', $city);
        $cityName = trim($parts[0]);
        $country = isset($parts[1]) ? trim($parts[1]) : null;

        $location = $locationRepository->findByCityAndCountry($cityName, $country);
        if (!$location) {
            throw $this->createNotFoundException(sprintf('Location "%s" not found.', $city));
        }
        $measurements = $repository->findByLocation($location);
        return $this->render('weather/city.html.twig', [
            'location' => $location,
            'measurements' => $measurements,
        ]);
    }
}