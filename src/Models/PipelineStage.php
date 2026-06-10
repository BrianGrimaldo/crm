<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use App\Core\TenantContext;
use PDO;

class PipelineStage extends BaseModel
{
    protected string $table = 'pipeline_stages';

    /**
     * Obtiene todas las etapas ordenadas por posición.
     */
    public function allOrdered(): array
    {
        $tenantId = TenantContext::getTenantId();
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id ORDER BY position ASC"
        );
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
