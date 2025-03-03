<?php
require_once './includes/db.php';

$query = "SELECT * FROM Produit WHERE nom LIKE ? OR categorie LIKE ? OR reference LIKE ? ORDER BY nom";
$stmt = $pdo->prepare($query);
$queryInput = isset($_GET['query']) ? $_GET['query'] : '';
$searchTerm = "%$queryInput%";
$stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $product): ?>
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
<?php endforeach; ?> 