CREATE DATABASE IF NOT EXISTS comercializadora 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE comercializadora;

-- Tabla Usuarios (corregida)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,  -- Cambiado de 'nombres' a 'nombre'
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    usuario_estado ENUM('Suspendido', 'Inactivo', 'Activo') DEFAULT 'Activo',
    rol ENUM('admin', 'cliente') DEFAULT 'cliente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla Direcciones (sin cambios)
CREATE TABLE IF NOT EXISTS direcciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    alias VARCHAR(50) NOT NULL COMMENT 'Nombre para identificar la dirección (ej: Casa, Trabajo)',
    direccion TEXT NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(10) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    pais VARCHAR(50) DEFAULT 'México',
    instrucciones_entrega TEXT COMMENT 'Instrucciones especiales para el repartidor',
    es_principal BOOLEAN DEFAULT FALSE COMMENT 'Indica si es la dirección principal del usuario',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla Categorías (sin cambios)
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT
);

-- Tabla Productos (corregida)
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    cantidad INT DEFAULT 0 COMMENT 'Cantidad en stock',
    estado ENUM('disponible', 'agotado', 'suspendido', 'poco_stock') DEFAULT 'disponible',
    imagen VARCHAR(500),
    destacado ENUM('si', 'no') DEFAULT 'no',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- Tabla Carrito (sin cambios)
CREATE TABLE IF NOT EXISTS carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    UNIQUE KEY usuario_producto (usuario_id, producto_id)
);

-- Tabla Pedidos (sin cambios)
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    direccion_id INT,
    total DECIMAL(10, 2) NOT NULL,
    estado ENUM('pendiente', 'confirmado', 'preparando', 'en_camino', 'entregado', 'cancelado') DEFAULT 'pendiente',
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') DEFAULT 'efectivo',
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (direccion_id) REFERENCES direcciones(id) ON DELETE SET NULL
);

-- Tabla Detalles de Pedido (sin cambios)
CREATE TABLE IF NOT EXISTS detalles_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT,
    nombre_producto VARCHAR(255) NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL
);

-- Tabla Productos Favoritos
CREATE TABLE IF NOT EXISTS productos_favoritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    UNIQUE KEY usuario_producto_fav (usuario_id, producto_id)
);
-- todas son "password" hasheadas
INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, email, contraseña, telefono, rol) VALUES
('Juan', 'Pérez', 'Gómez', 'juan@comercializadora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5512345678', 'admin'),
('María', 'López', 'Hernández', 'maria@comercializadora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5523456789', 'cliente'),
('Carlos', 'García', 'Martínez', 'carlos@comercializadora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5534567890', 'cliente');

INSERT INTO categorias (nombre, descripcion) VALUES
('Electrónicos', 'Dispositivos electrónicos y gadgets'),
('Hogar', 'Artículos para el hogar'),
('Oficina', 'Productos de oficina y papelería');

INSERT INTO productos (categoria_id, nombre, descripcion, precio, cantidad, estado) VALUES
(1, 'Smartphone X', 'Teléfono inteligente última generación', 8999.99, 50, 'disponible'),
(1, 'Tablet Pro', 'Tablet de 10 pulgadas con stylus', 4599.50, 30, 'disponible'),
(2, 'Juego de sábanas', 'Juego de sábanas de algodón king size', 899.00, 100, 'disponible'),
(3, 'Paquete de hojas', 'Resma de 500 hojas tamaño carta', 120.00, 200, 'disponible'),
(1, 'Laptop Elite', 'Laptop profesional i7 16GB RAM', 18999.00, 5, 'poco_stock');

INSERT INTO direcciones (usuario_id, alias, direccion, ciudad, codigo_postal, estado, es_principal) VALUES
(1, 'Casa', 'Av. Insurgentes 123, Col. Condesa', 'Ciudad de México', '06140', 'CDMX', TRUE),
(2, 'Oficina', 'Paseo de la Reforma 505, Piso 12', 'Ciudad de México', '06500', 'CDMX', TRUE),
(3, 'Casa', 'Calzada Guadalupe 456, Col. Lindavista', 'Ciudad de México', '07300', 'CDMX', TRUE);

INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES
(2, 1, 1),  -- María tiene un Smartphone X en su carrito
(2, 3, 2),  -- María tiene 2 juegos de sábanas
(3, 5, 1);  -- Carlos tiene una Laptop Elite

-- Pedido 1
INSERT INTO pedidos (usuario_id, direccion_id, total, estado, metodo_pago) VALUES
(2, 2, 10797.99, 'entregado', 'tarjeta');

INSERT INTO detalles_pedido (pedido_id, producto_id, nombre_producto, cantidad, precio_unitario, total) VALUES
(1, 1, 'Smartphone X', 1, 8999.99, 8999.99),
(1, 3, 'Juego de sábanas', 2, 899.00, 1798.00);

-- Pedido 2
INSERT INTO pedidos (usuario_id, direccion_id, total, estado, metodo_pago) VALUES
(3, 3, 18999.00, 'preparando', 'transferencia');

INSERT INTO detalles_pedido (pedido_id, producto_id, nombre_producto, cantidad, precio_unitario, total) VALUES
(2, 5, 'Laptop Elite', 1, 18999.00, 18999.00);


INSERT INTO productos_favoritos (usuario_id, producto_id) VALUES
(1, 5), -- Juan: favorita la Laptop Elite
(1, 1), -- Juan: Smartphone X
(2, 2), -- María: Tablet Pro
(2, 3), -- María: Juego de sábanas
(3, 1), -- Carlos: Smartphone X
(3, 5); -- Carlos: Laptop Elite