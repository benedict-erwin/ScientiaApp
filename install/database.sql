-- Adminer 4.2.5 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `j_menu`;
CREATE TABLE `j_menu` (
  `id_jmenu` int(11) NOT NULL AUTO_INCREMENT COMMENT 'For Menu Privileges',
  `id_menu` int(11) NOT NULL,
  `idrole` int(11) NOT NULL,
  PRIMARY KEY (`id_jmenu`),
  KEY `id_menu` (`id_menu`),
  KEY `idrole` (`idrole`),
  CONSTRAINT `j_menu_ibfk_6` FOREIGN KEY (`id_menu`) REFERENCES `m_menu` (`id_menu`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `j_menu_ibfk_7` FOREIGN KEY (`idrole`) REFERENCES `m_role` (`idrole`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `j_menu` (`id_jmenu`, `id_menu`, `idrole`) VALUES
(1,	2,	1),
(2,	3,	1),
(3,	4,	1),
(4,	5,	1),
(5,	6,	1),
(6,	7,	1),
(7,	8,	1),
(8,	9,	1),
(9,	10,	1),
(10,	11,	1),
(11,	12,	1),
(12,	13,	1),
(13,	14,	1),
(14,	15,	1),
(15,	16,	1),
(16,	17,	1),
(17,	18,	1),
(18,	19,	1),
(19,	20,	1),
(20,	21,	1),
(21,	22,	1),
(22,	23,	1),
(23,	24,	1),
(24,	25,	1),
(25,	26,	1),
(26,	27,	1),
(27,	28,	1),
(28,	29,	1),
(29,	30,	1),
(30,	31,	1),
(31,	32,	1),
(32,	33,	1),
(33,	34,	1),
(34,	35,	1),
(35,	36,	1),
(36,	37,	1),
(37,	38,	1),
(38,	39,	1);

DROP TABLE IF EXISTS `l_auditlog`;
CREATE TABLE `l_auditlog` (
  `idauditlog` int(11) NOT NULL AUTO_INCREMENT,
  `iduser` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Menu url',
  `http_method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'json data',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idauditlog`),
  KEY `iduser` (`iduser`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `m_config`;
CREATE TABLE `m_config` (
  `id_config` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scope` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: private, 1:public, 2: global',
  PRIMARY KEY (`id_config`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `m_groupmenu`;
CREATE TABLE `m_groupmenu` (
  `id_groupmenu` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urut` int(11) NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_groupmenu`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `m_groupmenu` (`id_groupmenu`, `nama`, `icon`, `urut`, `aktif`) VALUES
(1,	'Dashboard',	'notika-icon notika-house',	1,	1),
(2,	'Master',	'notika-icon notika-app',	2,	1),
(3,	'Settings',	'notika-icon notika-settings',	3,	1);

DROP TABLE IF EXISTS `m_menu`;
CREATE TABLE `m_menu` (
  `id_menu` int(11) NOT NULL AUTO_INCREMENT,
  `id_groupmenu` int(11) NOT NULL,
  `nama` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `controller` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ClassName:method',
  `tipe` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'GET, POST, PUT, DELETE',
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `urut` int(11) NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'For Public API, access without token',
  PRIMARY KEY (`id_menu`),
  KEY `id_groupmenu` (`id_groupmenu`),
  CONSTRAINT `m_menu_ibfk_1` FOREIGN KEY (`id_groupmenu`) REFERENCES `m_groupmenu` (`id_groupmenu`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `m_menu` (`id_menu`, `id_groupmenu`, `nama`, `icon`, `url`, `controller`, `tipe`, `aktif`, `urut`, `is_public`) VALUES
(1,	3,	'Login',	'',	'/clogin',	'LoginController:index',	'POST',	1,	0,	1),
(2,	3,	'Logout',	'',	'/clogout',	'LogoutController:logout',	'GET',	1,	0,	0),
(3,	3,	'Authentication Controller',	'',	'/cauth',	'MenuController:getAuthMenu',	'POST',	1,	0,	0),
(4,	3,	'Menu Controller',	'',	'/cmenu',	'MenuController:index',	'POST',	1,	0,	0),
(5,	1,	'Home',	'',	'/home',	'C_home:index',	'GET',	1,	0,	0),
(6,	3,	'Generator',	'',	'/generator',	'CRUDGenerator',	'MENU',	1,	6,	0),
(7,	3,	'CRUD Generator',	'',	'/crud_gen',	'CRUDGenerator:index',	'POST',	1,	1,	0),
(8,	2,	'Role',	'',	'/role',	'M_role',	'MENU',	1,	0,	0),
(9,	2,	'Api Role Create',	'',	'/role/create',	'M_role:create',	'POST',	1,	0,	0),
(10,	2,	'Api Role Read',	'',	'/role/read',	'M_role:read',	'POST',	1,	0,	0),
(11,	2,	'Api Role Update',	'',	'/role',	'M_role:update',	'PUT',	1,	0,	0),
(12,	2,	'Api Role Delete',	'',	'/role',	'M_role:delete',	'DELETE',	1,	0,	0),
(13,	2,	'Api Role Batch Delete',	'',	'/role/batch',	'M_role:delete',	'DELETE',	1,	5,	0),
(14,	2,	'User',	'',	'/user',	'M_user',	'MENU',	1,	1,	0),
(15,	2,	'Api User Create',	'',	'/user/create',	'M_user:create',	'POST',	1,	0,	0),
(16,	2,	'Api User Read',	'',	'/user/read',	'M_user:read',	'POST',	1,	0,	0),
(17,	2,	'Api User Update',	'',	'/user',	'M_user:update',	'PUT',	1,	0,	0),
(18,	2,	'Api User Delete',	'',	'/user',	'M_user:delete',	'DELETE',	1,	0,	0),
(19,	2,	'Api User Batch Delete',	'',	'/user/batch',	'M_user:delete',	'DELETE',	1,	0,	0),
(20,	2,	'Groupmenu',	'',	'/groupmenu',	'M_groupmenu',	'MENU',	1,	3,	0),
(21,	2,	'Api Groupmenu Create',	'',	'/groupmenu/create',	'M_groupmenu:create',	'POST',	1,	0,	0),
(22,	2,	'Api Groupmenu Read',	'',	'/groupmenu/read',	'M_groupmenu:read',	'POST',	1,	0,	0),
(23,	2,	'Api Groupmenu Update',	'',	'/groupmenu',	'M_groupmenu:update',	'PUT',	1,	0,	0),
(24,	2,	'Api Groupmenu Delete',	'',	'/groupmenu',	'M_groupmenu:delete',	'DELETE',	1,	0,	0),
(25,	2,	'Api Groupmenu Batch Delete',	'',	'/groupmenu/batch',	'M_groupmenu:delete',	'DELETE',	1,	6,	0),
(26,	2,	'Menu',	'',	'/menu',	'M_menu',	'MENU',	1,	4,	0),
(27,	2,	'Api Menu Create',	'',	'/menu/create',	'M_menu:create',	'POST',	1,	0,	0),
(28,	2,	'Api Menu Read',	'',	'/menu/read',	'M_menu:read',	'POST',	1,	0,	0),
(29,	2,	'Api Menu Update',	'',	'/menu',	'M_menu:update',	'PUT',	1,	0,	0),
(30,	2,	'Api Menu Delete',	'',	'/menu',	'M_menu:delete',	'DELETE',	1,	0,	0),
(31,	2,	'Api Menu Batch Delete',	'',	'/menu/batch',	'M_menu:delete',	'DELETE',	1,	6,	0),
(32,	2,	'Api Menu jabatanMenu',	'',	'/c_menu_jabatanmenu',	'M_menu:jabatanMenu',	'POST',	1,	0,	0),
(33,	2,	'Api Menu setPermission',	'',	'/c_menu_setpermission',	'M_menu:setPermission',	'POST',	1,	0,	0),
(34,	2,	'Config',	'',	'/config',	'M_config',	'MENU',	1,	5,	0),
(35,	2,	'Api Config Create',	'',	'/config/create',	'M_config:create',	'POST',	1,	0,	0),
(36,	2,	'Api Config Read',	'',	'/config/read',	'M_config:read',	'POST',	1,	0,	0),
(37,	2,	'Api Config Update',	'',	'/config',	'M_config:update',	'PUT',	1,	0,	0),
(38,	2,	'Api Config Delete',	'',	'/config',	'M_config:delete',	'DELETE',	1,	0,	0),
(39,	2,	'Api Config Batch Delete',	'',	'/config/batch',	'M_config:delete',	'DELETE',	1,	6,	0);

DROP TABLE IF EXISTS `m_role`;
CREATE TABLE `m_role` (
  `idrole` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idrole`),
  UNIQUE KEY `nama` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `m_role` (`idrole`, `nama`, `deskripsi`) VALUES
(1,	'super',	'Super User / Developer');

DROP TABLE IF EXISTS `m_user`;
CREATE TABLE `m_user` (
  `iduser` int(11) NOT NULL AUTO_INCREMENT,
  `idrole` int(11) NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Fullname',
  `email` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telpon` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastlogin` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`iduser`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idrole` (`idrole`),
  CONSTRAINT `m_user_ibfk_1` FOREIGN KEY (`idrole`) REFERENCES `m_role` (`idrole`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2019-07-05 06:57:14