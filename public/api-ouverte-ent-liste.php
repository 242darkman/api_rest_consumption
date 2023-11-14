<?php

    //Vérifier le verbe HTTP utilisé 
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        
        // Vérifie que le format est bon
        $format = $_GET['format'];
        if (!in_array($format, ['json', 'csv'])) {
            header('HTTP/1.1 406 Not Acceptable');
            echo("erreur 406");
            exit;
        }
        else{

            // Ouvrir le fichier qui contient les entreprises en mode lecture
            $fichier = fopen('enterprises.txt', "r");

            //vérifier que le fichier n'est pas vide
            if (filesize('enterprises.txt') > 0) {
                echo fread($fichier, filesize('enterprises.txt'));

                 //Retourne la liste des entreprises au format demandé
                if ($format === 'json') {
                    header('Content-Type: application/json');
                    echo json_encode($fichier);
                    //echo $fichier;
                } else {
                    header('Content-Type: text/csv');
                    echo $fichier;
                }

            } else {
                echo "Aucune entreprise enregistrée";
            }
            
            // fclose($fichier);
        }

    }
    else{
        header('HTTP/1.1 405');
        echo("Erreur 405");
        exit();
    }
?>
