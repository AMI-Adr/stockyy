<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require_once 'includes/db.php';

// Récupération des statistiques réelles
try {
    // Total des produits en stock
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Produit");
    $productStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalProducts = $productStats['total'];

    // Ventes du mois courant
    $stmt = $pdo->prepare("SELECT SUM(prix_total) as total_sales FROM Ventes WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())");
    $stmt->execute();
    $salesStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $monthlySales = $salesStats['total_sales'] ? $salesStats['total_sales'] : 0;

    // Total des utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM Utilisateur");
    $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalUsers = $userStats['total_users'];

    // Produits en stock critique
    // Stock bas (quantité < 10)
    $stmt = $pdo->query("SELECT COUNT(*) as low_stock FROM Produit WHERE quantite < 10");
    $lowStockStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $lowStockCount = $lowStockStats['low_stock'];

    // Stock élevé (quantité > 80)
    $stmt = $pdo->query("SELECT COUNT(*) as high_stock FROM Produit WHERE quantite > 80");
    $highStockStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $highStockCount = $highStockStats['high_stock'];

} catch(PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Dashboard Admin</title>
    <link rel="stylesheet" href="/fonts/css/all.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <style>
        /* Styles supplémentaires pour le dashboard et le graphique */
        .dashboard-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .stat-card {
            flex: 1 1 200px;
            background: var(--primary);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }
        /* Style spécifique pour le card des produits critiques */
        .critical-card {
            background: #ff4d4d;
            cursor: pointer;
        }
        /* Section du graphique */
        .sales-chart-section {
            margin-top: 2rem;
            padding: 1rem;
            background: var(--light);
            border-radius: 8px;
            overflow: hidden; /* Empêche l'overflow vertical */
        }
        .sales-chart-section h2 {
            text-align: center;
            margin-bottom: 1rem;
        }
        .sales-chart-section select {
            display: block;
            margin: 0 auto 1rem auto;
            padding: 0.5rem;
            border-radius: 5px;
            border: 1px solid var(--secondary);
        }
        /* Conteneur du graphique avec hauteur fixe pour éviter le scroll */
        .sales-chart-container {
            position: relative;
            width: 100%;
            height: 400px; /* Hauteur ajustable selon vos besoins */
            max-width: 100%;
        }
        #salesChart {
            width: 100% !important;
            height: 100% !important;
        }
        /* Style de la modale pour les produits critiques */
        .cyber-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 10000;
        }
        .cyber-modal-content {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            width: 100%;
            max-width: 700px;
            margin: 5% auto;
            position: relative;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.5);
        }
        .cyber-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            color: #333;
            cursor: pointer;
            transition: color 0.3s;
        }
        .cyber-close:hover {
            color: #ff0000;
        }
        /* Styles pour le loader de déconnexion */
        .logout-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
            font-size: 1.2rem;
            z-index: 20000;
        }
        .logout-spinner {
            border: 8px solid rgba(255, 255, 255, 0.3);
            border-top: 8px solid #00f3ff;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
    <script src="js/jquery-2.2.3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="./chartjs@4.4.4/dist/chart.umd.js"></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <header class="dashboard-header">
            <h1>📦 Gestion de Stock</h1>
            <div class="user-profile">
                <div class="user-profile-card">
                    <span><i class="fas fa-user-astronaut"></i> <?= htmlspecialchars($_SESSION['user']['nom']) ?></span>
                    <form id="logoutForm" action="logout.php" method="POST" onsubmit="handleLogout(event)">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" class="logout-btn">
                            <i class="fas fa-power-off"></i> Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Statistiques dynamiques -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>📈 Produits en Stock</h3>
                <p><?= $totalProducts ?></p>
            </div>
            <div class="stat-card">
                <h3>💰 Ventes du mois</h3>
                <p>Ar <?= number_format($monthlySales, 2) ?></p>
            </div>
            <div class="stat-card">
                <h3>👥 Utilisateurs</h3>
                <p><?= $totalUsers ?></p>
            </div>
            <!-- Nouveau card pour les produits en stock critique -->
            <div class="stat-card critical-card" id="criticalStockCard">
                <h3>⚠️ Stock Critique</h3>
                <p>Basse: <?= $lowStockCount ?>, Élevée: <?= $highStockCount ?></p>
            </div>
        </div>

        <!-- Section du graphique des ventes -->
        <section class="sales-chart-section">
            <h2>Ventes par période</h2>
            <select id="salesFilter">
                <option value="monthly">Mensuel</option>
                <option value="daily">Journalier</option>
            </select>
            <div class="sales-chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </section>

    </div>

    <!-- Modal pour afficher les produits en stock critique -->
    <div class="cyber-modal" id="criticalModal">
        <div class="cyber-modal-content">
            <span class="cyber-close" id="closeCriticalModal">&times;</span>
            <h2>Produits en Stock Critique</h2>
            <div id="criticalProductsContent">
                <!-- Contenu chargé via AJAX -->
            </div>
        </div>
    </div>

    <!-- Loader de déconnexion -->
    <div id="logoutLoader" class="logout-loader" style="display: none;">
        <div class="logout-spinner"></div>
        <p>Déconnexion en cours...</p>
    </div>

    <script>
    // Fonction de déconnexion avec affichage du loader
    async function handleLogout(event) {
        event.preventDefault();
        // Afficher le loader
        $('#logoutLoader').fadeIn();
        const form = event.target;
        try {
            // Envoi de la requête de déconnexion en POST
            await fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            });
        } catch (error) {
            console.error('Erreur lors de la déconnexion:', error);
        } finally {
            // Rediriger vers login.php après 1.5 secondes pour laisser le temps au loader de s'afficher
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 1500);
        }
    }

    // Gestion du graphique avec Chart.js
    $(document).ready(function(){
        let salesChart;
        function updateChart(filter) {
            $.ajax({
                url: 'chart_data.php',
                type: 'GET',
                data: { filter: filter },
                dataType: 'json',
                success: function(data) {
                    // Vérifier que le canvas est bien récupéré
                    const canvasElem = document.getElementById('salesChart');
                    if(!canvasElem) {
                        console.error("Le canvas #salesChart n'a pas été trouvé.");
                        return;
                    }
                    const ctx = canvasElem.getContext('2d');
                    console.log("Données reçues :", data);
                    if (salesChart) {
                        salesChart.destroy();
                    }
                    salesChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Ventes (Ar)',
                                data: data.sales,
                                backgroundColor: 'rgba(0, 123, 255, 0.2)',
                                borderColor: 'rgba(0, 123, 255, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            scales: {
                                x: {
                                    title: { display: true, text: filter === 'monthly' ? 'Mois' : 'Jour' }
                                },
                                y: {
                                    beginAtZero: true,
                                    title: { display: true, text: 'Montant (€)' }
                                }
                            }
                        }
                    });
                },
                error: function(xhr, status, err){
                    console.error("Erreur lors du chargement des données du graphique.", status, err);
                    alert('Erreur lors du chargement des données du graphique.');
                }
            });
        }
        // Chargement initial avec le filtre mensuel
        updateChart($('#salesFilter').val());

        $('#salesFilter').on('change', function(){
            updateChart($(this).val());
        });

        // Lorsque le card "Stock Critique" est cliqué, charger et afficher les produits critiques
        $('#criticalStockCard').on('click', function(){
            $.ajax({
                url: 'critical_products.php',
                type: 'GET',
                success: function(response){
                    $('#criticalProductsContent').html(response);
                    $('#criticalModal').fadeIn();
                },
                error: function(){
                    alert("Erreur lors du chargement des produits critiques.");
                }
            });
        });

        // Fermeture de la modale des produits critiques
        $('#closeCriticalModal').on('click', function(){
            $('#criticalModal').fadeOut();
        });
    });
    </script>
</body>
</html>
