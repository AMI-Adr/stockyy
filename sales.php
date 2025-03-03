<?php
require_once 'includes/db.php';
// Récupération des ventes avec indication de l'utilisateur et du produit
try {
    $stmt = $pdo->query("SELECT v.*, u.nom AS user_nom, p.nom AS product_nom 
                          FROM Ventes v
                          JOIN Utilisateur u ON v.user_id = u.id
                          JOIN Produit p ON v.product_id = p.id
                          ORDER BY v.created_at DESC");
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💰 Historique des Ventes</title>
    <link rel="stylesheet" href="/fonts/css/all.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <!-- Inclusion de jQuery -->
    <script src="js/jquery-2.2.3.min.js"></script>
    <style>
        /* Style local pour le tableau et la barre de recherche */
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        .animated-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }
        .animated-table th,
        .animated-table td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #ddd;
            word-wrap: break-word;
        }
        .search-bar {
            display: flex;
            gap: 15px;
            margin: 20px 0;
        }
        .search-bar input {
            padding: 12px 20px;
            border: 2px solid var(--secondary);
            border-radius: 25px;
            width: 300px;
            transition: all 0.3s ease;
        }
        .search-bar input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 15px rgba(42, 42, 114, 0.2);
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <header class="dashboard-header">
            <h1>💰 Historique des Ventes</h1>
            <!-- Optionnel : Vous pouvez ajouter ici un bouton pour créer une nouvelle vente si nécessaire -->
        </header>
        
        <!-- Barre de recherche pour les ventes -->
        <div class="search-bar">
            <input type="text" id="salesSearch" placeholder="Rechercher par utilisateur, produit...">
        </div>
        
        <div class="table-container">
            <table class="animated-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="salesTable">
                    <?php foreach ($sales as $sale): ?>
                    <tr data-id="<?= $sale['id'] ?>">
                        <td>#<?= $sale['id'] ?></td>
                        <td><?= htmlspecialchars($sale['user_nom']) ?></td>
                        <td><?= htmlspecialchars($sale['product_nom']) ?></td>
                        <td><?= $sale['quantite'] ?></td>
                        <td>Ar <?= number_format($sale['prix_total'], 2) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Recherche en temps réel des ventes
        $('#salesSearch').on('input', function() {
            const searchValue = $(this).val();
            $.ajax({
                type: 'GET',
                url: './search_sales.php',
                data: { search: searchValue },
                success: function(response) {
                    $('#salesTable').html(response);
                },
                error: function() {
                    alert("Erreur lors de la recherche des ventes.");
                }
            });
        });
    });
    </script>
</body>
</html> 