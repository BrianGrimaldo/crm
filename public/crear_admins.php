<?php

/**
 * Script para crear un Superusuario (Dueño/Admin) por cada empresa existente.
 * Ejecutar desde la terminal o el navegador.
 */

require __DIR__ . '/../config/database.php';

$config = require __DIR__ . '/../config/database.php';

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $db = new PDO($dsn, $config['username'], $config['password'], $config['options']);

    // Obtener todas las empresas
    $stmt = $db->query("SELECT id, name FROM tenants WHERE is_active = 1");
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h1>Creación de Superusuarios por Empresa</h1>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; text-align: left;'>";
    echo "<tr><th>Empresa</th><th>Nombre de Usuario</th><th>Correo (Login)</th><th>Contraseña Temporal</th><th>Estado</th></tr>";

    foreach ($tenants as $tenant) {
        $tenantId = $tenant['id'];
        $tenantName = $tenant['name'];
        
        // Crear un sufijo limpio para el correo (sin espacios ni caracteres raros)
        $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $tenantName));
        $email = "admin@" . $cleanName . ".com";
        $password = "Einsur2026!"; // Contraseña segura por defecto
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Verificar si el correo ya existe
        $check = $db->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute([':email' => $email]);
        
        if ($check->rowCount() > 0) {
            echo "<tr>
                    <td>{$tenantName}</td>
                    <td>Administrador {$tenantName}</td>
                    <td>{$email}</td>
                    <td><em>(Ya existía)</em></td>
                    <td style='color: orange;'>Omitido - El correo ya existe</td>
                  </tr>";
            continue;
        }

        // Insertar usuario
        $insertUser = $db->prepare("
            INSERT INTO users (first_name, last_name, email, password_hash, is_active, is_superadmin) 
            VALUES (:first_name, :last_name, :email, :password_hash, 1, 0)
        ");
        $insertUser->execute([
            ':first_name' => 'Admin',
            ':last_name' => $tenantName,
            ':email' => $email,
            ':password_hash' => $passwordHash
        ]);

        $userId = $db->lastInsertId();

        // Obtener el ID del rol 'admin' (si existe) para este tenant
        $roleStmt = $db->prepare("SELECT id FROM roles WHERE slug = 'admin' AND tenant_id = :tenant_id LIMIT 1");
        $roleStmt->execute([':tenant_id' => $tenantId]);
        $roleId = $roleStmt->fetchColumn();
        
        // Asignar a la empresa como Dueño (is_owner = 1)
        $insertTenantUser = $db->prepare("
            INSERT INTO tenant_users (tenant_id, user_id, role_id, is_owner, is_active)
            VALUES (:tenant_id, :user_id, :role_id, 1, 1)
        ");
        $insertTenantUser->execute([
            ':tenant_id' => $tenantId,
            ':user_id' => $userId,
            ':role_id' => $roleId ?: null
        ]);

        echo "<tr>
                <td><strong>{$tenantName}</strong></td>
                <td>Admin {$tenantName}</td>
                <td><strong>{$email}</strong></td>
                <td><strong>{$password}</strong></td>
                <td style='color: green;'>¡Creado con éxito!</td>
              </tr>";
    }

    echo "</table>";
    echo "<br><p><strong>Importante:</strong> Pide a los administradores que inicien sesión con estas credenciales y cambien su contraseña desde la sección 'Mi Perfil'.</p>";

} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage();
}
