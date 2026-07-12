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

-- Usuario demo para probar el login.
-- Correo: demo@ug.edu.ec
-- Contraseña real (en texto plano, solo para que la escribas en el formulario): 123456
-- El hash de abajo fue generado con bcrypt (equivalente a password_hash() de PHP) y sí es funcional con password_verify().
INSERT INTO usuarios (nombre, apellido, correo, password, rol) VALUES
('Ronald', 'Andagoya', 'demo@ug.edu.ec', '$2b$10$WclFhGuucUkYR71kBrOO3ejabR4P7qOv8SfG51nWRUA1zgC/n7xP6', 'Estudiante');
