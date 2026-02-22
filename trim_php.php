<?php
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.'));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();

        // Skip vendor or other non-essential directories if they existed, though this is a simple app

        $content = file_get_contents($path);

        // Use regex to look for leading whitespace/newlines before the first <?php
        if (preg_match('/^(\s+)<\?php/i', $content, $matches) || preg_match('/^\xEF\xBB\xBF<\?php/i', $content)) {
            // Trim leading whitespace or BOM
            $new_content = preg_replace('/^(\s+)<\?php/i', '<?php', $content);
            $new_content = preg_replace('/^\xEF\xBB\xBF<\?php/i', '<?php', $new_content); // BOM
            if ($content !== $new_content) {
                file_put_contents($path, $new_content);
                echo "Trimmed whitespace/BOM from $path\n";
            }
        }
    }
}
?>