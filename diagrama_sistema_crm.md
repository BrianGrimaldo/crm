# 🏗️ Diagrama Completo del Sistema — CRM Einsur Global

---

## 1. Arquitectura General del Sistema

```mermaid
graph TB
    subgraph Cliente["🌐 Cliente (Navegador)"]
        Browser["Browser HTTP Request"]
    end

    subgraph Server["⚙️ Servidor Apache/XAMPP"]
        HT[".htaccess<br/>Rewrite Rules"]
        Entry["public/index.php<br/>Entry Point"]
    end

    subgraph Core["🔧 Core Framework"]
        Router["Router.php<br/>Despacho de Rutas"]
        MW["Middlewares"]
        TM["TenantMiddleware"]
        AM["AuthMiddleware"]
        RM["RoleMiddleware"]
    end

    subgraph MVC["📦 MVC + Repository"]
        Controllers["Controllers<br/>(23 controladores)"]
        Models["Models<br/>(13 modelos)"]
        Views["Views<br/>(24 secciones)"]
        Repos["Repositories<br/>(BaseRepository)"]
    end

    subgraph Services["🔌 Servicios"]
        Email["EmailService<br/>(PHPMailer + SMTP)"]
        Perm["Permission<br/>(RBAC Engine)"]
        TC["TenantContext<br/>(Aislamiento)"]
        DB["Database<br/>(PDO Singleton)"]
    end

    subgraph Data["🗄️ MySQL"]
        MySQL["Base de datos compartida<br/>Aislamiento por tenant_id"]
    end

    Browser --> HT --> Entry
    Entry --> Router
    Router --> MW
    MW --> TM & AM & RM
    TM --> TC
    MW --> Controllers
    Controllers --> Models
    Controllers --> Views
    Models --> Repos
    Repos --> DB
    DB --> MySQL
    Controllers --> Email
    Controllers --> Perm
```

---

## 2. Flujo Completo de una Petición HTTP

```mermaid
sequenceDiagram
    participant B as 🌐 Browser
    participant H as .htaccess
    participant E as public/index.php
    participant R as Router
    participant TM as TenantMiddleware
    participant C as Controller
    participant M as Model/Repository
    participant DB as MySQL
    participant V as View (PHP)

    B->>H: GET /oportunidades
    H->>E: Rewrite → index.php
    E->>E: 1. Composer Autoload<br/>2. Cargar .env<br/>3. session_start()
    E->>R: new Router() + require routes/web.php
    R->>R: dispatch(URI, METHOD)
    R->>R: Match: /oportunidades → DealController::index
    R->>TM: handle() — ¿Hay tenant_id en sesión?
    TM->>TM: TenantContext::set(tenant_id, user_id)
    TM-->>R: ✅ Continuar
    R->>C: DealController->index()
    C->>C: Permission::require('deals', 'view')
    C->>M: $dealModel->all() / getByStage()
    M->>DB: SELECT * FROM deals WHERE tenant_id = :tid
    DB-->>M: Resultados filtrados
    M-->>C: Array de deals
    C->>V: require Views/deals/index.php
    V-->>B: HTML renderizado
```

---

## 3. Sistema Multi-Tenant — Aislamiento de Datos

```mermaid
graph TB
    subgraph Global["🌍 Nivel Global (Sin tenant_id)"]
        Users["users"]
        Perms["permissions"]
    end

    subgraph Tenants["🏢 Tenants (Empresas)"]
        T1["Tenant 1<br/>Empresa A"]
        T2["Tenant 2<br/>Empresa B"]
        T3["Tenant N<br/>Empresa ..."]
    end

    subgraph IsolationMechanism["🔒 Mecanismo de Aislamiento"]
        TC2["TenantContext::set(tenant_id)"]
        BM["BaseModel / BaseRepository<br/>WHERE tenant_id = :tid"]
    end

    subgraph DataPerTenant["📊 Datos por Tenant"]
        Contacts["contacts"]
        Accounts["accounts"]
        Deals["deals"]
        Invoices["invoices"]
        Tickets["tickets"]
        Tasks["tasks"]
        Products["products"]
        Roles["roles"]
        Goals["goals"]
    end

    Users -->|"tenant_users (pivote)"| T1 & T2 & T3
    T1 & T2 & T3 --> TC2
    TC2 --> BM
    BM --> DataPerTenant
```

