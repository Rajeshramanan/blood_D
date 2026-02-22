<?php
$dirs = ['admin', 'user'];
foreach ($dirs as $dir) {
    if (!is_dir($dir))
        continue;
    $files = glob("$dir/*.php");
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $content = str_replace("require_once '../", "require_once __DIR__ . '/../", $content);
        $content = str_replace("include '../", "include __DIR__ . '/../", $content);
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
?>