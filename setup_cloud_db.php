<?php
// Retrieve credentials from environment variables or set them here manually for local execution (DO NOT COMMIT SECRETS)
$host = 'mysql-2d9c8d5-rajesh02112005-7793.j.aivencloud.com';
$port = '15398';
$db = 'defaultdb';
$user = 'avnadmin';
$pass = getenv('DB_PASS') ?: 'YOUR_PASSWORD_HERE';

// Try standard DSN first
// Note: standard PHP PDO DSN does not support ssl-mode in all versions, but we can try commands.
$dsn = "mysql:host=$host;port=$port;dbname=$db";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_SSL_CA => true, // Try to trigger SSL use
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];

try {
    echo "Connecting to Cloud Database ($host)...\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected successfully!\n";

    // Read the SQL file
    $sqlFile = 'db_schema.sql';
    $sql = file_get_contents($sqlFile);

    if (!$sql) {
        die("Error: Could not read $sqlFile\n");
    }

    echo "Importing schema from $sqlFile...\n";

    // Execute multiple statements
    $pdo->exec($sql);

    echo "Schema imported successfully!\n";

}
catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    if ($e->getCode() == 1045) {
        echo "Check your username and password.\n";
    }
}
?>
