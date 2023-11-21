<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EnterpriseStorageService
{
    private $session;
    private $filePath = 'enterprises.txt';

    public function storeInSession(array $enterpriseData, SessionInterface $session): void
    {
        $existingEnterprises = $this->session->get('enterprises', []);

        if (!is_array($existingEnterprises)) {
            $existingEnterprises = unserialize($existingEnterprises) ?: [];
        }

        $siren = $enterpriseData['siren'];
        $existingEnterprises[$siren] = $enterpriseData;

        $this->session->set('enterprises', serialize($existingEnterprises));
    }

    public function storeInFileUnique($enterpriseData): void
    {
        $siren = $enterpriseData->getSiren();

        $saveData = [
            'save_enterprises' => []
        ];

        if (file_exists($this->filePath)) {
            $contents = file_get_contents($this->filePath);
            $decodedContents = json_decode($contents, true);
            $saveData['save_enterprises'] = $decodedContents['save_enterprises'] ?? [];
        }

        $saveData['save_enterprises'][$siren] = [
            'siren' => $siren,
            'siret' => $enterpriseData->getSiret(),
            'Raison_sociale' => $enterpriseData->getRaisonSociale(),
            'Adresse' => [
                'Num' => $enterpriseData->getAdresse()["Num"],
                'Voie' => $enterpriseData->getAdresse()["Voie"],
                'Code_postal' => $enterpriseData->getAdresse()["Code_postal"],
                'Ville' => $enterpriseData->getAdresse()["Ville"],
                'Geo_adresse' => $enterpriseData->getAdresse()["Geo_adresse"],
                'GPS' => [
                    'Latitude' => $enterpriseData->getAdresse()["GPS"]['Latitude'],
                    'Longitude' => $enterpriseData->getAdresse()["GPS"]['Longitude'],
                ]
            ]
        ];

        $jsonData = json_encode($saveData, JSON_PRETTY_PRINT);
        file_put_contents($this->filePath, $jsonData);



    /**
     * Loads the enterprises from the specified file path.
     *
     * @throws \Exception if the specified file does not exist or cannot be read
     * @throws \Exception if there is an error decoding the JSON file
     * @throws \Exception if the key 'save_enterprises' is not found in the JSON file
     * @return array the array of enterprises loaded from the file
     */
    public function loadEnterprises(): array {
        if (!file_exists($this->filePath)) {
            throw new \Exception("Le fichier spécifié n'existe pas: {$this->filePath}");
        }

        $fileContent = file_get_contents($this->filePath);
        if ($fileContent === false) {
            throw new \Exception("Impossible de lire le fichier: {$this->filePath}");
        }

        $decodedJson = json_decode($fileContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Erreur de décodage JSON: " . json_last_error_msg());
        }

        if (!isset($decodedJson['save_enterprises'])) {
            throw new \Exception("La clé 'save_enterprises' n'est pas trouvée dans le fichier.");
        }

        return $decodedJson['save_enterprises'];
    }

/**
 * Updates the enterprise data with the given SIREN.
 *
 * @param string $siren The SIREN of the enterprise to update.
 * @param array $updatedData An associative array containing the updated data.
 * @throws \Exception If there is an error loading the enterprise data or if no enterprise is found with the given SIREN.
 */
    public function updateEnterpriseData($siren, $updatedData) {
        $enterprises = $this->loadEnterprises();

        if (!is_array($enterprises)) {
            throw new \Exception("Erreur lors du chargement des données d'entreprises.");
        }

        if (!array_key_exists($siren, $enterprises)) {
            throw new \Exception("Aucune entreprise trouvée avec le SIREN: $siren");
        }

        $enterpriseData = $enterprises[$siren];
        $addressData = $updatedData['Adresse'] ?? null;

        $enterpriseDTO = new EnterpriseDTO(
            $enterpriseData['siren'],
            $enterpriseData['siret'],
            $enterpriseData['Raison_sociale'],
            $this->createAddressDTO($enterpriseData['Adresse'])->toArray()
        );

        if ($addressData) {
            $addressDTO = $this->createAddressDTO($addressData);
            $enterpriseDTO->Adresse = [
                'Num' => $addressDTO->getNumber(),
                'Voie' => $addressDTO->getStreet(),
                'Code_postal' => $addressDTO->getPostalCode(),
                'Ville' => $addressDTO->getCity(),
                'Geo_adresse' => $addressDTO->getGeoAdresse(),
                'GPS' => $addressDTO->getGps()
            ];
        }

        foreach ($updatedData as $key => $value) {
            if ($key !== 'Adresse' && property_exists($enterpriseDTO, $key)) {
                $enterpriseDTO->{$key} = $value;
            }
        }

        $storedEnterprise = $this->storeInFileUnique($enterpriseDTO);

        return $storedEnterprise;
    }

    private function createAddressDTO($addressData) {
        $gpsArray = [
            'Latitude' => $addressData['GPS']['Latitude'],
            'Longitude' => $addressData['GPS']['Longitude']
        ];

        return new AddressDTO(
            $addressData['Num'],
            $addressData['Voie'],
            $addressData['Code_postal'],
            $addressData['Ville'],
            $addressData['Geo_adresse'],
            $gpsArray
        );
    }
    }


}