-- phpMyAdmin SQL Dump
-- version 3.3.1-rc1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: 03 gen, 2014 at 07:50 PM
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
-- Struttura della tabella `LISTA_ULTIME_TIMBRATURE`
--

CREATE TABLE IF NOT EXISTS `LISTA_ULTIME_TIMBRATURE` (
  `mov` int(11) NOT NULL,
  `inc` varchar(3) collate utf8_bin NOT NULL,
  `cod_operaio` varchar(3) collate utf8_bin NOT NULL,
  `num_riga` int(11) NOT NULL,
  `d` varchar(8) collate utf8_bin NOT NULL,
  `t` varchar(5) collate utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dump dei dati per la tabella `LISTA_ULTIME_TIMBRATURE`
--

INSERT INTO `LISTA_ULTIME_TIMBRATURE` (`mov`, `inc`, `cod_operaio`, `num_riga`, `d`, `t`) VALUES
(265723, 'C', '15', 1043, '20140103', '18:30'),
(265723, 'C', '5', 1044, '20140103', '18:30'),
(265724, 'B', '13', 1656, '20140103', '18:30'),
(265724, 'B', '21', 1658, '20140103', '18:30'),
(265724, 'B', '28', 1632, '20131230', '18:30'),
(265724, 'B', '4', 1657, '20140103', '18:30'),
(265724, 'C', '12', 912, '20140103', '18:30'),
(265724, 'C', '2', 911, '20140103', '18:30'),
(742162, 'A', '14', 1, '20131228', '09:19'),
(742693, 'A', '25', 2, '20140103', '18:30'),
(743166, 'A', '24', 1, '20131231', '12:15'),
(743304, 'A', '19', 1, '20140102', '12:15'),
(743380, 'A', '27', 2, '20140103', '18:30'),
(743399, 'A', '17', 5, '20140103', '18:30'),
(743631, 'A', '18', 2, '20140103', '18:30'),
(743664, 'A', '6', 1, '20140103', '12:15'),
(743770, 'A', '1', 1, '20140103', '17:42'),
(743786, 'A', '11', 1, '20140103', '16:00'),
(743813, 'A', '10', 1, '20140103', '18:30'),
(743826, 'A', '3', 1, '20140103', '18:30'),
(743838, 'A', '23', 1, '20140103', '18:11');
