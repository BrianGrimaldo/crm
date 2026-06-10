<?php
$files = glob(__DIR__ . '/../database/*.sql');
foreach ($files as $file) {
    $content = file_get_contents($file);
    // Remove USE statement
    $content = preg_replace('/USE `crm_einsurglobal`;/i', '', $content);
    // Remove CREATE DATABASE statement
    $content = preg_replace('/CREATE DATABASE IF NOT EXISTS `crm_einsurglobal`[^;]+;/i', '', $content);
    file_put_contents($file, $content);
}
echo "Fix complete.";
