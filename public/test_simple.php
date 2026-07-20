<?php
// test_simple.php - Test direct avec PDO

echo "<h1>Test de la base de données SQLite</h1>";

try {
    // Connexion directe avec PDO
    $dbPath = __DIR__ . '/../writable/database.db';
    
    // Vérifier si le fichier existe
    if (!file_exists($dbPath)) {
        echo "<p style='color:orange'> La base de données n'existe pas à : $dbPath</p>";
        echo "<p>Création en cours...</p>";
        
        // Créer la base de données
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Lire et exécuter base.sql
        $sql = file_get_contents(__DIR__ . '/../base.sql');
        if ($sql) {
            $db->exec($sql);
            echo "<p style='color:green' Base de données créée avec succès !</p>";
        } else {
            echo "<p style='color:red'> Impossible de lire base.sql</p>";
        }
    } else {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p style='color:green'> Connexion à la base de données réussie !</p>";
    }
    
    // Tester la connexion
    echo "<h2>1. Test de connexion</h2>";
    echo "<p style='color:green'> SQLite version : " . $db->getAttribute(PDO::ATTR_SERVER_VERSION) . "</p>";
    
    // Lister les tables
    echo "<h2>2. Tables trouvées</h2>";
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    $tables = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $table) {
            // Compter les lignes
            $count = $db->query("SELECT COUNT(*) as total FROM " . $table['name'])->fetch(PDO::FETCH_ASSOC);
            echo "<li><strong>" . $table['name'] . "</strong> (" . $count['total'] . " lignes)</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange'> Aucune table trouvée.</p>";
    }
    
    // Vérifier les types d'opérations
    echo "<h2>3. Types d'opérations</h2>";
    $result = $db->query("SELECT * FROM types_operations");
    $types = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($types) > 0) {
        echo "<ul>";
        foreach ($types as $type) {
            echo "<li><strong>" . $type['code'] . "</strong> : " . $type['nom'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange'> Aucun type d'opération trouvé.</p>";
    }
    
    // Vérifier les configurations
    echo "<h2>4. Configurations</h2>";
    $result = $db->query("SELECT * FROM configurations");
    $configs = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($configs) > 0) {
        echo "<ul>";
        foreach ($configs as $config) {
            echo "<li><strong>" . $config['cle'] . "</strong> : " . $config['valeur'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange'> Aucune configuration trouvée.</p>";
    }
    
    // Vérifier les barèmes
    echo "<h2>5. Barèmes de frais</h2>";
    $result = $db->query("SELECT * FROM frais_baremes LIMIT 10");
    $baremes = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($baremes) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Type</th><th>Montant min</th><th>Montant max</th><th>Frais fixe</th></tr>";
        foreach ($baremes as $bareme) {
            // Récupérer le nom du type
            $typeResult = $db->query("SELECT nom FROM types_operations WHERE id = " . $bareme['type_operation_id']);
            $typeName = $typeResult->fetch(PDO::FETCH_ASSOC);
            echo "<tr>";
            echo "<td>" . ($typeName ? $typeName['nom'] : 'N/A') . "</td>";
            echo "<td>" . number_format($bareme['montant_min'], 0) . " Ar</td>";
            echo "<td>" . number_format($bareme['montant_max'], 0) . " Ar</td>";
            echo "<td>" . number_format($bareme['frais_fixe'], 0) . " Ar</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange'> Aucun barème trouvé.</p>";
    }
    
    // Test des clients
    echo "<h2>6. Test des clients</h2>";
    
    // Créer un client de test
    $testTelephone = '0331234567';
    
    // Vérifier si le client existe déjà
    $check = $db->prepare("SELECT id FROM clients WHERE numero_telephone = ?");
    $check->execute([$testTelephone]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Supprimer le client existant
        $db->exec("DELETE FROM clients WHERE id = " . $existing['id']);
        echo "<p>🗑️ Client existant supprimé</p>";
    }
    
    // Créer un nouveau client
    $insert = $db->prepare("INSERT INTO clients (numero_telephone, nom, prenom, solde) VALUES (?, ?, ?, ?)");
    $insert->execute([$testTelephone, 'Dupont', 'Jean', 100000]);
    $clientId = $db->lastInsertId();
    echo "<p style='color:green'> Client créé avec ID : " . $clientId . "</p>";
    
    // Récupérer le client
    $result = $db->query("SELECT * FROM clients WHERE id = " . $clientId);
    $client = $result->fetch(PDO::FETCH_ASSOC);
    echo "<ul>";
    echo "<li>Nom : " . $client['prenom'] . " " . $client['nom'] . "</li>";
    echo "<li>Téléphone : " . $client['numero_telephone'] . "</li>";
    echo "<li>Solde : " . number_format($client['solde'], 2) . " Ar</li>";
    echo "</ul>";
    
    // Nettoyer
    $db->exec("DELETE FROM clients WHERE id = " . $clientId);
    echo "<p>🗑️ Client de test supprimé</p>";
    
    echo "<h2 style='color:green'> Tous les tests sont passés !</h2>";
    echo "<p>La base de données fonctionne correctement.</p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color:red'> Erreur PDO</h2>";
    echo "<p><strong>Message :</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Code :</strong> " . $e->getCode() . "</p>";
} catch (Exception $e) {
    echo "<h2 style='color:red'> Erreur générale</h2>";
    echo "<p><strong>Message :</strong> " . $e->getMessage() . "</p>";
}