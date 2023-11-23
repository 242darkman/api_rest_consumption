<?php

use Exception as RequestException;

/**
 * Handles the GET request from the user
 */
function handleGetRequest($get) {
    $siren = getSirenParameter($get);

    if (!$siren) {
        sendErrorResponse(400, "Erreur 400 : Le paramètre 'siren' est requis.");
        return;
    }

    $content = readEnterpriseFile();
    if ($content === false) {
        sendErrorResponse(500, "Erreur 500 : Problème lors de l'ouverture du fichier");
        return;
    }

    $enterpriseList = decodeEnterpriseContent($content);
    if ($enterpriseList === null) {
        sendErrorResponse(500, "Erreur 500 : Problème lors de la conversion du contenu en JSON");
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
    foreach ($enterpriseList['save_enterprises'] as $enterprise) {
        if ($enterprise['siren'] === $siren) {
            sendEnterpriseResponse($enterprise);
            return;
        }
    }
    echo "Aucune entreprise trouvée, la liste est vide";
}

/**
 * Sends an error response with a specific status code and message
 */
function sendErrorResponse($statusCode, $message) {
    header("HTTP/1.1 $statusCode");
    echo $message;
    throw new RequestException($message);
}

/**
 * Sends the enterprise information as a JSON response
 */
function sendEnterpriseResponse($enterprise) {
    header('HTTP/1.1 200 OK');
    header('Content-Type: application/json');
    echo json_encode($enterprise, JSON_PRETTY_PRINT);
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
    sendErrorResponse(405, "Erreur 405 : Méthode non autorisée");
}

?>
