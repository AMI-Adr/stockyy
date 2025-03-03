<?php
require_once './includes/db.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';

// Adaptation de la requête pour filtrer par utilisateur, produit ou même l'ID de la vente
$query = "SELECT v.*, u.nom AS user_nom, p.nom AS product_nom 
          FROM Ventes v
          JOIN Utilisateur u ON v.user_id = u.id
          JOIN Produit p ON v.product_id = p.id
          WHERE u.nom LIKE ? OR p.nom LIKE ? OR v.id LIKE ?
          ORDER BY v.created_at DESC";
$stmt = $pdo->prepare($query);
$searchTerm = "%$search%";
$stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($sales as $sale): ?>
    <tr data-id="<?= $sale['id'] ?>">
        <td>#<?= $sale['id'] ?></td>
        <td><?= htmlspecialchars($sale['user_nom']) ?></td>
        <td><?= htmlspecialchars($sale['product_nom']) ?></td>
        <td><?= $sale['quantite'] ?></td>
        <td>Ar <?= number_format($sale['prix_total'], 2) ?></td>
        <td><?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?></td>
    </tr>
<?php endforeach; ?> 