> [!IMPORTANT]
> **Todas las tablas operativas** incluyen una columna `tenant_id`. Las clases `BaseModel` y `BaseRepository` **inyectan automáticamente** el filtro `WHERE tenant_id = :tid` en cada consulta CRUD, haciendo imposible la fuga de datos entre empresas.

### Tabla Pivote `tenant_users`

```mermaid
erDiagram
    USERS ||--o{ TENANT_USERS : "pertenece a"
    TENANTS ||--o{ TENANT_USERS : "tiene"
    ROLES ||--o{ TENANT_USERS : "asigna"

    TENANT_USERS {
        bigint id PK
        bigint tenant_id FK
        bigint user_id FK
        bigint role_id FK
        bool is_owner
        bool is_active
    }
```

---

## 4. Sistema RBAC — Roles y Permisos

```mermaid
graph TD
    subgraph Roles["👥 Roles del Sistema"]
        SA["🔴 SUPERADMIN<br/>CEO / Grupo Einsur<br/>Vista global analítica"]
        ADM["🟠 GERENTE (Admin)<br/>Gerente de Empresa<br/>CRUD total en su tenant"]
        VEN["🟢 VENDEDOR<br/>Operativo individual<br/>Solo sus registros"]
        COB["🔵 COBRANZA<br/>Gestor de cobros<br/>Ve todas las facturas"]
    end

    subgraph Access["🔐 Nivel de Acceso a Datos"]
        SA_DATA["Sin filtro de owner<br/>Puede cambiar entre tenants<br/>Dashboard: /grupo-einsur"]
        ADM_DATA["WHERE tenant_id = :tid<br/>Ve todo el tenant<br/>Dashboard: /dashboard"]
        VEN_DATA["WHERE tenant_id = :tid<br/>AND owner_id = :uid<br/>Dashboard: /dashboard"]
        COB_DATA["WHERE tenant_id = :tid<br/>canViewAllInvoices = true<br/>Dashboard: /finanzas"]
    end

    SA --> SA_DATA
    ADM --> ADM_DATA
    VEN --> VEN_DATA
    COB --> COB_DATA
```

### Flujo de Decisión RBAC

```mermaid
flowchart TD
    Start["Permission::has(module, action)"] --> IsSA{¿Es Superadmin?}
    IsSA -->|Sí| AllAccess["✅ Acceso total a todo"]
    IsSA -->|No| IsAdmin{¿Es Gerente/Admin?}
    IsAdmin -->|Sí| CheckWild{"¿Tiene '*' en permisos?"}
    CheckWild -->|Sí| AllAccess2["✅ Acceso total en su tenant"]
    CheckWild -->|No| CheckMod{"¿Tiene 'module.*'?"}
    CheckMod -->|Sí| AllAccess2
    CheckMod -->|No| CheckExact{"¿Tiene 'module.action'?"}
    CheckExact -->|Sí| AllAccess2
    CheckExact -->|No| Denied["❌ Sin permiso"]
    IsAdmin -->|No| IsOp{"¿Módulo operativo?<br/>deals, contacts, accounts,<br/>tasks, activities, finance"}
    IsOp -->|Sí + view/create/update| Allowed["✅ Permitido (solo sus datos)"]
    IsOp -->|No o delete| CheckPerm{"¿Permiso explícito?"}
    CheckPerm -->|Sí| Allowed
    CheckPerm -->|No| Denied
```

---

## 5. Diagrama Entidad-Relación (Base de Datos)

