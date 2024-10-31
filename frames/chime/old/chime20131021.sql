-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: 21 ott, 2013 at 10:40 AM
-- Versione MySQL: 5.1.44
-- Versione PHP: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `chime`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `indici`
--

CREATE TABLE IF NOT EXISTS `indici` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `report` int(11) NOT NULL,
  `tag` varchar(8) COLLATE utf8_bin NOT NULL,
  `data` varchar(8) COLLATE utf8_bin NOT NULL,
  `stato` varchar(5) COLLATE utf8_bin NOT NULL,
  `parametri` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=47 ;

--
-- Dump dei dati per la tabella `indici`
--

INSERT INTO `indici` (`ID`, `report`, `tag`, `data`, `stato`, `parametri`) VALUES
(1, 1, '20131002', '20130928', 'done', ''),
(2, 1, '20130927', '20130926', 'done', ''),
(3, 1, '20131016', '20131003', 'done', ''),
(46, 1, '20131031', '20131021', 'done', 0x7b22666f726d5f64617461223a223230313331303331227d);

-- --------------------------------------------------------

--
-- Struttura della tabella `mail`
--

CREATE TABLE IF NOT EXISTS `mail` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `from` tinytext COLLATE utf8_bin NOT NULL,
  `oggetto` tinytext COLLATE utf8_bin NOT NULL,
  `codice` text COLLATE utf8_bin NOT NULL,
  `tag` varchar(20) COLLATE utf8_bin NOT NULL,
  `descrizione` mediumtext COLLATE utf8_bin NOT NULL,
  `attivo` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `mail`
--

