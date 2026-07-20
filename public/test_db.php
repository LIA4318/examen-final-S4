<?php
// test_db.php - Test simple de la base de données

// Charger l'environnement CodeIgniter
require_once '../app/Config/Paths.php';
require_once '../vendor/autoload.php';

use CodeIgniter\Config\Factories;

echo "<h1>Test de la base de données SQLite</h1>";

try {
    // Récupérer la connexion
    $db = \Config\Database::connect();
    
    echo "<p style='color:green'> Connexion à la base de données réussie !</p>";
    
    // Vérifier les tables
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->getResultArray();
    
    echo "<h2>Tables trouvées :</h2>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . $table['name'] . "</li>";
    }
    echo "</ul>";
    
    // Vérifier les types d'opérations
    $types = $db->query("SELECT * FROM types_operations")->getResultArray();
    
    echo "<h2>Types d'opérations :</h2>";
    if (count($types) > 0) {
        echo "<ul>";
        foreach ($types as $type) {
            echo "<li><strong>{$type['code']}</strong> : {$type['nom']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange'> Aucun type d'opération trouvé. Vérifiez que base.sql a été importé.</p>";
    }
    
    // Vérifier les configurations
    $configs = $db->query("SELECT * FROM configurations")->getResultArray();
    echo "<h2>Configurations :</h2>";
    if (count($configs) > 0) {
        echo "<ul>";
        foreach ($configs as $config) {
            echo "<li><strong>{$config['cle']}</strong> : {$config['valeur']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange'> Aucune configuration trouvée.</p>";
    }
    
    // Vérifier les barèmes
    $baremes = $db->query("SELECT * FROM frais_baremes LIMIT 5")->getResultArray();
    echo "<h2>Barèmes de frais (5 premiers) :</h2>";
    if (count($baremes) > 0) {
        echo "<ul>";
        foreach ($baremes as $bareme) {
            echo "<li>Montant : {$bareme['montant_min']} - {$bareme['montant_max']} Ar → Frais : {$bareme['frais_fixe']} Ar</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange'>Aucun barème trouvé.</p>";
    }
    
    echo "<h2 style='color:green'> Tous les tests sont passés !</h2>";
    
} catch (Exception $e) {
    echo "<h2 style='color:red'> Erreur</h2>";
    echo "<p><strong>Message :</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Fichier :</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
}