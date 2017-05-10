ALTER TABLE `prefixes` ADD `PrefixVLAN` INT NOT NULL AFTER `PrefixState`;

CREATE TABLE `addresses` (
  `AddressID` int(11) NOT NULL,
  `Address` varbinary(16) NOT NULL,
  `AddressAFI` int(1) NOT NULL,
  `AddressState` tinyint(1) NOT NULL,
  `AddressName` varchar(255) NOT NULL,
  `AddressFQDN` varchar(255) NOT NULL,
  `AddressMAC` varchar(8) NOT NULL,
  `AddressTT` varchar(255) NOT NULL,
  `AddressDescription` longtext NOT NULL,
  `AddressPrefix` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;