INSERT INTO `mail` (`ID`, `from`, `oggetto`, `codice`, `tag`, `descrizione`, `attivo`) VALUES
(1, 0x736572766963652e766f6c6b73776167656e40676162656c6c696e692e6974, 0x496e666f20412e476162656c6c696e692053726c, 0x3c68746d6c3e3c626f64793e3c6469763e3c2364617461233e3c2f6469763e3c6469763e3c236f7261233e3c2f6469763e3c2f626f64793e3c2f68746d6c3e, 'Promozione attiva', 0x4f6666657274652064656c206d657365, 1),
(2, '', '', 0x3c68746d6c3e3c2f68746d6c3e, 'Extra', 0x4f666665727465206c616d706f, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `report`
--

CREATE TABLE IF NOT EXISTS `report` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(25) COLLATE utf8_bin NOT NULL,
  `reparto` varchar(3) COLLATE utf8_bin NOT NULL,
  `tipo` varchar(6) COLLATE utf8_bin NOT NULL,
  `today` tinytext COLLATE utf8_bin,
  `back` int(1) NOT NULL DEFAULT '1',
  `forw` int(1) NOT NULL DEFAULT '1',
  `indice` int(1) NOT NULL DEFAULT '0',
  `storico` int(1) NOT NULL DEFAULT '0',
  `descrizione` text COLLATE utf8_bin NOT NULL,
  `inc` tinytext COLLATE utf8_bin NOT NULL,
  `sms` tinyint(1) NOT NULL DEFAULT '1',
  `mail` tinyint(1) NOT NULL DEFAULT '0',
  `link` tinyint(1) NOT NULL DEFAULT '0',
  `attivo` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `report`
--

INSERT INTO `report` (`ID`, `tag`, `reparto`, `tipo`, `today`, `back`, `forw`, `indice`, `storico`, `descrizione`, `inc`, `sms`, `mail`, `link`, `attivo`) VALUES
(1, 'Reminder appuntamento', 'VWS', 'GIORNO', 0x2b3120646179, 0, 1, 2, 2, 0x506572207269636f726461726520616920636c69656e7469206c27617070756e74616d656e746f20696d6d696e656e74652065206d616e646172676c6920756e61206d61696c20636f6e206c65206f6666657274652061747475616c692e, 0x5657535f617070, 1, 1, 0, 1),
(2, 'Avviso revisione', 'VWS', 'MESE', 0x2d322079656172, 0, 1, 1, 1, 0x5269636f726461206c612073636164656e7a612064656c6c61207265766973696f6e65, '', 1, 0, 0, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `rep_mail`
--

CREATE TABLE IF NOT EXISTS `rep_mail` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `report` int(11) NOT NULL,
  `mail` int(11) NOT NULL,
  `def` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `rep_mail`
--

INSERT INTO `rep_mail` (`ID`, `report`, `mail`, `def`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `rep_sms`
--

CREATE TABLE IF NOT EXISTS `rep_sms` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `report` int(11) NOT NULL,
  `sms` int(11) NOT NULL,
  `def` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `rep_sms`
--

INSERT INTO `rep_sms` (`ID`, `report`, `sms`, `def`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `sms`
--

CREATE TABLE IF NOT EXISTS `sms` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `testo` tinytext COLLATE utf8_bin NOT NULL,
  `tag` varchar(20) COLLATE utf8_bin NOT NULL,
  `attivo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `sms`
--

INSERT INTO `sms` (`ID`, `testo`, `tag`, `attivo`) VALUES
(1, 0x4c65207269636f726469616d6f206c27617070756e74616d656e746f2070657220696c2067696f726e6f203c646174613e20616c6c65203c6f72613e, 'reminder', 1),
(2, 0x7365636f6e646f2072656d61696e646572, 'second', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `storico`
--

CREATE TABLE IF NOT EXISTS `storico` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `indice` int(11) NOT NULL,
  `elementi` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=38 ;

--
-- Dump dei dati per la tabella `storico`
--

INSERT INTO `storico` (`ID`, `indice`, `elementi`) VALUES
(37, 46, 0x5b7b22636873756d223a223732343335365f3230313331303331222c2264617469223a7b22696e74657374617a696f6e65223a2241646f6c662043756e6e696e6768616d222c2274656c61696f223a227776777a7a7a396e7a6162313233343536222c226b6d223a223233343736222c22636f6e7365676e61223a223230313030353233222c2264617461223a2233312d31302d3133222c226f7261223a2231313a3436227d2c2263686b5f656c223a312c2263686b5f736d73223a302c2263686b5f6d61696c223a302c2263686b5f6c696e6b223a302c2270686f6e65223a22222c2261646472657373223a22222c226d6f64656c5f736d73223a302c226d6f64656c5f6d61696c223a302c22737461746f5f656c223a22656e61626c6564222c22737461746f5f736d73223a2264697361626c6564222c22737461746f5f6d61696c223a2264697361626c6564222c22737461746f5f6c696e6b223a2264697361626c6564222c2269636f6e5f736d73223a22696d672f6f6b2e706e67222c2269636f6e5f6d61696c223a22696d672f73746f702e706e67222c2269636f6e5f6c696e6b223a22696d672f73746f702e706e67227d2c7b22636873756d223a223733303232345f3230313331303331222c2264617469223a7b22696e74657374617a696f6e65223a2253747564696f206173736f636961746f2066696c69707075636369202620432e222c2274656c61696f223a227776777a7a7a376e7a444e303938333435222c226b6d223a22313230303030222c22636f6e7365676e61223a223230313331323130222c2264617461223a2233312d31302d3133222c226f7261223a2232333a3534227d2c2263686b5f656c223a312c2263686b5f736d73223a302c2263686b5f6d61696c223a302c2263686b5f6c696e6b223a302c2270686f6e65223a2233333331333233323932222c2261646472657373223a22222c226d6f64656c5f736d73223a2231222c226d6f64656c5f6d61696c223a302c22737461746f5f656c223a22656e61626c6564222c22737461746f5f736d73223a2264697361626c6564222c22737461746f5f6d61696c223a2264697361626c6564222c22737461746f5f6c696e6b223a2264697361626c6564222c2269636f6e5f736d73223a22696d672f6f6b2e706e67222c2269636f6e5f6d61696c223a22696d672f73746f702e706e67222c2269636f6e5f6c696e6b223a22696d672f73746f702e706e67227d5d);

-- --------------------------------------------------------

--
-- Struttura della tabella `test_app`
--

CREATE TABLE IF NOT EXISTS `test_app` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `odl` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `intestazione` tinytext COLLATE utf8_bin NOT NULL,
  `telaio` varchar(17) COLLATE utf8_bin NOT NULL,
  `km` int(11) NOT NULL,
  `consegna` varchar(8) COLLATE utf8_bin NOT NULL,
  `tel1` varchar(12) COLLATE utf8_bin NOT NULL,
  `tel2` varchar(12) COLLATE utf8_bin NOT NULL,
  `mail` tinytext COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

--
-- Dump dei dati per la tabella `test_app`
--

INSERT INTO `test_app` (`ID`, `odl`, `data`, `intestazione`, `telaio`, `km`, `consegna`, `tel1`, `tel2`, `mail`) VALUES
(1, 724356, '2013-10-31 11:46:13', 0x41646f6c662043756e6e696e6768616d, 'wvwzzz9nzab123456', 23476, '20100523', '0721282324', '3331323292', 0x746f6d657461406d61632e636f6d),
(2, 730224, '2013-10-31 23:54:54', 0x53747564696f206173736f636961746f2066696c69707075636369202620432e, 'wvwzzz7nzDN098345', 120000, '20131210', '03334567789', '3331323292', 0x6477667764776477),
(3, 345678, '2013-11-08 11:24:29', 0x736466736673667361666661666166, 'asdfghjklzxcvbnma', 3232, '', '', '', '');
