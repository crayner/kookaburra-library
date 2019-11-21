CREATE TABLE `__prefix__Library` (
                                     `id` int(3) UNSIGNED ZEROFILL NOT NULL,
                                     `facility_id` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
                                     `department_id` int(4) UNSIGNED ZEROFILL DEFAULT NULL,
                                     `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The library name should be unique.',
                                     `abbr` varchar(6) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The library Abbreviation should be unique.',
                                     `active` tinyint(1) NOT NULL,
                                     `lending_period` smallint(6) NOT NULL COMMENT 'Lending period default for this library in days.',
                                     `bg_colour` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'white',
                                     `bg_image` longblob,
                                     `borrow_limit` int(3) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `__prefix__LibraryItem`
--

CREATE TABLE `__prefix__LibraryItem` (
                                         `id` int(10) UNSIGNED ZEROFILL NOT NULL,
                                         `library_id` int(3) UNSIGNED ZEROFILL DEFAULT NULL,
                                         `created_by` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
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
                                         `facility_id` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
                                         `person_ownership` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
                                         `department_id` int(4) UNSIGNED ZEROFILL DEFAULT NULL,
                                         `replacement_year` int(3) UNSIGNED ZEROFILL DEFAULT NULL,
                                         `responsible_for_status` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
                                         `status_recorder` int(10) UNSIGNED ZEROFILL NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `__prefix__LibraryItemEvent`
--

CREATE TABLE `__prefix__LibraryItemEvent` (
                                              `id` int(14) UNSIGNED ZEROFILL NOT NULL,
                                              `type` varchar(12) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Other' COMMENT 'This is maintained even after the item is returned, so we know what type of event it was.',
                                              `status` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Available',
                                              `timestamp_out` datetime DEFAULT NULL COMMENT 'The time the event was recorded(DC2Type:datetime_immutable)',
                                              `timestamp_in` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                                              `library_item_id` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
                                              `responsible_for_status` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
                                              `person_out` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
                                              `person_in` int(10) UNSIGNED ZEROFILL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `__prefix__LibraryReturnAction`
--

CREATE TABLE `__prefix__LibraryReturnAction` (
                                                 `id` int(10) UNSIGNED ZEROFILL NOT NULL,
                                                 `library_item_id` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
                                                 `return_action` varchar(16) COLLATE utf8_unicode_ci NOT NULL COMMENT 'What to do when the item is returned?',
                                                 `action_by` int(10) UNSIGNED ZEROFILL NOT NULL,
                                                 `created_by` int(10) UNSIGNED ZEROFILL NOT NULL,
                                                 `created_on` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `__prefix__Library`
--
ALTER TABLE `__prefix__Library`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `name` (`name`),
    ADD UNIQUE KEY `abbr` (`abbr`),
    ADD KEY `facility` (`facility_id`),
    ADD KEY `department` (`department_id`);

--
-- Indexes for table `__prefix__LibraryItem`
--
ALTER TABLE `__prefix__LibraryItem`
    ADD PRIMARY KEY (`id`) USING BTREE,
    ADD UNIQUE KEY `identifier` (`identifier`),
    ADD KEY `space` (`facility_id`),
    ADD KEY `person_ownership` (`person_ownership`),
    ADD KEY `department` (`department_id`),
    ADD KEY `replacement_year` (`replacement_year`),
    ADD KEY `responsible_for_status` (`responsible_for_status`),
    ADD KEY `item_type` (`item_type`),
    ADD KEY `library` (`library_id`),
    ADD KEY `created_by` (`created_by`) USING BTREE,
    ADD KEY `status_recorder` (`status_recorder`);

--
-- Indexes for table `__prefix__LibraryItemEvent`
--
ALTER TABLE `__prefix__LibraryItemEvent`
    ADD PRIMARY KEY (`id`),
    ADD KEY `person_out` (`person_out`),
    ADD KEY `person_in` (`person_in`),
    ADD KEY `item` (`library_item_id`),
    ADD KEY `responsible_for_status` (`responsible_for_status`) USING BTREE;

--
-- Indexes for table `__prefix__LibraryReturnAction`
--
ALTER TABLE `__prefix__LibraryReturnAction`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `item` (`library_item_id`),
    ADD KEY `action_by` (`action_by`),
    ADD KEY `created_by` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `__prefix__Library`
--
ALTER TABLE `__prefix__Library`
    MODIFY `id` int(3) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `__prefix__LibraryItem`
--
ALTER TABLE `__prefix__LibraryItem`
    MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `__prefix__LibraryItemEvent`
--
ALTER TABLE `__prefix__LibraryItemEvent`
    MODIFY `id` int(14) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `__prefix__LibraryReturnAction`
--
ALTER TABLE `__prefix__LibraryReturnAction`
    MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `__prefix__Library`
--
ALTER TABLE `__prefix__Library`
    ADD CONSTRAINT `FK_A14FB50B105994B2` FOREIGN KEY (`facility_id`) REFERENCES `gibbonSpace` (`gibbonSpaceID`),
    ADD CONSTRAINT `FK_A14FB50BCD1DE18A` FOREIGN KEY (`department_id`) REFERENCES `gibbonDepartment` (`gibbonDepartmentID`);

--
-- Constraints for table `__prefix__LibraryItem`
--
ALTER TABLE `__prefix__LibraryItem`
    ADD CONSTRAINT `FK_7D8DF16E2797629C` FOREIGN KEY (`replacement_year`) REFERENCES `gibbonSchoolyear` (`gibbonSchoolYearID`),
    ADD CONSTRAINT `FK_7D8DF16E6DFE7E92` FOREIGN KEY (`department_id`) REFERENCES `gibbonDepartment` (`gibbonDepartmentID`),
    ADD CONSTRAINT `FK_7D8DF16E7268946F` FOREIGN KEY (`status_recorder`) REFERENCES `gibbonPerson` (`gibbonPersonID`),
    ADD CONSTRAINT `FK_7D8DF16EA18098BC` FOREIGN KEY (`library_id`) REFERENCES `__prefix__Library` (`id`),
    ADD CONSTRAINT `FK_7D8DF16EC4597887` FOREIGN KEY (`person_ownership`) REFERENCES `gibbonPerson` (`gibbonPersonID`),
    ADD CONSTRAINT `FK_7D8DF16ED8D64BA0` FOREIGN KEY (`facility_id`) REFERENCES `gibbonSpace` (`gibbonSpaceID`),
    ADD CONSTRAINT `FK_7D8DF16EDE12AB56` FOREIGN KEY (`created_by`) REFERENCES `gibbonPerson` (`gibbonPersonID`),
    ADD CONSTRAINT `FK_7D8DF16EE0330702` FOREIGN KEY (`responsible_for_status`) REFERENCES `gibbonPerson` (`gibbonPersonID`);

--
-- Constraints for table `__prefix__LibraryItemEvent`
--
ALTER TABLE `__prefix__LibraryItemEvent`
    ADD CONSTRAINT `FK_91CB12251EBCE61E` FOREIGN KEY (`person_in`) REFERENCES `gibbonPerson` (`gibbonPersonID`),
    ADD CONSTRAINT `FK_91CB122520179A14` FOREIGN KEY (`library_item_id`) REFERENCES `__prefix__LibraryItem` (`id`),
    ADD CONSTRAINT `FK_91CB1225D2C06C05` FOREIGN KEY (`person_out`) REFERENCES `gibbonPerson` (`gibbonPersonID`),
    ADD CONSTRAINT `FK_91CB1225E0330702` FOREIGN KEY (`responsible_for_status`) REFERENCES `gibbonPerson` (`gibbonPersonID`);

--
-- Constraints for table `__prefix__LibraryReturnAction`
--
ALTER TABLE `__prefix__LibraryReturnAction`
    ADD CONSTRAINT `FK_C8FEAEE11DC04527` FOREIGN KEY (`action_by`) REFERENCES `gibbonPerson` (`gibbonPersonID`),
    ADD CONSTRAINT `FK_C8FEAEE11F1B251E` FOREIGN KEY (`library_item_id`) REFERENCES `__prefix__LibraryItem` (`id`),
    ADD CONSTRAINT `FK_C8FEAEE1DE12AB56` FOREIGN KEY (`created_by`) REFERENCES `gibbonPerson` (`gibbonPersonID`);

INSERT INTO `__prefix__Library` (`active`, `name`, `abbr`, `lending_period`) VALUES (1, 'General Library', 'GEN', 14);