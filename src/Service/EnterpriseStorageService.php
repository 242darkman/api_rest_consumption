<?php

namespace App\Service;

use App\DTO\AddressDTO;
use App\DTO\EnterpriseDTO;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EnterpriseStorageService
{
    private $session;
    private $filePath = 'enterprises.txt';

    /**
     * Stores the given enterprise data in the session.
     *
     * @param array $enterpriseData The enterprise data to store.
     * @param SessionInterface $session The session object.
     * @throws \Some_Exception_Class Description of the exception.
     * @return void
     */
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

    /**
     * Store enterprise data in a file, ensuring uniqueness based on the SIREN number.
     *
     * @param mixed $enterpriseData The data of the enterprise to be stored.
     * @throws Exception If there is an error accessing or modifying the file.
     */
    public function storeInFileUnique($enterpriseData)
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

        $address = $enterpriseData->getAdresse();

        $saveData['save_enterprises'][$siren] = [
            'siren' => $siren,
            'siret' => $enterpriseData->getSiret(),
            'Raison_sociale' => $enterpriseData->getRaisonSociale(),
            'Adresse' => [
                'Num' => $address['Num'],
                'Voie' => $address['Voie'],
                'Code_postal' => $address['Code_postal'],
                'Ville' => $address['Ville'],
                'Geo_adresse' => $address['Geo_adresse'],
                'GPS' => [
                    'Latitude' => $address['GPS']['Latitude'],
                    'Longitude' => $address['GPS']['Longitude'],
                ]
            ]
        ];

        $jsonData = json_encode($saveData, JSON_PRETTY_PRINT);
        file_put_contents($this->filePath, $jsonData);

        return $saveData['save_enterprises'][$siren];
    }


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

    /**
     * Deletes an enterprise from the list of enterprises based on its SIREN.
     *
     * @param string $siren The SIREN of the enterprise to be deleted.
     * @throws \Exception if no enterprise is found with the given SIREN.
     * @return void
     */
    public function deleteEnterprise($siren) {
        $enterprises = $this->loadEnterprises();

        unset($enterprises[$siren]);

        if (empty($enterprises)) {
            unlink($this->filePath);
            return;
        } 

        $jsonData = json_encode(['save_enterprises' => $enterprises], JSON_PRETTY_PRINT);
        file_put_contents($this->filePath, $jsonData);
    }


}