-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-05-2025 a las 23:10:43
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
-- Base de datos: `restaurante_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden`
--

CREATE TABLE `detalle_orden` (
  `id` int(11) NOT NULL,
  `orden_id` int(11) DEFAULT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `modificadores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`modificadores`)),
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_orden`
--

INSERT INTO `detalle_orden` (`id`, `orden_id`, `menu_id`, `cantidad`, `modificadores`, `notas`) VALUES
(1, 1, 1, 2, NULL, NULL),
(2, 1, 7, 3, '{\"sin_cebolla\":true}', 'Extra salsa'),
(3, 1, 12, 2, NULL, NULL),
(4, 1, 15, 3, '[\"sin_almendra\"]', NULL),
(5, 2, 8, 1, NULL, 'Salsa extra picante'),
(6, 2, 3, 2, NULL, NULL),
(7, 2, 17, 4, NULL, NULL),
(8, 2, 20, 1, NULL, NULL),
(9, 3, 13, 1, NULL, NULL),
(10, 3, 2, 2, NULL, NULL),
(11, 3, 18, 2, NULL, NULL),
(12, 4, 9, 1, NULL, NULL),
(13, 4, 7, 4, NULL, NULL),
(14, 4, 16, 2, NULL, NULL),
(15, 4, 22, 6, NULL, NULL),
(16, 5, 6, 2, NULL, NULL),
(17, 5, 11, 1, NULL, 'Extra parmesano'),
(18, 5, 17, 1, NULL, NULL),
(19, 5, 21, 3, NULL, NULL),
(20, 6, 4, 2, NULL, NULL),
(21, 6, 10, 2, NULL, NULL),
(22, 6, 14, 1, NULL, NULL),
(23, 6, 19, 4, NULL, NULL),
(24, 7, 12, 1, NULL, 'Sin cebolla en la hamburguesa'),
(25, 7, 8, 2, NULL, NULL),
(26, 7, 15, 1, NULL, NULL),
(27, 8, 3, 3, NULL, NULL),
(28, 8, 5, 1, NULL, 'Sin aceite trufa'),
(29, 8, 11, 1, NULL, 'Extra champiñones'),
(30, 9, 2, 1, NULL, NULL),
(31, 9, 6, 1, NULL, NULL),
(32, 9, 10, 2, NULL, NULL),
(33, 9, 20, 2, NULL, NULL),
(34, 10, NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos_simulaciones`
--

CREATE TABLE `eventos_simulaciones` (
  `id` int(11) NOT NULL,
  `tipo` enum('evento','simulacion') NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `fecha` date NOT NULL,
  `menu_especial` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`menu_especial`)),
  `recursos_asignados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recursos_asignados`)),
  `parametros_simulacion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parametros_simulacion`)),
  `resultado_simulacion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`resultado_simulacion`)),
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos_simulaciones`
--

INSERT INTO `eventos_simulaciones` (`id`, `tipo`, `nombre`, `fecha`, `menu_especial`, `recursos_asignados`, `parametros_simulacion`, `resultado_simulacion`, `usuario_id`) VALUES
(1, 'evento', 'Cena Maridaje de Vinos', '2025-06-15', '[{\"nombre\": \"Tostadas de Tinga de Pollo\", \"precio\": 120.00}, {\"nombre\": \"Filete de Salmón con Salsa de Maracuyá\", \"precio\": 250.00}, {\"nombre\": \"Cheesecake de Frutos Rojos\", \"precio\": 130.00}]', '{\"sommelier\": \"María López\", \"meseros\": [\"Luis Hernández\", \"Carla Martínez\"]}', NULL, NULL, 8),
(2, 'simulacion', 'Predicción Ventas Fin de Semana', '2025-05-25', NULL, NULL, '{\"periodo\": \"2025-05-23 a 2025-05-25\", \"canal\": \"local\", \"horas_pico\": [\"19:00\", \"20:00\"]}', '{\"ventas_est_May25\": 12500.00, \"platillos_top\": [\"Hamburguesa Clásica\", \"Ribeye a la Parrilla\"]}', 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id` int(11) NOT NULL,
  `nombre_ingrediente` varchar(100) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `unidad` enum('kg','litros','unidades') NOT NULL,
  `umbral_minimo` decimal(10,2) NOT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `fecha_ultima_reposicion` date DEFAULT NULL,
  `desperdicio_cantidad` decimal(10,2) DEFAULT 0.00,
  `desperdicio_causa` enum('sobreproduccion','caducidad','error_preparacion','otros') DEFAULT NULL,
  `usuario_reporta_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id`, `nombre_ingrediente`, `cantidad`, `unidad`, `umbral_minimo`, `proveedor`, `fecha_ultima_reposicion`, `desperdicio_cantidad`, `desperdicio_causa`, `usuario_reporta_id`) VALUES
