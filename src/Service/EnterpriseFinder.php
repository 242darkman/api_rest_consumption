<?php

namespace App\Service;

class EnterpriseFinder
{
    private $filePath;

    public function __construct(string $filePath = "")
    {
        $this->filePath = $filePath;
    }

    public function findBySirenIntoJsonFile($siren)
    {
        $jsonContent = file_get_contents($this->filePath);
        $enterprises = json_decode($jsonContent, true);

        foreach ($enterprises as $enterprise) {
            if ($enterprise['siren'] === $siren) {
                return $enterprise;
            }
        }

        return null;
    }

    public function findBySirenIntoArray($enterprises, $siren)
    {
        foreach ($enterprises as $enterprise) {
            if ($enterprise['siren'] === $siren) {
                return $enterprise;
            }
        }

        return null;
    }
}
