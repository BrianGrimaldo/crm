<?php
$files = [
    'src/Views/tickets/index.php',
    'src/Views/tickets/show.php',
    'src/Views/tasks/index.php',
    'src/Views/settings/pipeline/index.php',
    'src/Views/roles/index.php',
    'src/Views/reports/index.php',
    'src/Views/products/index.php',
    'src/Views/finanzas/index.php',
    'src/Views/contacts/index.php',
    'src/Views/deals/index.php',
    'src/Views/accounts/index.php'
];

foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    if (!file_exists($path)) {
        continue;
    }

    $content = file_get_contents($path);
    
    $content = preg_replace_callback('/href="(\/[^"]*?)<\?=\s*(.*?)\s*\?>([^"]*)"/s', function($matches) {
        $prefix = $matches[1];
        $phpVar = trim($matches[2]);
        $suffix = $matches[3];
        
        $phpOpen = "<?=";
        $phpClose = "?>";
        
        $urlCall = "url('{$prefix}' . {$phpVar}";
        if ($suffix !== '') {
            $urlCall .= " . '{$suffix}'";
        }
        $urlCall .= ")";
        
        return 'href="' . $phpOpen . ' ' . $urlCall . ' ' . $phpClose . '"';
    }, $content);

    file_put_contents($path, $content);
}
echo "¡Botones restantes corregidos exitosamente!\n";
