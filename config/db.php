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
    // Auto-create sessions table for Vercel Serverless state persistence
    $pdo->exec("CREATE TABLE IF NOT EXISTS php_sessions (
        id VARCHAR(128) PRIMARY KEY,
        data TEXT,
        last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    class DatabaseSessionHandler implements SessionHandlerInterface
    {
        private $pdo;
        public function __construct($pdo)
        {
            $this->pdo = $pdo;
        }
        public function open($path, $name): bool
        {
            return true;
        }
        public function close(): bool
        {
            return true;
        }
        public function read($id): string|false
        {
            $stmt = $this->pdo->prepare("SELECT data FROM php_sessions WHERE id = ?");
            $stmt->execute([$id]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['data'] ?? '';
            }
            return '';
        }
        public function write($id, $data): bool
        {
            $stmt = $this->pdo->prepare("REPLACE INTO php_sessions (id, data) VALUES (?, ?)");
            return $stmt->execute([$id, $data]);
        }
        public function destroy($id): bool
        {
            $stmt = $this->pdo->prepare("DELETE FROM php_sessions WHERE id = ?");
            return $stmt->execute([$id]);
        }
        public function gc($max_lifetime): int|false
        {
            $stmt = $this->pdo->prepare("DELETE FROM php_sessions WHERE last_accessed < DATE_SUB(NOW(), INTERVAL ? SECOND)");
            $stmt->execute([$max_lifetime]);
            return $stmt->rowCount();
        }
    }

    $handler = new DatabaseSessionHandler($pdo);
    session_set_save_handler($handler, true);

} catch (PDOException $e) {
    // Show expanded error for debugging Vercel issues
    die("ERROR: Could not connect. " . $e->getMessage() . " (Host: $host, Port: $port)");
}
?>