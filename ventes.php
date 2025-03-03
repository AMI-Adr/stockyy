<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';

// On traite le POST et on fait une redirection pour éviter la resubmission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    try {
        $pdo->beginTransaction();
        
        // Vérifier le stock
        $stmt = $pdo->prepare("SELECT quantite FROM Produit WHERE id = ?");
        $stmt->execute([$productId]);
        $stock = $stmt->fetchColumn();
        
        if ($stock >= $quantity) {
            // Mise à jour du stock
            $stmt = $pdo->prepare("UPDATE Produit SET quantite = quantite - ? WHERE id = ?");
            $stmt->execute([$quantity, $productId]);
            
            // Enregistrement de la vente
            $stmt = $pdo->prepare("INSERT INTO Ventes (user_id, product_id, quantite, prix_total) 
                                 VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user']['id'],
                $productId,
                $quantity,
                $_POST['prix_total']
            ]);
            
            $pdo->commit();
            $_SESSION['flash_success'] = "✅ Vente enregistrée avec succès !";
        } else {
            $_SESSION['flash_error'] = "⚠️ Stock insuffisant !";
        }
    } catch(PDOException $e) {
        $pdo->rollBack();
        $_SESSION['flash_error'] = "Erreur : " . $e->getMessage();
    }
    header("Location: ventes.php");
    exit;
}