(1, 'Aguacate Hass', 25.00, 'kg', 5.00, 'Proveedor Frutas Verdes S.A.', '2025-05-20', 0.00, NULL, 8),
(2, 'Tomate Saladette', 40.00, 'kg', 10.00, 'Agrícola El Campo', '2025-05-15', 0.00, NULL, 8),
(3, 'Cebolla Morada', 30.00, 'kg', 8.00, 'Verduras La Huerta', '2025-05-18', 0.00, NULL, 9),
(4, 'Cilantro', 10.00, 'kg', 2.00, 'Herbales Frescos S.A.', '2025-05-19', 0.00, NULL, 9),
(5, 'Limón Verde', 50.00, 'kg', 12.00, 'Cítricos del Bajío', '2025-05-10', 0.00, NULL, 8),
(6, 'Tortilla de Maíz', 200.00, 'unidades', 50.00, 'Tortillas Don José', '2025-05-22', 0.00, NULL, 9),
(7, 'Queso Oaxaca', 15.00, 'kg', 5.00, 'Lácteos La Pradera', '2025-05-20', 0.00, NULL, 8),
(8, 'Chorizo Artesanal', 10.00, 'kg', 3.00, 'Carnes Selectas S.A.', '2025-05-18', 0.00, NULL, 9),
(9, 'Calamar Fresco', 8.00, 'kg', 2.00, 'Mariscos del Pacífico', '2025-05-16', 0.00, NULL, 8),
(10, 'Harina de Trigo', 50.00, 'kg', 15.00, 'Molinos Modernos', '2025-05-12', 0.00, NULL, 9),
(11, 'Setas Mixtas', 12.00, 'kg', 4.00, 'Hongos Gourmet S.A.', '2025-05-17', 0.00, NULL, 8),
(12, 'Pan Brioche', 100.00, 'unidades', 30.00, 'Panadería Tradicional', '2025-05-23', 0.00, NULL, 9),
(13, 'Pasta Fettuccine', 25.00, 'kg', 6.00, 'Pastas del Valle', '2025-05-15', 0.00, NULL, 8),
(14, 'Pechuga de Pollo', 20.00, 'kg', 5.00, 'Avícola La Granja', '2025-05-19', 0.00, NULL, 9),
(15, 'Arroz Arborio', 10.00, 'kg', 3.00, 'Granos Selectos', '2025-05-14', 0.00, NULL, 8),
(16, 'Chocolate Semiamargo', 5.00, 'kg', 1.00, 'Cacao Real S.A.', '2025-05-11', 0.00, NULL, 9),
(17, 'Leche Entera', 30.00, 'litros', 10.00, 'Lácteos La Pradera', '2025-05-18', 0.00, NULL, 8),
(18, 'Crema Espesa', 15.00, 'litros', 5.00, 'Lácteos El Valle', '2025-05-20', 0.00, NULL, 9),
(19, 'Ron Blanco', 10.00, 'litros', 3.00, 'Destilería Caribeña', '2025-05-05', 0.00, NULL, 8),
(20, 'Jugo de Piña', 20.00, 'litros', 8.00, 'Jugos Tropicales S.A.', '2025-05-10', 0.00, NULL, 9),
(21, 'Hojas de Hierbabuena', 5.00, 'kg', 1.00, 'Herbales Frescos S.A.', '2025-05-19', 0.00, NULL, 8),
(22, 'Cerveza IPA (Lúpulo)', 50.00, 'litros', 15.00, 'Cervecería Artesanal La Cumbre', '2025-05-08', 0.00, NULL, 9),
(23, 'Uvas Tinto', 25.00, 'kg', 10.00, 'Viñedos del Valle', '2025-05-02', 0.00, NULL, 8),
(24, 'Agua Mineral', 100.00, 'litros', 50.00, 'Aguas Cristalinas S.A.', '2025-05-22', 0.00, NULL, 9),
(25, 'Mantequilla', 10.00, 'kg', 2.00, 'Lácteos La Pradera', '2025-05-21', 0.00, NULL, 8);

