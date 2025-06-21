-- inventario_setup.sql
-- Script de creación de la base de datos para el sistema de inventario
-- Incluye tablas, relaciones y datos de ejemplo

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS inventario CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE inventario;

-- Tabla de categorías
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de productos
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    cantidad INT NOT NULL DEFAULT 0,
    categoria_id INT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    INDEX (nombre),
    INDEX (categoria_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar categorías de ejemplo
INSERT INTO categorias (nombre, descripcion) VALUES
('Electrónicos', 'Dispositivos electrónicos y gadgets'),
('Ropa', 'Prendas de vestir para hombres, mujeres y niños'),
('Alimentos', 'Productos alimenticios y bebidas'),
('Hogar', 'Artículos para el hogar y decoración'),
('Oficina', 'Suministros y equipos de oficina');

-- Insertar productos de ejemplo
INSERT INTO productos (nombre, descripcion, precio, cantidad, categoria_id) VALUES
('Smartphone X', 'Último modelo de smartphone con cámara de 108MP', 899.99, 15, 1),
('Laptop Pro', 'Laptop de 15 pulgadas con procesador i7 y 16GB RAM', 1299.99, 8, 1),
('Camisa de algodón', 'Camisa 100% algodón, talla M', 29.99, 45, 2),
('Arroz integral', 'Paquete de 1kg de arroz integral orgánico', 3.49, 120, 3),
('Sofá de cuero', 'Sofá de 3 plazas en cuero genuino', 899.00, 3, 4),
('Monitor 24"', 'Monitor Full HD de 24 pulgadas', 179.99, 12, 1),
('Juego de sábanas', 'Juego de sábanas de algodón egipcio, tamaño queen', 89.99, 18, 4),
('Café molido', 'Paquete de 500g de café molido premium', 8.99, 65, 3),
('Impresora láser', 'Impresora láser multifunción', 249.99, 7, 5),
('Zapatos deportivos', 'Zapatos para correr, talla 42', 59.99, 22, 2),
('Teclado inalámbrico', 'Teclado ergonómico inalámbrico', 45.99, 30, 5),
('Aceite de oliva', 'Botella de 500ml de aceite de oliva extra virgen', 7.99, 85, 3),
('Mesa de centro', 'Mesa de centro de madera maciza', 149.99, 5, 4),
('Pantalón vaquero', 'Pantalón jeans, talla 32', 39.99, 28, 2),
('Router WiFi', 'Router dual band de última generación', 79.99, 14, 1);

-- Crear usuario para la aplicación (opcional)
CREATE USER IF NOT EXISTS 'inventario_user'@'localhost' IDENTIFIED BY 'Inventario123!';
GRANT ALL PRIVILEGES ON inventario.* TO 'inventario_user'@'localhost';
FLUSH PRIVILEGES;

-- Consulta para verificar las tablas creadas
SHOW TABLES;

-- Consulta para ver la estructura de las tablas
DESCRIBE categorias;
DESCRIBE productos;

-- Consulta para ver los datos de ejemplo
SELECT * FROM categorias;
SELECT p.id, p.nombre, p.precio, p.cantidad, c.nombre as categoria 
FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id 
ORDER BY p.fecha_actualizacion DESC;