<?php

$dir = __DIR__ . '/src/Views';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$count = 0;

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;
    
    $content = file_get_contents($file->getPathname());
    $original = $content;

    $content = preg_replace_callback(
        '/(href|action|src)="(\/[^"]*)"/i',
        function ($matches) {
            $attr = $matches[1];
            $path = $matches[2];
            
            if (strpos($path, chr(60).'?') !== false) {
                return $matches[0];
            }

            $phpOpen = chr(60) . '?=';
            $phpClose = '?' . chr(62);
            return $attr . '="' . $phpOpen . " url('" . $path . "') " . $phpClose . '"';
        },
        $content
    );

    if ($content !== $original) {
        file_put_contents($file->getPathname(), $content);
        $count++;
        echo "Updated: " . $file->getPathname() . "\n";
    }
}

echo "Total view files updated: $count\n";
