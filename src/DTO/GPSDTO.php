<?php

namespace App\DTO;

class GPSDTO
{
  public ?string $latitude;
  public ?string $longitude;

  public function __construct(?string $latitude, ?string $longitude)
  {
    $this->latitude = $latitude;
    $this->longitude = $longitude;
  }

  public function getLatitude(): ?string
  {
    return $this->latitude;
  }

  public function getLongitude(): ?string
  {
    return $this->longitude;
  }
}
