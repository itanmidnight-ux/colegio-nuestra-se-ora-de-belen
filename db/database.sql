-- ==========================
-- Limpiar tablas previas
-- ==========================
DROP TABLE IF EXISTS comentarios;
DROP TABLE IF EXISTS periodicos;
DROP TABLE IF EXISTS users;

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

