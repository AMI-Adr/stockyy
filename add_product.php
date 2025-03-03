<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération des données en s'assurant qu'elles existent
    $nom         = $_POST['nom'] ?? '';
    $categorie   = $_POST['categorie'] ?? '';
    $reference   = $_POST['reference'] ?? '';
    $quantite    = $_POST['quantite'] ?? 0;
    $fournisseur = $_POST['fournisseur'] ?? '';
    $prix        = $_POST['prix'] ?? 0.0;

    // Insertion dans la BDD
    $stmt = $pdo->prepare("INSERT INTO Produit (nom, categorie, reference, quantite, fournisseur, prix) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$nom, $categorie, $reference, $quantite, $fournisseur, $prix])) {
        // Récupérer l'ID du produit inséré
        $id = $pdo->lastInsertId();

        // Retourner le code HTML de la nouvelle ligne
        echo '<tr data-id="'. $id .'">';
        echo '<td>' . htmlspecialchars($nom) . '</td>';
        echo '<td>' . htmlspecialchars($categorie) . '</td>';
        echo '<td>' . htmlspecialchars($reference) . '</td>';
        echo '<td>' . $quantite . '</td>';
        echo '<td>Ar' . number_format($prix, 2) . '</td>';
        echo '<td><button class="edit-btn" data-id="'. $id .'">✏️</button> <button class="delete-btn" data-id="'. $id .'">🗑️</button></td>';
        echo '</tr>';
    } else {
        http_response_code(500);
        echo "Erreur lors de l'insertion";
    }
}
?> 