--
-- Disparadores `inventario`
--
DELIMITER $$
CREATE TRIGGER `trigger_stock_bajo` AFTER UPDATE ON `inventario` FOR EACH ROW BEGIN
    IF NEW.cantidad < NEW.umbral_minimo THEN
        INSERT INTO mantenimiento_alertas (tipo, descripcion, usuario_asignado_id)
        VALUES (
            'stock_bajo',
            CONCAT('Stock crítico: ', NEW.nombre_ingrediente, ' (', NEW.cantidad, ' ', NEW.unidad, ')'),
            (SELECT id FROM usuarios WHERE rol = 'gerente' LIMIT 1)
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimiento_alertas`
--

CREATE TABLE `mantenimiento_alertas` (
  `id` int(11) NOT NULL,
  `tipo` enum('stock_bajo','desperdicio_alto','fallo_equipo','no_show') NOT NULL,
  `equipo` varchar(100) DEFAULT NULL,
  `descripcion` text NOT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `resuelta` tinyint(1) DEFAULT 0,
  `usuario_asignado_id` int(11) DEFAULT NULL,
  `temperatura` decimal(5,2) DEFAULT NULL,
  `presion` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` enum('entrada','plato_fuerte','postre','bebida') NOT NULL,
  `precio_local` decimal(10,2) NOT NULL,
  `precio_entrega` decimal(10,2) DEFAULT NULL,
  `precio_takeaway` decimal(10,2) DEFAULT NULL,
  `alergenos` varchar(255) DEFAULT NULL,
  `food_cost` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `menu`
--

INSERT INTO `menu` (`id`, `nombre`, `descripcion`, `categoria`, `precio_local`, `precio_entrega`, `precio_takeaway`, `alergenos`, `food_cost`, `activo`) VALUES
(1, 'Guacamole con Totopos', 'Guacamole fresco preparado con aguacates mexicanos, tomate, cebolla y cilantro. Servido con totopos caseros.', 'entrada', 120.00, 130.00, 125.00, 'aguacate, cilantro, cebolla', 35.00, 1),
(2, 'Queso Fundido con Chorizo', 'Queso Oaxaca derretido con chorizo artesanal, servido con tortillas de harina calientes.', 'entrada', 150.00, 155.00, 150.00, 'lácteos, cerdo', 50.00, 1),
(3, 'Tostadas de Tinga de Pollo', 'Tinga de pollo en salsa de chipotle sobre tostada crujiente, con crema y queso fresco.', 'entrada', 90.00, 95.00, 92.00, 'pollo, lacteos', 28.00, 1),
(4, 'Calamares Fritos', 'Aros de calamar empanizados en panko, acompañados de alioli de limón.', 'entrada', 140.00, 150.00, 145.00, 'mariscos, gluten, huevo', 45.00, 1),
(5, 'Bruschettas Mediterráneas', 'Pan artesanal tostado con tomate, albahaca fresca, oliva y queso parmesano.', 'entrada', 110.00, 115.00, 112.00, 'gluten, lácteos', 30.00, 1),
(6, 'Tacos al Pastor (3 piezas)', 'Tres tacos suaves de maíz rellenos de pastor marinado, piña y cebolla, con salsa verde.', 'plato_fuerte', 95.00, 105.00, 100.00, 'cerdo, cilantro', 25.00, 1),
(7, 'Enchiladas Verdes de Pollo', 'Tres enchiladas de pollo desmenuzado bañadas en salsa verde, crema y queso fresco.', 'plato_fuerte', 130.00, 140.00, 135.00, 'pollo, lácteos, gluten', 40.00, 1),
(8, 'Ribeye a la Parrilla', 'Ribeye de 250g sellado a la parrilla, servido con papas gajo y vegetales al vapor.', 'plato_fuerte', 280.00, 290.00, 285.00, 'ninguno', 120.00, 1),
(9, 'Filete de Salmón con Salsa de Maracuyá', 'Filete de salmón fresco a la plancha con salsa de maracuyá, acompañado de arroz y ensalada verde.', 'plato_fuerte', 260.00, 270.00, 265.00, 'pescado', 110.00, 1),
(10, 'Risotto de Champiñones', 'Risotto cremoso con setas variadas y queso parmesano, terminado con aceite de trufa.', 'plato_fuerte', 200.00, 210.00, 205.00, 'lácteos, gluten', 80.00, 1),
(11, 'Hamburguesa Clásica', 'Pan brioche, 180g de carne de res, queso cheddar, lechuga, tomate, cebolla y pepinillos. Acompañada de papas fritas.', 'plato_fuerte', 170.00, 180.00, 175.00, 'lácteos, gluten', 65.00, 1),
(12, 'Pasta Alfredo con Camarones', 'Pasta fettuccine en salsa Alfredo casera con camarones salteados y perejil fresco.', 'plato_fuerte', 190.00, 200.00, 195.00, 'lácteos, mariscos, gluten', 75.00, 1),
(13, 'Ensalada César con Pollo', 'Lechuga romana, crutones, pollo a la parrilla, queso parmesano y aderezo César.', 'plato_fuerte', 145.00, 150.00, 147.00, 'lácteos, gluten', 50.00, 1),
(14, 'Brownie con Helado de Vainilla', 'Brownie de chocolate caliente servido con bola de helado de vainilla y nueces caramelizadas.', 'postre', 100.00, 105.00, 102.00, 'gluten, lacteos, frutos secos', 35.00, 1),
(15, 'Cheesecake de Frutos Rojos', 'Tarta de queso cremoso con base de galleta, cubierta con salsa de frutos rojos.', 'postre', 110.00, 115.00, 112.00, 'lácteos, gluten', 40.00, 1),
(16, 'Churros con Chocolate', 'Churros calientes espolvoreados con azúcar y canela, acompañados de salsa de chocolate.', 'postre', 90.00, 95.00, 92.00, 'gluten, lacteos', 30.00, 1),
(17, 'Crepa de Nutella y Plátano', 'Crepa francesa rellena de Nutella y rodajas de plátano, decorada con azúcar glass.', 'postre', 95.00, 100.00, 97.00, 'gluten, lacteos', 32.00, 1),
(18, 'Mojito Clásico', 'Ron blanco, hojas de hierbabuena, azúcar, limón y soda. Servido con hielo.', 'bebida', 120.00, NULL, 120.00, 'menta', 45.00, 1),
(19, 'Piña Colada', 'Ron blanco, crema de coco, jugo de piña y hielo. Decorada con rodaja de piña.', 'bebida', 130.00, NULL, 130.00, 'coco', 50.00, 1),
(20, 'Cerveza Artesanal (500ml)', 'Cerveza artesanal elaborada en sitio, variedad IPA o Lager según disponibilidad.', 'bebida', 80.00, NULL, 80.00, 'cebada', 25.00, 1),
(21, 'Vino Tinto Reserva (Botella)', 'Vino tinto reserva de la casa (750ml), notas a frutos rojos y roble.', 'bebida', 450.00, NULL, 450.00, 'uva', 200.00, 1),
(22, 'Agua Mineral (500ml)', 'Agua mineral con gas en botella de 500ml.', 'bebida', 50.00, NULL, 50.00, NULL, 10.00, 1),
(23, 'Guacamole con Totopos', 'Guacamole fresco preparado con aguacates mexicanos, tomate, cebolla y cilantro. Servido con totopos caseros.', 'entrada', 120.00, 130.00, 125.00, 'aguacate, cilantro, cebolla', 35.00, 1),
(24, 'Queso Fundido con Chorizo', 'Queso Oaxaca derretido con chorizo artesanal, servido con tortillas de harina calientes.', 'entrada', 150.00, 155.00, 150.00, 'lácteos, cerdo', 50.00, 1),
(25, 'Tostadas de Tinga de Pollo', 'Tinga de pollo en salsa de chipotle sobre tostada crujiente, con crema y queso fresco.', 'entrada', 90.00, 95.00, 92.00, 'pollo, lacteos', 28.00, 1),
(26, 'Calamares Fritos', 'Aros de calamar empanizados en panko, acompañados de alioli de limón.', 'entrada', 140.00, 150.00, 145.00, 'mariscos, gluten, huevo', 45.00, 1),
(27, 'Bruschettas Mediterráneas', 'Pan artesanal tostado con tomate, albahaca fresca, oliva y queso parmesano.', 'entrada', 110.00, 115.00, 112.00, 'gluten, lácteos', 30.00, 1),
(28, 'Tacos al Pastor (3 piezas)', 'Tres tacos suaves de maíz rellenos de pastor marinado, piña y cebolla, con salsa verde.', 'plato_fuerte', 95.00, 105.00, 100.00, 'cerdo, cilantro', 25.00, 1),
(29, 'Enchiladas Verdes de Pollo', 'Tres enchiladas de pollo desmenuzado bañadas en salsa verde, crema y queso fresco.', 'plato_fuerte', 130.00, 140.00, 135.00, 'pollo, lácteos, gluten', 40.00, 0),
(30, 'Ribeye a la Parrilla', 'Ribeye de 250g sellado a la parrilla, servido con papas gajo y vegetales al vapor.', 'plato_fuerte', 280.00, 290.00, 285.00, 'ninguno', 120.00, 1),
(31, 'Filete de Salmón con Salsa de Maracuyá', 'Filete de salmón fresco a la plancha con salsa de maracuyá, acompañado de arroz y ensalada verde.', 'plato_fuerte', 260.00, 270.00, 265.00, 'pescado', 110.00, 1),
(32, 'Risotto de Champiñones', 'Risotto cremoso con setas variadas y queso parmesano, terminado con aceite de trufa.', 'plato_fuerte', 200.00, 210.00, 205.00, 'lácteos, gluten', 80.00, 1),
(33, 'Hamburguesa Clásica', 'Pan brioche, 180g de carne de res, queso cheddar, lechuga, tomate, cebolla y pepinillos. Acompañada de papas fritas.', 'plato_fuerte', 170.00, 180.00, 175.00, 'lácteos, gluten', 65.00, 1),
(34, 'Pasta Alfredo con Camarones', 'Pasta fettuccine en salsa Alfredo casera con camarones salteados y perejil fresco.', 'plato_fuerte', 190.00, 200.00, 195.00, 'lácteos, mariscos, gluten', 75.00, 1),
(35, 'Ensalada César con Pollo', 'Lechuga romana, crutones, pollo a la parrilla, queso parmesano y aderezo César.', 'plato_fuerte', 145.00, 150.00, 147.00, 'lácteos, gluten', 50.00, 1),
(36, 'Brownie con Helado de Vainilla', 'Brownie de chocolate caliente servido con bola de helado de vainilla y nueces caramelizadas.', 'postre', 100.00, 105.00, 102.00, 'gluten, lacteos, frutos secos', 35.00, 1),
(37, 'Cheesecake de Frutos Rojos', 'Tarta de queso cremoso con base de galleta, cubierta con salsa de frutos rojos.', 'postre', 110.00, 115.00, 112.00, 'lácteos, gluten', 40.00, 1),
(38, 'Churros con Chocolate', 'Churros calientes espolvoreados con azúcar y canela, acompañados de salsa de chocolate.', 'postre', 90.00, 95.00, 92.00, 'gluten, lacteos', 30.00, 1),
(39, 'Crepa de Nutella y Plátano', 'Crepa francesa rellena de Nutella y rodajas de plátano, decorada con azúcar glass.', 'postre', 95.00, 100.00, 97.00, 'gluten, lacteos', 32.00, 1),
(40, 'Mojito Clásico', 'Ron blanco, hojas de hierbabuena, azúcar, limón y soda. Servido con hielo.', 'bebida', 120.00, NULL, 120.00, 'menta', 45.00, 1),
(41, 'Piña Colada', 'Ron blanco, crema de coco, jugo de piña y hielo. Decorada con rodaja de piña.', 'bebida', 130.00, NULL, 130.00, 'coco', 50.00, 1),
(42, 'Cerveza Artesanal (500ml)', 'Cerveza artesanal elaborada en sitio, variedad IPA o Lager según disponibilidad.', 'bebida', 80.00, NULL, 80.00, 'cebada', 25.00, 1),
(43, 'Vino Tinto Reserva (Botella)', 'Vino tinto reserva de la casa (750ml), notas a frutos rojos y roble.', 'bebida', 450.00, NULL, 450.00, 'uva', 200.00, 1),
(44, 'Agua Mineral (500ml)', 'Agua mineral con gas en botella de 500ml.', 'bebida', 50.00, NULL, 50.00, NULL, 10.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `capacidad` int(11) NOT NULL,
  `estado` enum('libre','ocupada','reservada') DEFAULT 'libre',
  `ubicacion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id`, `numero`, `capacidad`, `estado`, `ubicacion`) VALUES
(1, 1, 4, 'libre', 'ventana'),
(2, 2, 6, 'reservada', 'terraza'),
(3, 3, 2, 'libre', 'interior'),
(4, 4, 4, 'ocupada', 'interior'),
(5, 5, 4, 'ocupada', 'terraza'),
(6, 6, 4, 'libre', 'ventana'),
(7, 7, 4, 'libre', 'interior'),
(8, 8, 6, 'ocupada', 'ventana'),
(9, 9, 4, 'libre', 'terraza'),
(10, 10, 2, 'libre', 'interior');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes`
--

CREATE TABLE `ordenes` (
  `id` int(11) NOT NULL,
  `mesa_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `canal` enum('local','entrega','takeaway') NOT NULL,
  `estado` enum('pendiente','en_proceso','completada','cancelada') DEFAULT 'pendiente',
  `total` decimal(10,2) NOT NULL,
  `fecha_hora_inicio` datetime DEFAULT current_timestamp(),
  `fecha_hora_finalizacion` datetime DEFAULT NULL,
  `tiempo_estimado_preparacion` int(11) DEFAULT NULL,
  `tiempo_real_preparacion` int(11) DEFAULT NULL,
  `tiempo_estimado_entrega` int(11) DEFAULT NULL,
  `tiempo_real_entrega` int(11) DEFAULT NULL,
  `calidad_revision` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ordenes`
--

INSERT INTO `ordenes` (`id`, `mesa_id`, `usuario_id`, `canal`, `estado`, `total`, `fecha_hora_inicio`, `fecha_hora_finalizacion`, `tiempo_estimado_preparacion`, `tiempo_real_preparacion`, `tiempo_estimado_entrega`, `tiempo_real_entrega`, `calidad_revision`) VALUES
(1, 4, 3, 'local', 'completada', 385.00, '2025-05-20 13:15:00', '2025-05-20 13:45:00', 30, 28, NULL, NULL, 1),
(2, NULL, 4, 'entrega', 'completada', 250.00, '2025-05-20 13:30:00', NULL, NULL, NULL, 45, 50, 0),
(3, NULL, 5, 'takeaway', 'pendiente', 180.00, '2025-05-20 14:00:00', NULL, NULL, NULL, NULL, NULL, 0),
(4, 8, 6, 'local', 'completada', 520.00, '2025-05-20 14:20:00', '2025-05-20 15:10:00', 50, 55, NULL, NULL, 1),
(5, 5, 3, 'local', 'en_proceso', 305.00, '2025-05-20 14:45:00', NULL, 35, NULL, NULL, NULL, 0),
(6, NULL, 4, 'entrega', 'pendiente', 415.00, '2025-05-20 15:10:00', NULL, NULL, NULL, 60, NULL, 0),
(7, 1, 5, 'local', 'completada', 260.00, '2025-05-19 18:05:00', '2025-05-19 18:35:00', 30, 32, NULL, NULL, 1),
(8, 3, 6, 'local', 'completada', 150.00, '2025-05-19 19:00:00', '2025-05-19 19:25:00', 25, 23, NULL, NULL, 1),
(9, NULL, 5, 'takeaway', 'completada', 340.00, '2025-05-19 19:30:00', '2025-05-19 20:00:00', NULL, NULL, NULL, NULL, 1),
(10, NULL, 4, 'entrega', 'cancelada', 0.00, '2025-05-19 20:15:00', '2025-05-19 20:20:00', NULL, NULL, 20, NULL, 0),
(11, 4, 3, 'local', 'completada', 385.00, '2025-05-20 13:15:00', '2025-05-20 13:45:00', 30, 28, NULL, NULL, 1),
(12, NULL, 4, 'takeaway', 'cancelada', 250.00, '2025-05-20 13:30:00', NULL, NULL, NULL, 45, 50, 0),
(13, NULL, 5, 'takeaway', 'pendiente', 180.00, '2025-05-20 14:00:00', NULL, NULL, NULL, NULL, NULL, 0),
(14, 8, 6, 'local', 'completada', 520.00, '2025-05-20 14:20:00', '2025-05-20 15:10:00', 50, 55, NULL, NULL, 1),
(15, 5, 3, 'local', 'en_proceso', 305.00, '2025-05-20 14:45:00', NULL, 35, NULL, NULL, NULL, 0),
(16, NULL, 4, 'entrega', 'pendiente', 415.00, '2025-05-20 15:10:00', NULL, NULL, NULL, 60, NULL, 0),
(17, 1, 5, 'local', 'completada', 260.00, '2025-05-19 18:05:00', '2025-05-19 18:35:00', 30, 32, NULL, NULL, 1),
(18, 3, 6, 'local', 'completada', 150.00, '2025-05-19 19:00:00', '2025-05-19 19:25:00', 25, 23, NULL, NULL, 1),
(19, NULL, 5, 'takeaway', 'completada', 340.00, '2025-05-19 19:30:00', '2025-05-19 20:00:00', NULL, NULL, NULL, NULL, 1),
(20, NULL, 4, 'entrega', 'cancelada', 0.00, '2025-05-19 20:15:00', '2025-05-19 20:20:00', NULL, NULL, 20, NULL, 0);

--
-- Disparadores `ordenes`
--
DELIMITER $$
CREATE TRIGGER `trigger_exceso_tiempo` AFTER UPDATE ON `ordenes` FOR EACH ROW BEGIN
    IF NEW.tiempo_real_preparacion > NEW.tiempo_estimado_preparacion THEN
        INSERT INTO mantenimiento_alertas (tipo, descripcion, usuario_asignado_id)
        VALUES (
            'tiempo_excedido',
            CONCAT('Orden #', NEW.id, ': Tiempo de preparación excedido'),
            NEW.usuario_id
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contrasena_hash` varchar(255) NOT NULL,
  `rol` enum('gerente','mesero','cocina','mantenimiento') NOT NULL,
  `rfid_token` varchar(50) DEFAULT NULL,
  `puntos_lealtad` int(11) DEFAULT 0,
  `tasa_upselling` decimal(5,2) DEFAULT 0.00,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `contrasena_hash`, `rol`, `rfid_token`, `puntos_lealtad`, `tasa_upselling`, `fecha_registro`) VALUES
