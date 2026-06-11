<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\TenantContext;
use PDO;

class Product
{
    private PDO $db;
    private string $table = 'products';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAllForTenant(): array
    {
        $tenantId = TenantContext::getTenantId();
        $stmt = $this->db->prepare("
            SELECT p.*, i.quantity, i.reserved, c.name as category_name
            FROM {$this->table} p
            LEFT JOIN inventory i ON i.product_id = p.id AND i.tenant_id = p.tenant_id
            LEFT JOIN product_categories c ON c.id = p.category_id
            WHERE p.tenant_id = :tenant_id AND p.is_active = 1
            ORDER BY p.name ASC
        ");
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findById(int $id): ?object
    {
        $tenantId = TenantContext::getTenantId();
        $stmt = $this->db->prepare("
            SELECT p.*, i.quantity, i.reserved, i.reorder_level
            FROM {$this->table} p
            LEFT JOIN inventory i ON i.product_id = p.id AND i.tenant_id = p.tenant_id
            WHERE p.id = :id AND p.tenant_id = :tenant_id LIMIT 1
        ");
        $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ?: null;
    }

    public function create(array $data): bool
    {
        $tenantId = TenantContext::getTenantId();
        
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO {$this->table} (tenant_id, sku, name, description, unit_price, cost_price, is_active) 
                    VALUES (:tenant_id, :sku, :name, :description, :unit_price, :cost_price, 1)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $tenantId,
                ':sku' => $data['sku'] ?? strtoupper(substr(uniqid(), -6)),
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null,
                ':unit_price' => $data['unit_price'] ?? 0,
                ':cost_price' => $data['cost_price'] ?? 0
            ]);

            $productId = (int)$this->db->lastInsertId();

            // Initialize Inventory
            $sqlInv = "INSERT INTO inventory (tenant_id, product_id, warehouse, quantity) VALUES (:tid, :pid, 'principal', :qty)";
            $stmtInv = $this->db->prepare($sqlInv);
            $stmtInv->execute([
                ':tid' => $tenantId,
                ':pid' => $productId,
                ':qty' => $data['initial_stock'] ?? 0
            ]);

            // Add movement log
            if (($data['initial_stock'] ?? 0) > 0) {
                $sqlMov = "INSERT INTO inventory_movements (tenant_id, product_id, warehouse, type, quantity, notes, created_by) 
                           VALUES (:tid, :pid, 'principal', 'in', :qty, 'Stock Inicial', :uid)";
                $stmtMov = $this->db->prepare($sqlMov);
                $stmtMov->execute([
                    ':tid' => $tenantId,
                    ':pid' => $productId,
                    ':qty' => $data['initial_stock'],
                    ':uid' => $_SESSION['user_id'] ?? null
                ]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        $tenantId = TenantContext::getTenantId();
        
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE {$this->table} 
                    SET sku = :sku, name = :name, description = :description, unit_price = :unit_price, cost_price = :cost_price
                    WHERE id = :id AND tenant_id = :tenant_id";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':sku' => $data['sku'],
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null,
                ':unit_price' => $data['unit_price'] ?? 0,
                ':cost_price' => $data['cost_price'] ?? 0,
                ':id' => $id,
                ':tenant_id' => $tenantId
            ]);

            // Update Stock
            if (isset($data['add_stock']) && $data['add_stock'] > 0) {
                $stmtInv = $this->db->prepare("UPDATE inventory SET quantity = quantity + :qty WHERE product_id = :pid AND tenant_id = :tid");
                $stmtInv->execute([':qty' => $data['add_stock'], ':pid' => $id, ':tid' => $tenantId]);

                $sqlMov = "INSERT INTO inventory_movements (tenant_id, product_id, warehouse, type, quantity, notes, created_by) 
                           VALUES (:tid, :pid, 'principal', 'in', :qty, 'Entrada Manual', :uid)";
                $stmtMov = $this->db->prepare($sqlMov);
                $stmtMov->execute([
                    ':tid' => $tenantId,
                    ':pid' => $id,
                    ':qty' => $data['add_stock'],
                    ':uid' => $_SESSION['user_id'] ?? null
                ]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $tenantId = TenantContext::getTenantId();
        // Soft delete
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_active = 0 WHERE id = :id AND tenant_id = :tenant_id");
        return $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
    }
}
