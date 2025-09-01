-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 17-06-2025 a las 18:07:01
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
-- Base de datos: `sistema_guardias`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones_guardia`
--

CREATE TABLE `asignaciones_guardia` (
  `id_asignacion` int(11) NOT NULL,
  `id_personal` int(11) NOT NULL,
  `id_guardia` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `turno` enum('12h','24h') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignaciones_guardia`
--

INSERT INTO `asignaciones_guardia` (`id_asignacion`, `id_personal`, `id_guardia`, `id_rol`, `turno`) VALUES
(14, 3, 6, 1, '12h'),
(15, 1, 7, 1, '12h'),
(16, 12, 7, 7, '24h'),
(17, 14, 7, 2, '12h'),
(18, 14, 8, 4, '12h');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `guardias`
--

CREATE TABLE `guardias` (
  `id_guardia` int(11) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `guardias`
--

INSERT INTO `guardias` (`id_guardia`, `fecha`) VALUES
(6, '2025-06-03'),
(7, '2025-06-04'),
(8, '2025-06-10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `novedades`
--

CREATE TABLE `novedades` (
  `id_novedad` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `area` enum('Personal','Inteligencia','Seguridad','Operaciones','Adiestramiento','Logistica','Informacion general') NOT NULL,
  `id_guardia` int(11) NOT NULL,
  `id_personal_reporta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `novedades`
--

INSERT INTO `novedades` (`id_novedad`, `descripcion`, `fecha_registro`, `area`, `id_guardia`, `id_personal_reporta`) VALUES
(11, 'ahkdsdkjadhsakjdada', '2025-06-04 15:10:00', 'Inteligencia', 7, 14),
(12, 'dsadasdasdasdasdd', '2025-06-04 14:10:00', 'Inteligencia', 7, 14),
(14, 'Información general', '2025-06-10 17:21:00', 'Informacion general', 8, 14);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_salida`
--

CREATE TABLE `ordenes_salida` (
  `id_orden` int(11) NOT NULL,
  `destino` varchar(100) NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `fecha_salida` datetime NOT NULL,
  `fecha_retorno` datetime DEFAULT NULL,
  `id_vehiculo` int(11) NOT NULL,
  `id_personal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ordenes_salida`
--

INSERT INTO `ordenes_salida` (`id_orden`, `destino`, `motivo`, `fecha_salida`, `fecha_retorno`, `id_vehiculo`, `id_personal`) VALUES
(3, 'caracas', 'comision', '2025-05-30 10:57:00', '2025-05-30 10:57:00', 16, 4),
(8, 'barquisimeto', 'comision', '2025-06-04 10:11:00', NULL, 16, 3),
(9, 'barquisimeto', 'comision', '2025-06-10 13:22:00', NULL, 16, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `id_personal` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `grado` varchar(20) NOT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`id_personal`, `nombre`, `apellido`, `grado`, `estado`) VALUES
(1, 'Oscar', 'Riera', 'TN', 1),
(2, 'Josue', 'Ventura', 'Sarg', 1),
(3, 'pedro', 'perez', 'TN', 1),
(4, 'carlos', 'garcia', 'cap', 1),
(6, 'jeliena', 'Ventura', 'TN', 1),
(7, 'Roger', 'Ruiz', 'TN', 1),
(10, 'jose', 'perez', 'TN', 1),
(11, 'yidith', 'Ventura', 'TN', 1),
(12, 'Victor Miguel', 'Garcia Ruiz', 'CN', 1),
(14, 'andrea', 'perez', 'TN', 1),
(15, 'andrea', 'terter', 'TN', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_guardia`
--

CREATE TABLE `roles_guardia` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles_guardia`
--

INSERT INTO `roles_guardia` (`id_rol`, `nombre_rol`) VALUES
(1, 'Oficial jefe de la guardia'),
(2, 'Oficial inspección edificio 1'),
(3, 'Recorrida edificio 1'),
(4, 'Recorrida edificio 2'),
(5, 'Servicios generales'),
(6, 'Chófer de guardia'),
(7, 'Médico de guardia'),
(8, 'Emergencia'),
(9, 'Hospitalización'),
(10, 'Guardia área de laboratorio'),
(11, 'Guardia área de radiología'),
(12, 'Guardia área de farmacia'),
(13, 'Camarera');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id_servicio` int(11) NOT NULL,
  `tipo` enum('agua','combustible') NOT NULL,
  `medida` decimal(10,2) NOT NULL,
  `unidad` enum('litros','porcentaje') DEFAULT 'litros',
  `observaciones` text DEFAULT NULL,
  `responsable` int(11) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id_servicio`, `tipo`, `medida`, `unidad`, `observaciones`, `responsable`, `fecha_registro`) VALUES
(5, 'agua', 2000.00, 'litros', 'llenado', 1, '2025-06-03 10:14:03'),
(6, 'combustible', 10.00, 'litros', 'llenado', 1, '2025-06-03 10:14:03'),
(7, 'agua', 1000.00, 'litros', 'llenado', 12, '2025-06-10 13:23:02'),
(8, 'agua', 20.00, 'litros', 'llenado', 12, '2025-06-10 13:23:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('admin','personal') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `usuario`, `contrasena`, `rol`) VALUES
(1, 'admin', '$2y$10$341bTzcubA5L4vPE0GEiquI5UdwRoJ6YKYsQ/Z2Wh5ABXdrli7in2', 'admin'),
(2, 'comun', '$2a$10$tOHccK/KaMRbLX2JWadfLOxatu9qYJyqGTSPhJYSZOdSd/wucvnui', 'personal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id_vehiculo` int(11) NOT NULL,
  `placa` varchar(20) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `tipo` enum('ambulancia','administrativo') NOT NULL,
  `combustible` enum('lleno','3/4','medio','1/4','reserva','vacio') NOT NULL,
  `operativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id_vehiculo`, `placa`, `marca`, `tipo`, `combustible`, `operativo`) VALUES
(16, 'DJLSADJAKSDAD', 'toyota', 'ambulancia', 'lleno', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignaciones_guardia`
--
ALTER TABLE `asignaciones_guardia`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD UNIQUE KEY `unica_guardia_personal` (`id_guardia`,`id_personal`),
  ADD KEY `id_personal` (`id_personal`),
  ADD KEY `id_rol` (`id_rol`);

--
-- Indices de la tabla `guardias`
--
ALTER TABLE `guardias`
  ADD PRIMARY KEY (`id_guardia`);

--
-- Indices de la tabla `novedades`
--
ALTER TABLE `novedades`
  ADD PRIMARY KEY (`id_novedad`),
  ADD KEY `id_guardia` (`id_guardia`),
  ADD KEY `id_personal_reporta` (`id_personal_reporta`);

--
-- Indices de la tabla `ordenes_salida`
--
ALTER TABLE `ordenes_salida`
  ADD PRIMARY KEY (`id_orden`),
  ADD KEY `id_vehiculo` (`id_vehiculo`),
  ADD KEY `id_personal` (`id_personal`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id_personal`);

--
-- Indices de la tabla `roles_guardia`
--
ALTER TABLE `roles_guardia`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id_servicio`),
  ADD KEY `fk_servicio_responsable` (`responsable`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id_vehiculo`),
  ADD UNIQUE KEY `placa` (`placa`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignaciones_guardia`
--
ALTER TABLE `asignaciones_guardia`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `guardias`
--
ALTER TABLE `guardias`
  MODIFY `id_guardia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `novedades`
--
ALTER TABLE `novedades`
  MODIFY `id_novedad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `ordenes_salida`
--
ALTER TABLE `ordenes_salida`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id_personal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `roles_guardia`
--
ALTER TABLE `roles_guardia`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id_vehiculo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignaciones_guardia`
--
ALTER TABLE `asignaciones_guardia`
  ADD CONSTRAINT `asignaciones_guardia_ibfk_1` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`),
  ADD CONSTRAINT `asignaciones_guardia_ibfk_2` FOREIGN KEY (`id_guardia`) REFERENCES `guardias` (`id_guardia`),
  ADD CONSTRAINT `asignaciones_guardia_ibfk_3` FOREIGN KEY (`id_rol`) REFERENCES `roles_guardia` (`id_rol`);

--
-- Filtros para la tabla `novedades`
--
ALTER TABLE `novedades`
  ADD CONSTRAINT `novedades_ibfk_1` FOREIGN KEY (`id_guardia`) REFERENCES `guardias` (`id_guardia`),
  ADD CONSTRAINT `novedades_ibfk_2` FOREIGN KEY (`id_personal_reporta`) REFERENCES `personal` (`id_personal`);

--
-- Filtros para la tabla `ordenes_salida`
--
ALTER TABLE `ordenes_salida`
  ADD CONSTRAINT `ordenes_salida_ibfk_1` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`),
  ADD CONSTRAINT `ordenes_salida_ibfk_2` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`);

--
-- Filtros para la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD CONSTRAINT `fk_servicio_responsable` FOREIGN KEY (`responsable`) REFERENCES `personal` (`id_personal`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
