<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/src/Views');
$iterator = new RecursiveIteratorIterator($dir);

$replacements = [
    '/#e2e8f0/i' => 'var(--border)',
    '/#f1f5f9/i' => 'var(--border)',
    "/'#f1f5f9'/i" => "'var(--primary-light)'",
    "/'#f8fafc'/i" => "'var(--primary-light)'",
    '/#f8fafc/i' => 'var(--bg-main)',
    '/#cbd5e1/i' => 'var(--text-muted)',
    '/#f3f2f1/i' => 'var(--bg-main)',
    '/#edebe9/i' => 'var(--border)',
    '/#605e5c/i' => 'var(--text-muted)',
    '/#323130/i' => 'var(--text-main)',
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
