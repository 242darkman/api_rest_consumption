<?php

namespace App\DTO;


class EnterpriseDTO
{
  public string $siren;
  public string $siret;
  public string $Raison_sociale;
  public array $Adresse;

  public function __construct($siren, $siret, $businessName, $address)
  {
    $this->siren = $siren;
    $this->siret = $siret;
    $this->Raison_sociale = $businessName;
    $this->Adresse = $address;
  }

  public function getSiren()
  {
    return $this->siren;
  }

  public function getSiret()
  {
    return $this->siret;
  }

  public function getRaisonSociale()
  {
    return $this->Raison_sociale;
  }

  public function getAdresse()
  {
    return $this->Adresse;
  }
}
