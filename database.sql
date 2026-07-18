-- Ejecutar en phpMyAdmin (XAMPP) o en la consola de MySQL

CREATE DATABASE IF NOT EXISTS sistema_eventos
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sistema_eventos;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(50) DEFAULT 'Estudiante',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE eventos(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    fecha DATE NOT NULL,
    lugar VARCHAR(150) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    descripcion TEXT
);

INSERT INTO eventos(nombre,fecha,lugar,categoria,estado,descripcion)
VALUES
('Feria de Emprendimiento','2026-06-12','Auditorio Principal','Academico','Activo','Evento de emprendimiento'),

('Conferencia de Tecnologia','2026-06-18','Bloque B Aula 203','Tecnologia','Finalizado','Conferencia tecnologica');

-- Usuario demo para probar el login.
-- Correo: demo@ug.edu.ec
-- Contraseña real (en texto plano, solo para que la escribas en el formulario): 123456
-- El hash de abajo fue generado con bcrypt (equivalente a password_hash() de PHP) y sí es funcional con password_verify().
INSERT INTO usuarios (nombre, apellido, correo, password, rol) VALUES
('Ronald', 'Andagoya', 'demo@ug.edu.ec', '$2b$10$WclFhGuucUkYR71kBrOO3ejabR4P7qOv8SfG51nWRUA1zgC/n7xP6', 'Estudiante');

CREATE TABLE IF NOT EXISTS inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    carrera VARCHAR(150) DEFAULT NULL,
    fecha_inscripcion DATE NOT NULL,
    hora_evento TIME DEFAULT NULL,
    estado VARCHAR(50) NOT NULL DEFAULT 'Activo',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE
);

INSERT INTO inscripciones (evento_id, nombre, apellido, correo, carrera, fecha_inscripcion, estado) VALUES
(1, 'Ana', 'Mendoza', 'ana.mendoza@ug.edu.ec', 'Ingeniería en Sistemas', '2026-06-02', 'Activo'),
(1, 'Luis', 'Paredes', 'luis.paredes@ug.edu.ec', 'Derecho', '2026-06-04', 'Activo'),
(2, 'Carmen', 'Rojas', 'carmen.rojas@ug.edu.ec', 'Administración', '2026-06-01', 'Cancelado');

--Tabla y inserts de asistencia

USE sistema_eventos;

-- Tabla de asistencia
CREATE TABLE IF NOT EXISTS asistencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inscripcion_id INT DEFAULT NULL,
    usuario_id INT NOT NULL,
    evento VARCHAR(150) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    estado ENUM('Presente', 'Ausente', 'Tardanza') DEFAULT 'Presente',
    observaciones TEXT DEFAULT NULL,
    estado_inscripcion VARCHAR(50) DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inscripcion_id) REFERENCES inscripciones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Datos de ejemplo para probar
INSERT INTO asistencia (usuario_id, evento, fecha, hora, estado, observaciones) VALUES
(1, 'Hackathon UG 2025',  '2025-05-15', '08:05:00', 'Presente',  'Sin observaciones'),
(1, 'Feria de Ciencias',  '2025-05-16', '09:30:00', 'Tardanza',  'Tráfico en vía principal'),
(1, 'Seminario IA',       '2025-05-17', '07:50:00', 'Presente',  NULL),
(1, 'Hackathon UG 2025',  '2025-05-18', '10:00:00', 'Ausente',   'Enfermedad'),
(1, 'Feria de Ciencias',  '2025-05-19', '08:00:00', 'Presente',  NULL);