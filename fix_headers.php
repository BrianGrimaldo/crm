<?php

$dir = __DIR__ . '/src';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$count = 0;

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;
    
    $content = file_get_contents($file->getPathname());
    $original = $content;

    // Pattern to find: header('Location: /some/path');
    // We capture the path part starting with a slash
    $content = preg_replace(
        '/header\s*\(\s*[\'"]Location:\s*(\/[^\'"]*)[\'"]\s*\)\s*;/i',
        "header('Location: ' . url('$1'));",
        $content
    );

    // Also handle cases with concatenation like: header('Location: /path?id=' . $id);
    // Pattern: header('Location: /some/path' . $var);
    $content = preg_replace(
        '/header\s*\(\s*[\'"]Location:\s*(\/[^\'"]*)[\'"]\s*\.\s*(.+?)\s*\)\s*;/i',
        "header('Location: ' . url('$1') . $2);",
        $content
    );

    if ($content !== $original) {
        file_put_contents($file->getPathname(), $content);
        $count++;
        echo "Updated: " . $file->getPathname() . "\n";
    }
}

echo "Total files updated: $count\n";
