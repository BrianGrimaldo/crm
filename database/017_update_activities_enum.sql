-- Actualizar el ENUM de la columna type en la tabla activities para incluir WhatsApp
ALTER TABLE activities MODIFY COLUMN type ENUM('Llamada', 'Correo', 'WhatsApp', 'Visita', 'Nota') NOT NULL;
