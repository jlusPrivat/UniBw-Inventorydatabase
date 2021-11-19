-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 19, 2021 at 11:54 AM
-- Server version: 5.7.35-0ubuntu0.18.04.1
-- PHP Version: 8.0.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `id_groups`
--

CREATE TABLE `id_groups` (
  `GID` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `admin` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `id_inventories`
--

CREATE TABLE `id_inventories` (
  `IID` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `id_items`
--

CREATE TABLE `id_items` (
  `ITID` int(10) UNSIGNED NOT NULL,
  `IID` int(10) UNSIGNED NOT NULL,
  `barcode` int(30) NOT NULL,
  `description` varchar(300) NOT NULL,
  `qty` int(10) NOT NULL,
  `classification` varchar(300) NOT NULL,
  `manufacturer` varchar(300) NOT NULL,
  `stocktaking_date` date DEFAULT NULL,
  `added_date` date NOT NULL,
  `room` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `id_lendings`
--

CREATE TABLE `id_lendings` (
  `LID` int(15) UNSIGNED NOT NULL,
  `ITID` int(10) UNSIGNED NOT NULL,
  `UID_lender` int(10) UNSIGNED NOT NULL,
  `UID_receiver` int(10) UNSIGNED NOT NULL,
  `receiver_signed` tinyint(1) NOT NULL,
  `qty` int(5) NOT NULL,
  `lent` date NOT NULL,
  `returned` date NOT NULL,
  `due` date NOT NULL,
  `note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `id_match_gi`
--

CREATE TABLE `id_match_gi` (
  `GID` int(10) UNSIGNED NOT NULL,
  `IID` int(10) UNSIGNED NOT NULL,
  `view_items` tinyint(1) NOT NULL DEFAULT '0',
  `lend_from` tinyint(1) NOT NULL DEFAULT '0',
  `return_to` tinyint(1) NOT NULL DEFAULT '0',
  `add_delete_items` tinyint(1) NOT NULL DEFAULT '0',
  `view_users` tinyint(1) NOT NULL DEFAULT '0',
  `manage_users` tinyint(1) NOT NULL DEFAULT '0',
  `perform_stocktaking` tinyint(1) NOT NULL DEFAULT '0',
  `edit_items` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `id_match_ug`
--

CREATE TABLE `id_match_ug` (
  `UID` int(10) UNSIGNED NOT NULL,
  `GID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `id_users`
--

CREATE TABLE `id_users` (
  `UID` int(10) UNSIGNED NOT NULL,
  `RZID` varchar(14) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `email_verification` varchar(30) DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `id_groups`
--
ALTER TABLE `id_groups`
  ADD PRIMARY KEY (`GID`);

--
-- Indexes for table `id_inventories`
--
ALTER TABLE `id_inventories`
  ADD PRIMARY KEY (`IID`);

--
-- Indexes for table `id_items`
--
ALTER TABLE `id_items`
  ADD PRIMARY KEY (`ITID`),
  ADD KEY `IID` (`IID`);

--
-- Indexes for table `id_lendings`
--
ALTER TABLE `id_lendings`
  ADD PRIMARY KEY (`LID`),
  ADD KEY `ITID` (`ITID`),
  ADD KEY `UID_lender` (`UID_lender`),
  ADD KEY `UID_receiver` (`UID_receiver`);

--
-- Indexes for table `id_match_gi`
--
ALTER TABLE `id_match_gi`
  ADD PRIMARY KEY (`GID`,`IID`),
  ADD KEY `IID2` (`IID`);

--
-- Indexes for table `id_match_ug`
--
ALTER TABLE `id_match_ug`
  ADD PRIMARY KEY (`UID`,`GID`),
  ADD KEY `GID2` (`GID`);

--
-- Indexes for table `id_users`
--
ALTER TABLE `id_users`
  ADD PRIMARY KEY (`UID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `id_groups`
--
ALTER TABLE `id_groups`
  MODIFY `GID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `id_inventories`
--
ALTER TABLE `id_inventories`
  MODIFY `IID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `id_items`
--
ALTER TABLE `id_items`
  MODIFY `ITID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `id_lendings`
--
ALTER TABLE `id_lendings`
  MODIFY `LID` int(15) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `id_users`
--
ALTER TABLE `id_users`
  MODIFY `UID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `id_items`
--
ALTER TABLE `id_items`
  ADD CONSTRAINT `IID` FOREIGN KEY (`IID`) REFERENCES `id_inventories` (`IID`) ON UPDATE CASCADE;

--
-- Constraints for table `id_lendings`
--
ALTER TABLE `id_lendings`
  ADD CONSTRAINT `ITID` FOREIGN KEY (`ITID`) REFERENCES `id_items` (`ITID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `UID_lender` FOREIGN KEY (`UID_lender`) REFERENCES `id_users` (`UID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `UID_receiver` FOREIGN KEY (`UID_receiver`) REFERENCES `id_users` (`UID`) ON UPDATE CASCADE;

--
-- Constraints for table `id_match_gi`
--
ALTER TABLE `id_match_gi`
  ADD CONSTRAINT `GID` FOREIGN KEY (`GID`) REFERENCES `id_groups` (`GID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `IID2` FOREIGN KEY (`IID`) REFERENCES `id_inventories` (`IID`) ON UPDATE CASCADE;

--
-- Constraints for table `id_match_ug`
--
ALTER TABLE `id_match_ug`
  ADD CONSTRAINT `GID2` FOREIGN KEY (`GID`) REFERENCES `id_groups` (`GID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `UID` FOREIGN KEY (`UID`) REFERENCES `id_users` (`UID`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
