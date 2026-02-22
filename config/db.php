<?php
$host = trim(getenv('DB_HOST') ?: 'localhost');
$dbname = trim(getenv('DB_NAME') ?: 'blood_donation_db');
$username = trim(getenv('DB_USER') ?: 'root');
$password = trim(getenv('DB_PASS') ?: '');
$port = trim(getenv('DB_PORT') ?: '3306');
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
if ($host !== 'localhost' && $host !== '127.0.0.1') {
    $resolved_ip = gethostbyname($host);
    if ($resolved_ip !== $host) {
        $host = $resolved_ip; 
    }
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; 
}
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password, $options);
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
    die("ERROR: Could not connect. " . $e->getMessage() . " (Host: $host, Port: $port)");
}
?>