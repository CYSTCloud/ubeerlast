<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BeerApiService
{
    private $httpClient;
    private $apiUrl = 'https://ubeer-production.up.railway.app';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getBeerById(int $id)
    {
        $response = $this->httpClient->request(
            'GET',
            "{$this->apiUrl}/beers/{$id}"
        );

        return $response->toArray();
    }

    public function updateBeer(int $id, array $data)
    {
        $response = $this->httpClient->request(
            'PATCH',
            "{$this->apiUrl}/beers/{$id}",
            [
                'json' => $data,
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json'
                ]
            ]
        );

        return $response->toArray();
    }

    public function getAllBeers()
    {
        $response = $this->httpClient->request(
            'GET',
            "{$this->apiUrl}/beers"
        );

        return $response->toArray();
    }
}
