<?php
echo "<h1>Diagnostic SQLite</h1>";

$paths = [
    __DIR__ . '/../writable/database.db',
    '/Users/mac/Documents/mr-rojo/examen-final-S4/writable/database.db',
    'writable/database.db'
];

foreach ($paths as $path) {
    echo "<h3>Test: $path</h3>";
    if (file_exists($path)) {
        echo "✅ Le fichier existe<br>";
        echo "Permissions: " . substr(sprintf('%o', fileperms($path)), -4) . "<br>";
        echo "Taille: " . filesize($path) . " octets<br>";
    } else {
        echo "❌ Le fichier n'existe pas<br>";
    }
    
    try {
        $db = new PDO('sqlite:' . $path);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Connexion réussie !<br>";
        
        // Tester une requête
        $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' LIMIT 5");
        $tables = $result->fetchAll(PDO::FETCH_ASSOC);
        if (count($tables) > 0) {
            echo "✅ Tables trouvées : " . count($tables) . "<br>";
        } else {
            echo "⚠️ Aucune table trouvée<br>";
        }
        
        $db = null;
    } catch (PDOException $e) {
        echo "❌ Erreur: " . $e->getMessage() . "<br>";
    }
    echo "<hr>";
}
