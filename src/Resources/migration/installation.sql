INSERT INTO `__prefix__Role` (`category`,`name`,`nameShort`,`description`,`type`,`canLoginRole`,`futureYearsLogin`,`pastYearsLogin`,`restriction`) VALUES
('Staff','Librarian','LIB','Library Staff','Core','Y','N','N','Admin Only');

CREATE TABLE `__prefix__Library` (
                                     `id` int(3) UNSIGNED AUTO_INCREMENT,
                                     `facility` int(10) UNSIGNED DEFAULT NULL,
                                     `department` int(4) UNSIGNED DEFAULT NULL,
                                     `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The library name should be unique.',
                                     `abbr` varchar(6) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The library Abbreviation should be unique.',
                                     `active` tinyint(1) NOT NULL,
                                     `lending_period` smallint(6) NOT NULL COMMENT 'Lending period default for this library in days.',
                                     `bg_colour` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'white',
                                     `bg_image` longblob,
                                     `borrow_limit` int(3) UNSIGNED DEFAULT NULL,
                                     PRIMARY KEY (`id`),
                                     UNIQUE KEY `name` (`name`),
                                     UNIQUE KEY `abbr` (`abbr`),
                                     KEY `facility` (`facility`),
                                     KEY `department` (`department`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `__prefix__LibraryItem` (
                                         `id` int(10)  UNSIGNED AUTO_INCREMENT,
                                         `library` int(3) UNSIGNED DEFAULT NULL,
                                         `created_by` int(10) UNSIGNED DEFAULT NULL,
                                         `item_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
                                         `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                                         `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name for book, model for computer, etc.',
                                         `producer` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Author for book, manufacturer for computer, etc',
                                         `vendor` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
                                         `fields` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
                                         `purchase_date` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                                         `invoice_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
                                         `image_type` varchar(4) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Type of image. Image should be 240px x 240px, or smaller.',
                                         `image_location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'URL or local FS path of image.',
                                         `comment` longtext COLLATE utf8_unicode_ci,
                                         `location_detail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                                         `ownership_type` varchar(12) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'School',
                                         `replacement` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                                         `replacement_cost` decimal(10,2) DEFAULT NULL,
                                         `physical_condition` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
                                         `bookable` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                                         `borrowable` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
                                         `status` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Available' COMMENT 'The current status of the item.',
                                         `timestamp_status` datetime DEFAULT NULL COMMENT 'The time the status was recorded(DC2Type:datetime_immutable)',
                                         `return_expected` date DEFAULT NULL COMMENT 'The time when the event expires.(DC2Type:date_immutable)',
                                         `created_on` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                                         `facility` int(10) UNSIGNED DEFAULT NULL,
                                         `person_ownership` int(10) UNSIGNED DEFAULT NULL,
                                         `department` int(4) UNSIGNED DEFAULT NULL,
                                         `replacement_date` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                                         `responsible_for_status` int(10) UNSIGNED DEFAULT NULL,
                                         `status_recorder` int(10) UNSIGNED NOT NULL,
                                          PRIMARY KEY (`id`) USING BTREE,
                                          UNIQUE KEY `identifier` (`identifier`),
                                          KEY `space` (`facility`),
                                          KEY `person_ownership` (`person_ownership`),
                                          KEY `department` (`department`),
                                          KEY `responsible_for_status` (`responsible_for_status`),
                                          KEY `item_type` (`item_type`),
                                          KEY `library` (`library`),
                                          KEY `created_by` (`created_by`) USING BTREE,
                                          KEY `status_recorder` (`status_recorder`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `__prefix__LibraryItemEvent` (
                                              `id` int(14) UNSIGNED AUTO_INCREMENT,
                                              `type` varchar(12) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Other' COMMENT 'This is maintained even after the item is returned, so we know what type of event it was.',
                                              `status` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Available',
                                              `timestamp_out` datetime DEFAULT NULL COMMENT 'The time the event was recorded(DC2Type:datetime_immutable)',
                                              `timestamp_in` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                                              `library_item` int(10) UNSIGNED DEFAULT NULL,
                                              `responsible_for_status` int(10) UNSIGNED DEFAULT NULL,
                                              `person_out` int(10) UNSIGNED DEFAULT NULL,
                                              `person_in` int(10) UNSIGNED DEFAULT NULL,
                                               PRIMARY KEY (`id`),
                                               KEY `person_out` (`person_out`),
                                               KEY `person_in` (`person_in`),
                                               KEY `item` (`library_item`),
                                               KEY `responsible_for_status` (`responsible_for_status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `__prefix__LibraryReturnAction` (
                                                 `id` int(10) UNSIGNED AUTO_INCREMENT,
                                                 `library_item` int(10) UNSIGNED DEFAULT NULL,
                                                 `return_action` varchar(16) COLLATE utf8_unicode_ci NOT NULL COMMENT 'What to do when the item is returned?',
                                                 `action_by` int(10) UNSIGNED NOT NULL,
                                                 `created_by` int(10) UNSIGNED NOT NULL,
                                                 `created_on` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                                                  PRIMARY KEY (`id`),
                                                  UNIQUE KEY `item` (`library_item`),
                                                  KEY `action_by` (`action_by`),
                                                  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
