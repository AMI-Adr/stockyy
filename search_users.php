<?php
require_once './includes/db.php'; // Adaptez le chemin selon votre structure

$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT * FROM Utilisateur WHERE nom LIKE ? OR email LIKE ? OR role LIKE ? ORDER BY nom";
$stmt = $pdo->prepare($query);
$searchTerm = "%$search%";
$stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user): ?>
    <tr data-id="<?= $user['id'] ?>">
        <td><?= htmlspecialchars($user['nom']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td><?= htmlspecialchars($user['role']) ?></td>
        <td>
            <button class="edit-btn" data-id="<?= $user['id'] ?>">✏️</button>
            <button class="delete-btn" data-id="<?= $user['id'] ?>">🗑️</button>
        </td>
    </tr>
<?php endforeach; ?> 