<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
require 'src/Core/Database.php';
$userModel = new App\Models\User();
$data = [
    'first_name' => 'Test',
    'last_name' => 'User',
    'email' => 'test2@test.com',
    'phone' => '123456789',
    'password' => 'secret123'
];
$success = $userModel->createUserForTenant($data, 1, 1);
if ($success) echo "Success\n";
else {
    echo "Failed\n";
    print_r(App\Core\Database::getInstance()->errorInfo());
}
