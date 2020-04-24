-- Deshabilitar verificación de llaves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- Insert countries
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('2', '5', 'Albania', 'al', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('5', '5', 'Andorra', 'ad', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('14', '5', 'Armenia', 'am', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('18', '5', 'Austria', 'at', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('19', '5', 'Azerbaijan', 'az', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('27', '5', 'Belarus', 'by', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('39', '5', 'Belgium', 'be', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('29', '5', 'Bosnia and Herzegovina', 'ba', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('35', '5', 'Bulgaria', 'bg', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('56', '5', 'Croatia', 'hr', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('48', '5', 'Cyprus', 'cy', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('185', '5', 'Czechia', 'cz', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('58', '5', 'Denmark', 'dk', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('72', '5', 'Estonia', 'ee', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
UPDATE `i_countries` SET `deleted` = '0' WHERE `i_countries`.`id` = 76;
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('77', '5', 'France', 'fr', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('83', '5', 'Georgia', 'ge', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('87', '5', 'Greece', 'gr', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
UPDATE `i_countries` SET `deleted` = '0' WHERE `i_countries`.`id` = 101;
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('108', '5', 'Iceland', 'is', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('105', '5', 'Ireland', 'ie', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('120', '5', 'Italy', 'it', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('124', '5', 'Kazakhstan', 'kz', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('130', '5', 'Latvia', 'lv', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('134', '5', 'Liechtenstein', 'li', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('135', '5', 'Lithuania', 'lt', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('136', '5', 'Luxembourg', 'lu', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('144', '5', 'Malta', 'mt', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('151', '5', 'Moldova', 'md', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('157', '5', 'Monaco', 'mc', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('201', '5', 'Montenegro', 'me', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('98', '5', 'Netherlands', 'nl', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('139', '5', 'North Macedonia', 'mk', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('166', '5', 'Norway', 'no', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('179', '5', 'Poland', 'pl', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('180', '5', 'Portugal', 'pt', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('190', '5', 'Romania', 'ro', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
UPDATE `i_countries` SET `deleted` = '0' WHERE `i_countries`.`id` = 191;
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('195', '5', 'San Marino', 'sm', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('68', '5', 'Slovakia', 'sk', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('69', '5', 'Slovenia', 'si', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('70', '5', 'Spain', 'es', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
UPDATE `i_countries` SET `deleted` = '0' WHERE `i_countries`.`id` = 211;
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('212', '5', 'Switzerland', 'ch', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('224', '5', 'Turkey', 'tr', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('227', '5', 'Ukraine', 'ua', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_countries` (`id`, `market_id`, `name`, `code`, `deleted`, `created_at`, `updated_at`) VALUES ('65', '5', 'Vatican City', 'va', '0', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
UPDATE `i_countries` SET `deleted` = '1' WHERE `i_countries`.`id` = 156;

-- Insert regions
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('50', '2', 'Albania', 'Albania', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('51', '5', 'Andorra', 'Andorra', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('52', '14', 'Armenia', 'Armenia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('53', '18', 'Austria', 'Austria', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('54', '19', 'Azerbaijan', 'Azerbaijan', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('55', '27', 'Belarus', 'Belarus', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('56', '39', 'Belgium', 'Belgium', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('57', '29', 'Bosnia and Herzegovina', 'bosnia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('58', '35', 'Bulgaria', 'Bulgaria', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('59', '56', 'Croatia', 'Croatia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('60', '48', 'Cyprus', 'Cyprus', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('61', '185', 'Czechia', 'Czechia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('62', '58', 'Denmark', 'Denmark', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('63', '72', 'Estonia', 'Estonia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('64', '77', 'France', 'France', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('65', '83', 'Georgia', 'Georgia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('66', '87', 'Greece', 'Greece', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('67', '108', 'Iceland', 'Iceland', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('68', '105', 'Ireland', 'Ireland', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('69', '120', 'Italy', 'Italy', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('70', '124', 'Kazakhstan', 'Kazakhstan', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('71', '130', 'Latvia', 'Latvia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('72', '134', 'Liechtenstein', 'Liechtenstein', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('73', '135', 'Lithuania', 'Lithuania', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('74', '136', 'Luxembourg', 'Luxembourg', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('75', '144', 'Malta', 'Malta', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('76', '151', 'Moldova', 'Moldova', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('77', '157', 'Monaco', 'Monaco', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('78', '201', 'Montenegro', 'Montenegro', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('79', '98', 'Netherlands', 'Netherlands', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('80', '139', 'North Macedonia', 'NorthMacedonia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('81', '166', 'Norway', 'Norway', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('82', '179', 'Poland', 'Poland', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('83', '180', 'Portugal', 'Portugal', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('84', '190', 'Romania', 'Romania', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('85', '195', 'San Marino', 'SanMarino', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('86', '68', 'Slovakia', 'Slovakia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('87', '69', 'Slovenia', 'Slovenia', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('88', '70', 'Spain', 'Spain', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('89', '212', 'Switzerland', 'Switzerland', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('90', '224', 'Turkey', 'Turkey', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('91', '227', 'Ukraine', 'Ukraine', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);
INSERT INTO `i_regions` (`id`, `country_id`, `name`, `code`, `created_at`, `updated_at`) VALUES ('92', '65', 'Vatican City', 'VaticanCity', '2019-03-11 00:00:00', CURRENT_TIMESTAMP);

-- Insert territories
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('95', '50', 'Albania', 'Albania', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('96', '51', 'Andorra', 'Andorra', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('97', '52', 'Armenia', 'Armenia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('98', '53', 'Austria', 'Austria', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('99', '54', 'Azerbaijan', 'Azerbaijan', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('100', '55', 'Belarus', 'Belarus', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('101', '56', 'Belgium', 'Belgium', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('102', '57', 'Bosnia and Herzegovina', 'Bosnia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('103', '58', 'Bulgaria', 'Bulgaria', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('104', '59', 'Croatia', 'Croatia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('105', '60', 'Cyprus', 'Cyprus', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('106', '61', 'Czechia', 'Czechia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('107', '62', 'Denmark', 'Denmark', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('108', '63', 'Estonia', 'Estonia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('109', '64', 'France', 'France', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('110', '65', 'Georgia', 'Georgia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('111', '66', 'Greece', 'Greece', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('112', '67', 'Iceland', 'Iceland', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('113', '68', 'Ireland', 'Ireland', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('114', '69', 'Italy', 'Italy', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('115', '70', 'Kazakhstan', 'Kazakhstan', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('116', '71', 'Latvia', 'Latvia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('117', '72', 'Liechtenstein', 'Liechtenstein', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('118', '73', 'Lithuania', 'Lithuania', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('119', '74', 'Luxembourg', 'Luxembourg', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('120', '75', 'Malta', 'Malta', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('121', '76', 'Moldova', 'Moldova', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('122', '77', 'Monaco', 'Monaco', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('123', '78', 'Montenegro', 'Montenegro', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('124', '79', 'Netherlands', 'Netherlands', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('125', '80', 'North Macedonia', 'NorthMacedonia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('126', '81', 'Norway', 'Norway', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('127', '82', 'Poland', 'Poland', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('128', '83', 'Portugal', 'Portugal', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('129', '84', 'Romania', 'Romania', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('130', '85', 'San Marino', 'SanMarino', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('131', '86', 'Slovakia', 'Slovakia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('132', '87', 'Slovenia', 'Slovenia', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('133', '88', 'Spain', 'Spain', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('134', '89', 'Switzerland', 'Switzerland', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('135', '90', 'Turkey', 'Turkey', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('136', '91', 'Ukraine', 'Ukraine', CURRENT_TIMESTAMP);
INSERT INTO `i_territories` (`id`, `region_id`, `name`, `code`, `created_at`) VALUES ('137', '92', 'Vatican City', 'VaticanCity', CURRENT_TIMESTAMP);

-- Update names of territories
UPDATE `i_territories` SET `name` = 'Hungary' WHERE `i_territories`.`id` = 59;
UPDATE `i_territories` SET `name` = 'Russia' WHERE `i_territories`.`id` = 60;
UPDATE `i_territories` SET `name` = 'Sweden' WHERE `i_territories`.`id` = 62;
UPDATE `i_territories` SET `name` = 'Finland' WHERE `i_territories`.`id` = 63;

-- Insert territory companies
INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Finland', '76', '60', 'admin', 'FINLAND', '5', '76', '21', '63');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Hungary', '101', '60', 'admin', 'HUNGARY', '5', '101', '17', '59');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Russia', '191', '60', 'admin', 'RUSSIA', '5', '191', '18', '60');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Sweden', '211', '60', 'admin', 'SWEDEN', '5', '211', '20', '62');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Albania', '2', '60', 'admin', 'Albania', '5', '2', '50', '95');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Andorra', '5', '60', 'admin', 'Andorra', '5', '5', '51', '96');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Armenia', '14', '60', 'admin', 'Armenia', '5', '14', '52', '97');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Austria', '18', '60', 'admin', 'Austria', '5', '18', '53', '98');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Azerbaijan', '19', '60', 'admin', 'Azerbaijan', '5', '19', '54', '99');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Belarus', '27', '60', 'admin', 'Belarus', '5', '27', '55', '100');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Belgium', '39', '60', 'admin', 'Belgium', '5', '39', '56', '101');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Bosnia and Herzegovina', '29', '60', 'admin', 'Bosnia', '5', '29', '57', '102');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Bulgaria', '35', '60', 'admin', 'Bulgaria', '5', '35', '58', '103');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Croatia', '56', '60', 'admin', 'Croatia', '5', '56', '59', '104');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Cyprus', '48', '60', 'admin', 'Cyprus', '5', '48', '60', '105');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Czechia', '185', '60', 'admin', 'Czechia', '5', '185', '61', '106');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Denmark', '58', '60', 'admin', 'Denmark', '5', '58', '62', '107');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Estonia', '72', '60', 'admin', 'Estonia', '5', '72', '63', '108');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech France', '77', '60', 'admin', 'France', '5', '77', '64', '109');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Georgia', '83', '60', 'admin', 'Georgia', '5', '83', '65', '110');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Greece', '87', '60', 'admin', 'Greece', '5', '87', '66', '111');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Iceland', '108', '60', 'admin', 'Iceland', '5', '108', '67', '112');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Ireland', '105', '60', 'admin', 'Ireland', '5', '105', '68', '113');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Italy', '120', '60', 'admin', 'Italy', '5', '120', '69', '114');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Kazakhstan', '124', '60', 'admin', 'Kazakhstan', '5', '124', '70', '115');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Latvia', '130', '60', 'admin', 'Latvia', '5', '130', '71', '116');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Liechtenstein', '134', '60', 'admin', 'Liechtenstein', '5', '134', '72', '117');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Lithuania', '135', '60', 'admin', 'Lithuania', '5', '135', '73', '118');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Luxembourg', '136', '60', 'admin', 'Luxembourg', '5', '136', '74', '119');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Malta', '144', '60', 'admin', 'Malta', '5', '144', '75', '120');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Moldova', '151', '60', 'admin', 'Moldova', '5', '151', '76', '121');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Monaco', '157', '60', 'admin', 'Monaco', '5', '157', '77', '122');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Montenegro', '201', '60', 'admin', 'Montenegro', '5', '201', '78', '123');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Netherlands', '98', '60', 'admin', 'Netherlands', '5', '98', '79', '124');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech North Macedonia', '139', '60', 'admin', 'NorthMacedonia', '5', '139', '80', '125');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Norway', '166', '60', 'admin', 'Norway', '5', '166', '81', '126');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Poland', '179', '60', 'admin', 'Poland', '5', '179', '82', '127');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Portugal', '180', '60', 'admin', 'Portugal', '5', '180', '83', '128');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Romania', '190', '60', 'admin', 'Romania', '5', '190', '84', '129');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech San Marino', '195', '60', 'admin', 'SanMarino', '5', '195', '85', '130');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Slovakia', '68', '60', 'admin', 'Slovakia', '5', '68', '86', '131');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Slovenia', '69', '60', 'admin', 'Slovenia', '5', '69', '87', '132');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Spain', '70', '60', 'admin', 'Spain', '5', '70', '88', '133');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Switzerland', '212', '60', 'admin', 'Switzerland', '5', '212', '89', '134');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Turkey', '224', '60', 'admin', 'Turkey', '5', '224', '90', '135');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Ukraine', '227', '60', 'admin', 'Ukraine', '5', '227', '91', '136');

INSERT INTO `empresas` (`id`, `name`, `country_id`, `group_bucket_id`, `type`, `region`,`i_market_id`, `i_country_id`, `region_id`, `territory_id`) 
VALUES (NULL, 'ContiTech Vatican City', '65', '60', 'admin', 'VaticanCity', '5', '65', '92', '137');