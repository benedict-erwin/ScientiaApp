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
  `idjabatan` int(11) NOT NULL,
  PRIMARY KEY (`id_jmenu`),
  KEY `id_menu` (`id_menu`),
  KEY `idjabatan` (`idjabatan`),
  CONSTRAINT `j_menu_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `m_menu` (`id_menu`),
  CONSTRAINT `j_menu_ibfk_2` FOREIGN KEY (`idjabatan`) REFERENCES `m_jabatan` (`idjabatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `j_menu` (`id_jmenu`, `id_menu`, `idjabatan`) VALUES
(1,	1,	1),
(2,	2,	1),
(3,	3,	1),
(4,	4,	1),
(5,	5,	1),
(6,	6,	1),
(7,	7,	1),
(8,	8,	1),
(9,	9,	1),
(10,	10,	1),
(11,	11,	1),
(12,	12,	1),
(13,	13,	1),
(14,	14,	1),
(15,	15,	1),
(16,	16,	1),
(17,	17,	1),
(18,	18,	1),
(19,	19,	1),
(20,	20,	1),
(21,	21,	1),
(22,	22,	1),
(23,	23,	1),
(24,	24,	1),
(25,	25,	1),
(26,	26,	1),
(27,	27,	1),
(28,	28,	1);

DROP TABLE IF EXISTS `l_auditlog`;
CREATE TABLE `l_auditlog` (
  `idauditlog` int(11) NOT NULL AUTO_INCREMENT,
  `iduser` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Menu url',
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'json data',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idauditlog`),
  KEY `iduser` (`iduser`),
  CONSTRAINT `l_auditlog_ibfk_2` FOREIGN KEY (`iduser`) REFERENCES `m_user` (`iduser`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `m_groupmenu`;
CREATE TABLE `m_groupmenu` (
  `id_groupmenu` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urut` int(11) NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_groupmenu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `m_groupmenu` (`id_groupmenu`, `nama`, `icon`, `urut`, `aktif`) VALUES
(1,	'Dashboard',	'lnr lnr-home',	1,	1),
(2,	'Master',	'lnr lnr-database',	2,	1),
(3,	'Settings',	'lnr lnr-cog',	3,	1);

DROP TABLE IF EXISTS `m_jabatan`;
CREATE TABLE `m_jabatan` (
  `idjabatan` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idjabatan`),
  UNIQUE KEY `nama` (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `m_jabatan` (`idjabatan`, `nama`, `deskripsi`) VALUES
(1,	'super',	'Super User / Developer');

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
  `publik` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'For Public API, access without token',
  PRIMARY KEY (`id_menu`),
  UNIQUE KEY `url` (`url`),
  KEY `id_groupmenu` (`id_groupmenu`),
  CONSTRAINT `m_menu_ibfk_1` FOREIGN KEY (`id_groupmenu`) REFERENCES `m_groupmenu` (`id_groupmenu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `m_menu` (`id_menu`, `id_groupmenu`, `nama`, `icon`, `url`, `controller`, `tipe`, `aktif`, `urut`, `publik`) VALUES
(1,	1,	'Home',	'',	'/home',	'C_home:index',	'GET',	1,	0,	0),
(2,	3,	'CRUD Generator',	'',	'/crud_gen',	'CRUDGenerator:index',	'POST',	1,	1,	0),
(3,	3,	'Generator',	'',	'/generator',	'CRUDGenerator:index',	'GET',	1,	1,	0),
(4,	2,	'Api Menu Create',	'',	'/c_menu_create',	'M_menu:create',	'POST',	1,	0,	0),
(5,	2,	'Menu',	'',	'/menu',	'M_menu:index',	'GET',	1,	2,	0),
(6,	2,	'Api Menu Read',	'',	'/c_menu_read',	'M_menu:read',	'POST',	1,	0,	0),
(7,	2,	'Api Menu Update',	'',	'/c_menu_update',	'M_menu:update',	'POST',	1,	0,	0),
(8,	2,	'Api Menu Delete',	'',	'/c_menu_delete',	'M_menu:delete',	'POST',	1,	0,	0),
(9,	2,	'Api Menu jabatanMenu',	'',	'/c_menu_jabatanmenu',	'M_menu:jabatanMenu',	'POST',	1,	0,	0),
(10,	2,	'Api Menu setPermission',	'',	'/c_menu_setpermission',	'M_menu:setPermission',	'POST',	1,	0,	0),
(11,	3,	'Authentication Controller',	'',	'/cauth',	'MenuController:getAuthMenu',	'POST',	1,	0,	0),
(12,	3,	'Menu Controller',	'',	'/cmenu',	'MenuController:index',	'POST',	1,	0,	0),
(13,	3,	'Logout',	'',	'/clogout',	'LoginController:logout',	'POST',	1,	0,	0),
(14,	2,	'Api Groupmenu Create',	'',	'/c_groupmenu_create',	'M_groupmenu:create',	'POST',	1,	0,	0),
(15,	2,	'Api Groupmenu Read',	'',	'/c_groupmenu_read',	'M_groupmenu:read',	'POST',	1,	0,	0),
(16,	2,	'Api Groupmenu Update',	'',	'/c_groupmenu_update',	'M_groupmenu:update',	'POST',	1,	0,	0),
(17,	2,	'Api Groupmenu Delete',	'',	'/c_groupmenu_delete',	'M_groupmenu:delete',	'POST',	1,	0,	0),
(18,	2,	'Groupmenu',	'',	'/groupmenu',	'M_groupmenu:index',	'GET',	1,	1,	0),
(19,	2,	'Api Jabatan Create',	'',	'/c_jabatan_create',	'M_jabatan:create',	'POST',	1,	0,	0),
(20,	2,	'Api Jabatan Read',	'',	'/c_jabatan_read',	'M_jabatan:read',	'POST',	1,	0,	0),
(21,	2,	'Api Jabatan Update',	'',	'/c_jabatan_update',	'M_jabatan:update',	'POST',	1,	0,	0),
(22,	2,	'Api Jabatan Delete',	'',	'/c_jabatan_delete',	'M_jabatan:delete',	'POST',	1,	0,	0),
(23,	2,	'Jabatan',	'',	'/jabatan',	'M_jabatan:index',	'GET',	1,	0,	0),
(24,	2,	'Api User Create',	'',	'/c_user_create',	'M_user:create',	'POST',	1,	0,	0),
(25,	2,	'Api User Read',	'',	'/c_user_read',	'M_user:read',	'POST',	1,	0,	0),
(26,	2,	'Api User Update',	'',	'/c_user_update',	'M_user:update',	'POST',	1,	0,	0),
(27,	2,	'Api User Delete',	'',	'/c_user_delete',	'M_user:delete',	'POST',	1,	0,	0),
(28,	2,	'User',	'',	'/user',	'M_user:index',	'GET',	1,	3,	0);

DROP TABLE IF EXISTS `m_user`;
CREATE TABLE `m_user` (
  `iduser` int(11) NOT NULL AUTO_INCREMENT,
  `idjabatan` int(11) NOT NULL,
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
  KEY `idjabatan` (`idjabatan`),
  CONSTRAINT `m_user_ibfk_1` FOREIGN KEY (`idjabatan`) REFERENCES `m_jabatan` (`idjabatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2019-06-22 12:20:09