<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Vercel Debug Info</h1>";

// 1. Check Document Root and Script Filename
echo "<h2>1. Paths</h2>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
echo "Current Dir: " . __DIR__ . "<br>";

// 2. Check Assets
echo "<h2>2. Assets Check</h2>";
$css_path = __DIR__ . '/css/style.css';
echo "Checking for css/style.css: ";
if (file_exists($css_path)) {
    echo "<span style='color:green'>FOUND</span> (Size: " . filesize($css_path) . " bytes)<br>";
}
else {
    echo "<span style='color:red'>NOT FOUND</span> at $css_path<br>";
}

// 3. Database Connection Test
echo "<h2>3. Database Connection</h2>";

$db_host = trim(getenv('DB_HOST') ?: 'localhost');
$db_port = trim(getenv('DB_PORT') ?: '3306');

echo "DB_HOST: $db_host <br>";
echo "DB_PORT: $db_port <br>";

// A. DNS Check
echo "<h3>A. DNS Resolution</h3>";
$ip = gethostbyname($db_host);
echo "gethostbyname(): " . ($ip !== $db_host ? "<span style='color:green'>$ip</span>" : "<span style='color:red'>FAILED (Returned Hostname)</span>") . "<br>";

$dns = dns_get_record($db_host, DNS_A);
echo "dns_get_record(): <pre>" . print_r($dns, true) . "</pre>";

// B. TCP Connection Check (Raw)
echo "<h3>B. Raw TCP Connection (fsockopen)</h3>";
$connection = @fsockopen($db_host, $db_port, $errno, $errstr, 5);
if (is_resource($connection)) {
    echo "<span style='color:green'>SUCCESS: Connected to $db_host:$db_port</span><br>";
    fclose($connection);
}
else {
    echo "<span style='color:red'>FAILURE: Could not connect. Error $errno: $errstr</span><br>";
}

// C. PDO Check
echo "<h3>C. PDO Connection</h3>";
require_once 'config/db.php'; // This might fail if paths are wrong, but we'll see error

try {
    if ($pdo) {
        echo "<span style='color:green'>PDO Object Created Successfully</span><br>";
        echo "Client Version: " . $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION) . "<br>";
        echo "Server Version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br>";
    }
    else {
        echo "<span style='color:red'>PDO Object is NULL</span><br>";
    }
}
catch (PDOException $e) {
    echo "<span style='color:red'>Connection Failed: " . $e->getMessage() . "</span><br>";
}

// 4. PHP Info (Selected)
echo "<h2>4. Environment</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Extensions: " . implode(", ", get_loaded_extensions()) . "<br>";
?>
