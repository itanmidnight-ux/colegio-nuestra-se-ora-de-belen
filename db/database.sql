-- ==========================
-- Limpiar tablas previas
-- ==========================
DROP TABLE IF EXISTS comentarios;
DROP TABLE IF EXISTS periodicos;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS secciones_periodico;
DROP TABLE IF EXISTS contactos;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','editor') DEFAULT 'editor',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS periodicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    director VARCHAR(100) NOT NULL,
    participantes TEXT,
    descripcion TEXT,
    archivo_pdf VARCHAR(255) NOT NULL,
    publicado_en DATE,
    usuario_id INT,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL
);



CREATE TABLE IF NOT EXISTS secciones_periodico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT,
    contenido LONGTEXT,
    imagen VARCHAR(255),
    orden_visual INT DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    tipo VARCHAR(60) NOT NULL,
    mensaje TEXT NOT NULL,
    solicita_contacto TINYINT(1) NOT NULL DEFAULT 0,
    grado VARCHAR(80) DEFAULT NULL,
    urgente TINYINT(1) NOT NULL DEFAULT 0,
    nombre_contacto VARCHAR(120) DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_nombre VARCHAR(100) NOT NULL,
    comentario TEXT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    periodico_id INT,
    FOREIGN KEY (periodico_id) REFERENCES periodicos(id) ON DELETE CASCADE
);

-- ==========================
-- Usuario inicial (texto plano)
-- ==========================
INSERT INTO users (nombre, email, password, rol)
VALUES 
('fabian', 'fabian', 'admin123', 'admin'),
('ingrid', 'ingrid', 'admin123', 'admin');

