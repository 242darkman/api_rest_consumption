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

  public function search(string $query, int $page = 1, int $perPage = 10): array
  {
    $response = $this->client->request(
      'GET',
      'https://recherche-entreprises.api.gouv.fr/search',
      [
        'query' => [
          'q' => $query,
          'per_page' => $perPage,
          'page' => $page,
        ],
      ]
    );

    if (200 !== $response->getStatusCode()) {
      throw new \Exception('Failed to retrieve data from the API.');
    }

    $content = $response->getContent();
    $enterprises = json_decode($content, true);

    if (null === $enterprises) {
      throw new \Exception('Failed to decode JSON data.');
    }

    return $enterprises;
  }
}
