<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $categorie = $_POST['categorie'];
    $reference = $_POST['reference'];
    $quantite = $_POST['quantite'];
    $fournisseur = $_POST['fournisseur'];
    $prix = $_POST['prix'];

    try {
        $stmt = $pdo->prepare("UPDATE Produit SET nom = ?, categorie = ?, reference = ?, quantite = ?, fournisseur = ?, prix = ? WHERE id = ?");
        $stmt->execute([$nom, $categorie, $reference, $quantite, $fournisseur, $prix, $id]);

        // Récupérer le produit mis à jour
        $stmt = $pdo->prepare("SELECT * FROM Produit WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        // Génération dynamique du code HTML pour la ligne du produit
        ob_start();
        ?>
        <tr data-id="<?= $product['id'] ?>">
            <td>
                <?= htmlspecialchars($product['nom']) ?>
                <?php if ($product['quantite'] < 10 || $product['quantite'] > 80): ?>
                    <span class="critical-indicator" title="Stock critique">⚠️</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($product['categorie']) ?></td>
            <td><?= htmlspecialchars($product['reference']) ?></td>
            <td><?= $product['quantite'] ?></td>
            <td>Ar<?= number_format($product['prix'], 2) ?></td>
            <td>
                <button class="edit-btn" data-id="<?= $product['id'] ?>">✏️</button>
                <button class="delete-btn" data-id="<?= $product['id'] ?>">🗑️</button>
            </td>
        </tr>
        <?php
        $row = ob_get_clean();
        echo $row;
    } catch(PDOException $e) {
        http_response_code(500);
        echo "Erreur de base de données : " . $e->getMessage();
    }
} else {
    http_response_code(405);
    echo "Méthode non autorisée";
}
?> 