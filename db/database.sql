SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS comentarios;
DROP TABLE IF EXISTS encuesta_metricas;
DROP TABLE IF EXISTS encuesta_historial;
DROP TABLE IF EXISTS encuesta_respuestas;
DROP TABLE IF EXISTS encuesta_opciones;
DROP TABLE IF EXISTS encuestas;
DROP TABLE IF EXISTS periodicos;
DROP TABLE IF EXISTS secciones_periodico;
DROP TABLE IF EXISTS contactos;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','editor') DEFAULT 'editor',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS periodicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    director VARCHAR(100) NOT NULL,
    participantes TEXT,
    descripcion TEXT,
    archivo_pdf VARCHAR(255) NOT NULL,
    publicado_en DATE NOT NULL,
    usuario_id INT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_periodico_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_periodicos_fecha (publicado_en),
    INDEX idx_periodicos_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS secciones_periodico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT,
    contenido LONGTEXT,
    imagen VARCHAR(255),
    bloques_extra LONGTEXT,
    orden_visual INT DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_seccion_orden (orden_visual),
    INDEX idx_seccion_titulo (titulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contactos_fecha (creado_en),
    INDEX idx_contactos_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_nombre VARCHAR(100) NOT NULL,
    comentario TEXT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    periodico_id INT NOT NULL,
    CONSTRAINT fk_comentario_periodico FOREIGN KEY (periodico_id) REFERENCES periodicos(id) ON DELETE CASCADE,
    INDEX idx_comentarios_periodico (periodico_id),
    INDEX idx_comentarios_fecha (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS encuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(180) NOT NULL,
    pregunta TEXT NOT NULL,
    ubicacion ENUM('on_entry','on_header_nav','on_virtual_read_end','on_download','on_sections_menu') NOT NULL,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    creada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    finalizada_en TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_encuestas_ubicacion (ubicacion),
    INDEX idx_encuestas_activa (activa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS encuesta_opciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encuesta_id INT NOT NULL,
    texto VARCHAR(255) NOT NULL,
    orden_visual INT DEFAULT 0,
    CONSTRAINT fk_opciones_encuesta FOREIGN KEY (encuesta_id) REFERENCES encuestas(id) ON DELETE CASCADE,
    INDEX idx_opciones_encuesta (encuesta_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS encuesta_respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encuesta_id INT NOT NULL,
    opcion_id INT NOT NULL,
    session_token VARCHAR(120) DEFAULT NULL,
    contexto VARCHAR(80) DEFAULT NULL,
    respondida_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_respuestas_encuesta FOREIGN KEY (encuesta_id) REFERENCES encuestas(id) ON DELETE CASCADE,
    CONSTRAINT fk_respuestas_opcion FOREIGN KEY (opcion_id) REFERENCES encuesta_opciones(id) ON DELETE CASCADE,
    INDEX idx_respuestas_encuesta (encuesta_id),
    INDEX idx_respuestas_opcion (opcion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS encuesta_historial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encuesta_id INT NULL,
    accion ENUM('create','update','finish','delete','answer') NOT NULL,
    payload_json LONGTEXT,
    usuario_id INT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historial_encuesta FOREIGN KEY (encuesta_id) REFERENCES encuestas(id) ON DELETE SET NULL,
    CONSTRAINT fk_historial_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_historial_encuesta (encuesta_id),
    INDEX idx_historial_accion (accion),
    INDEX idx_historial_fecha (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS encuesta_metricas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encuesta_id INT NOT NULL,
    opcion_id INT NOT NULL,
    total_respuestas INT NOT NULL DEFAULT 0,
    porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_metricas_encuesta FOREIGN KEY (encuesta_id) REFERENCES encuestas(id) ON DELETE CASCADE,
    CONSTRAINT fk_metricas_opcion FOREIGN KEY (opcion_id) REFERENCES encuesta_opciones(id) ON DELETE CASCADE,
    UNIQUE KEY uq_metricas_encuesta_opcion (encuesta_id, opcion_id),
    INDEX idx_metricas_encuesta (encuesta_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (nombre, email, password, rol)
VALUES
('fabian', 'fabian', 'admin123', 'admin'),
('ingrid', 'ingrid', 'admin123', 'admin');
