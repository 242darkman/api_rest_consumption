<?php

// File path for storing enterprises data
$filename = 'enterprises.txt';

/**
 * Handles the main request flow
 */
function handleRequest($filename, $requestMethod) {
    if ($requestMethod !== 'POST') {
        sendResponse(405, ["erreur" => "Méthode non autorisée"]);
        return;
    }
    handlePostRequest($filename);
}

/**
 * Processes POST requests
 */
function handlePostRequest($filename) {
    $data = getJsonInputData();

    if (!isValidData($data)) {
        sendResponse(400, ["erreur" => "Format JSON invalide ou données manquantes"]);
        return;
    }

    $allEnterprises = getEnterprisesData($filename);
    processEnterpriseData($allEnterprises, $data, $filename);
}

/**
 * Reads JSON input data from the request body
 */
function getJsonInputData() {
    $jsonData = file_get_contents('php://input');
    return json_decode($jsonData, true);
}

/**
 * Validates the input data
 */
function isValidData($data) {
    if (!$data || !isset($data['Siren'], $data['Raison_sociale'], $data['Adresse'])) {
        return false;
    }

    if (!preg_match('/^[0-9]{9}$/', $data['Siren'])) {
        return false;
    }

    if (trim($data['Raison_sociale']) === '') {
        return false;
    }

    if (!isset($data['Adresse']['Code_postale']) || !preg_match('/^[0-9]{5}$/', (string)$data['Adresse']['Code_postale'])) {
        return false;
    }

    if (trim($data['Adresse']['Ville']) === '') {
        return false;
    }

    return true;
}

/**
 * Retrieves the current stored enterprises data
 */
function getEnterprisesData($filename) {
    $fileContent = file_get_contents($filename);
    return json_decode($fileContent, true) ?? [];
}

/**
 * Processes the new enterprise data
 */
function processEnterpriseData($allEnterprises, $data, $filename) {
    $enterprises = $allEnterprises['save_enterprises'] ?? [];

    if (isset($enterprises[$data['Siren']])) {
        sendResponse(409, ["erreur" => "Entreprise déjà existante"]);
        return;
    }

    addNewEnterprise($enterprises, $data, $filename);
}

/**
 * Adds a new enterprise to the data and saves it
 */
function addNewEnterprise(&$enterprises, $data, $filename) {
    $enterprises[$data['Siren']] = $data;
    $allEnterprises['save_enterprises'] = $enterprises;
    file_put_contents($filename, json_encode($allEnterprises, JSON_PRETTY_PRINT));
    sendResponse(201, [
        'message' => 'Entreprise créée',
        '_links' => [
                    'self' => [
                        'href' => '/api-ouverte-ent.php?siren='.$data['Siren'],
                    ]
                ],
        ]
    );
}

/**
 * Sends an HTTP response with the given status code and data
 */
function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo is_array($data) ? json_encode($data, JSON_PRETTY_PRINT) : $data;
}

handleRequest($filename, $_SERVER['REQUEST_METHOD']);

?>
