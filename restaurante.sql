-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-12-2025 a las 14:36:27
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `restaurante`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(50) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre_categoria`, `descripcion`, `activo`) VALUES
(1, 'Entradas', 'Platos appetizers y entradas para comenzar', 1),
(2, 'Sopas y Cremas', 'Sopas tradicionales y cremas', 1),
(3, 'Ensaladas', 'Ensaladas frescas y saludables', 1),
(4, 'Platos Criollos', 'Comida tradicional', 1),
(5, 'Carnes', 'Cortes de res, cerdo y cordero', 1),
(6, 'Aves', 'Platos con pollo y pavo', 1),
(7, 'Pescados y Mariscos', 'Platos con productos del mar', 1),
(8, 'Pastas', 'Fideos y pastas italianas', 1),
(9, 'Sandwiches y Hamburguesas', 'Opción rápida y deliciosa', 1),
(10, 'Parrillas', 'Carnes a la parrilla y brasas', 1),
(11, 'Bebidas Sin Alcohol', 'Refrescos, jugos y gaseosas', 1),
(12, 'Bebidas Alcohólicas', 'Cervezas, vinos y cocteles', 1),
(13, 'Postres', 'Dulces y postres tradicionales', 1),
(14, 'Extras y Guarniciones', 'Acompañamientos adicionales', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `telefono`, `correo`, `direccion`, `fecha_registro`) VALUES
(1, 'Cliente General', '999-9999', 'cliente@ejemplo.com', 'Dirección no especificada', '2025-12-01 01:33:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pedido`
--

CREATE TABLE `detalles_pedido` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL CHECK (`cantidad` > 0),
  `precio_unitario` decimal(10,2) NOT NULL CHECK (`precio_unitario` >= 0),
  `subtotal` decimal(10,2) NOT NULL CHECK (`subtotal` >= 0),
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_pedido`
--

INSERT INTO `detalles_pedido` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`, `subtotal`, `notas`) VALUES
(1, 1, 21, 1, 32.00, 32.00, NULL),
(2, 1, 68, 2, 16.00, 32.00, NULL),
(3, 1, 38, 1, 28.00, 28.00, NULL),
(4, 2, 21, 1, 32.00, 32.00, NULL),
(5, 2, 68, 1, 16.00, 16.00, NULL),
(6, 3, 21, 2, 32.00, 64.00, NULL),
(7, 4, 60, 1, 4.00, 4.00, NULL),
(8, 4, 57, 1, 8.00, 8.00, NULL),
(9, 4, 61, 1, 9.00, 9.00, NULL),
(10, 5, 21, 2, 32.00, 64.00, NULL),
(11, 5, 68, 3, 16.00, 48.00, NULL),
(12, 6, 21, 2, 32.00, 64.00, NULL),
(13, 6, 68, 3, 16.00, 48.00, NULL),
(14, 7, 68, 4, 16.00, 64.00, NULL),
(15, 8, 68, 5, 16.00, 80.00, NULL),
(16, 8, 21, 2, 32.00, 64.00, NULL),
(17, 8, 38, 4, 28.00, 112.00, NULL),
(18, 9, 4, 1, 22.00, 22.00, NULL),
(19, 9, 75, 1, 13.00, 13.00, NULL),
(20, 9, 71, 1, 14.00, 14.00, NULL),
(21, 9, 69, 1, 12.00, 12.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `total` decimal(10,2) NOT NULL CHECK (`total` >= 0),
  `metodo_pago` varchar(50) NOT NULL,
  `id_personal` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `estado` enum('pendiente','completado','cancelado') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `fecha_hora`, `total`, `metodo_pago`, `id_personal`, `id_cliente`, `estado`) VALUES
