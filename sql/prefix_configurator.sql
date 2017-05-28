-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Počítač: localhost
-- Vytvořeno: Ned 28. kvě 2017, 23:51
-- Verze serveru: 10.0.29-MariaDB-0ubuntu0.16.04.1
-- Verze PHP: 7.0.15-0ubuntu0.16.04.4

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
-- Struktura tabulky `prefix_configurator`
--

CREATE TABLE `prefix_configurator` (
  `id` int(11) NOT NULL,
  `id_locale` int(11) DEFAULT NULL COMMENT 'vazba na jazyk',
  `id_ident` int(11) NOT NULL COMMENT 'vazba na ident',
  `type` varchar(50) DEFAULT NULL COMMENT 'typ hodnoty',
  `content` text COMMENT 'hodnota',
  `enable` tinyint(1) DEFAULT '0' COMMENT 'povoleno'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='obecna konfigurace';

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `prefix_configurator`
--
ALTER TABLE `prefix_configurator`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `locale_ident_UNIQUE` (`id_locale`,`id_ident`),
  ADD KEY `fk_configurator_locale_idx` (`id_locale`),
  ADD KEY `fk_configurator_configurator_ident_idx` (`id_ident`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `prefix_configurator`
--
ALTER TABLE `prefix_configurator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `prefix_configurator`
--
ALTER TABLE `prefix_configurator`
  ADD CONSTRAINT `fk_configurator_configurator_ident` FOREIGN KEY (`id_ident`) REFERENCES `prefix_configurator_ident` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_configurator_locale` FOREIGN KEY (`id_locale`) REFERENCES `prefix_locale` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