```mermaid
erDiagram
    TENANTS ||--o{ ROLES : "tiene"
    TENANTS ||--o{ TENANT_USERS : "membresías"
    TENANTS ||--o{ CONTACTS : "tiene"
    TENANTS ||--o{ ACCOUNTS : "tiene"
    TENANTS ||--o{ DEALS : "tiene"
    TENANTS ||--o{ PRODUCTS : "tiene"
    TENANTS ||--o{ TICKETS : "tiene"
    TENANTS ||--o{ TASKS : "tiene"
    TENANTS ||--o{ INVOICES : "tiene"
    TENANTS ||--o{ GOALS : "tiene"
    TENANTS ||--o{ PIPELINE_STAGES : "tiene"
    TENANTS ||--o{ AUDIT_LOGS : "tiene"
    TENANTS ||--o{ IA_CONVERSATIONS : "tiene"

    USERS ||--o{ TENANT_USERS : "pertenece"
    ROLES ||--o{ TENANT_USERS : "asigna"
    ROLES ||--o{ ROLE_PERMISSIONS : "tiene"
    PERMISSIONS ||--o{ ROLE_PERMISSIONS : "asignado a"

    ACCOUNTS ||--o{ CONTACTS : "agrupa"
    ACCOUNTS ||--o{ DEALS : "asociado"
    ACCOUNTS ||--o{ TICKETS : "reporta"
    CONTACTS ||--o{ DEALS : "vinculado"
    CONTACTS ||--o{ TICKETS : "crea"
    DEALS ||--o{ INVOICES : "factura"
    PIPELINE_STAGES ||--o{ DEALS : "etapa actual"
    INVOICES ||--o{ INVOICE_PAYMENTS : "pagos"

    USERS ||--o{ DEALS : "owner"
    USERS ||--o{ CONTACTS : "owner"
    USERS ||--o{ ACCOUNTS : "owner"
    USERS ||--o{ TASKS : "asignado"
    USERS ||--o{ TICKETS : "asignado"
    USERS ||--o{ GOALS : "meta de"

    PRODUCTS ||--o{ PRODUCT_CATEGORIES : "categoría"
    PRODUCTS ||--o{ INVENTORY : "stock"
    PRODUCTS ||--o{ INVENTORY_MOVEMENTS : "movimientos"

    IA_CONVERSATIONS ||--o{ IA_MESSAGES : "mensajes"

    TENANTS {
        bigint id PK
        char uuid
        varchar name
        varchar slug
        enum plan
        char currency_code
        bool is_active
    }

    USERS {
        bigint id PK
        varchar email UK
        varchar password_hash
        varchar smtp_host
        varchar smtp_email
        bool is_superadmin
        bool is_active
    }

    DEALS {
        bigint id PK
        bigint tenant_id FK
        bigint account_id FK
        bigint contact_id FK
        bigint owner_id FK
        bigint stage_id FK
        varchar name
        decimal amount
        date expected_close_date
        bool is_won
    }

    CONTACTS {
        bigint id PK
        bigint tenant_id FK
        bigint account_id FK
        bigint owner_id FK
        varchar type
        varchar first_name
        varchar email
        varchar phone
    }

    INVOICES {
        bigint id PK
        bigint tenant_id FK
        bigint deal_id FK
        bigint account_id FK
        bigint owner_id FK
        varchar invoice_number
        decimal total
        decimal amount_paid
        enum status
        date due_date
    }

    TICKETS {
        bigint id PK
        bigint tenant_id FK
        bigint contact_id FK
        bigint assigned_to FK
        varchar subject
        enum priority
        enum status
    }
```

---

## 6. Mapa de Módulos y Controladores

```mermaid
graph LR
    subgraph AUTH["🔐 Autenticación"]
        AuthC["AuthController"]
        AuthC --> Login["GET /login"]
        AuthC --> Auth2["POST /login"]
        AuthC --> Logout["GET /logout"]
        AuthC --> Switch["GET /switch-tenant"]
    end

    subgraph CRM["💼 CRM Core"]
        CC["ContactController<br/>CRUD + Email + Search"]
        AC["AccountController<br/>CRUD Organizaciones"]
        DC["DealController<br/>CRUD + Pipeline + Funnel"]
        PC["PipelineController<br/>Config Etapas"]
    end

    subgraph CATALOG["📦 Catálogo"]
        ProdC["ProductController<br/>CRUD Productos"]
    end

    subgraph FINANCE["💰 Finanzas"]
        FC["FinanzasController<br/>Facturas + Pagos + Auditoría"]
    end

    subgraph SUPPORT["🎫 Soporte"]
        TC2["TicketController<br/>Tickets + Comentarios"]
        PortC["PortalController<br/>Portal Cliente Externo"]
    end

    subgraph ACTIVITY["📋 Actividades"]
        TaskC["TaskController<br/>Tareas + Completar"]
        ActC["ActivityController<br/>Registro actividades"]
    end

    subgraph ANALYTICS["📊 Analítica"]
        RC["ReportController<br/>Reportes + Exportar"]
        GC["GoalsController<br/>Metas de Venta"]
        HC["HomeController<br/>Dashboard KPIs"]
    end

    subgraph AI["🤖 IA"]
        IAC["IAController<br/>Chat + Insights + Historial"]
    end

    subgraph ADMIN["⚙️ Administración"]
        RoleC["RoleController<br/>CRUD Roles"]
        UserC["UserController<br/>Gestión Usuarios"]
        TenC["TenantController<br/>Gestión Empresas"]
        ProfC["ProfileController<br/>Perfil + SMTP"]
        ImpC["ImportController<br/>Importar CSV"]
        GrupoC["GrupoEinsurController<br/>Dashboard CEO"]
        VendC["VendedoresController<br/>Vista Vendedores"]
    end
```