// Récupération des messages flash
$success = "";
$error = "";
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// Récupération des produits disponibles (stock > 0)
try {
    $stmt = $pdo->query("SELECT * FROM Produit WHERE quantite > 0");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

// Récupération des dernières ventes réalisées par le vendeur connecté (limité à 5)
try {
    $stmt = $pdo->prepare("SELECT v.*, p.nom AS product_nom 
                           FROM Ventes v 
                           JOIN Produit p ON v.product_id = p.id 
                           WHERE v.user_id = ? 
                           ORDER BY v.created_at DESC 
                           LIMIT 5");
    $stmt->execute([$_SESSION['user']['id']]);
    $latestSales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $latestSales = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>💰 Terminal de Vente</title>
    <link rel="stylesheet" href="styles/login.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="/fonts/css/all.css">
    <style>
        /* Global */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(-45deg, #0a0a2a, #1a1a4a, #2a0a2a, #0a2a2a);
            color: #fff;
        }
        /* Header fixe et compact */
        .dashboard-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background: #1d1d3a;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 1000;
        }
        .dashboard-header h1 {
            font-size: 1.4rem;
            margin: 0;
        }
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-profile span {
            font-size: 0.95rem;
        }
        .dashboard-header form {
            margin: 0;
            display: inline;
        }
        .user-profile button {
            background: #f05454;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            color: #fff;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }
        .user-profile button:hover {
            background: #c0392b;
        }
        /* Contenu principal avec décalage pour header fixe */
        .main-content {
            margin: 80px auto 20px auto;
            padding: 20px;
            max-width: 1400px;
        }
        /* Conteneur en deux colonnes */
        .vente-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .vente-stocks,
        .vente-form-and-sales {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 20px;
            flex: 1;
            min-width: 300px;
        }
        /* Table de stocks */
        .stocks-table {
            width: 100%;
            border-collapse: collapse;
        }
        .stocks-table th, .stocks-table td {
            padding: 10px;
            border: 1px solid #444;
            text-align: left;
            font-size: 0.9rem;
        }
        .stocks-table th {
            background-color: #2a2a5a;
        }
        /* Formulaire de vente */
        .vente-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
        }
        .vente-form .input-group {
            display: flex;
            flex-direction: column;
        }
        .vente-form label {
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .vente-form input, .vente-form select {
            padding: 10px;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
        }
        .vente-form input[readonly] {
            background: #ccc;
        }
        .vente-form button {
            background: #28a745;
            border: none;
            padding: 12px;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            color: #fff;
            transition: background 0.3s ease;
        }
        .vente-form button:hover {
            background: #218838;
        }
        /* Dernières ventes */
        .latest-sales {
            margin-top: 30px;
            max-height: 300px;
            overflow-y: auto;
        }
        .latest-sales h3 {
            margin-bottom: 10px;
            color: #00f3ff;
            font-size: 1.1rem;
        }
        .sale-item {
            background: rgba(0, 0, 0, 0.4);
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        /* Messages */
        .success-message, .error-message {
            text-align: center;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        .success-message { background: #28a745; }
        .error-message { background: #dc3545; }
        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-header h1 {
                font-size: 1.2rem;
            }
            .vente-form {
                max-width: 90%;
            }
        }
    </style>
</head>
<body>
    <header class="dashboard-header">
        <h1>Terminal de Vente</h1>
        <div class="user-profile">
            <span>👤 <?= htmlspecialchars($_SESSION['user']['nom']) ?></span>
            <form action="logout.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <button type="submit">Déconnexion</button>
            </form>
        </div>
    </header>
    <div class="main-content">
        <div class="vente-container">
            <!-- Colonne de gauche : Stocks disponibles -->
            <div class="vente-stocks">
                <h2 style="color: #00f3ff; margin-bottom: 15px;">Stock Disponible</h2>
                <table class="stocks-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Référence</th>
                            <th>Stock</th>
                            <th>Prix</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['nom']) ?></td>
                            <td><?= htmlspecialchars($product['reference']) ?></td>
                            <td><?= $product['quantite'] ?></td>
                            <td>Ar<?= number_format($product['prix'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Colonne de droite : Formulaire de vente et dernières ventes -->
            <div class="vente-form-and-sales">
                <h2 style="color: #00f3ff; margin-bottom: 15px;">Effectuer une Vente</h2>
                <?php if($success): ?>
                    <div class="success-message"><?= $success ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="error-message"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST" class="vente-form">
                    <div class="input-group">
                        <label for="product_id">Produit</label>
                        <select name="product_id" id="product_id" class="cyber-input" required>
                            <?php foreach($products as $product): ?>
                                <option value="<?= $product['id'] ?>" data-prix="<?= $product['prix'] ?>">
                                    <?= htmlspecialchars($product['nom']) ?> (Stock: <?= $product['quantite'] ?> • Ar<?= $product['prix'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="quantity">Quantité</label>
                        <input type="number" name="quantity" id="quantity" class="cyber-input" min="1" required>
                    </div>
                    <div class="input-group">
                        <label for="prix_total">Prix Total</label>
                        <input type="number" step="0.01" name="prix_total" id="prix_total" class="cyber-input" readonly>
                    </div>
                    <button type="submit">Valider la Vente</button>
                </form>
                <div class="latest-sales">
                    <h3>Dernières Ventes</h3>
                    <?php if(count($latestSales) > 0): ?>
                        <?php foreach($latestSales as $sale): ?>
                            <div class="sale-item">
                                <span>🕒 <?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?></span>
                                • <span><?= htmlspecialchars($sale['product_nom']) ?></span>
                                • <span><?= $sale['quantite'] ?> unités</span>
                                • <span>Ar<?= number_format($sale['prix_total'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucune vente récente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="js/jquery-2.2.3.min.js"></script>
    <script>
        // Mise à jour automatique du prix total en fonction de la quantité et du produit sélectionné
        const quantityInput = document.getElementById('quantity');
        const productSelect = document.getElementById('product_id');
        const priceInput = document.getElementById('prix_total');
        
        function updatePrice(){
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-prix'));
            const quantity = parseFloat(quantityInput.value) || 0;
            priceInput.value = (price * quantity).toFixed(2);
        }
        quantityInput.addEventListener('input', updatePrice);
        productSelect.addEventListener('change', updatePrice);
        // Initialisation
        updatePrice();
        
        // Masquer automatiquement le message après 3 secondes
        setTimeout(function(){
            $('.success-message, .error-message').fadeOut();
        }, 3000);
    </script>
</body>
</html> 