<?php

namespace App\Service;

use App\DTO\EnterpriseDTO;
use App\DTO\AddressDTO;
use App\DTO\GPSDTO;

class EnterpriseDTOTransformer
{
  public function transform(array $inputData): EnterpriseDTO
  {
    $headOffice = $inputData['siege'] ?? $inputData[0]['siege'];
    $officeAdress = intval($headOffice['numero_voie']) . ' ' . $headOffice['type_voie'] . ' ' . $headOffice['libelle_voie'] . ' ' . $headOffice['code_postal'] . ' ' . $headOffice['libelle_commune'];

    $gpsDTO = new GPSDTO($headOffice['latitude'], $headOffice['longitude']);
    $addressDTO = new AddressDTO(
      intval($headOffice['numero_voie']),
      $headOffice['type_voie'] . ' ' . $headOffice['libelle_voie'],
      $headOffice['code_postal'],
      $headOffice['libelle_commune'],
      $officeAdress,
      ['Latitude' => $gpsDTO->latitude, 'Longitude' => $gpsDTO->longitude]
    );
    $siren = $inputData['siren'] ?? $inputData[0]['siren'];
    $siret = $headOffice['siret'] ?? $inputData[0]['siret'];
    $raison_sociale = $inputData['nom_complet'] ?? $inputData[0]['nom_complet'];

    return new EnterpriseDTO(
      $siren,
      $siret,
      $raison_sociale,
      [
        'Num' => $addressDTO->number,
        'Voie' => $addressDTO->street,
        'Code_postal' => $addressDTO->postalCode,
        'Ville' => $addressDTO->city,
        'Geo_adresse' => $addressDTO->geo_adresse,
        'GPS' => $addressDTO->gps
      ]
    );
  }

  public function transformAll(array $results): array
  {
    return array_map([$this, 'transform'], $results);
  }
}
