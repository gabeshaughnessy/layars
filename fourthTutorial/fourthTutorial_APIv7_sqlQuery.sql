-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 19, 2012 at 12:17 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.2-1ubuntu4.14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `xuandb`
--

-- --------------------------------------------------------

--
-- Table structure for table `Actions`
--

CREATE TABLE IF NOT EXISTS `Actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visionpoiID` varchar(255) CHARACTER SET latin1 NOT NULL,
  `uri` varchar(255) CHARACTER SET latin1 NOT NULL,
  `label` varchar(30) CHARACTER SET latin1 NOT NULL,
  `contentType` varchar(255) CHARACTER SET latin1 DEFAULT 'application/vnd.layar.internal',
  `method` enum('GET','POST') CHARACTER SET latin1 DEFAULT 'GET',
  `params` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `activityType` int(2) DEFAULT NULL,
  `autoTriggerOnly` tinyint(1) DEFAULT '0',
  `showActivity` tinyint(1) DEFAULT '1',
  `activityMessage` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `autoTrigger` tinyint(1) NOT NULL DEFAULT '0',
  `LayerID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visionpoiID` (`visionpoiID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `Actions`
--


-- --------------------------------------------------------

--
-- Table structure for table `Animation`
--

CREATE TABLE IF NOT EXISTS `Animation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event` enum('onCreate','onUpdate','onFocus','onClick','onDelete') NOT NULL,
  `type` enum('scale','translate','rotate') NOT NULL,
  `length` int(11) NOT NULL,
  `delay` int(11) DEFAULT '0',
  `interpolation` enum('linear','accelerateDecelerate','accelerate','decelerate','bounce','cycle','anticipateOvershoot','anticipate','overshoot') DEFAULT 'linear',
  `interpolationParam` decimal(10,2) DEFAULT NULL,
  `persist` tinyint(1) DEFAULT '0',
  `repeat` tinyint(1) DEFAULT '0',
  `from` decimal(10,2) DEFAULT NULL,
  `to` decimal(10,2) DEFAULT NULL,
  `axis_x` decimal(10,2) DEFAULT NULL,
  `axis_y` decimal(10,2) DEFAULT NULL,
  `axis_z` decimal(10,2) DEFAULT NULL,
  `visionpoiID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `Animation`
--

INSERT INTO `Animation` (`id`, `event`, `type`, `length`, `delay`, `interpolation`, `interpolationParam`, `persist`, `repeat`, `from`, `to`, `axis_x`, `axis_y`, `axis_z`, `visionpoiID`) VALUES
(1, 'onFocus', 'scale', 2000, 3000, 'bounce', '1.00', 0, 1, '0.20', '1.00', '1.00', '1.00', '1.00', NULL),
(2, 'onClick', 'rotate', 1000, 0, 'linear', NULL, 1, 1, '0.00', '360.00', '0.00', '0.00', '1.00', NULL),
(3, 'onClick', 'translate', 2000, 0, 'accelerateDecelerate', NULL, 1, 0, '0.00', '1.00', '-0.08', '0.08', '0.00', NULL),
(4, 'onCreate', 'translate', 3000, 0, 'linear', NULL, 0, 0, '1.00', '0.00', '-0.10', '0.00', '0.00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Layer`
--

CREATE TABLE IF NOT EXISTS `Layer` (
  `layer` varchar(255) NOT NULL,
  `refreshInterval` int(10) DEFAULT '300',
  `fullRefresh` tinyint(1) DEFAULT '1',
  `showMessage` varchar(255) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `layer` (`layer`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `Layer`
--

INSERT INTO `Layer` (`layer`, `refreshInterval`, `fullRefresh`, `showMessage`, `id`) VALUES
('visiontutorial', 300, 1, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `LayerAction`
--

CREATE TABLE IF NOT EXISTS `LayerAction` (
  `layerID` int(11) NOT NULL,
  `label` varchar(30) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `contentType` varchar(255) DEFAULT 'application/vnd.layar.internal',
  `method` enum('GET','POST') DEFAULT 'GET',
  `activityType` int(2) DEFAULT NULL,
  `params` varchar(255) DEFAULT NULL,
  `showActivity` tinyint(1) DEFAULT '1',
  `activityMessage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `layerID` (`layerID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `LayerAction`
--


-- --------------------------------------------------------

--
-- Table structure for table `Object`
--

CREATE TABLE IF NOT EXISTS `Object` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contentType` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `size` float(15,5) NOT NULL,
  `previewImage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `Object`
--

INSERT INTO `Object` (`id`, `contentType`, `url`, `size`, `previewImage`) VALUES
(1, 'model/vnd.layar.l3d', 'http://maomao.fixedpoint.nl/temp/layar_l3d/music.l3d', 0.50000, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Transform`
--

CREATE TABLE IF NOT EXISTS `Transform` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rel` tinyint(1) DEFAULT '0',
  `angle` decimal(5,2) DEFAULT '0.00',
  `rotate_x` decimal(2,1) DEFAULT '0.0',
  `rotate_y` decimal(2,1) DEFAULT '0.0',
  `rotate_z` decimal(2,1) DEFAULT '1.0',
  `translate_x` decimal(5,1) DEFAULT '0.0',
  `translate_y` decimal(5,1) DEFAULT '0.0',
  `translate_z` decimal(5,1) DEFAULT '0.0',
  `scale` decimal(12,2) NOT NULL DEFAULT '1.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `Transform`
--


-- --------------------------------------------------------

--
-- Table structure for table `VisionPoi`
--

CREATE TABLE IF NOT EXISTS `VisionPoi` (
  `id` varchar(255) NOT NULL,
  `objectID` int(11) DEFAULT NULL,
  `transformID` int(11) DEFAULT NULL,
  `referenceImage` varchar(255) NOT NULL,
  `layerID` int(11) NOT NULL,
  `animationId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `objectID` (`objectID`),
  KEY `transformID` (`transformID`),
  KEY `layerID` (`layerID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `VisionPoi`
--

INSERT INTO `VisionPoi` (`id`, `objectID`, `transformID`, `referenceImage`, `layerID`, `animationId`) VALUES
('vision_1', 1, 1, 'menu', 0, 1);
