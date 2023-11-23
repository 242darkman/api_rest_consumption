<?php

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $format = $_GET['format'];

        // Vérifier le format demandé
        if (!in_array($format, ['json', 'csv'])) {
            header('HTTP/1.1 406 Not Acceptable');
            echo "erreur 406";
            exit;
        }

         // Lecture du fichier qui contient les entreprises
        $content = file_get_contents('enterprises.txt'); //
        
        // Vérifier si le fichier est vide
        if (filesize('enterprises.txt') > 0) {
           
            // Envoyer le contenu au format JSON
            if ($format === 'json') {
                header('Content-Type: application/json');
                echo $content;
            }
            elseif ($format === 'csv') {
                header('Content-Type: text/csv');
                
                // Convertir le JSON en tableau associatif
                $enterpriseList = json_decode($content, true);
        
                // Écrire les en-têtes CSV
                echo "siren,siret,Raison_sociale,Adresse_Num,Adresse_Voie,Adresse_Code_postal,Adresse_Ville,Geo_adresse,GPS_Latitude,GPS_Longitude\n";

                // Écrire le contenu CSV
                foreach ($enterpriseList['save_enterprises'] as $enterprise) {
                    echo "{$enterprise['siren']},{$enterprise['siret']},{$enterprise['Raison_sociale']},{$enterprise['Adresse']['Num']},{$enterprise['Adresse']['Voie']},{$enterprise['Adresse']['Code_postal']},{$enterprise['Adresse']['Ville']},{$enterprise['Adresse']['Geo_adresse']},{$enterprise['Adresse']['GPS']['Latitude']},{$enterprise['Adresse']['GPS']['Longitude']}\n";
                }
            }
        }
        else{
            echo "Aucune entreprise trouvée, la liste est vide";
            exit;
        }
    } else {
        header('HTTP/1.1 405 Method Not Allowed');
        echo "Erreur 405 : Méthode non autorisée";
        exit();
    }

?>
