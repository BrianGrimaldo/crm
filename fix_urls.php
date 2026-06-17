<?php
$viewsDir = __DIR__ . '/src/Views';
$old = '/crm_einsurglobal/public/';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS)
);
$count = 0;

// Build replacement fragments as plain strings (no PHP tag interpretation)
$phpOpen = chr(60) . '?= ';   // <?= 
$phpClose = ' ?' . chr(62);    // ?>

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    if (strpos($content, $old) === false) continue;
    $original = $content;

    // href="/crm_einsurglobal/public/xxx"
    $content = preg_replace(
        '#href="/crm_einsurglobal/public/([^"]*)"#',
        'href="' . $phpOpen . 'url(\'/${1}\')' . $phpClose . '"',
        $content
    );

    // action="/crm_einsurglobal/public/xxx"
    $content = preg_replace(
        '#action="/crm_einsurglobal/public/([^"]*)"#',
        'action="' . $phpOpen . 'url(\'/${1}\')' . $phpClose . '"',
        $content
    );

    // src="/crm_einsurglobal/public/xxx"
    $content = preg_replace(
        '#src="/crm_einsurglobal/public/([^"]*)"#',
        'src="' . $phpOpen . 'url(\'/${1}\')' . $phpClose . '"',
        $content
    );

    // JS: '/crm_einsurglobal/public/ in onchange etc
    $jsReplace = '\'' . $phpOpen . 'url(\'/\')' . $phpClose . '\'+\'';
    $content = str_replace('\'/crm_einsurglobal/public/', $jsReplace, $content);

    // PHP: ?? '/crm_einsurglobal/public/img/xxx'
    $content = preg_replace(
        '#\?\? \'/crm_einsurglobal/public/([^\']*)\'#',
        '?? url(\'/${1}\')',
        $content
    );

    if ($content !== $original) {
        file_put_contents($file->getPathname(), $content);
        $count++;
        echo "Updated: " . basename($file->getPathname()) . "\n";
    }
}
echo "\nTotal files updated: $count\n";
