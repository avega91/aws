ALTER TABLE `folder_apps` ADD `buoy_system_id` BIGINT(20) NOT NULL AFTER `folder_id`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Estructura de tabla para la tabla `asset_metadata`
--

CREATE TABLE `asset_metadata` (
  `id` bigint(20) NOT NULL,
  `folder_app_id` bigint(20) NOT NULL,
  `unique_id_tag` varchar(50) DEFAULT NULL,
  `manufacturer` text,
  `weight` decimal(10,3) DEFAULT NULL,
  `material` text,
  `reference` text,
  `size_diam` decimal(10,3) DEFAULT NULL,
  `size_len` decimal(10,3) DEFAULT NULL,
  `design_code` text,
  `design_pressure` decimal(10,3) DEFAULT NULL,
  `design_temp` decimal(10,3) DEFAULT NULL,
  `flange_class` text,
  `number_paths` int(11) DEFAULT NULL,
  `type` text,
  `mbl` decimal(10,3) DEFAULT NULL,
  `swl` decimal(10,3) DEFAULT NULL,
  `description` text,
  `service` text,
  `comments` text,
  `delivery_date` varchar(10) DEFAULT '00/0000',
  `end_life` varchar(10) DEFAULT '00/0000',
  `status` varchar(30) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asset_metadata`
--
ALTER TABLE `asset_metadata`
  ADD PRIMARY KEY (`id`),
  ADD KEY `metadata_asset_id_fk` (`folder_app_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asset_metadata`
--
ALTER TABLE `asset_metadata`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asset_metadata`
--
ALTER TABLE `asset_metadata`
  ADD CONSTRAINT `asset_metadata_ibfk_1` FOREIGN KEY (`folder_app_id`) REFERENCES `folder_apps` (`id`);
COMMIT;