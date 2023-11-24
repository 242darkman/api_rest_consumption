<?php

use Exception as RequestException;

/************************************************************************************************
 *      This files contains utils functions that allows us to retrieve a specific enterprise
 ***********************************************************************************************/

/**
 * Handles the GET request from the user
 */
function handleGetRequest($get) {
    $siren = getSirenParameter($get);

    if (!$siren) {
        sendResponse(400, ['Erreur' => "Le paramètre 'siren' est obligatoire"]);
        return;
    }

    $content = readEnterpriseFile();
    if ($content === false) {
        sendResponse(500, ['Erreur' => "Problème lors de l'ouverture du fichier"]);
        return;
    }

    $enterpriseList = decodeEnterpriseContent($content);
    if ($enterpriseList === null) {
        sendResponse(500, ['Erreur' => "Problème lors de la conversion du contenu en JSON"]);
        return;
    }

    findAndSendEnterprise($enterpriseList, $siren);
}

/**
 * Retrieves the 'siren' parameter from the GET request
 */
function getSirenParameter($get) {
    return isset($get['siren']) ? $get['siren'] : null;
}

/**
 * Reads content from the enterprises file
 */
function readEnterpriseFile() {
    return filesize('enterprises.txt') > 0 ? file_get_contents('enterprises.txt') : false;
}

/**
 * Decodes the JSON content of the file
 */
function decodeEnterpriseContent($content) {
    return json_decode($content, true);
}

/**
 * Finds and sends the enterprise information if available
 */
function findAndSendEnterprise($enterpriseList, $siren) {
    foreach ($enterpriseList['save_enterprises'] as $enterpriseId => $enterprise) {
        if ($enterpriseId == $siren) {
            sendResponse(200, ['enterprise' => [$enterprise]]);
            return;
        }
    }
    sendResponse(200, ['enterprise' => []]);
}

/**
 * Sends an error response with a specific status code and message
 */
function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

/**
 * Check the request method, and process accordingly
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        handleGetRequest($_GET);
    } catch (RequestException $e) {
        echo 'Une erreur est survenue. Veuillez réessayer plus tard.';
        error_log($e->getMessage());
    }
} else {
    sendResponse(405, ['message' => 'Méthode non autorisée']);
}

?>
