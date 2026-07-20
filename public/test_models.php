<?php
// test_models.php - Test direct avec PDO

echo "<h1>Test de la base de données Mobile Money</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

try {
    $dbPath = __DIR__ . '/../writable/database.db';
    
    if (!file_exists($dbPath)) {
        die("<p class='error'> Base de données non trouvée à : $dbPath</p>");
    }
    
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'> Connexion à la base de données réussie !</p>";
    
    // 1. Types d'opérations
    echo "<h2>1. Types d'opérations</h2>";
    $result = $db->query("SELECT * FROM types_operations");
    $types = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($types) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Libellé</th></tr>";
        foreach ($types as $type) {
            echo "<tr><td>{$type['id']}</td><td>{$type['libelle']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'> Aucun type d'opération trouvé</p>";
    }
    
    // 2. Barèmes de frais
    echo "<h2>2. Barèmes de frais</h2>";
    $result = $db->query("
        SELECT 
            b.*, 
            t.libelle as type_libelle 
        FROM baremes_frais b
        JOIN types_operations t ON t.id = b.type_operation_id
        ORDER BY t.libelle, b.montant_min
    ");
    $baremes = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($baremes) > 0) {
        echo "<table>";
        echo "<tr><th>Type</th><th>Montant min</th><th>Montant max</th><th>Frais</th></tr>";
        foreach ($baremes as $bareme) {
            echo "<tr>";
            echo "<td>{$bareme['type_libelle']}</td>";
            echo "<td>" . number_format($bareme['montant_min'], 0) . " Ar</td>";
            echo "<td>" . number_format($bareme['montant_max'], 0) . " Ar</td>";
            echo "<td>" . number_format($bareme['frais'], 0) . " Ar</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 3. Calcul des frais pour un montant
        echo "<h2>3. Test calcul des frais</h2>";
        
        // Trouver le type RETRAIT
        $typeRetrait = $db->query("SELECT id FROM types_operations WHERE libelle = 'RETRAIT'")->fetch(PDO::FETCH_ASSOC);
        if ($typeRetrait) {
            $montant = 5000;
            $result = $db->query("
                SELECT frais FROM baremes_frais 
                WHERE type_operation_id = {$typeRetrait['id']} 
                AND montant_min <= {$montant} 
                AND montant_max >= {$montant}
            ");
            $frais = $result->fetch(PDO::FETCH_ASSOC);
            $fraisMontant = $frais ? $frais['frais'] : 0;
            echo "<p> Frais pour un retrait de <strong>" . number_format($montant, 0) . " Ar</strong> : <strong>" . number_format($fraisMontant, 0) . " Ar</strong></p>";
        }
    } else {
        echo "<p class='warning'> Aucun barème trouvé</p>";
    }
    
    // 4. Clients
    echo "<h2>4. Clients</h2>";
    $result = $db->query("SELECT * FROM clients");
    $clients = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($clients) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Téléphone</th><th>Solde</th><th>Date création</th></tr>";
        foreach ($clients as $client) {
            echo "<tr>";
            echo "<td>{$client['id']}</td>";
            echo "<td>{$client['numero_telephone']}</td>";
            echo "<td>" . number_format($client['solde'], 2) . " Ar</td>";
            echo "<td>{$client['date_creation']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Statistiques
        $stats = $db->query("
            SELECT 
                COUNT(*) as total_clients,
                SUM(solde) as total_solde,
                AVG(solde) as solde_moyen
            FROM clients
        ")->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Statistiques clients</h3>";
        echo "<ul>";
        echo "<li>Total clients : <strong>{$stats['total_clients']}</strong></li>";
        echo "<li>Solde total : <strong>" . number_format($stats['total_solde'], 2) . " Ar</strong></li>";
        echo "<li>Solde moyen : <strong>" . number_format($stats['solde_moyen'], 2) . " Ar</strong></li>";
        echo "</ul>";
    } else {
        echo "<p class='warning'> Aucun client trouvé</p>";
    }
    
    // 5. Test insertion client
    echo "<h2>5. Test insertion client</h2>";
    $testTelephone = '0339998888';
    
    // Vérifier si existe déjà
    $check = $db->prepare("SELECT id FROM clients WHERE numero_telephone = ?");
    $check->execute([$testTelephone]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        $db->exec("DELETE FROM clients WHERE id = " . $existing['id']);
        echo "<p> Client existant supprimé</p>";
    }
    
    // Insérer
    $insert = $db->prepare("INSERT INTO clients (numero_telephone, solde) VALUES (?, ?)");
    $insert->execute([$testTelephone, 100000]);
    $clientId = $db->lastInsertId();
    echo "<p class='success'> Client créé avec ID : {$clientId}</p>";
    
    // Récupérer le client
    $result = $db->query("SELECT * FROM clients WHERE id = {$clientId}");
    $client = $result->fetch(PDO::FETCH_ASSOC);
    echo "<ul>";
    echo "<li>Téléphone : {$client['numero_telephone']}</li>";
    echo "<li>Solde : " . number_format($client['solde'], 2) . " Ar</li>";
    echo "</ul>";
    
    // Mettre à jour le solde
    $db->exec("UPDATE clients SET solde = solde + 25000 WHERE id = {$clientId}");
    $result = $db->query("SELECT solde FROM clients WHERE id = {$clientId}");
    $nouveauSolde = $result->fetch(PDO::FETCH_ASSOC);
    echo "<p> Après ajout de 25 000 Ar : <strong>" . number_format($nouveauSolde['solde'], 2) . " Ar</strong></p>";
    
    // Nettoyer
    $db->exec("DELETE FROM clients WHERE id = {$clientId}");
    echo "<p> Client de test supprimé</p>";
    
    echo "<hr>";
    echo "<h2 class='success'> Tous les tests sont passés !</h2>";
    echo "<p>La base de données fonctionne correctement.</p>";
    
} catch (PDOException $e) {
    echo "<h2 class='error'> Erreur PDO</h2>";
    echo "<p><strong>Message :</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Code :</strong> " . $e->getCode() . "</p>";
    echo "<p><strong>Fichier :</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
} catch (Exception $e) {
    echo "<h2 class='error'> Erreur générale</h2>";
    echo "<p><strong>Message :</strong> " . $e->getMessage() . "</p>";
}