---

## 7. Pipeline de Ventas — Flujo de Negocio

```mermaid
flowchart LR
    subgraph Pipeline["Embudo de Ventas (pipeline_stages)"]
        S1["1️⃣ Prospección"]
        S2["2️⃣ Calificación"]
        S3["3️⃣ Propuesta"]
        S4["4️⃣ Negociación"]
        S5["5️⃣ Cierre"]
    end

    subgraph Outcome["Resultado"]
        Won["✅ Ganado<br/>is_won = 1"]
        Lost["❌ Perdido<br/>is_won = 0 + lost_reason"]
    end

    subgraph PostSale["Post-Venta"]
        Invoice["💰 Factura<br/>(invoices)"]
        Payment["💳 Pago<br/>(invoice_payments)"]
        Ticket["🎫 Ticket Soporte<br/>(tickets)"]
    end

    Contact["👤 Contacto"] --> Account["🏢 Organización"]
    Contact --> Deal["📋 Oportunidad"]
    Account --> Deal
    Deal --> S1 --> S2 --> S3 --> S4 --> S5
    S5 --> Won & Lost
    Won --> Invoice --> Payment
    Won --> Ticket
```

### Ciclo de Vida de una Factura

```mermaid
stateDiagram-v2
    [*] --> Borrador: Crear factura
    Borrador --> Emitida: Emitir
    Emitida --> Parcial: Pago parcial
    Emitida --> Pagada: Pago completo
    Emitida --> Vencida: Pasó fecha de vencimiento
    Parcial --> Pagada: Pago final
    Parcial --> Vencida: Pasó fecha de vencimiento
    Vencida --> Parcial: Pago parcial tardío
    Vencida --> Pagada: Pago completo tardío
    Borrador --> Cancelada: Cancelar
    Emitida --> Cancelada: Cancelar
    Pagada --> [*]
    Cancelada --> [*]
```

---

## 8. Portal de Clientes (Externo)

```mermaid
flowchart TD
    subgraph External["🌐 Portal Público"]
        PLogin["GET /portal<br/>Login por email"]
        PAuth["POST /portal/login<br/>Autenticación sin password"]
        PDash["GET /portal/dashboard<br/>Ver tickets del contacto"]
        PTicket["POST /portal/ticket<br/>Crear ticket nuevo"]
        PLogout["GET /portal/logout"]
    end

    subgraph Internal["🔒 CRM Interno"]
        Tickets2["TicketController<br/>Gestión por equipo"]
        Comments["Comentarios internos<br/>(is_internal = 0/1)"]
    end

    Client["👤 Cliente Externo"] --> PLogin --> PAuth
    PAuth -->|"Busca email en contacts"| PDash
    PDash --> PTicket
    PTicket -->|"Crea ticket en BD"| Tickets2
    Tickets2 --> Comments
```

---

## 9. Estructura de Carpetas del Proyecto

