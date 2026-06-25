<?php
$hash = password_hash('password123', PASSWORD_DEFAULT);
echo "HASH: " . $hash . "\n";
