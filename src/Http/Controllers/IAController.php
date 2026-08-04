<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\TenantContext;
use PDO;

/**
 * IAController (v3 — "Guía y Soporte del CRM")
 *
 * CAMBIO DE ENFOQUE (acordado en reunión):
 *  La IA deja de ser un asistente general de análisis de ventas y pasa a
 *  ser EXCLUSIVAMENTE un guía de soporte: explica módulos, da instrucciones
 *  paso a paso de la interfaz y responde dudas sobre el uso del CRM.
 *
 *  Qué cambió respecto a v2:
 *   1. Nuevo system prompt fijo (ver buildSystemPrompt()), enfocado 100% en
 *      onboarding/soporte de interfaz, no en datos de ventas.
 *   2. Ya NO se inyectan datos de negocio (deals, KPIs, metas, top vendedores)
 *      al prompt de la IA. buildContext() se conserva únicamente porque
 *      index() todavía lo usa para pintar las tarjetas KPI de la vista;
 *      su resultado ya no se pasa a callGroq().
 *   3. No existían "tools"/function-calling reales hacia Groq (no había un
 *      arreglo `tools` en el payload), así que no hay lógica de ejecución
 *      de acciones que remover ahí. Se deja explícito en el prompt que la
 *      IA es de solo lectura y no ejecuta acciones en la base de datos.
 *   4. Nuevo: resolveCurrentModule() detecta el módulo donde está el
 *      usuario (por parámetro explícito del frontend o, si no llega,
 *      por el header Referer) y lo inyecta dinámicamente en el prompt.
 *
 *  NOTA: generateInsights()/detectStaleDeals()/detectGoalsAtRisk() se
 *  dejaron intactos porque alimentan el panel "Alertas Proactivas" de la
 *  UI, no el chat. Si quieren que la IA sea 100% guía y ya no genere
 *  alertas de negocio tampoco, avísenme y los desactivo/retiro.
 */
class IAController
{
    private \PDO $db;

    /** Máximo de mensajes previos que se reenvían a Groq como contexto. */
    private const MAX_HISTORY_MESSAGES = 12;

