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
echo "DB_HOST Env Var: " . ($db_host ? "SET (Length: " . strlen($db_host) . ")" : "NOT SET") . "<br>";
echo "Resolving Host '$db_host': ";
$ip = gethostbyname($db_host);
if ($ip !== $db_host) {
    echo "<span style='color:green'>RESOLVED to $ip</span><br>";
}
else {
    echo "<span style='color:red'>RESOLUTION FAILED (returned hostname)</span><br>";
}

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
