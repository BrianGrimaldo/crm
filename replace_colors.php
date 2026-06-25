<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/src/Views');
$iterator = new RecursiveIteratorIterator($dir);

$replacements = [
    '/#1e293b/i' => 'var(--text-main)',
    '/#334155/i' => 'var(--text-main)',
    '/#475569/i' => 'var(--text-main)',
    '/#64748b/i' => 'var(--text-muted)'
];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $newContent = preg_replace(array_keys($replacements), array_values($replacements), $content);
        if ($newContent !== $content) {
            file_put_contents($path, $newContent);
            echo "Updated $path\n";
        }
    }
}
