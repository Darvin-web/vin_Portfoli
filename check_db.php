<?php
require_once 'config.php';

echo "<h2>Database Check</h2>";

// Check if messages table exists
try {
    $tables = $pdo->query("SHOW TABLES LIKE 'messages'");
    if ($tables->rowCount() > 0) {
        echo "<p style='color:green'>✓ Table 'messages' exists</p>";
        
        // Check table structure
        $structure = $pdo->query("DESCRIBE messages");
        echo "<h3>Table Structure:</h3><pre>";
        print_r($structure->fetchAll());
        echo "</pre>";
        
        // Check data
        $data = $pdo->query("SELECT * FROM messages ORDER BY id DESC");
        $messages = $data->fetchAll();
        echo "<h3>Messages (" . count($messages) . " total):</h3><pre>";
        print_r($messages);
        echo "</pre>";
        
    } else {
        echo "<p style='color:red'>✗ Table 'messages' does NOT exist!</p>";
        
        // Create table
        echo "<p>Creating table...</p>";
        $pdo->exec("CREATE TABLE messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "<p style='color:green'>✓ Table created successfully!</p>";
    }
} catch(PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>