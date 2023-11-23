<?php

namespace App\DTO;

class AddressDTO
{
  public int $number;
  public string $street;
  public string $postalCode;
  public string $city;
  public string $geo_adresse;
  public array $gps;

  public function __construct($number, $street, $postalCode, $city, $geo_adresse, $gps)
  {
    $this->number = $number;
    $this->street = $street;
    $this->postalCode = $postalCode;
    $this->city = $city;
    $this->geo_adresse = $geo_adresse;
    $this->gps = $gps;
  }

  public function getNumber(): int
  {
    return $this->number;
  }

  public function getStreet(): string
  {
    return $this->street;
  }

  public function getPostalCode(): string
  {
    return $this->postalCode;
  }

  public function getCity(): string
  {
    return $this->city;
  }

  public function getGeoAdresse(): string
  {
    return $this->geo_adresse;
  }

  public function getGps(): array
  {
    return $this->gps;
  }
}
