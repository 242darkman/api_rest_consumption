<?php

namespace App\Service;

class EnterpriseFinder
{
    private $filePath;

    public function __construct(string $filePath = "")
    {
        $this->filePath = $filePath;
    }

    /**
     * Finds and returns an enterprise with the given SIREN number from a JSON file.
     *
     * @param int $siren The SIREN number of the enterprise to find.
     * @return array|null The enterprise with the given SIREN number, or null if not found.
     */
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

    /**
     * Find an enterprise by its SIREN number and return it as an array.
     *
     * @param array $enterprises The array of enterprises to search through.
     * @param string $siren The SIREN number to search for.
     * @return array|null The found enterprise as an array, or null if not found.
     */
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
