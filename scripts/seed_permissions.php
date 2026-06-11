<?php
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
require __DIR__ . '/../src/Core/Database.php';

$db = \App\Core\Database::getInstance();

// 1. Asegurarnos de que todos los permisos posibles existan en la tabla permissions
$defaultPermissions = [
    ['users', 'view', 'Ver usuarios'], ['users', 'create', 'Crear usuarios'], ['users', 'update', 'Editar usuarios'], ['users', 'delete', 'Eliminar usuarios'],
    ['deals', 'view', 'Ver oportunidades'], ['deals', 'create', 'Crear oportunidades'], ['deals', 'update', 'Editar oportunidades'], ['deals', 'delete', 'Eliminar oportunidades'],
    ['contacts', 'view', 'Ver contactos'], ['contacts', 'create', 'Crear contactos'], ['contacts', 'update', 'Editar contactos'], ['contacts', 'delete', 'Eliminar contactos'],
    ['accounts', 'view', 'Ver organizaciones'], ['accounts', 'create', 'Crear organizaciones'], ['accounts', 'update', 'Editar organizaciones'], ['accounts', 'delete', 'Eliminar organizaciones'],
    ['products', 'view', 'Ver productos'], ['products', 'create', 'Crear productos'], ['products', 'update', 'Editar productos'], ['products', 'delete', 'Eliminar productos'],
    ['activities', 'view', 'Ver actividades'], ['activities', 'create', 'Crear actividades'], ['activities', 'update', 'Editar actividades'], ['activities', 'delete', 'Eliminar actividades'],
    ['reports', 'view', 'Ver reportes'],
    ['settings', 'view', 'Ver configuración'], ['settings', 'update', 'Editar configuración']
];

foreach ($defaultPermissions as $perm) {
    $stmt = $db->prepare("INSERT IGNORE INTO permissions (module, action, description) VALUES (?, ?, ?)");
    $stmt->execute($perm);
}

// 2. Mapeo de permisos por ROL (slug)
$rolePermissionsMap = [
    'sales-rep' => [
        // El vendedor ve y crea todo lo operativo (la restricción de SÓLO LOS SUYOS se hace a nivel código en Permission::isRestrictedToOwnRecords)
        'deals:view', 'deals:create', 'deals:update', 'deals:delete',
        'contacts:view', 'contacts:create', 'contacts:update', 'contacts:delete',
        'accounts:view', 'accounts:create', 'accounts:update', 'accounts:delete',
        'activities:view', 'activities:create', 'activities:update', 'activities:delete',
        'products:view', // Solo ver productos
    ],
    'sales-mgr' => [
        // Gerente hace todo lo del vendedor + reportes
        'deals:view', 'deals:create', 'deals:update', 'deals:delete',
        'contacts:view', 'contacts:create', 'contacts:update', 'contacts:delete',
        'accounts:view', 'accounts:create', 'accounts:update', 'accounts:delete',
        'activities:view', 'activities:create', 'activities:update', 'activities:delete',
        'products:view', 'products:create', 'products:update',
        'reports:view'
    ]
];

// Obtener los IDs de cada permiso
$permIds = [];
$stmt = $db->query("SELECT id, module, action FROM permissions");
foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $p) {
    $permIds["{$p->module}:{$p->action}"] = $p->id;
}

// 3. Asignar los permisos a los roles existentes
$rolesStmt = $db->query("SELECT id, slug FROM roles");
$roles = $rolesStmt->fetchAll(PDO::FETCH_OBJ);

foreach ($roles as $role) {
    if (isset($rolePermissionsMap[$role->slug])) {
        // Limpiar permisos actuales para evitar duplicados en el re-seed
        $db->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$role->id]);
        
        $permsToInsert = $rolePermissionsMap[$role->slug];
        foreach ($permsToInsert as $permStr) {
            if (isset($permIds[$permStr])) {
                $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)")
                   ->execute([$role->id, $permIds[$permStr]]);
            }
        }
        echo "Permisos asignados al rol {$role->slug} (ID: {$role->id})\n";
    }
}

echo "Proceso completado exitosamente.\n";
