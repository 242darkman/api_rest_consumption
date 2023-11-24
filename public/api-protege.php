<?php

// Define the file path for storing and retrieve enterprise data
$filename = 'enterprises.txt';

// User for our basic authentication
$validUsers = [
    'apiuser' => password_hash('apipassword', PASSWORD_BCRYPT)
];

function authenticate($validUsers, $serverAuthUser, $serverAuthPw) {
    if (!isset($serverAuthUser, $serverAuthPw)) {
        header('WWW-Authenticate: Basic realm="Restricted Area"');
        sendResponse(401, ["erreur" => "Autorisation requise"]);
        return false;
    }

    $user = $serverAuthUser;
    $pass = $serverAuthPw;

    if (!isset($validUsers[$user]) || !password_verify($pass, $validUsers[$user])) {
        sendResponse(401, ["erreur" => "Accès non authorisé"]);
        return false;
    }

    return true;
}

/**
 * Handles the main request flow
 */
function handleRequest($filename, $requestMethod, $validUsers, $authUser, $authPw) {
    if (!authenticate($validUsers, $authUser, $authPw)) {
        return;
    }

    switch ($requestMethod) {
        case 'PATCH':
            handlePatchRequest($filename);
            break;
        case 'DELETE':
            handleDeleteRequest($filename);
            break;
        default:
            sendResponse(405, ["erreur" => "Méthode non autorisée"]);
    }
}

/**
 * Processes PATCH requests
 */
function handlePatchRequest($filename) {
    $data = getJsonInputData();

    if (!isValidPatchData($data)) {
        sendResponse(400, ["erreur" => "Format JSON invalide ou données manquantes"]);
        return;
    }

    $allEnterprises = getEnterprisesData($filename);
    $idEnterprise = strval($data['siren']);

    if (!isset($allEnterprises['save_enterprises'][$idEnterprise])) {
        sendResponse(404, ["erreur" => "Entreprise non trouvée"]);
        return;
    }

    updateEnterprise($allEnterprises, $data, $filename);
}

/**
 * Processes DELETE requests
 */
function handleDeleteRequest($filename) {
    $data = getJsonInputData();

    $sirenKey = getSirenKey($data);
    if (!$sirenKey || !preg_match('/^[0-9]{9}$/', $data[$sirenKey])) {
        sendResponse(400, ["erreur" => "SIREN invalide"]);
        return;
    }

    $allEnterprises = getEnterprisesData($filename);
    $siren = $data[$sirenKey];
    if (!isset($allEnterprises['save_enterprises'][$siren])) {
        sendResponse(404, ["erreur" => "Entreprise non trouvée"]);
        return;
    }

    deleteEnterprise($allEnterprises, $siren, $filename);
}

function getSirenKey($data) {
    foreach ($data as $key => $value) {
        if (strtolower($key) === 'siren') {
            return $key;
        }
    }
    return null;
}

/**
 * Reads JSON input data from the request body
 */
function getJsonInputData() {
    $jsonData = file_get_contents('php://input');
    return json_decode($jsonData, true);
}

/**
 * Validates the input data for PATCH request
 */
function isValidPatchData($data) {
    return isset($data['siren']) && preg_match('/^[0-9]{9}$/', $data['siren']);
}

/**
 * Retrieves the current stored enterprises data
 */
function getEnterprisesData($filename) {
    if (!file_exists($filename)) {
        return [];
    }

    $fileContent = file_get_contents($filename);
    return json_decode($fileContent, true) ?? [];
}

/**
 * Update an existing enterprise
 */
function updateEnterprise(&$allEnterprises, $updates, $filename) {
    $lowerCaseUpdates = array_change_key_case($updates, CASE_LOWER);
    $siren = $lowerCaseUpdates['siren'] ?? null;

    if (!isset($allEnterprises['save_enterprises'][$siren])) {
        sendResponse(400, ["erreur" => "SIREN non trouvé"]);
        return;
    }

    foreach ($updates as $key => $value) {
        $lowerCaseKey = strtolower($key);

        if ($lowerCaseKey === 'siren') continue;

        $lowerCaseEnterpriseKeys = array_change_key_case($allEnterprises['save_enterprises'][$siren], CASE_LOWER);
        if (array_key_exists($lowerCaseKey, $lowerCaseEnterpriseKeys)) {
            // find enterprise original key
            $originalKeyIndex = array_search($lowerCaseKey, array_keys($lowerCaseEnterpriseKeys));
            $originalKey = array_keys($allEnterprises['save_enterprises'][$siren])[$originalKeyIndex];

            if (is_array($value) && is_array($allEnterprises['save_enterprises'][$siren][$originalKey])) {
                // update nesting array
                foreach ($value as $subKey => $subValue) {
                    $lowerCaseSubKey = strtolower($subKey);
                    if (array_key_exists($lowerCaseSubKey, $lowerCaseEnterpriseKeys[$lowerCaseKey])) {
                        $originalSubKeyIndex = array_search($lowerCaseSubKey, array_keys($lowerCaseEnterpriseKeys[$lowerCaseKey]));
                        $originalSubKey = array_keys($allEnterprises['save_enterprises'][$siren][$originalKey])[$originalSubKeyIndex];
                        $allEnterprises['save_enterprises'][$siren][$originalKey][$originalSubKey] = $subValue;
                    }
                }
                continue;
            }

            $allEnterprises['save_enterprises'][$siren][$originalKey] = $value;
        }
    }

    file_put_contents($filename, json_encode($allEnterprises, JSON_PRETTY_PRINT));
    sendResponse(200, [
        "message" => "Entreprise modifiée",
        '_links' => [
            'self' => [
                'href' => '/api-ouverte-ent.php?siren=' . $siren,
            ]
        ],
    ]);
}


/**
 * Delete an enterprise
 */
function deleteEnterprise(&$allEnterprises, $siren, $filename) {
    unset($allEnterprises['save_enterprises'][$siren]);

    if (empty($allEnterprises)) {
        unlink($filename);
        sendResponse(200, ["message" => "Entreprise supprimée et fichier supprimé"]);
        return;
    }

    file_put_contents($filename, json_encode($allEnterprises, JSON_PRETTY_PRINT));
    sendResponse(200, ["message" => "Entreprise supprimée"]);
}


/**
 * Sends an HTTP response with the given status code and data
 */
function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

handleRequest($filename, $_SERVER['REQUEST_METHOD'], $validUsers, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

?>