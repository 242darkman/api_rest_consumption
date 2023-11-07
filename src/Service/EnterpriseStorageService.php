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
    }


}