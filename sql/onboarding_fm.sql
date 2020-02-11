# noinspection SqlNoDataSourceInspectionForFile

-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  lun. 10 fév. 2020 à 13:19
-- Version du serveur :  10.4.10-MariaDB
-- Version de PHP :  7.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `onboarding_fm`
--

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

DROP TABLE IF EXISTS `commande`;
CREATE TABLE IF NOT EXISTS `commande` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `truckday_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6EEAA67D7D0729A9` (`truckday_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`id`, `truckday_id`, `quantity`, `date`, `status`) VALUES
(38, 187, 900, '2020-02-06', 'done'),
(39, 191, 5300, '2020-02-06', 'done'),
(40, 184, 500, '2020-02-06', 'done'),
(41, 184, 2000, '2020-02-06', 'done'),
(42, 184, 2000, '2020-02-06', 'done'),
(43, 187, 3000, '2020-02-06', 'done'),
(44, 185, 2000, '2020-02-06', 'done'),
(45, 186, 500, '2020-02-07', 'done');

-- --------------------------------------------------------

--
-- Structure de la table `truckday`
--

DROP TABLE IF EXISTS `truckday`;
CREATE TABLE IF NOT EXISTS `truckday` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `truck` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `capacity` int(11) NOT NULL,
  `postal_code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=205 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `truckday`
--

INSERT INTO `truckday` (`id`, `date`, `truck`, `capacity`, `postal_code`) VALUES
(175, '2020-02-01', '4CV8F', 9000, '92500'),
(176, '2020-02-02', '8Z25M', 12000, '92500'),
(177, '2020-02-03', '8Z25M', 12000, '92500'),
(178, '2020-02-04', '8Z25M', 3000, '92500'),
(179, '2020-02-05', '4CV8F', 3000, '92500'),
(180, '2020-02-06', '4CV8F', 3600, '92500'),
(181, '2020-02-07', 'PMS51', 7800, '92500'),
(182, '2020-02-08', '44FK9', 10000, '92500'),
(183, '2020-02-09', '8Z25M', 4600, '92500'),
(184, '2020-02-10', '4CV8F', 4500, '92500'),
(185, '2020-02-11', '4CV8F', 2100, '92500'),
(186, '2020-02-12', '8Z25M', 10000, '92500'),
(187, '2020-02-13', '8Z25M', 3900, '92500'),
(188, '2020-02-14', '8Z25M', 11100, '92500'),
(189, '2020-02-15', '44FK9', 13200, '92500'),
(190, '2020-02-16', '44FK9', 3600, '92500'),
(191, '2020-02-17', '4CV8F', 5300, '92500'),
(192, '2020-02-18', 'WY8ZD', 5200, '92500'),
(193, '2020-02-19', '44FK9', 9500, '92500'),
(194, '2020-02-20', '44FK9', 6000, '92500'),
(195, '2020-02-21', '8Z25M', 7000, '92500'),
(196, '2020-02-22', '4CV8F', 2300, '92500'),
(197, '2020-02-23', '8Z25M', 2000, '92500'),
(198, '2020-02-24', '4CV8F', 2000, '92500'),
(199, '2020-02-25', 'PMS51', 14000, '92500'),
(200, '2020-02-26', '44FK9', 2300, '92500'),
(201, '2020-02-27', 'PMS51', 18000, '92500'),
(202, '2020-02-28', '44FK9', 16000, '92500'),
(203, '2020-02-29', '4CV8F', 8100, '92500');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `FK_6EEAA67D7D0729A9` FOREIGN KEY (`truckday_id`) REFERENCES `truckday` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
