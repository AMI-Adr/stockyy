<?php
session_start();
require_once 'includes/db.php';

try {
    // Sélectionner les produits en stock critique (stock bas ou stock élevé)
    $stmt = $pdo->query("SELECT * FROM Produit WHERE quantite < 10 OR quantite > 80");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>
<table class="animated-table">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Catégorie</th>
            <th>Référence</th>
            <th>Quantité</th>
            <th>Prix</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $prod): ?>
        <tr>
            <td><?= htmlspecialchars($prod['nom']) ?></td>
            <td><?= htmlspecialchars($prod['categorie']) ?></td>
            <td><?= htmlspecialchars($prod['reference']) ?></td>
            <td><?= $prod['quantite'] ?></td>
            <td>Ar <?= number_format($prod['prix'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table> 