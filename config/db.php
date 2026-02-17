<?php
// Configuration
$host = trim(getenv('DB_HOST') ?: 'localhost');
$dbname = trim(getenv('DB_NAME') ?: 'blood_donation_db');
$username = trim(getenv('DB_USER') ?: 'root');
$password = trim(getenv('DB_PASS') ?: '');
$port = trim(getenv('DB_PORT') ?: '3306');

// SSL Options for Cloud Databases (Aiven, etc.)
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// If running in production (not localhost), assume SSL is needed for cloud DB
if ($host !== 'localhost' && $host !== '127.0.0.1') {
    // Attempt manual DNS resolution to debug/bypass 'getaddrinfo failed'
    $resolved_ip = gethostbyname($host);
    if ($resolved_ip !== $host) {
        $host = $resolved_ip; // Use IP if resolution worked
    }

    // $options[PDO::MYSQL_ATTR_SSL_CA] = 'path/to/ca.pem'; // We don't have the cert file on Vercel ephemeral
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; // Disable verification for easier setup
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password, $options);
}
catch (PDOException $e) {
    // Show expanded error for debugging Vercel issues
    die("ERROR: Could not connect. " . $e->getMessage() . " (Host: $host, Port: $port)");
}
?>
