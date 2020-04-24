-- Deshabilitar verificación de llaves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- Insert countries
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('18', '5', 'Austria', 'at', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('39', '5', 'Belgium', 'be', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('35', '5', 'Bulgaria', 'bg', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('56', '5', 'Croatia', 'hr', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('48', '5', 'Cyprus', 'cy', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('185', '5', 'Czechia', 'cz', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('58', '5', 'Denmark', 'dk', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('72', '5', 'Estonia', 'ee', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
UPDATE `i_countries` SET `deleted` = '0' WHERE `i_countries`.`id` = 76;
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('77', '5', 'France', 'fr', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('87', '5', 'Greece', 'gr', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
UPDATE `i_countries` SET `deleted` = '0' WHERE `i_countries`.`id` = 101;
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('105', '5', 'Ireland', 'ie', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('120', '5', 'Italy', 'it', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('130', '5', 'Latvia', 'lv', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('135', '5', 'Lithuania', 'lt', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('136', '5', 'Luxembourg', 'lu', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('144', '5', 'Malta', 'mt', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('98', '5', 'Netherlands', 'nl', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('179', '5', 'Poland', 'pl', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('180', '5', 'Portugal', 'pt', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('190', '5', 'Romania', 'ro', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('68', '5', 'Slovakia', 'sk', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('69', '5', 'Slovenia', 'si', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('70', '5', 'Spain', 'es', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
UPDATE `i_countries` SET `deleted` = '0' WHERE `i_countries`.`id` = 211;
UPDATE `i_countries` SET `deleted` = '1' WHERE `i_countries`.`id` = 156;

-- Insert regions
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('50', '18', 'Austria', 'Austria', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('51', '39', 'Belgium', 'Belgium', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('52', '35', 'Bulgaria', 'Bulgaria', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('53', '56', 'Croatia', 'Croatia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('54', '48', 'Cyprus', 'Cyprus', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('55', '185', 'Czechia', 'Czechia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('56', '58', 'Denmark', 'Denmark', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('57', '72', 'Estonia', 'Estonia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('58', '77', 'France', 'France', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('59', '87', 'Greece', 'Greece', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('60', '105', 'Ireland', 'Ireland', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('61', '120', 'Italy', 'Italy', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('62', '130', 'Latvia', 'Latvia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('63', '135', 'Lithuania', 'Lithuania', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('64', '136', 'Luxembourg', 'Luxembourg', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('65', '144', 'Malta', 'Malta', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('66', '98', 'Netherlands', 'Netherlands', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('67', '179', 'Poland', 'Poland', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('68', '180', 'Portugal', 'Portugal', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('69', '190', 'Romania', 'Romania', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('70', '68', 'Slovakia', 'Slovakia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('71', '69', 'Slovenia', 'Slovenia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('72', '70', 'Spain', 'Spain', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);

-- Insert territories
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('95', '50', 'Austria', 'Austria', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('96', '51', 'Belgium', 'Belgium', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('97', '52', 'Bulgaria', 'Bulgaria', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('98', '53', 'Croatia', 'Croatia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('99', '54', 'Cyprus', 'Cyprus', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('100', '55', 'Czechia', 'Czechia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('101', '56', 'Denmark', 'Denmark', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('102', '57', 'Estonia', 'Estonia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('103', '58', 'France', 'France', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('104', '59', 'Greece', 'Greece', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('105', '60', 'Ireland', 'Ireland', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('106', '61', 'Italy', 'Italy', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('107', '62', 'Latvia', 'Latvia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('108', '63', 'Lithuania', 'Lithuania', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('109', '64', 'Luxembourg', 'Luxembourg', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('110', '65', 'Malta', 'Malta', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('111', '66', 'Netherlands', 'Netherlands', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('112', '67', 'Poland', 'Poland', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('113', '68', 'Portugal', 'Portugal', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('114', '69', 'Romania', 'Romania', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('115', '70', 'Slovakia', 'Slovakia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('116', '71', 'Slovenia', 'Slovenia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('117', '72', 'Spain', 'Spain', CURRENT_TIMESTAMP);

-- Update names of territories
UPDATE `i_territories` SET `name` = 'Hungary' WHERE `i_territories`.`id` = 59;
UPDATE `i_territories` SET `name` = 'Sweden' WHERE `i_territories`.`id` = 62;
UPDATE `i_territories` SET `name` = 'Finland' WHERE `i_territories`.`id` = 63;

-- Insert territory companies
INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Finland', '76', '60', 'admin', 'FINLAND', '5', '76', '21', '63');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Hungary', '101', '60', 'admin', 'HUNGARY', '5', '101', '17', '59');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Sweden', '211', '60', 'admin', 'SWEDEN', '5', '211', '20', '62');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Austria', '18', '60', 'admin', 'Austria', '5', '18', '50', '95');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Belgium', '39', '60', 'admin', 'Belgium', '5', '39', '51', '96');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Bulgaria', '35', '60', 'admin', 'Bulgaria', '5', '35', '52', '97');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Croatia', '56', '60', 'admin', 'Croatia', '5', '56', '53', '98');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Cyprus', '48', '60', 'admin', 'Cyprus', '5', '48', '54', '99');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Czechia', '185', '60', 'admin', 'Czechia', '5', '185', '55', '100');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Denmark', '58', '60', 'admin', 'Denmark', '5', '58', '56', '101');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Estonia', '72', '60', 'admin', 'Estonia', '5', '72', '57', '102');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech France', '77', '60', 'admin', 'France', '5', '77', '58', '103');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Greece', '87', '60', 'admin', 'Greece', '5', '87', '59', '104');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Ireland', '105', '60', 'admin', 'Ireland', '5', '105', '60', '105');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Italy', '120', '60', 'admin', 'Italy', '5', '120', '61', '106');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Latvia', '130', '60', 'admin', 'Latvia', '5', '130', '62', '107');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Lithuania', '135', '60', 'admin', 'Lithuania', '5', '135', '63', '108');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Luxembourg', '136', '60', 'admin', 'Luxembourg', '5', '136', '64', '109');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Malta', '144', '60', 'admin', 'Malta', '5', '144', '65', '110');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Netherlands', '98', '60', 'admin', 'Netherlands', '5', '98', '66', '111');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Poland', '179', '60', 'admin', 'Poland', '5', '179', '67', '112');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Portugal', '180', '60', 'admin', 'Portugal', '5', '180', '68', '113');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Romania', '190', '60', 'admin', 'Romania', '5', '190', '69', '114');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Slovakia', '68', '60', 'admin', 'Slovakia', '5', '68', '70', '115');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Slovenia', '69', '60', 'admin', 'Slovenia', '5', '69', '71', '116');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Spain', '70', '60', 'admin', 'Spain', '5', '70', '72', '117');