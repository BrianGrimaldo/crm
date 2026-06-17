<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Database;
use App\Core\Permission;
use App\Models\AuditLog;
use PDO;

class ImportController
{
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->auditLog = new AuditLog();
    }

    // ──────────────────────────────────────────────────────────
    //  GET /importar  — muestra la página principal
    // ──────────────────────────────────────────────────────────
    public function index(): void
    {
        Permission::require('contacts', 'create');
        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';
        require __DIR__ . '/../../Views/import/index.php';
    }

    // ──────────────────────────────────────────────────────────
    //  POST /importar/preview  — lee el CSV y devuelve JSON
    // ──────────────────────────────────────────────────────────
    public function preview(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        Permission::require('contacts', 'create');

        try {
            if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo CSV.']);
                exit;
            }

            $type   = $_POST['import_type'] ?? 'contacts'; // contacts | accounts
            $tmpPath = $_FILES['csv_file']['tmp_name'];
            $rows    = $this->parseCsv($tmpPath);

            if (empty($rows)) {
                echo json_encode(['success' => false, 'message' => 'El archivo está vacío o no tiene filas válidas.']);
                exit;
            }

            // Validate headers
            $headers = array_keys($rows[0]);
            $required = $type === 'contacts'
                ? ['first_name']
                : ['name'];

            $missing = array_diff($required, $headers);
            if (!empty($missing)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Faltan columnas obligatorias: ' . implode(', ', $missing)
                ]);
                exit;
            }

            // Return preview (max 100 rows shown, full count stored in session)
            $_SESSION['import_pending'] = [
                'type' => $type,
                'rows' => $rows,
            ];

            echo json_encode([
                'success'   => true,
                'type'      => $type,
                'total'     => count($rows),
                'headers'   => $headers,
                'preview'   => array_slice($rows, 0, 10),
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ──────────────────────────────────────────────────────────
    //  POST /importar/commit  — inserta en la base de datos
    // ──────────────────────────────────────────────────────────
    public function commit(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        Permission::require('contacts', 'create');

        if (empty($_SESSION['import_pending'])) {
            echo json_encode(['success' => false, 'message' => 'No hay datos pendientes. Carga el CSV primero.']);
            exit;
        }

        $pending  = $_SESSION['import_pending'];
        $type     = $pending['type'];
        $rows     = $pending['rows'];
        $tenantId = (int)($_SESSION['tenant_id'] ?? 0);
        $ownerId  = (int)($_SESSION['user_id'] ?? 0);
        $db       = Database::getInstance();

        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        try {
            $db->beginTransaction();

            if ($type === 'contacts') {
                $sql = "INSERT INTO contacts
                    (tenant_id, owner_id, type, first_name, last_name, email, phone, mobile,
                     job_title, department, linkedin, country, city, postal_code, address, created_at)
                    VALUES
                    (:tenant_id, :owner_id, :type, :first_name, :last_name, :email, :phone, :mobile,
                     :job_title, :department, :linkedin, :country, :city, :postal_code, :address, NOW())
                    ON DUPLICATE KEY UPDATE updated_at = NOW()";

                $stmt = $db->prepare($sql);

                foreach ($rows as $i => $row) {
                    $fn = trim($row['first_name'] ?? '');
                    if ($fn === '') { $skipped++; continue; }

                    try {
                        $stmt->execute([
                            ':tenant_id'  => $tenantId,
                            ':owner_id'   => $ownerId,
                            ':type'       => $this->sanitize($row['type'] ?? 'Prospecto'),
                            ':first_name' => $this->sanitize($fn),
                            ':last_name'  => $this->sanitize($row['last_name'] ?? ''),
                            ':email'      => $this->sanitize($row['email'] ?? ''),
                            ':phone'      => $this->sanitize($row['phone'] ?? ''),
                            ':mobile'     => $this->sanitize($row['mobile'] ?? ''),
                            ':job_title'  => $this->sanitize($row['job_title'] ?? ''),
                            ':department' => $this->sanitize($row['department'] ?? ''),
                            ':linkedin'   => $this->sanitize($row['linkedin'] ?? ''),
                            ':country'    => $this->sanitize($row['country'] ?? ''),
                            ':city'       => $this->sanitize($row['city'] ?? ''),
                            ':postal_code'=> $this->sanitize($row['postal_code'] ?? ''),
                            ':address'    => $this->sanitize($row['address'] ?? ''),
                        ]);
                        $inserted++;
                    } catch (\Exception $e) {
                        $skipped++;
                        $errors[] = "Fila " . ($i + 2) . ": " . $e->getMessage();
                    }
                }

            } else { // accounts
                $sql = "INSERT INTO accounts
                    (tenant_id, owner_id, name, type, priority, industry, website, linkedin,
                     phone, email, country, city, postal_code, billing_address, notes, created_at)
                    VALUES
                    (:tenant_id, :owner_id, :name, :type, :priority, :industry, :website, :linkedin,
                     :phone, :email, :country, :city, :postal_code, :billing_address, :notes, NOW())
                    ON DUPLICATE KEY UPDATE updated_at = NOW()";

                $stmt = $db->prepare($sql);

                foreach ($rows as $i => $row) {
                    $name = trim($row['name'] ?? '');
                    if ($name === '') { $skipped++; continue; }

                    try {
                        $stmt->execute([
                            ':tenant_id'       => $tenantId,
                            ':owner_id'        => $ownerId,
                            ':name'            => $this->sanitize($name),
                            ':type'            => $this->sanitize($row['type'] ?? 'customer'),
                            ':priority'        => $this->sanitize($row['priority'] ?? 'B'),
                            ':industry'        => $this->sanitize($row['industry'] ?? ''),
                            ':website'         => $this->sanitize($row['website'] ?? ''),
                            ':linkedin'        => $this->sanitize($row['linkedin'] ?? ''),
                            ':phone'           => $this->sanitize($row['phone'] ?? ''),
                            ':email'           => $this->sanitize($row['email'] ?? ''),
                            ':country'         => $this->sanitize($row['country'] ?? ''),
                            ':city'            => $this->sanitize($row['city'] ?? ''),
                            ':postal_code'     => $this->sanitize($row['postal_code'] ?? ''),
                            ':billing_address' => $this->sanitize($row['billing_address'] ?? ''),
                            ':notes'           => $this->sanitize($row['notes'] ?? ''),
                        ]);
                        $inserted++;
                    } catch (\Exception $e) {
                        $skipped++;
                        $errors[] = "Fila " . ($i + 2) . ": " . $e->getMessage();
                    }
                }
            }

            $db->commit();

            $this->auditLog->log(
                'create',
                "import_{$type}",
                0,
                null,
                ['inserted' => $inserted, 'skipped' => $skipped]
            );

            unset($_SESSION['import_pending']);

            echo json_encode([
                'success'  => true,
                'inserted' => $inserted,
                'skipped'  => $skipped,
                'errors'   => array_slice($errors, 0, 20),
            ]);

        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Error en la transacción: ' . $e->getMessage()]);
        }
        exit;
    }

    // ──────────────────────────────────────────────────────────
    //  GET /importar/plantilla  — descarga plantilla CSV
    // ──────────────────────────────────────────────────────────
    public function template(): void
    {
        Permission::require('contacts', 'create');

        $type = $_GET['type'] ?? 'contacts';

        if ($type === 'contacts') {
            $filename  = 'Plantilla_Contactos_' . date('Y-m-d') . '.xls';
            $title     = 'Plantilla de Importación — Contactos';
            $subtitle  = 'Completa los campos y guarda como CSV (UTF-8). El campo first_name es obligatorio.';
            $headers   = [
                'first_name'  => 'Nombre *',
                'last_name'   => 'Apellido',
                'type'        => 'Tipo (Prospecto / Cliente / Proveedor)',
                'email'       => 'Correo Electrónico',
                'phone'       => 'Teléfono',
                'mobile'      => 'Celular',
                'job_title'   => 'Puesto',
                'department'  => 'Departamento',
                'linkedin'    => 'LinkedIn URL',
                'country'     => 'País',
                'city'        => 'Ciudad',
                'postal_code' => 'Código Postal',
                'address'     => 'Dirección',
            ];
            $samples = [
                ['Juan',  'García',  'Cliente',   'juan@empresa.com',  '55-1234-5678', '55-8765-4321', 'Gerente',  'Ventas',    'https://linkedin.com/in/juan', 'México', 'CDMX',        '06600', 'Av. Principal 100'],
                ['María', 'López',   'Prospecto', 'maria@corp.mx',     '55-0000-1111', '',             'Directora','Marketing', '',                            'México', 'Guadalajara', '44100', ''],
                ['Carlos','Ramírez', 'Proveedor', 'carlos@proveedor.com','55-2222-3333','55-4444-5555','Director', 'Compras',   '',                            'México', 'Monterrey',   '64000', 'Blvd. Industria 200'],
            ];
        } else {
            $filename  = 'Plantilla_Organizaciones_' . date('Y-m-d') . '.xls';
            $title     = 'Plantilla de Importación — Organizaciones';
            $subtitle  = 'Completa los campos y guarda como CSV (UTF-8). El campo name es obligatorio.';
            $headers   = [
                'name'            => 'Nombre de la Empresa *',
                'type'            => 'Tipo (customer / partner / vendor / other)',
                'priority'        => 'Prioridad (A / B / C)',
                'industry'        => 'Industria / Giro',
                'website'         => 'Sitio Web',
                'linkedin'        => 'LinkedIn URL',
                'phone'           => 'Teléfono',
                'email'           => 'Correo',
                'country'         => 'País',
                'city'            => 'Ciudad',
                'postal_code'     => 'Código Postal',
                'billing_address' => 'Dirección de Facturación',
                'notes'           => 'Notas',
            ];
            $samples = [
                ['Empresa Ejemplo S.A. de C.V.', 'customer', 'A', 'Tecnología',  'https://empresa.com',    'https://linkedin.com/company/ejemplo', '55-1234-0000', 'info@empresa.com', 'México', 'CDMX',       '06600', 'Av. Reforma 500, Piso 10', 'Cliente frecuente'],
                ['Distribuciones XYZ',            'partner',  'B', 'Logística',   '',                        '',                                     '55-9876-0000', 'ventas@xyz.mx',    'México', 'Monterrey',  '64000', '',                        ''],
                ['Manufactura ABC S.A.',           'vendor',   'C', 'Manufactura', 'https://abc-mx.com',     '',                                     '55-5555-0000', 'contacto@abc.mx',  'México', 'Guadalajara','44100', 'Parque Industrial Sur 300', 'Proveedor principal'],
            ];
        }

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Pragma: no-cache');
        header('Expires: 0');

        $colKeys = array_keys($headers);

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        echo '<style>
            body   { font-family: "Segoe UI", Arial, sans-serif; margin: 20px; }
            table  { border-collapse: collapse; width: 100%; margin-top: 10px; }

            /* Title rows */
            .row-title    { font-size: 16pt; font-weight: bold; color: #0F172A; }
            .row-subtitle { font-size: 10pt; color: #64748B; }
            .row-warning  { font-size: 9pt; color: #92400E; background-color: #FEF3C7; font-style: italic; }

            /* Column header row */
            .col-header   { background-color: #002D62; color: #FFFFFF; font-weight: bold;
                            border: 1px solid #94A3B8; padding: 10px 8px; text-align: left;
                            font-size: 10pt; white-space: nowrap; }
            .col-key      { background-color: #1E3A5F; color: #6EDFF6; font-size: 8.5pt;
                            border: 1px solid #94A3B8; padding: 4px 8px; font-family: "Courier New", monospace; }

            /* Data rows */
            td { border: 1px solid #CBD5E1; padding: 7px 8px; font-size: 10pt; vertical-align: middle; }
            tr.even td { background-color: #F8FAFC; }
            tr.odd  td { background-color: #FFFFFF; }

            /* Required asterisk column */
            td.req { color: #002D62; font-weight: 700; }
        </style>';
        echo '</head><body>';

        // Title block (no table border)
        echo '<table style="border:none;margin-bottom:12px;">';
        echo '<tr><td class="row-title" colspan="' . count($headers) . '" style="border:none;">' . htmlspecialchars($title) . '</td></tr>';
        echo '<tr><td class="row-subtitle" colspan="' . count($headers) . '" style="border:none;">Generado el: ' . date('d/m/Y H:i') . '</td></tr>';
        echo '<tr><td class="row-subtitle" colspan="' . count($headers) . '" style="border:none;">' . htmlspecialchars($subtitle) . '</td></tr>';
        echo '<tr><td class="row-warning" colspan="' . count($headers) . '" style="border:none;">
                ⚠  Instrucciones: (1) No elimines ni reordenes las columnas.
                (2) Al guardar en Excel elige "CSV UTF-8 (delimitado por comas)".
                (3) Los campos marcados con * son obligatorios — las filas sin ellos serán ignoradas.
              </td></tr>';
        echo '</table>';

        // Data table
        echo '<table>';

        // Row 1 — human-readable header names
        echo '<thead>';
        echo '<tr>';
        foreach ($headers as $key => $label) {
            $isRequired = str_contains($label, '*');
            echo '<th class="col-header">' . htmlspecialchars($label) . '</th>';
        }
        echo '</tr>';
        // Row 2 — technical column keys (for reference)
        echo '<tr>';
        foreach ($colKeys as $key) {
            echo '<th class="col-key">' . htmlspecialchars($key) . '</th>';
        }
        echo '</tr>';
        echo '</thead>';

        // Sample rows
        echo '<tbody>';
        foreach ($samples as $i => $row) {
            $cls = ($i % 2 === 0) ? 'even' : 'odd';
            echo '<tr class="' . $cls . '">';
            foreach ($row as $j => $val) {
                $colKey = $colKeys[$j] ?? '';
                $isReq  = in_array($colKey, ['first_name', 'name']);
                echo '<td' . ($isReq ? ' class="req"' : '') . '>' . htmlspecialchars($val) . '</td>';
            }
            echo '</tr>';
        }
        // 7 blank rows for the user to fill in
        for ($b = 0; $b < 7; $b++) {
            $cls = ((count($samples) + $b) % 2 === 0) ? 'even' : 'odd';
            echo '<tr class="' . $cls . '">';
            foreach ($colKeys as $k => $key) {
                echo '<td>&nbsp;</td>';
            }
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</body></html>';
        exit;
    }

    // ──────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────
    /**
     * Known column names that identify a valid header row.
     * If the first row doesn't contain any of these, we skip rows
     * until we find one that does (handles template title/subtitle rows).
     */
    private const KNOWN_COLUMNS = [
        'first_name', 'last_name', 'email', 'phone', 'mobile',
        'job_title', 'department', 'linkedin', 'country', 'city',
        'postal_code', 'address', 'name', 'type', 'priority',
        'industry', 'website', 'billing_address', 'notes',
    ];

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) throw new \RuntimeException('No se pudo abrir el archivo.');

        // Strip BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        $headers = null;
        $rows    = [];

        while (($line = fgetcsv($handle, 4096, ',')) !== false) {
            // Try semicolon if single column
            if (count($line) === 1 && strpos($line[0], ';') !== false) {
                $line = str_getcsv($line[0], ';');
            }

            // Normalize all cells for comparison
            $normalized = array_map(
                fn($h) => strtolower(trim(str_replace([' ', '*', '(', ')'], ['_', '', '', ''], $h))),
                $line
            );

            // If we haven't found headers yet, check if this row looks like one
            if ($headers === null) {
                $matchCount = count(array_intersect($normalized, self::KNOWN_COLUMNS));

                if ($matchCount >= 2) {
                    // This row has at least 2 known column names → use as header
                    $headers = $normalized;
                }
                // Otherwise skip this row (it's a title/subtitle/instruction row)
                continue;
            }

            // Build associative row from headers
            $row = [];
            foreach ($headers as $i => $h) {
                if ($h === '' || $h === '&nbsp;') continue; // skip empty header columns
                $row[$h] = trim($line[$i] ?? '');
            }

            // Skip fully empty rows
            if (array_filter($row, fn($v) => $v !== '' && $v !== '&nbsp;') === []) continue;

            $rows[] = $row;
        }

        fclose($handle);

        if ($headers === null) {
            throw new \RuntimeException(
                'No se encontraron las columnas esperadas en el archivo. '
                . 'Asegúrate de que la primera fila de datos contenga los nombres de columna '
                . '(first_name, last_name, email, etc.).'
            );
        }

        return $rows;
    }

    private function sanitize(string $value): ?string
    {
        $v = trim(strip_tags($value));
        return $v === '' ? null : mb_substr($v, 0, 500);
    }
}
