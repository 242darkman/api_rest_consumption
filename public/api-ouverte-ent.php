<?php

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Récupérer le SIREN de l'entreprise depuis la requête
        $siren = isset($_GET['siren']) ? $_GET['siren'] : null;

        // Vérifier si le SIREN est fourni
        if ($siren === null) {
            header('HTTP/1.1 400 Bad Request');
            echo "Erreur 400 : Le paramètre 'siren' est requis.";
            exit;
        }

        $content = file_get_contents('enterprises.txt');
        
        if (filesize('enterprises.txt') > 0) {

            if (!$content) {
                header('HTTP/1.1 500 Internal Server Error');
                echo "Erreur 500 : Problème lors de l'ouverture du fichier";
                exit;
            }
    
            $enterpriseList = json_decode($content, true);
    
            if ($enterpriseList === null) {
                header('HTTP/1.1 500 Internal Server Error');
                echo "Erreur 500 : Problème lors de la conversion du contenu en JSON";
                exit;
            }
    
            // Rechercher l'entreprise avec le SIREN spécifié
            $found = false;
            $selectedEnterprise = null;
            foreach ($enterpriseList['save_enterprises'] as $enterprise) {
                if ($enterprise['siren'] === $siren) {
                    $found = true;
                    $selectedEnterprise = $enterprise;
                    break;
                }
            }
    
            // Si l'entreprise avec le SIREN est trouvée, retourner les informations au format JSON
            if ($found) {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json');
                echo json_encode($selectedEnterprise, JSON_PRETTY_PRINT);
            } else {
                header('HTTP/1.1 404 Not Found');
                echo "Erreur 404 : Aucune entreprise trouvée.";
            }
        }
        else{
            echo "Aucune entreprise trouvée, la liste est vide";
            exit;
        }

    } else {
        // Méthode HTTP non autorisée
        header('HTTP/1.1 405 Method Not Allowed');
        echo "Erreur 405 : Méthode non autorisée";
        exit();
    }
?>
