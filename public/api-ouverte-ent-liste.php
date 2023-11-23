<?php

/************************************************************************************************
 *      This files contains utils functions that allows us to retrieve all saved enterprises
 ***********************************************************************************************/

/**
 * Handle the GET request from user
 */
function handleGetRequest($format) {
    if (!isValidFormat($format)) {
        sendErrorResponse(406, "Format non valide");
        return;
    }

    $content = file_get_contents('enterprises.txt');

    if (filesize('enterprises.txt') <= 0) {
        echo "Aucune entreprise trouvée, la liste est vide";
        return;
    }

    switch ($format) {
        case 'json':
            sendJsonResponse($content);
            break;
        case 'csv':
            sendCsvResponse($content);
            break;
    }
}

/**
 * Check if the format is valid
 */
function isValidFormat($format) {
    return in_array($format, ['json', 'csv']);
}

/**
 * Send a JSON response
 */
function sendJsonResponse($content) {
    header('Content-Type: application/json');
    echo $content;
}

/**
 * Send a CSV response
 */
function sendCsvResponse($content) {
    header('Content-Type: text/csv');
    $enterpriseList = json_decode($content, true);
    outputCsvHeaders();
    outputCsvContent($enterpriseList['save_enterprises']);
}

/**
 * Output CSV headers
 */
function outputCsvHeaders() {
    echo "siren,siret,Raison_sociale,Adresse_Num,Adresse_Voie,Adresse_Code_postal,Adresse_Ville,Geo_adresse,GPS_Latitude,GPS_Longitude\n";
}

/**
 * Output CSV content
 */
function outputCsvContent($enterprises) {
    foreach ($enterprises as $enterprise) {
        echo implode(',', [
            $enterprise['siren'],
            $enterprise['siret'],
            $enterprise['Raison_sociale'],
            $enterprise['Adresse']['Num'],
            $enterprise['Adresse']['Voie'],
            $enterprise['Adresse']['Code_postal'],
            $enterprise['Adresse']['Ville'],
            $enterprise['Adresse']['Geo_adresse'],
            $enterprise['Adresse']['GPS']['Latitude'],
            $enterprise['Adresse']['GPS']['Longitude']
        ]) . "\n";
    }
}

/**
 * Send an error response
 */
function sendErrorResponse($statusCode, $message) {
    header("HTTP/1.1 $statusCode");
    echo $message;
}

/**
 * Check if the request method is GET
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handleGetRequest($_GET['format']);
} else {
    sendErrorResponse(405, "Erreur 405 : Méthode non autorisée");
}

?>