(1, '2025-11-30 22:20:06', 92.00, 'efectivo', 2, NULL, 'completado'),
(2, '2025-12-01 01:27:07', 48.00, 'tarjeta', 2, NULL, 'completado'),
(3, '2025-12-01 01:53:06', 64.00, 'efectivo', 2, NULL, 'completado'),
(4, '2025-12-01 01:53:40', 21.00, 'efectivo', 2, NULL, 'completado'),
(5, '2025-12-01 01:54:33', 112.00, 'efectivo', 2, 1, 'completado'),
(6, '2025-12-01 01:59:15', 112.00, 'efectivo', 2, 1, 'completado'),
(7, '2025-12-01 05:56:11', 64.00, 'efectivo', 2, NULL, 'completado'),
(8, '2025-12-01 08:08:29', 256.00, 'efectivo', 2, 1, 'completado'),
(9, '2025-12-01 08:27:56', 61.00, 'efectivo', 2, NULL, 'completado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `id_personal` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`id_personal`, `nombre`, `usuario`, `contrasena`, `id_rol`, `activo`, `fecha_creacion`) VALUES
(1, 'Jhoel Chacaguasay ', 'jhoel', '123456', 1, 1, '2025-12-01 01:33:57'),
(2, 'Juanfran', 'jordan', '1234567', 2, 1, '2025-12-01 01:33:57'),
(3, 'Juanfran', 'juanfran', '12345678', 3, 1, '2025-12-01 11:54:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL CHECK (`precio` >= 0),
  `id_categoria` int(11) NOT NULL,
  `stock` int(11) DEFAULT 0 CHECK (`stock` >= 0),
  `descripcion` text DEFAULT NULL,
  `imagen_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `precio`, `id_categoria`, `stock`, `descripcion`, `imagen_url`) VALUES
(1, 'Ceviche Clásico', 28.00, 1, 40, 'Pescado fresco marinado en limón con cebolla, camote y choclo', NULL),
(2, 'Tiradito de Pescado', 26.00, 1, 35, 'Láminas de pescado con salsa de ají amarillo', NULL),
(3, 'Causa Limeña', 18.00, 1, 30, 'Papa amarilla con ají rellena de pollo o atún', NULL),
(4, 'Anticuchos de Corazón', 22.00, 1, 24, 'Brochetas de corazón de res con maíz y papa', NULL),
(5, 'Papa a la Huancaína', 16.00, 1, 40, 'Papa cocida con salsa de ají amarillo y queso', NULL),
(6, 'Tequeños de Queso', 14.00, 1, 50, 'Masa rellena de queso frito, acompañado de salsa de ají', NULL),
(7, 'Choros a la Chalaca', 20.00, 1, 30, 'Mejillones con cebolla, maíz y limón', NULL),
(8, 'Alitas BBQ', 24.00, 1, 45, 'Alitas de pollo en salsa barbacoa', NULL),
(9, 'Sopa a la Minuta', 15.00, 2, 30, 'Sopa tradicional con fideos, leche y huevo', NULL),
(10, 'Chupe de Camarones', 32.00, 2, 20, 'Sopa espesa con camarones, papa, arroz y huevo', NULL),
(11, 'Caldo de Gallina', 18.00, 2, 35, 'Caldo sustancioso con gallina, fideos y verduras', NULL),
(12, 'Crema de Espárragos', 14.00, 2, 25, 'Crema suave de espárragos con crotones', NULL),
(13, 'Sopa Wantán', 16.00, 2, 30, 'Sopa china con wantanes de cerdo y verduras', NULL),
(14, 'Menestrón', 20.00, 2, 25, 'Sopa de frijoles con carne, fideos y verduras', NULL),
(15, 'Ensalada César', 22.00, 3, 25, 'Lechuga romana, crutones, parmesano y aderezo césar', NULL),
(16, 'Ensalada Griega', 24.00, 3, 20, 'Lechuga, tomate, pepino, aceitunas y queso feta', NULL),
(17, 'Ensalada de Quinua', 20.00, 3, 30, 'Quinua con verduras frescas y vinagreta de hierbas', NULL),
(18, 'Ensalada Caprese', 26.00, 3, 15, 'Tomate, mozzarella fresca y albahaca', NULL),
(19, 'Ensalada de Pollo', 23.00, 3, 25, 'Mix de lechugas con pollo grillado y aderezo de mostaza', NULL),
(20, 'Lomo Saltado', 38.00, 4, 30, 'Lomo de res salteado con cebolla, tomate y papas fritas', NULL),
(21, 'Aji de Gallina', 32.00, 4, 15, 'Gallina deshilachada en salsa de ají amarillo con papa', NULL),
(22, 'Arroz con Pollo', 28.00, 4, 35, 'Arroz verde con pollo, culantro y verduras', NULL),
(23, 'Carapulcra', 34.00, 4, 20, 'Papa seca con carne de cerdo y maní', NULL),
(24, 'Tacu Tacu con Lomo', 36.00, 4, 25, 'Tacu tacu de frijoles con lomo fino', NULL),
(25, 'Pachamanca', 45.00, 4, 15, 'Carnes y papas cocidas bajo tierra al estilo andino', NULL),
(26, 'Rocoto Relleno', 30.00, 4, 20, 'Rocoto relleno de carne, queso y hierbas', NULL),
(27, 'Cau Cau de Mondongo', 26.00, 4, 25, 'Guiso de mondongo con papa y hierbabuena', NULL),
(28, 'Bife de Chorizo', 48.00, 5, 20, 'Corte premium de res a la parrilla con guarnición', NULL),
(29, 'Asado de Tira', 42.00, 5, 25, 'Costillas de res a la parrilla con salsa criolla', NULL),
(30, 'Filet Mignon', 55.00, 5, 15, 'Corte tierno de res con salsa de vino tinto', NULL),
(31, 'Churrasco', 40.00, 5, 30, 'Bistec de res a la plancha con cebolla caramelizada', NULL),
(32, 'Costillas BBQ', 46.00, 5, 20, 'Costillas de cerdo en salsa barbacoa con papas', NULL),
(33, 'Lomo de Cerdo', 38.00, 5, 25, 'Lomo de cerdo al horno con salsa de ciruelas', NULL),
(34, 'Pollo a la Brasa', 35.00, 6, 40, 'Pollo entero marinado y horneado con papas y ensalada', NULL),
(35, 'Pollo Saltado', 30.00, 6, 35, 'Pollo salteado con cebolla, tomate y papas fritas', NULL),
(36, 'Pechuga Grillada', 32.00, 6, 30, 'Pechuga de pollo a la plancha con vegetales', NULL),
(37, 'Pollo al Curry', 34.00, 6, 25, 'Pollo en salsa de curry con arroz basmati', NULL),
(38, 'Alitas Picantes', 28.00, 6, 40, 'Alitas de pollo en salsa picante con apio y zanahoria', NULL),
(39, 'Pescado a la Chorrillana', 42.00, 7, 20, 'Filete de pescado con cebolla, tomate y vino blanco', NULL),
(40, 'Arroz con Mariscos', 45.00, 7, 25, 'Arroz con mix de mariscos y culantro', NULL),
(41, 'Chicharrón de Calamar', 38.00, 7, 30, 'Calamares fritos crujientes con salsa tártara', NULL),
(42, 'Parihuela', 48.00, 7, 15, 'Sopa espesa con variedad de mariscos y pescados', NULL),
(43, 'Langostinos al Ajillo', 52.00, 7, 18, 'Langostinos salteados con ajo y perejil', NULL),
(44, 'Filete de Corvina', 40.00, 7, 22, 'Filete de corvina a la plancha con mantequilla de hierbas', NULL),
(45, 'Tallarines Verdes', 26.00, 8, 30, 'Pasta con salsa de albahaca, espinaca y nueces', NULL),
(46, 'Fettuccine Alfredo', 28.00, 8, 25, 'Pasta con salsa cremosa de queso parmesano', NULL),
(47, 'Lasaña de Carne', 32.00, 8, 20, 'Láminas de pasta con carne, salsa bechamel y queso', NULL),
(48, 'Spaghetti Bolognesa', 26.00, 8, 35, 'Pasta con salsa de carne molida y tomate', NULL),
(49, 'Ravioles de Ricotta', 30.00, 8, 22, 'Ravioles rellenos de ricotta con salsa de tomate', NULL),
(50, 'Hamburguesa Clásica', 22.00, 9, 40, 'Carne, queso, lechuga, tomate y papas fritas', NULL),
(51, 'Sandwich de Chicharrón', 20.00, 9, 35, 'Chicharrón de cerdo con camote y salsa criolla', NULL),
(52, 'Butifarra', 18.00, 9, 45, 'Jamón criollo con cebolla, ají y lechuga', NULL),
(53, 'Triple Sandwich', 25.00, 9, 30, 'Tres pisos de pollo, jamón y pavo con vegetales', NULL),
(54, 'Hamburguesa BBQ', 28.00, 9, 25, 'Hamburguesa con salsa barbacoa y aros de cebolla', NULL),
(55, 'Inca Kola 500ml', 6.00, 11, 100, 'Refresco peruano sabor a hierba luisa', NULL),
(56, 'Coca Cola 500ml', 6.00, 11, 100, 'Refresco de cola', NULL),
(57, 'Chicha Morada', 8.00, 11, 79, 'Bebida de maíz morado con frutas', NULL),
(58, 'Maracuyá', 8.00, 11, 70, 'Jugo natural de maracuyá', NULL),
(59, 'Limonada', 7.00, 11, 90, 'Limonada fresca con hierbabuena', NULL),
(60, 'Agua Mineral 500ml', 4.00, 11, 119, 'Agua mineral sin gas', NULL),
(61, 'Jugo de Naranja', 9.00, 11, 59, 'Jugo de naranja natural exprimido', NULL),
(62, 'Pisco Sour', 18.00, 12, 50, 'Coctel nacional con pisco, limón y clara de huevo', NULL),
(63, 'Cerveza Cusqueña 330ml', 10.00, 12, 80, 'Cerveza lager peruana', NULL),
(64, 'Chilcano de Pisco', 16.00, 12, 45, 'Pisco con ginger ale y limón', NULL),
(65, 'Vino Tinto Copa', 15.00, 12, 60, 'Copa de vino tino reserva', NULL),
(66, 'Cerveza Artesanal', 14.00, 12, 40, 'Cerveza artesanal de la casa', NULL),
(67, 'Mojito', 17.00, 12, 35, 'Ron, menta, limón y soda', NULL),
(68, 'Algarrobina', 16.00, 12, 12, 'Coctel con pisco y algarrobina', NULL),
(69, 'Mazamorra Morada', 12.00, 13, 39, 'Postre de maíz morado con frutas secas', NULL),
(70, 'Arroz con Leche', 10.00, 13, 45, 'Arroz cocido en leche con canela', NULL),
(71, 'Suspiro Limeño', 14.00, 13, 29, 'Manjar blanco con merengue de vino oporto', NULL),
(72, 'Picarones', 15.00, 13, 35, 'Anillos fritos de camote con miel de chancaca', NULL),
(73, 'Torta Helada', 16.00, 13, 25, 'Torta de chocolate y vainilla helada', NULL),
(74, 'Flan de Vainilla', 12.00, 13, 40, 'Flan casero con caramelo', NULL),
(75, 'Crema Volteada', 13.00, 13, 34, 'Postre de huevo y leche con caramelo', NULL),
(76, 'Porción de Papas Fritas', 8.00, 14, 60, 'Papas fritas crujientes', NULL),
(77, 'Arroz Blanco', 5.00, 14, 80, 'Arroz graneado blanco', NULL),
(78, 'Ensalada Fresca', 6.00, 14, 50, 'Mix de lechugas con tomate y pepino', NULL),
(79, 'Porción de Tacu Tacu', 7.00, 14, 40, 'Tacu tacu de frijoles frito', NULL),
(80, 'Salsa Huancaína', 4.00, 14, 70, 'Salsa de ají amarillo con queso', NULL),
(81, 'Salsa Criolla', 3.00, 14, 75, 'Cebolla en juliana con limón y ají', NULL),
(82, 'Pan con Mantequilla', 3.00, 14, 100, 'Pan francés con mantequilla', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`, `fecha_creacion`) VALUES
(1, 'admin', 'Acceso completo al sistema, puede gestionar productos, personal y ver reportes', '2025-12-01 01:33:57'),
(2, 'Cajero', 'Puede realizar ventas y gestionar pedidos', '2025-12-01 01:33:57'),
(3, 'Comprador', 'Usuario que realiza compras en el sistema', '2025-12-01 01:33:57');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_personal` (`id_personal`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id_personal`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `id_rol` (`id_rol`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id_personal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD CONSTRAINT `detalles_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`),
  ADD CONSTRAINT `detalles_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);

--
-- Filtros para la tabla `personal`
--
ALTER TABLE `personal`
  ADD CONSTRAINT `personal_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
