<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Core\Permission;

class PermissionTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION = [];
    }

    public function testHasPermissionWithSuperadmin(): void
    {
        $_SESSION['is_superadmin'] = true;
        $this->assertTrue(Permission::has('contacts', 'view'));
    }

    public function testHasPermissionWithOwner(): void
    {
        $_SESSION['is_owner'] = true;
        $this->assertTrue(Permission::has('deals', 'delete'));
    }

    public function testHasPermissionWithWildcard(): void
    {
        $_SESSION['permissions'] = ['*'];
        $this->assertTrue(Permission::has('leads', 'create'));
    }

    public function testHasPermissionWithModuleWildcard(): void
    {
        $_SESSION['permissions'] = ['contacts.*'];
        $this->assertTrue(Permission::has('contacts', 'delete'));
        $this->assertFalse(Permission::has('deals', 'view'));
    }

    public function testHasSpecificPermission(): void
    {
        $_SESSION['permissions'] = ['contacts.view', 'deals.create'];
        $this->assertTrue(Permission::has('contacts', 'view'));
        $this->assertTrue(Permission::has('deals', 'create'));
        $this->assertFalse(Permission::has('contacts', 'delete'));
    }

    public function testIsRestrictedToOwnRecordsForRegularUser(): void
    {
        $_SESSION['is_superadmin'] = false;
        $_SESSION['is_owner'] = false;
        $this->assertTrue(Permission::isRestrictedToOwnRecords());
    }

    public function testIsRestrictedToOwnRecordsForAdminOrOwner(): void
    {
        $_SESSION['is_superadmin'] = true;
        $this->assertFalse(Permission::isRestrictedToOwnRecords());

        $_SESSION['is_superadmin'] = false;
        $_SESSION['is_owner'] = true;
        $this->assertFalse(Permission::isRestrictedToOwnRecords());
    }
}
