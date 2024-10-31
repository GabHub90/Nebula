-- phpMyAdmin SQL Dump
-- version 3.3.1-rc1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: 03 gen, 2014 at 07:51 PM
-- Versione MySQL: 5.0.92
-- Versione PHP: 5.3.26

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ststop`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `LISTA_TEMPI_APERTI`
--

CREATE TABLE IF NOT EXISTS `LISTA_TEMPI_APERTI` (
  `num_rif_movimento` int(11) NOT NULL,
  `cod_inconveniente` varchar(2) collate utf8_bin NOT NULL,
  `cod_operaio` int(11) NOT NULL,
  `num_riga` int(11) NOT NULL,
  `d` varchar(8) collate utf8_bin NOT NULL,
  `t` varchar(5) collate utf8_bin NOT NULL,
  `cod_off` varchar(3) collate utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dump dei dati per la tabella `LISTA_TEMPI_APERTI`
--

INSERT INTO `LISTA_TEMPI_APERTI` (`num_rif_movimento`, `cod_inconveniente`, `cod_operaio`, `num_riga`, `d`, `t`, `cod_off`) VALUES
(265723, 'C', 15, 1021, '20131227', '16:36', 'PV'),
(265724, 'B', 12, 1615, '20131227', '15:28', 'PA'),
(265724, 'B', 28, 1617, '20131227', '16:52', 'PA'),
(265724, 'C', 10, 884, '20131227', '16:12', 'PA'),
(265724, 'C', 13, 885, '20131227', '16:13', 'PA'),
(265724, 'C', 4, 886, '20131227', '17:25', 'PA'),
(265725, 'C', 23, 453, '20131227', '16:54', 'PP'),
(742064, 'A', 24, 2, '20131227', '16:26', 'PV'),
(742088, 'B', 27, 1, '20131227', '16:08', 'PP'),
(742101, 'A', 3, 2, '20131227', '14:30', 'PV'),
(742368, 'A', 25, 2, '20131227', '17:11', 'PV'),
(742686, 'A', 18, 1, '20131227', '14:30', 'PV'),
(742738, 'B', 11, 1, '20131227', '16:19', 'PV'),
(742779, 'A', 5, 1, '20131227', '15:50', 'PV'),
(742825, 'A', 21, 1, '20131227', '14:49', 'PV'),
(742869, 'A', 17, 1, '20131227', '17:00', 'PA');
