<?php
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.'));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        if (preg_match('/^(\s+)<\?php/i', $content, $matches) || preg_match('/^\xEF\xBB\xBF<\?php/i', $content)) {
            $new_content = preg_replace('/^(\s+)<\?php/i', '<?php', $content);
            $new_content = preg_replace('/^\xEF\xBB\xBF<\?php/i', '<?php', $new_content); 
            if ($content !== $new_content) {
                file_put_contents($path, $new_content);
                echo "Trimmed whitespace/BOM from $path\n";
            }
        }
    }
}
?>