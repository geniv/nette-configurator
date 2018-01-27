-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost:3306
-- Vytvořeno: Sob 27. led 2018, 20:27
-- Verze serveru: 10.1.26-MariaDB-0+deb9u1
-- Verze PHP: 7.0.27-0+deb9u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `netteweb`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `prefix_configurator_ident`
--

CREATE TABLE `prefix_configurator_ident` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ident` varchar(100) DEFAULT NULL COMMENT 'identifikator'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='obecna konfigurace - identy';

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `prefix_configurator_ident`
--
ALTER TABLE `prefix_configurator_ident`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ident_UNIQUE` (`ident`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `prefix_configurator_ident`
--
ALTER TABLE `prefix_configurator_ident`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