(3, 'Usuario3', 'usuario3@restaurante.com', 'hash3', 'mesero', NULL, 0, 0.00, '2025-05-14 01:51:00'),
(4, 'Usuario4', 'usuario4@restaurante.com', 'hash4', 'mesero', NULL, 0, 0.00, '2025-05-14 01:51:00'),
(5, 'Usuario5', 'usuario5@restaurante.com', 'hash5', 'mesero', NULL, 0, 0.00, '2025-05-14 01:51:00'),
(6, 'Usuario6', 'usuario6@restaurante.com', 'hash6', 'mesero', NULL, 0, 0.00, '2025-05-14 01:51:00'),
(8, 'Usuario8', 'usuario8@restaurante.com', 'hash8', 'gerente', NULL, 0, 0.00, '2025-05-14 01:51:00'),
(9, 'Usuario9', 'usuario9@restaurante.com', 'hash9', 'mesero', NULL, 0, 0.00, '2025-05-14 01:51:00'),
(11, 'JorgePrueba1', 'hola@hola.com', '$2y$10$mUyFZAeR8fmm0mGv1MBMqOI/RbnLdTBJYuPmc91W5QalORsYzAsJi', 'gerente', NULL, 0, 0.00, '2025-05-25 19:48:44'),
(12, 'mesero1', 'mesero@mesero.com', '$2y$10$rspF4CzKT9mPKnWEmJNGce9nqLNlChS0CL1yl1/6GWmr6387tYAn6', 'mesero', NULL, 0, 0.00, '2025-05-25 22:04:02'),
(13, 'cocina1', 'coci@coci.com', '$2y$10$CFqb5v9oCoQx.OP/CCgCier740rApPbMcIkLmkT2ukp2pT8j3vICa', 'cocina', NULL, 0, 0.00, '2025-05-26 00:58:43');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orden_id` (`orden_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indices de la tabla `eventos_simulaciones`
--
ALTER TABLE `eventos_simulaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_eventos_fecha` (`fecha`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_reporta_id` (`usuario_reporta_id`),
  ADD KEY `idx_inventario_nombre` (`nombre_ingrediente`);

--
-- Indices de la tabla `mantenimiento_alertas`
--
ALTER TABLE `mantenimiento_alertas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_asignado_id` (`usuario_asignado_id`);

--
-- Indices de la tabla `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_menu_categoria` (`categoria`);

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`);

--
-- Indices de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mesa_id` (`mesa_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_ordenes_estado` (`estado`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `rfid_token` (`rfid_token`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `eventos_simulaciones`
--
ALTER TABLE `eventos_simulaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `mantenimiento_alertas`
--
ALTER TABLE `mantenimiento_alertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD CONSTRAINT `detalle_orden_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `ordenes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_orden_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`);

--
-- Filtros para la tabla `eventos_simulaciones`
--
ALTER TABLE `eventos_simulaciones`
  ADD CONSTRAINT `eventos_simulaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`usuario_reporta_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `mantenimiento_alertas`
--
ALTER TABLE `mantenimiento_alertas`
  ADD CONSTRAINT `mantenimiento_alertas_ibfk_1` FOREIGN KEY (`usuario_asignado_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD CONSTRAINT `ordenes_ibfk_1` FOREIGN KEY (`mesa_id`) REFERENCES `mesas` (`id`),
  ADD CONSTRAINT `ordenes_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
