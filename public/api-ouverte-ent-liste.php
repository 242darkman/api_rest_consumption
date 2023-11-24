<?php

/************************************************************************************************
 *      This files contains utils functions that allows us to retrieve all saved enterprises
 ***********************************************************************************************/

/**
 * Handle the GET request from user
 */
function handleGetRequest($get) {
    if (!isset($get['format'])){
        sendResponse(400, ["Erreur" => "le paramètre 'format' est obligatoire."]);
        return;
    }

    $format = $get['format'];

    if (!isValidFormat($format)) {
        sendResponse(406, ["Erreur" => "Le paramètre 'format' doit être 'json' ou 'csv'."]);
        return;
    }

    $content = file_get_contents('enterprises.txt');

    if (filesize('enterprises.txt') <= 0) {
        sendResponse(200, ["enterprises" => []]);
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
    $decodedContent = json_decode($content, true); // Décode le contenu JSON en tableau PHP

    if ($decodedContent === null && json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(500, ["Erreur" => "Erreur de décodage JSON: " . json_last_error_msg()]);
        return;
    }

    sendResponse(200, ["enterprises" => $decodedContent['save_enterprises']]);
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
function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, true);
}

/**
 * Check if the request method is GET
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handleGetRequest($_GET);
} else {
    sendResponse(405, ['Erreur' => "Méthode non autorisée"]);
}

?>