```
crm_einsurglobal/
├── 📁 public/                    ← Document Root (Apache)
│   ├── index.php                 ← Entry Point único
│   ├── .htaccess                 ← Rewrite Rules
│   └── img/                      ← Assets estáticos
│
├── 📁 config/
│   ├── database.php              ← Conexión MySQL (PDO)
│   └── jwt.php                   ← Secreto JWT
│
├── 📁 routes/
│   └── web.php                   ← 70+ rutas (GET/POST)
│
├── 📁 src/
│   ├── 📁 Core/                  ← Framework propio
│   │   ├── Router.php            ← Despacho URI → Controller
│   │   ├── Database.php          ← PDO Singleton
│   │   ├── BaseModel.php         ← CRUD con tenant_id
│   │   ├── BaseRepository.php    ← CRUD avanzado + paginación
│   │   ├── TenantContext.php     ← Estado global del tenant
│   │   ├── Permission.php        ← Motor RBAC
│   │   ├── EmailService.php      ← PHPMailer SMTP
│   │   └── helpers.php           ← Funciones globales (url())
│   │
│   ├── 📁 Http/
│   │   ├── 📁 Controllers/       ← 23 controladores
│   │   │   ├── AuthController        ← Login/Logout/Switch
│   │   │   ├── HomeController        ← Dashboard KPIs
│   │   │   ├── ContactController     ← CRUD + Email + Search
│   │   │   ├── AccountController     ← CRUD Organizaciones
│   │   │   ├── DealController        ← CRUD + Pipeline
│   │   │   ├── FinanzasController    ← Facturas + Pagos + Auditoría
│   │   │   ├── TicketController      ← Soporte
│   │   │   ├── TaskController        ← Tareas
│   │   │   ├── ProductController     ← Catálogo
│   │   │   ├── ReportController      ← Reportes + Export
│   │   │   ├── IAController          ← Asistente IA
│   │   │   ├── GoalsController       ← Metas de venta
│   │   │   ├── ImportController      ← CSV Import
│   │   │   ├── PortalController      ← Portal clientes
│   │   │   ├── GrupoEinsurController ← Dashboard CEO
│   │   │   └── ...                   ← Roles, Users, Profile, etc.
│   │   │
│   │   └── 📁 Middleware/
│   │       ├── AuthMiddleware.php    ← JWT Bearer (API)
│   │       ├── TenantMiddleware.php  ← Sesión → TenantContext
│   │       └── RoleMiddleware.php    ← Guards por rol
│   │
│   ├── 📁 Models/                ← 13 modelos (extienden BaseModel)
│   │   ├── Account, Activity, AuditLog, Contact
│   │   ├── Deal, Invoice, PipelineStage, Product
│   │   ├── Role, Task, Tenant, Ticket, User
│   │
│   ├── 📁 Modules/              ← Patrón modular (Repository)
│   │   ├── Sales/Repositories/LeadRepository
│   │   ├── Sales/Services/LeadService
│   │   ├── Catalog/Repositories/
│   │   ├── Support/Repositories/
│   │   └── Activities/Repositories/
│   │
│   └── 📁 Views/                ← 24 secciones de vistas PHP
│       ├── auth/, dashboard/, contacts/, deals/
│       ├── finanzas/, tickets/, tasks/, products/
│       ├── reports/, roles/, users/, profile/
│       ├── empresas/, vendedores/, grupo_einsur/
│       ├── portal/, ia/, goals/, import/
│       ├── layouts/              ← Layout principal
│       └── partials/             ← Componentes reutilizables
│
├── 📁 database/                  ← 15 migraciones SQL
│   ├── 001_core_global.sql       ← tenants, users, roles, perms
│   ├── 002_sales_crm.sql        ← leads, accounts, contacts, deals
│   ├── 003_catalog_inventory.sql ← products, inventory
│   ├── 004_support_tickets.sql   ← tickets, comments
│   ├── 005_activities.sql        ← tasks, events, notes, attachments
│   ├── 010_finance_invoices.sql  ← invoices, payments
│   ├── 013_create_goals_table.sql
│   └── 014_ai_assistant_tables.sql
│
├── .env                          ← Variables de entorno
├── composer.json                 ← Dependencias PHP
└── vendor/                       ← Autoload (Composer)
```

---

## 10. Resumen Ejecutivo

| Aspecto | Detalle |
|---|---|
| **Tipo de App** | CRM Multi-Tenant para Grupo Einsur |
| **Stack** | PHP 8+ (vanilla MVC) + MySQL 8 + Apache |
| **Patrón** | MVC + Repository + Singleton (DB) |
| **Multi-Tenant** | Base de datos compartida, aislamiento por `tenant_id` en cada tabla |
| **Autenticación** | Sesiones PHP (web) + JWT (API) |
| **Autorización** | RBAC con 4 roles: Superadmin, Gerente, Vendedor, Cobranza |
| **Módulos** | Contactos, Organizaciones, Oportunidades (Pipeline), Finanzas, Tickets, Tareas, Productos, Reportes, IA, Metas, Import CSV |
| **Funciones Especiales** | Portal de clientes, Asistente IA, Auditoría CEO, Email SMTP por vendedor |
| **Tablas BD** | ~25 tablas con FK y aislamiento automático |
| **Controladores** | 23 controladores + 3 middlewares |
| **Vistas** | 24 secciones con layout compartido |
