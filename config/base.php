<?php
// Define base URL to handle relative paths correctly
$base_url = '';

// Check if running on localhost or 127.0.0.1
if (isset($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['HTTP_HOST'];
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        $base_url = '/blood_app';
    }
}
?>
