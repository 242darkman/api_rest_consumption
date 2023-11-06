<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EnterpriseApiConsumption
{
  private $client;

  public function __construct(HttpClientInterface $client)
  {
    $this->client = $client;
  }

  public function search(string $query): array
  {
    $response = $this->client->request(
      'GET',
      'https://recherche-entreprises.api.gouv.fr/search',
      [
        'query' => [
          'q' => $query,
          'per_page' => 10,
          'page' => 1,
        ],
      ]
    );

    if (200 !== $response->getStatusCode()) {
      throw new \Exception('Failed to retrieve data from the API.');
    }

    return $response->toArray();
  }
}
