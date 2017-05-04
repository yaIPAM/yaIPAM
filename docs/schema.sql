-- phpMyAdmin SQL Dump
-- version 4.6.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 04, 2017 at 11:43 AM
-- Server version: 5.5.53-MariaDB
-- PHP Version: 7.0.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yaipam`
--

-- --------------------------------------------------------

--
-- Table structure for table `otv`
--

CREATE TABLE `otv` (
  `OTVID` int(11) NOT NULL,
  `OTVName` varchar(255) NOT NULL,
  `OTVDescription` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `otv_domains`
--

CREATE TABLE `otv_domains` (
  `OTVID` int(11) NOT NULL,
  `DomainID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `prefixes`
--

CREATE TABLE `prefixes` (
  `PrefixID` int(11) NOT NULL,
  `Prefix` varbinary(16) NOT NULL,
  `PrefixLength` int(3) NOT NULL,
  `AFI` int(1) NOT NULL,
  `RangeFrom` varchar(255) NOT NULL,
  `RangeTo` varchar(255) NOT NULL,
  `PrefixDescription` varchar(255) NOT NULL,
  `ParentID` int(11) NOT NULL,
  `MasterVRF` int(11) NOT NULL,
  `PrefixState` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `prefixes_vrfs`
--

CREATE TABLE `prefixes_vrfs` (
  `PrefixID` int(11) NOT NULL,
  `VRFID` int(11) NOT NULL,
  `ParentID` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vlans`
--

CREATE TABLE `vlans` (
  `ID` int(11) NOT NULL,
  `VlanID` int(11) NOT NULL,
  `VlanName` varchar(32) NOT NULL,
  `VlanDomain` int(11) NOT NULL,
  `OTVDomain` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vlan_domains`
--

CREATE TABLE `vlan_domains` (
  `domain_id` int(11) NOT NULL,
  `domain_name` varchar(255) NOT NULL,
  `domain_description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vrfs`
--

CREATE TABLE `vrfs` (
  `VRFID` int(11) NOT NULL,
  `VRFName` varchar(255) NOT NULL,
  `VRFDescription` varchar(255) NOT NULL,
  `VRFRD` varchar(255) NOT NULL,
  `VRFRT` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `otv`
--
ALTER TABLE `otv`
  ADD PRIMARY KEY (`OTVID`);

--
-- Indexes for table `otv_domains`
--
ALTER TABLE `otv_domains`
  ADD UNIQUE KEY `OTVID` (`OTVID`,`DomainID`);

--
-- Indexes for table `prefixes`
--
ALTER TABLE `prefixes`
  ADD PRIMARY KEY (`PrefixID`);

--
-- Indexes for table `prefixes_vrfs`
--
ALTER TABLE `prefixes_vrfs`
  ADD PRIMARY KEY (`PrefixID`,`VRFID`);

--
-- Indexes for table `vlans`
--
ALTER TABLE `vlans`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `vlan_domains`
--
ALTER TABLE `vlan_domains`
  ADD PRIMARY KEY (`domain_id`);

--
-- Indexes for table `vrfs`
--
ALTER TABLE `vrfs`
  ADD PRIMARY KEY (`VRFID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `otv`
--
ALTER TABLE `otv`
  MODIFY `OTVID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `prefixes`
--
ALTER TABLE `prefixes`
  MODIFY `PrefixID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;
--
-- AUTO_INCREMENT for table `vlans`
--
ALTER TABLE `vlans`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `vlan_domains`
--
ALTER TABLE `vlan_domains`
  MODIFY `domain_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `vrfs`
--
ALTER TABLE `vrfs`
  MODIFY `VRFID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
