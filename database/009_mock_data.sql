USE crm_einsurglobal_dev;

-- Insertar Organizaciones (Cuentas)
INSERT INTO accounts (tenant_id, name, email, phone, website, country, city, owner_id) VALUES 
(1, 'TechCorp Inc.', 'contacto@techcorp.com', '555-0101', 'https://techcorp.com', 'México', 'CDMX', 1),
(1, 'Global Solutions', 'info@globalsolutions.net', '555-0202', 'https://globalsolutions.net', 'España', 'Madrid', 1),
(1, 'Logística Nacional', 'ventas@logisticanacional.mx', '555-0303', 'https://logisticanacional.mx', 'México', 'Guadalajara', 1),
(1, 'Servicios Médicos Plus', 'admin@smedicos.com', '555-0404', 'https://smedicos.com', 'Colombia', 'Bogotá', 1),
(1, 'Constructora Apex', 'proyectos@apex.com', '555-0505', 'https://apex.com', 'Chile', 'Santiago', 1);

-- Guardar IDs de Cuentas insertadas
SET @acc_techcorp = LAST_INSERT_ID();
SET @acc_global = LAST_INSERT_ID() + 1;
SET @acc_logistica = LAST_INSERT_ID() + 2;
SET @acc_medicos = LAST_INSERT_ID() + 3;
SET @acc_apex = LAST_INSERT_ID() + 4;

-- Insertar Contactos
INSERT INTO contacts (tenant_id, account_id, first_name, last_name, email, phone, job_title, type, owner_id) VALUES 
(1, @acc_techcorp, 'Carlos', 'Martínez', 'cmartinez@techcorp.com', '555-1111', 'CTO', 'Cliente', 1),
(1, @acc_techcorp, 'Ana', 'López', 'alopez@techcorp.com', '555-1112', 'Gerente de Compras', 'Cliente', 1),
(1, @acc_global, 'David', 'García', 'dgarcia@globalsolutions.net', '555-2221', 'CEO', 'Prospecto', 1),
(1, @acc_logistica, 'María', 'Rodríguez', 'mrodriguez@logisticanacional.mx', '555-3331', 'Directora de Operaciones', 'Cliente', 1),
(1, @acc_medicos, 'Jorge', 'Pérez', 'jperez@smedicos.com', '555-4441', 'Administrador General', 'Prospecto', 1),
(1, @acc_apex, 'Elena', 'Sánchez', 'esanchez@apex.com', '555-5551', 'Gerente de Proyectos', 'Prospecto', 1);

-- Guardar IDs de Contactos
SET @cnt_carlos = LAST_INSERT_ID();
SET @cnt_ana = LAST_INSERT_ID() + 1;
SET @cnt_david = LAST_INSERT_ID() + 2;
SET @cnt_maria = LAST_INSERT_ID() + 3;
SET @cnt_jorge = LAST_INSERT_ID() + 4;
SET @cnt_elena = LAST_INSERT_ID() + 5;

-- Insertar Deals (Oportunidades)
INSERT INTO deals (tenant_id, account_id, contact_id, stage_id, owner_id, name, amount, probability, expected_close_date, status, source) VALUES 
(1, @acc_techcorp, @cnt_carlos, 5, 1, 'Renovación Licencias Anuales', 25000.00, 100, DATE_ADD(CURRENT_DATE(), INTERVAL -10 DAY), 'Ganado', 'Web'),
(1, @acc_global, @cnt_david, 4, 1, 'Implementación Sistema ERP', 85000.00, 75, DATE_ADD(CURRENT_DATE(), INTERVAL 15 DAY), 'Abierto', 'Recomendación'),
(1, @acc_logistica, @cnt_maria, 3, 1, 'Consultoría Logística Q3', 12000.50, 50, DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY), 'Abierto', 'Campaña de correo'),
(1, @acc_medicos, @cnt_jorge, 2, 1, 'Equipamiento Clínico Fase 1', 145000.00, 30, DATE_ADD(CURRENT_DATE(), INTERVAL 45 DAY), 'Abierto', 'Centro de llamadas'),
(1, @acc_apex, @cnt_elena, 1, 1, 'Software Gestión de Obras', 40000.00, 10, DATE_ADD(CURRENT_DATE(), INTERVAL 60 DAY), 'Abierto', 'Redes sociales'),
(1, @acc_techcorp, @cnt_ana, 6, 1, 'Servidores de Respaldo', 15000.00, 0, DATE_ADD(CURRENT_DATE(), INTERVAL -5 DAY), 'Perdido', 'Otro');

-- Guardar IDs de Deals
SET @deal_renovacion = LAST_INSERT_ID();
SET @deal_erp = LAST_INSERT_ID() + 1;
SET @deal_consultoria = LAST_INSERT_ID() + 2;
SET @deal_equipamiento = LAST_INSERT_ID() + 3;
SET @deal_software = LAST_INSERT_ID() + 4;
SET @deal_servidores = LAST_INSERT_ID() + 5;

-- Insertar Actividades (Bitácora)
INSERT INTO activities (tenant_id, user_id, entity_type, entity_id, type, description, created_at) VALUES 
(1, 1, 'deal', @deal_renovacion, 'Llamada', 'Se acordó la renovación por un año más. Todo en orden.', DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL -15 DAY)),
(1, 1, 'deal', @deal_renovacion, 'Correo', 'Contrato firmado y enviado.', DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL -10 DAY)),

(1, 1, 'deal', @deal_erp, 'Visita', 'Reunión en sus oficinas para presentar la demo del ERP. Muy interesados.', DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL -3 DAY)),
(1, 1, 'deal', @deal_erp, 'Llamada', 'Negociación de precios. Piden un descuento del 5%.', DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL -1 DAY)),

(1, 1, 'deal', @deal_consultoria, 'Correo', 'Se envió la propuesta económica inicial.', DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL -7 DAY)),
(1, 1, 'deal', @deal_consultoria, 'Nota', 'Falta definir la fecha de inicio del proyecto.', CURRENT_TIMESTAMP()),

(1, 1, 'deal', @deal_equipamiento, 'Llamada', 'Primer contacto para entender sus necesidades médicas.', DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL -2 DAY)),

(1, 1, 'contact', @cnt_elena, 'Visita', 'Presentación corporativa de nuestros servicios en su obra actual.', DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL -5 DAY)),
(1, 1, 'account', @acc_techcorp, 'Nota', 'Cliente clave, siempre priorizar sus tickets de soporte.', DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL -30 DAY));
