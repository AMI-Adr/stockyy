<?php
session_start();
require_once 'includes/db.php';
// Récupération des produits
try {
    $stmt = $pdo->query("SELECT * FROM Produit");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📦 Gestion des Produits</title>
    <link rel="stylesheet" href="/fonts/css/all.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <style>
        /* Style du tableau */
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
        /* Style de la barre de recherche */
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
        /* Nouveau style du modal */
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
            max-width: 500px;
            margin: 10% auto;
            position: relative;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.5);
            text-align: center;
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
        /* Style du formulaire dans le modal */
        .styled-form input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .styled-form button {
            padding: 10px 20px;
            border: none;
            background-color: var(--secondary);
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .styled-form button:hover {
            background-color: var(--accent);
        }
        /* Indicateur de stock critique */
        .critical-indicator {
            color: red;
            font-size: 1.2rem;
            margin-left: 5px;
        }
        /* Notification pour les messages */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            display: none;
            z-index: 11000;
        }
    </style>
    <script src="js/jquery-2.2.3.min.js"></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Conteneur de notification -->
    <div id="notification" class="notification"></div>
    
    <div class="main-content">
        <header class="dashboard-header">
            <h1>📦 Gestion des Produits</h1>
            <button class="add-product" id="openModal">Ajouter un Produit</button>
        </header>

        <div class="search-bar">
            <input type="text" id="search" placeholder="Rechercher par nom, catégorie, référence...">
        </div>
        
        <div class="table-container">
            <table class="animated-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Référence</th>
                        <th>Quantité</th>
                        <th>Prix</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productTable">
                    <?php foreach ($products as $product): ?>
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
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal pour ajouter un produit -->
    <div class="cyber-modal" id="productModal">
        <div class="cyber-modal-content">
            <span class="cyber-close" id="closeModal">&times;</span>
            <h2>Ajouter un Produit</h2>
            <form id="productForm" class="styled-form">
                <input type="text" name="nom" placeholder="Nom" required>
                <input type="text" name="categorie" placeholder="Catégorie" required>
                <input type="text" name="reference" placeholder="Référence" required>
                <input type="number" name="quantite" placeholder="Quantité" required>
                <input type="text" name="fournisseur" placeholder="Fournisseur" required>
                <input type="number" step="0.01" name="prix" placeholder="Prix" required>
                <button type="submit">Créer Produit</button>
            </form>
        </div>
    </div>

    <!-- Modal pour éditer un produit -->
    <div class="cyber-modal" id="editProductModal">
        <div class="cyber-modal-content">
            <span class="cyber-close" id="closeEditModal">&times;</span>
            <h2>Modifier le Produit</h2>
            <form id="editProductForm" class="styled-form">
                <input type="hidden" name="id" id="editProductId">
                <input type="text" name="nom" placeholder="Nom" id="editProductNom" required>
                <input type="text" name="categorie" placeholder="Catégorie" id="editProductCategorie" required>
                <input type="text" name="reference" placeholder="Référence" id="editProductReference" required>
                <input type="number" name="quantite" placeholder="Quantité" id="editProductQuantite" required>
                <input type="text" name="fournisseur" placeholder="Fournisseur" id="editProductFournisseur" required>
                <input type="number" step="0.01" name="prix" placeholder="Prix" id="editProductPrix" required>
                <button type="submit">Mettre à jour le Produit</button>
            </form>
        </div>
    </div>

    <script>
    // Fonction pour afficher les notifications
    function showNotification(message) {
        var notification = $('#notification');
        notification.text(message).fadeIn();
        setTimeout(function(){
            notification.fadeOut();
        }, 3000);
    }

    $(document).ready(function() {
        // Ouverture du modal
        $('#openModal').on('click', function() {
            $('#productModal').fadeIn();
        });

        // Fermeture du modal
        $('#closeModal').on('click', function() {
            $('#productModal').fadeOut();
        });

        // Envoi du formulaire pour ajouter un produit
        $('#productForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'add_product.php',
                data: $(this).serialize(),
                success: function(response) {
                    // Ajout du nouveau produit dans le tableau
                    $('#productTable').append(response);
                    $('#productModal').fadeOut();
                    $('#productForm')[0].reset();
                    showNotification("Produit créé avec succès");
                },
                error: function() {
                    alert("Erreur lors de l'ajout du produit.");
                }
            });
        });

        // Suppression d'un produit via AJAX
        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            if (confirm("Voulez-vous vraiment supprimer ce produit ?")) {
                $.ajax({
                    type: 'POST',
                    url: 'delete_product.php',
                    data: { id: id },
                    success: function() {
                        $('tr[data-id="' + id + '"]').fadeOut();
                        showNotification("Produit supprimé avec succès");
                    },
                    error: function() {
                        alert("Erreur lors de la suppression du produit.");
                    }
                });
            }
        });

        // Ouverture du modal d'édition lors du clic sur "✏️"
        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            $.ajax({
                type: 'GET',
                url: 'get_product.php',
                data: { id: id },
                dataType: 'json',
                success: function(product) {
                    $('#editProductId').val(product.id);
                    $('#editProductNom').val(product.nom);
                    $('#editProductCategorie').val(product.categorie);
                    $('#editProductReference').val(product.reference);
                    $('#editProductQuantite').val(product.quantite);
                    $('#editProductFournisseur').val(product.fournisseur);
                    $('#editProductPrix').val(product.prix);
                    $('#editProductModal').fadeIn();
                },
                error: function() {
                    alert("Erreur lors du chargement des détails du produit.");
                }
            });
        });
        
        // Fermeture du modal d'édition
        $('#closeEditModal').on('click', function() {
            $('#editProductModal').fadeOut();
        });
        
        // Envoi du formulaire pour mettre à jour un produit
        $('#editProductForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'update_product.php',
                data: $(this).serialize(),
                success: function(response) {
                    var id = $('#editProductId').val();
                    $('tr[data-id="' + id + '"]').replaceWith(response);
                    $('#editProductModal').fadeOut();
                    showNotification("Produit mis à jour avec succès");
                },
                error: function() {
                    alert("Erreur lors de la mise à jour du produit.");
                }
            });
        });

        // Recherche via le bouton ou la touche Entrée (déjà présent)
        $('#searchBtn').on('click', function() {
            const query = $('#search').val();
            $.ajax({
                type: 'GET',
                url: 'search_products.php',
                data: { query: query },
                success: function(response) {
                    $('#productTable').html(response);
                },
                error: function() {
                    alert("Erreur lors de la recherche des produits.");
                }
            });
        });

        $('#search').on('keyup', function(e) {
            if (e.keyCode === 13) {
                $('#searchBtn').click();
            }
        });

        // Rechercher en temps réel dès que l'utilisateur tape dans la barre
        $('#search').on('input', function() {
            const query = $(this).val();
            $.ajax({
                type: 'GET',
                url: 'search_products.php',
                data: { query: query },
                success: function(response) {
                    $('#productTable').html(response);
                },
                error: function() {
                    alert("Erreur lors de la recherche des produits.");
                }
            });
        });
    });
    </script>
</body>
</html>