    /**
     * Mapa de rutas -> nombre amigable de módulo, usado para inyectar
     * contexto dinámico en el prompt cuando el frontend no envía
     * explícitamente el módulo actual (fallback vía Referer).
     */
    private const MODULE_MAP = [
        '/dashboard' => 'Panel de Control (Dashboard)',
        '/contacts' => 'Contactos',
        '/accounts' => 'Organizaciones (Cuentas)',
        '/deals' => 'Oportunidades / Pipeline de Ventas',
        '/finanzas' => 'Finanzas y Facturación',
        '/tickets' => 'Tickets de Soporte',
        '/tasks' => 'Tareas',
        '/products' => 'Catálogo de Productos',
        '/reports' => 'Reportes',
        '/goals' => 'Metas de Venta',
        '/import' => 'Importar CSV',
        '/roles' => 'Roles y Permisos',
        '/users' => 'Gestión de Usuarios',
        '/empresas' => 'Gestión de Empresas (Multi-tenant)',
        '/portal' => 'Portal de Clientes',
        '/grupo-einsur' => 'Dashboard CEO / Grupo Einsur',
        '/vendedores' => 'Vista de Vendedores',
        '/profile' => 'Perfil y Configuración',
        '/ia' => 'Asistente IA',
    ];

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }

    public function index(): void
    {
        \App\Core\Permission::require('deals', 'view');
        // Se conserva: la vista usa $context para las tarjetas KPI del
        // dashboard del propio asistente. Ya NO se envía a la IA.
        $context = $this->buildContext();
        $conversationId = $this->getOrCreateConversation();
        $history = $this->getConversationHistory($conversationId);
        require __DIR__ . '/../../Views/ia/index.php';
    }

    public function chat(): void
    {
        header('Content-Type: application/json');
        \App\Core\Permission::require('deals', 'view');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $userMessage = trim($input['message'] ?? '');

        if ($userMessage === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Mensaje vacío']);
            return;
        }

        if (mb_strlen($userMessage) > 2000) {
            http_response_code(422);
            echo json_encode(['error' => 'El mensaje es demasiado largo (máx. 2000 caracteres).']);
            return;
        }

        $conversationId = $this->getOrCreateConversation();
        $this->saveMessage($conversationId, 'user', $userMessage);

        // Módulo actual: preferimos lo que envía el frontend explícitamente
        // (data-module de la página que embebe el chat); si no llega, se
        // intenta inferir por el Referer.
        $currentModule = $this->resolveCurrentModule($input['current_module'] ?? null);

        $systemPrompt = $this->buildSystemPrompt($currentModule);
        $history = $this->getConversationHistory($conversationId);

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($history as $h) {
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
        }

        [$reply, $error] = $this->callGroq($messages);

        if ($error !== null) {
            http_response_code(502);
            echo json_encode(['error' => $error]);
            return;
        }

        $this->saveMessage($conversationId, 'assistant', $reply);
        echo json_encode(['reply' => $reply, 'conversation_id' => $conversationId]);
    }

    /**
     * Inicia una conversación nueva (botón "Nuevo chat" en la UI).
     */
    public function newConversation(): void
    {
        header('Content-Type: application/json');
        \App\Core\Permission::require('deals', 'view');

        $tenantId = TenantContext::getTenantId();
        $userId = (int) $_SESSION['user_id'];

        $this->db->prepare("UPDATE ia_conversations SET is_active = 0 WHERE tenant_id = ? AND user_id = ?")
            ->execute([$tenantId, $userId]);

        $id = $this->createConversation($tenantId, $userId);
        echo json_encode(['conversation_id' => $id]);
    }

    /**
     * Genera insights proactivos (alertas) y los persiste en ia_insights.
     * NOTA: esto alimenta el panel "Alertas Proactivas" de la UI, NO el
     * chat conversacional. Se mantiene fuera del alcance de este cambio
     * de rol; confirmar con el equipo si también debe desactivarse.
     */
    public function generateInsights(): void
    {
        header('Content-Type: application/json');
        \App\Core\Permission::require('deals', 'view');

        $tenantId = TenantContext::getTenantId();
        $insights = [];

        $insights = array_merge($insights, $this->detectStaleDeals($tenantId));
        $insights = array_merge($insights, $this->detectGoalsAtRisk($tenantId));

        foreach ($insights as $insight) {
            $stmt = $this->db->prepare("
                INSERT INTO ia_insights
                    (tenant_id, insight_type, severity, related_type, related_id, title, message)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $tenantId,
                $insight['type'],
                $insight['severity'],
                $insight['related_type'],
                $insight['related_id'],
                $insight['title'],
                $insight['message']
            ]);
        }

        echo json_encode(['generated' => count($insights)]);
    }

    public function listInsights(): void
    {
        header('Content-Type: application/json');
        \App\Core\Permission::require('deals', 'view');
        $tenantId = TenantContext::getTenantId();

        $stmt = $this->db->prepare("
            SELECT id, insight_type, severity, title, message, generated_at, is_read
            FROM ia_insights
            WHERE tenant_id = ? AND is_dismissed = 0
            ORDER BY generated_at DESC
            LIMIT 20
        ");
        $stmt->execute([$tenantId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // -------------------------------------------------------------
    // Memoria de conversación
    // -------------------------------------------------------------

    private function getOrCreateConversation(): int
    {
        $tenantId = TenantContext::getTenantId();
        $userId = (int) $_SESSION['user_id'];

        $stmt = $this->db->prepare("
            SELECT id FROM ia_conversations
            WHERE tenant_id = ? AND user_id = ? AND is_active = 1
            ORDER BY last_message_at DESC LIMIT 1
        ");
        $stmt->execute([$tenantId, $userId]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : $this->createConversation($tenantId, $userId);
    }

    private function createConversation(int $tenantId, int $userId): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ia_conversations (tenant_id, user_id) VALUES (?, ?)
        ");
        $stmt->execute([$tenantId, $userId]);
        return (int) $this->db->lastInsertId();
    }

    private function saveMessage(int $conversationId, string $role, string $content): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO ia_messages (conversation_id, role, content) VALUES (?, ?, ?)
        ");
        $stmt->execute([$conversationId, $role, $content]);

        $this->db->prepare("UPDATE ia_conversations SET last_message_at = NOW() WHERE id = ?")
            ->execute([$conversationId]);
    }

    /**
     * @return array<int, array{role:string, content:string}>
     */
    private function getConversationHistory(int $conversationId): array
    {
        $stmt = $this->db->prepare("
            SELECT role, content FROM ia_messages
            WHERE conversation_id = ?
            ORDER BY created_at DESC
            LIMIT " . self::MAX_HISTORY_MESSAGES
        );
        $stmt->execute([$conversationId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_reverse($rows); // orden cronológico ascendente para el API
    }

    // -------------------------------------------------------------
    // Llamada a Groq con manejo de errores real
    // (sin cambios: sigue siendo una llamada de chat simple, sin tools)
    // -------------------------------------------------------------

    /**
     * @return array{0: string, 1: ?string} [reply, error]
     */
    private function callGroq(array $messages): array
    {
        return $this->callGroqWithModel($messages, 'llama-3.3-70b-versatile');
    }

    /**
     * Llama a Groq con un modelo específico. Permite reintentar con modelos alternativos.
     * @return array{0: string, 1: ?string} [reply, error]
     */
    private function callGroqWithModel(array $messages, string $model): array
    {
        $groqKey = $_ENV['GROQ_API_KEY'] ?? '';
        if ($groqKey === '') {
            error_log('[IAController] GROQ_API_KEY no configurada.');
            return ['', 'El asistente no está configurado correctamente. Contacta al administrador.'];
        }

        $payload = json_encode([
            'model'       => $model,
            'messages'    => $messages,
            'max_tokens'  => 1024,
            'temperature' => 0.4
            // Nota: deliberadamente NO se define 'tools' / function-calling.
            // La IA no debe ejecutar acciones, solo conversar.
        ]);

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $groqKey
            ]
        ]);

        $response = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErrno !== 0) {
            error_log("[IAController] cURL error ({$curlErrno}): {$curlError}");
            return ['', "Error de conexión ({$curlErrno}): {$curlError}"];
        }

        if ($httpCode >= 400) {
            $body = (string) $response;
            error_log("[IAController] Groq HTTP {$httpCode}: " . substr($body, 0, 1000));

            // Detectar error específico de modelo no disponible y reintentar con modelo alternativo
            $decodedErr = json_decode($body, true);
            $errMsg = $decodedErr['error']['message'] ?? '';

            if ($httpCode === 429) {
                return ['', 'Límite de uso de la IA alcanzado (rate limit). Espera unos segundos e intenta de nuevo.'];
            }
            if ($httpCode === 401) {
                return ['', 'La clave de API de Groq es inválida o expiró. Contacta al administrador.'];
            }
            if (str_contains($errMsg, 'model') || str_contains($errMsg, 'decommissioned') || str_contains($errMsg, 'not found')) {
                // Reintentar con modelo de respaldo
                return $this->callGroqWithModel($messages, 'llama3-8b-8192');
            }

            return ['', "Error HTTP {$httpCode} de Groq: " . substr($errMsg ?: $body, 0, 200)];
        }

        $data = json_decode((string) $response, true);
        $reply = $data['choices'][0]['message']['content'] ?? null;

        if ($reply === null) {
            error_log('[IAController] Respuesta de Groq sin contenido esperado: ' . substr((string) $response, 0, 500));
            return ['', 'El asistente no devolvió una respuesta válida. Intenta reformular tu pregunta.'];
        }

        return [$reply, null];
    }

    // -------------------------------------------------------------
    // Contexto de negocio (SOLO para las tarjetas KPI de la vista ia/index.php)
    // Ya NO se envía a la IA — ver buildSystemPrompt().
    // -------------------------------------------------------------

    private function buildContext(): array
    {
        $tenantId = TenantContext::getTenantId();
        $userId = (int) $_SESSION['user_id'];
        $isVendedor = \App\Core\Permission::isVendedor();

        $sql = "
        SELECT d.name, d.amount, d.probability, d.expected_close_date, d.source,
               ps.name AS stage_name,
               CONCAT(u.first_name, ' ', IFNULL(u.last_name,'')) AS owner_name,
               DATEDIFF(NOW(), d.created_at) AS days_open
        FROM deals d
        LEFT JOIN pipeline_stages ps ON ps.id = d.stage_id
        LEFT JOIN users u ON u.id = d.owner_id
        WHERE d.tenant_id = ? AND d.status = 'Abierto'
    ";
        $params = [$tenantId];

        if ($isVendedor) {
            $sql .= " AND d.owner_id = ?";
            $params[] = $userId;
        }

        $sql .= " ORDER BY d.amount DESC LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $openDeals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("
        SELECT ps.name AS stage_name, COUNT(d.id) AS total,
               COALESCE(SUM(d.amount),0) AS total_amount
        FROM pipeline_stages ps
        LEFT JOIN deals d ON d.stage_id = ps.id AND d.tenant_id = ?
        WHERE ps.tenant_id = ?
        GROUP BY ps.id, ps.name, ps.position
        ORDER BY ps.position
    ");
        $stmt->execute([$tenantId, $tenantId]);
        $stagesSummary = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN status='Ganado' THEN 1 ELSE 0 END) AS won,
            SUM(CASE WHEN status='Perdido' THEN 1 ELSE 0 END) AS lost,
            SUM(CASE WHEN status='Abierto' THEN 1 ELSE 0 END) AS open,
            COALESCE(SUM(CASE WHEN status='Ganado' THEN amount ELSE 0 END),0) AS won_amount,
            COALESCE(SUM(CASE WHEN status='Abierto' THEN amount ELSE 0 END),0) AS open_amount
        FROM deals WHERE tenant_id = ?
    ");
        $stmt->execute([$tenantId]);
        $kpis = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("
        SELECT lost_reason, COUNT(*) AS total
        FROM deals
        WHERE tenant_id = ? AND status = 'Perdido' AND lost_reason IS NOT NULL
        GROUP BY lost_reason ORDER BY total DESC LIMIT 5
    ");
        $stmt->execute([$tenantId]);
        $lostReasons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("
        SELECT CONCAT(u.first_name,' ',IFNULL(u.last_name,'')) AS name,
               COUNT(d.id) AS won_deals,
               COALESCE(SUM(d.amount),0) AS won_amount
        FROM deals d
        JOIN users u ON u.id = d.owner_id
        WHERE d.tenant_id = ? AND d.status = 'Ganado'
        GROUP BY d.owner_id ORDER BY won_amount DESC LIMIT 5
    ");
        $stmt->execute([$tenantId]);
        $topSellers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $goalsController = new GoalsController();
        $today = new \DateTimeImmutable('today');
        $monthStart = $today->modify('first day of this month')->format('Y-m-d');
        $monthEnd = $today->modify('last day of this month')->format('Y-m-d');
        $currentGoals = $goalsController->getGoalsWithProgress($tenantId, $monthStart, $monthEnd);

        return compact('openDeals', 'stagesSummary', 'kpis', 'lostReasons', 'topSellers', 'currentGoals');
    }

    // -------------------------------------------------------------
    // NUEVO: resolución del módulo actual (contexto dinámico multi-tenant)
    // -------------------------------------------------------------

    /**
     * Determina el nombre amigable del módulo donde está el usuario.
     * Prioridad: valor explícito enviado por el frontend > Referer HTTP.
     */
    private function resolveCurrentModule(?string $clientProvided): ?string
    {
        if ($clientProvided !== null && trim($clientProvided) !== '') {
            // Saneamos: nunca insertamos HTML/JS del cliente tal cual en el prompt.
            return substr(trim(strip_tags($clientProvided)), 0, 80);
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($referer === '') {
            return null;
        }

        $path = (string) (parse_url($referer, PHP_URL_PATH) ?? '');
        foreach (self::MODULE_MAP as $prefix => $label) {
            if (str_starts_with($path, $prefix)) {
                return $label;
            }
        }

        return null;
    }

    // -------------------------------------------------------------
    // NUEVO SYSTEM PROMPT — Guía y Asistente de Soporte del CRM
    // -------------------------------------------------------------

    private function buildSystemPrompt(?string $currentModule): string
    {
        $moduleBlock = '';
        if ($currentModule !== null) {
            $moduleBlock = "\n\n## CONTEXTO DE NAVEGACIÓN ACTUAL\n"
                . "El usuario se encuentra actualmente en el módulo: **{$currentModule}**.\n"
                . "Si su pregunta es ambigua o genérica (ej. \"¿cómo hago esto?\"), asume primero que se refiere "
                . "a esta sección, pero si lo que pregunta claramente corresponde a otro módulo, guíalo hacia el correcto.\n";
        }

        return <<<PROMPT
Eres el Guía y Asistente de Soporte Avanzado exclusivo del CRM "Einsur Global". Tu único propósito es ayudar al usuario a entender la plataforma, explicarle para qué sirve cada módulo y darle instrucciones EXACTAS paso a paso para realizar sus tareas. Conoces el sistema a la perfección. Eres de solo lectura y no ejecutas acciones, guías al usuario.

**MAPA DEL SISTEMA Y CÓMO NAVEGAR (Úsalo para dar instrucciones precisas):**

1. **CRM & Ventas:**
   - **Contactos**: Menú lateral > "Contactos". Sirve para agregar personas (clientes). Tienen botón "Nuevo Contacto".
   - **Importar Datos**: Menú lateral > "Importar Datos". Sube un CSV para cargar múltiples contactos masivamente.
   - **Organizaciones**: Menú lateral > "Organizaciones". Para registrar empresas clientes. Tienen botón "Nueva Organización".
   - **Ventas (Pipeline)**: Menú lateral > "Ventas". Aquí está el Kanban de Oportunidades. Se pueden crear ("Nueva Oportunidad") y arrastrar tarjetas entre etapas (Prospección, Cotización, Ganado, etc).
   - **Metas y Objetivos**: Menú lateral > "Metas y Objetivos". Permite fijar cuotas mensuales/trimestrales en \$ para Ventas o Cobranza por vendedor.

2. **Finanzas (Solo con permisos):**
   - **Dashboard Finanzas**: Menú lateral > "Dashboard Finanzas". Gráficas de ingresos vs gastos, flujo de caja.
   - **Portal de Cobranza**: Menú lateral > "Portal de Cobranza". Para registrar abonos o pagos a facturas.
   - **Analíticas y Gráficas**: Menú lateral > "Analíticas y Gráficas". Reportes financieros detallados.

3. **Administración & Catálogos:**
   - **Control Vendedores**: Menú lateral > "Control Vendedores". Gestión del equipo comercial.
   - **Auditoría de Ventas**: Menú lateral > "Auditoría de Ventas". (Solo CEO/Admin).
   - **Inventario / Equipos**: Menú lateral > "Inventario / Equipos". Catálogo de productos.
   - **Configuración de Embudo**: Menú lateral > "Embudo de Ventas". Permite cambiar los nombres de las columnas del Kanban de ventas.
   - **Roles y Usuarios**: Menú lateral > "Roles y Permisos" o "Usuarios".

**Tono y Estilo:**
- Sé profesional, claro y empático. Usa siempre listas enumeradas (1, 2, 3) para guiar al usuario. 
- Utiliza la información del "MAPA DEL SISTEMA" para indicarle **exactamente** dónde hacer clic. No des respuestas vagas.
- Usa términos exactos de la interfaz (ej: "Menú lateral izquierdo", "botón Nueva Oportunidad", "Vista Kanban").

**Restricciones de Seguridad:**
- NO inventes funciones. Si el usuario pregunta algo que no está en el mapa, dile amablemente que el sistema actualmente no cuenta con esa opción o dale la alternativa más cercana.
- Redirige temas ajenos al CRM de vuelta al uso de la plataforma.

**Ejemplo de Interacción Excelente:**
Usuario: "¿Cómo creo un producto?"
Tú: "Para crear un nuevo producto en tu catálogo, sigue estos pasos exactos:
1. En el menú lateral izquierdo, busca la sección y haz clic en **'Inventario / Equipos'**.
2. En la parte superior derecha de esa pantalla, haz clic en el botón azul **'Nuevo Producto'**.
3. Llena el formulario con el Nombre, SKU y Precio.
4. Haz clic en **'Guardar'**."
PROMPT . $moduleBlock;
    }

    // -------------------------------------------------------------
    // Reglas de detección de insights proactivos
    // (panel "Alertas Proactivas" de la UI — no forma parte del chat)
    // -------------------------------------------------------------

    /**
     * Deals abiertos que llevan más de 21 días sin actividad registrada.
     * @return array<int, array<string,mixed>>
     */
    private function detectStaleDeals(int $tenantId): array
    {
        $isVendedor = \App\Core\Permission::isVendedor();
        $userId = (int) $_SESSION['user_id'];

        $sql = "
        SELECT d.id, d.name, d.amount,
               CONCAT(u.first_name,' ',IFNULL(u.last_name,'')) AS owner_name,
               COALESCE(DATEDIFF(NOW(), MAX(a.created_at)), DATEDIFF(NOW(), d.created_at)) AS days_inactive
        FROM deals d
        LEFT JOIN activities a ON a.entity_type = 'deal' AND a.entity_id = d.id
        LEFT JOIN users u ON u.id = d.owner_id
        WHERE d.tenant_id = ? AND d.status = 'Abierto'
    ";
        $params = [$tenantId];

        if ($isVendedor) {
            $sql .= " AND d.owner_id = ?";
            $params[] = $userId;
        }

        $sql .= "
        GROUP BY d.id, d.name, d.amount, owner_name, d.created_at
        HAVING days_inactive >= 21
        ORDER BY d.amount DESC
        LIMIT 10
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $insights = [];
        foreach ($rows as $r) {
            $insights[] = [
                'type' => 'stale_deal',
                'severity' => $r['days_inactive'] >= 35 ? 'critical' : 'warning',
                'related_type' => 'deal',
                'related_id' => $r['id'],
                'title' => "Deal sin movimiento: {$r['name']}",
                'message' => "El deal \"{$r['name']}\" (\${$r['amount']}, vendedor: {$r['owner_name']}) lleva {$r['days_inactive']} días sin actividad. Considera dar seguimiento antes de que se enfríe.",
            ];
        }

        return $insights;
    }

    /**
     * Metas del mes actual que están en riesgo según GoalsController.
     * @return array<int, array<string,mixed>>
     */
    private function detectGoalsAtRisk(int $tenantId): array
    {
        $goalsController = new GoalsController();
        $today = new \DateTimeImmutable('today');
        $monthStart = $today->modify('first day of this month')->format('Y-m-d');
        $monthEnd = $today->modify('last day of this month')->format('Y-m-d');
        $goals = $goalsController->getGoalsWithProgress($tenantId, $monthStart, $monthEnd);

        $insights = [];
        foreach ($goals as $g) {
            if (!$g['is_at_risk']) {
                continue;
            }
            $owner = $g['owner_name'] ?: 'el equipo';
            $insights[] = [
                'type' => 'goal_at_risk',
                'severity' => 'warning',
                'related_type' => 'goal',
                'related_id' => $g['id'],
                'title' => "Meta en riesgo: {$owner}",
                'message' => "La meta de {$owner} va en {$g['progress_pct']}% (\${$g['achieved_amount']} de \${$g['target_amount']}) y al ritmo actual no se alcanzará antes del fin del periodo. Faltan \${$g['remaining_amount']}.",
            ];
        }
        return $insights;
    }

    public function history(): void
    {
        header('Content-Type: application/json');
        \App\Core\Permission::require('deals', 'view');

        $conversationId = $this->getOrCreateConversation();
        $history = $this->getConversationHistory($conversationId);
        echo json_encode($history);
    }
}