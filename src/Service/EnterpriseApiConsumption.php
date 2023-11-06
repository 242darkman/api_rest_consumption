<?php

//namespace app\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EnterpriseApiConsumption
{

  private $client;

  public function __construct(HttpClientInterface $client)
  {
    $this->client = $client;
  }
}
