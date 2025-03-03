<?php
session_start();
require_once 'includes/db.php';
// Récupération des utilisateurs
try {
    $stmt = $pdo->query("SELECT * FROM Utilisateur");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📋 Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="/fonts/css/all.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <script src="js/jquery-2.2.3.min.js"></script>
    <style>
        /* Styles pour le tableau, la barre de recherche, les notifications et les modaux */
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
            box-shadow: 0 0 15px rgba(42,42,114,0.2);
        }
        /* Notification */
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
        /* Modaux */
        .cyber-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
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
        /* Formulaire */
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
        /* Nouveau style pour le bouton "Ajouter un Utilisateur" */
        .add-user {
            padding: 10px 20px;
            background-color: var(--secondary);
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .add-user:hover {
            background-color: var(--accent);
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Conteneur de notification -->
    <div id="notification" class="notification"></div>
    
    <div class="main-content">
        <header class="dashboard-header">
            <h1>📋 Gestion des Utilisateurs</h1>
            <button class="add-user" id="openUserModal">Ajouter un Utilisateur</button>
        </header>

        <!-- Barre de recherche -->
        <div class="search-bar">
            <input type="text" id="userSearch" placeholder="Rechercher par nom ou email...">
        </div>
        
        <div class="table-container">
            <table class="animated-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="userTable">
                    <?php foreach ($users as $user): ?>
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
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal pour ajouter un utilisateur -->
    <div class="cyber-modal" id="userModal">
        <div class="cyber-modal-content">
            <span class="cyber-close" id="closeUserModal">&times;</span>
            <h2>Ajouter un Utilisateur</h2>
            <form id="userForm" class="styled-form">
                <input type="text" name="nom" placeholder="Nom" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="role" placeholder="Rôle" required>
                <button type="submit">Créer Utilisateur</button>
            </form>
        </div>
    </div>
    
    <!-- Modal pour éditer un utilisateur -->
    <div class="cyber-modal" id="editUserModal">
        <div class="cyber-modal-content">
            <span class="cyber-close" id="closeEditUserModal">&times;</span>
            <h2>Modifier l'Utilisateur</h2>
            <form id="editUserForm" class="styled-form">
                <input type="hidden" name="id" id="editUserId">
                <input type="text" name="nom" placeholder="Nom" id="editUserNom" required>
                <input type="email" name="email" placeholder="Email" id="editUserEmail" required>
                <input type="text" name="role" placeholder="Rôle" id="editUserRole" required>
                <button type="submit">Mettre à jour l'Utilisateur</button>
            </form>
        </div>
    </div>
    
    <script>
    $(document).ready(function() {
        // Fonction d'affichage de notifications
        function showNotification(message) {
            var notification = $('#notification');
            notification.text(message).fadeIn();
            setTimeout(function(){
                notification.fadeOut();
            }, 3000);
        }
        
        // Ouverture du modal de création d'utilisateur
        $('#openUserModal').click(function(){
            $('#userModal').fadeIn();
        });
        
        // Fermeture du modal de création
        $('#closeUserModal').click(function(){
            $('#userModal').fadeOut();
        });
        
        // Soumission du formulaire d'ajout d'utilisateur
        $('#userForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'add_user.php',
                data: $(this).serialize(),
                success: function(response) {
                    // Ajout de la nouvelle ligne dans la table
                    $('#userTable').append(response);
                    $('#userModal').fadeOut();
                    $('#userForm')[0].reset();
                    showNotification("Utilisateur créé avec succès");
                },
                error: function() {
                    alert("Erreur lors de l'ajout de l'utilisateur.");
                }
            });
        });
        
        // Suppression d'un utilisateur via AJAX
        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            if(confirm("Voulez-vous vraiment supprimer cet utilisateur ?")) {
                $.ajax({
                    type: 'POST',
                    url: 'delete_user.php',
                    data: { id: id },
                    success: function() {
                        $('tr[data-id="'+id+'"]').fadeOut();
                        showNotification("Utilisateur supprimé avec succès");
                    },
                    error: function() {
                        alert("Erreur lors de la suppression de l'utilisateur.");
                    }
                });
            }
        });
        
        // Ouverture du modal d'édition d'utilisateur
        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            $.ajax({
                type: 'GET',
                url: 'get_user.php',
                data: { id: id },
                dataType: 'json',
                success: function(user) {
                    $('#editUserId').val(user.id);
                    $('#editUserNom').val(user.nom);
                    $('#editUserEmail').val(user.email);
                    $('#editUserRole').val(user.role);
                    $('#editUserModal').fadeIn();
                },
                error: function() {
                    alert("Erreur lors du chargement des détails de l'utilisateur.");
                }
            });
        });
        
        // Fermeture du modal d'édition d'utilisateur
        $('#closeEditUserModal').click(function(){
            $('#editUserModal').fadeOut();
        });
        
        // Soumission du formulaire d'édition d'utilisateur
        $('#editUserForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'update_user.php',
                data: $(this).serialize(),
                success: function(response) {
                    const id = $('#editUserId').val();
                    $('tr[data-id="'+id+'"]').replaceWith(response);
                    $('#editUserModal').fadeOut();
                    showNotification("Utilisateur mis à jour avec succès");
                },
                error: function() {
                    alert("Erreur lors de la mise à jour de l'utilisateur.");
                }
            });
        });

        // Recherche des utilisateurs en temps réel
        $('#userSearch').on('input', function() {
            const searchValue = $(this).val();
            $.ajax({
                url: './search_users.php', // Attention au chemin d'accès
                method: 'GET',
                data: { search: searchValue },
                success: function(data) {
                    $('#userTable').html(data);
                },
                error: function() {
                    alert("Erreur lors de la recherche des utilisateurs.");
                }
            });
        });
    });
    </script>
</body>
</html>
