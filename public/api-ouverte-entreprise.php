<?php

// Chemin vers le fichier de stockage des entreprises
$filename = 'enterprises.txt';

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupérer les données JSON de la requête
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    // Vérifier si le format JSON est valide et les données nécessaires sont présentes
    if ($data && isset($data['Siren'], $data['Raison_sociale'], $data['Adresse'])) {

        // Lire le contenu du fichier des entreprises
        $enterprises = file_get_contents($filename);
        $enterprises = json_decode($enterprises, true);

        // Vérifier si l'entreprise existe déjà
        if (isset($enterprises[$data['Siren']])) {
            http_response_code(409); // Conflict
            echo "Erreur 409 :Entreprise existe déjà";
        } else {
            // Ajouter la nouvelle entreprise
            $enterprises[$data['Siren']] = $data;

            // Enregistrer les entreprises mises à jour dans le fichier
            file_put_contents($filename, json_encode($enterprises, JSON_PRETTY_PRINT));

            // Répondre avec un code 201 (Created)
            http_response_code(201);
            echo json_encode(['message' => 'Entreprise créée']);
        }

    } else {
        // Données JSON invalides ou manquantes
        header('HTTP/1.1 400 Method Not Allowed');
        echo "Erreur 405 : Format JSON invalide ou données manquantes";
        exit();
    }

} else {
   // Méthode HTTP non autorisée
   header('HTTP/1.1 405 Method Not Allowed');
   echo "Erreur 405 : Méthode non autorisée";
   exit();
}

?>
