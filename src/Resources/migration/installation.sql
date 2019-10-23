CREATE TABLE gibbonLibrary (
    id INT(3) UNSIGNED ZEROFILL AUTO_INCREMENT,
    facility INT(10) UNSIGNED ZEROFILL,
    department INT(4) UNSIGNED ZEROFILL,
    name VARCHAR(50) NOT NULL COMMENT 'The library name should be unique.',
    abbr VARCHAR(6) NOT NULL COMMENT 'The library Abbreviation should be unique.',
    active TINYINT(1) NOT NULL,
    lending_period SMALLINT NOT NULL COMMENT 'Lending period default for this library in days.',
    INDEX IDX_A14FB50BCD1DE18A (department),
    INDEX facility (facility),
    UNIQUE INDEX name (name),
    UNIQUE INDEX abbr (abbr),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1;
CREATE TABLE `gibbonLibraryItem` (
   `gibbonLibraryItemID` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
   `library` int(3) UNSIGNED ZEROFILL DEFAULT NULL,
   `created_by` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
   `item_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
   `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
   `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name for book, model for computer, etc.',
   `producer` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Author for book, manufacturer for computer, etc',
   `vendor` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
   `fields` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
   `purchaseDate` date DEFAULT NULL COMMENT '	(DC2Type:date_immutable)',
   `invoiceNumber` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
   `imageType` varchar(4) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Type of image. Image should be 240px x 240px, or smaller.',
   `imageLocation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'URL or local FS path of image.',
   `comment` longtext COLLATE utf8_unicode_ci,
   `locationDetail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   `ownershipType` varchar(12) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'School',
   `replacement` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
   `replacementCost` decimal(10,2) DEFAULT NULL,
   `physicalCondition` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
   `bookable` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
   `borrowable` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
   `status` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Available' COMMENT 'The current status of the item.',
   `timestampStatus` datetime DEFAULT NULL COMMENT 'The time the status was recorded. 	(DC2Type:datetime_immutable)',
   `returnExpected` date DEFAULT NULL COMMENT 'The time when the event expires. 	(DC2Type:date_immutable)',
   `returnAction` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'What to do when the item is returned?',
   `created_on` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
   `gibbonSpaceID` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
   `gibbonPersonIDOwnership` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
   `gibbonDepartmentID` int(4) UNSIGNED ZEROFILL DEFAULT NULL,
   `gibbonSchoolYearIDReplacement` int(3) UNSIGNED ZEROFILL DEFAULT NULL,
   `gibbonPersonIDStatusResponsible` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
   `gibbonPersonIDStatusRecorder` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
   `gibbonPersonIDReturnAction` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
   PRIMARY KEY (`gibbonLibraryItemID`),
   UNIQUE KEY `id` (`id`),
   KEY `IDX_7D8DF16ED8D64BA0` (`gibbonSpaceID`),
   KEY `IDX_7D8DF16EC4597887` (`gibbonPersonIDOwnership`),
   KEY `IDX_7D8DF16E6DFE7E92` (`gibbonDepartmentID`),
   KEY `IDX_7D8DF16E2797629C` (`gibbonSchoolYearIDReplacement`),
   KEY `IDX_7D8DF16EE0330702` (`gibbonPersonIDStatusResponsible`),
   KEY `IDX_7D8DF16ECCCD7B64` (`gibbonPersonIDStatusRecorder`),
   KEY `IDX_7D8DF16EFA1AE1AB` (`gibbonPersonIDReturnAction`),
   KEY `IDX_7D8DF16EDE12AB56` (`created_by`),
   KEY `item_type` (`item_type`),
   KEY `library` (`library`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1;
CREATE TABLE `gibbonLibraryItemEvent` (
    `gibbonLibraryItemEventID` int(14) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `type` varchar(12) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Other' COMMENT 'This is maintained even after the item is returned, so we know what type of event it was.',
    `status` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Available',
    `timestampOut` datetime DEFAULT NULL COMMENT 'The time the event was recorded (DC2Type:datetime_immutable)',
    `returnExpected` date DEFAULT NULL COMMENT 'The time when the event expires. (DC2Type:date_immutable)',
    `returnAction` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'What to do when the item is returned? (DC2Type:datetime_immutable)',
    `timestampReturn` datetime DEFAULT NULL,
    `gibbonLibraryItemID` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
    `gibbonPersonIDStatusResponsible` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
    `gibbonPersonIDOut` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
    `gibbonPersonIDReturnAction` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
    `gibbonPersonIDIn` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
    PRIMARY KEY (`gibbonLibraryItemEventID`),
    KEY `IDX_91CB122520179A14` (`gibbonLibraryItemID`),
    KEY `IDX_91CB1225E0330702` (`gibbonPersonIDStatusResponsible`),
    KEY `IDX_91CB1225D2C06C05` (`gibbonPersonIDOut`),
    KEY `IDX_91CB1225FA1AE1AB` (`gibbonPersonIDReturnAction`),
    KEY `IDX_91CB12251EBCE61E` (`gibbonPersonIDIn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT = 1;
ALTER TABLE gibbonLibrary
    ADD CONSTRAINT FK_A14FB50B105994B2 FOREIGN KEY (facility) REFERENCES gibbonSpace (gibbonSpaceID),
    ADD CONSTRAINT FK_A14FB50BCD1DE18A FOREIGN KEY (department) REFERENCES gibbonDepartment (gibbonDepartmentID);
ALTER TABLE `gibbonLibraryItem`
    ADD CONSTRAINT `FK_7D8DF16E2797629C` FOREIGN KEY (`gibbonSchoolYearIDReplacement`) REFERENCES `gibbonschoolyear` (`gibbonSchoolYearID`),
    ADD CONSTRAINT `FK_7D8DF16E6DFE7E92` FOREIGN KEY (`gibbonDepartmentID`) REFERENCES `gibbondepartment` (`gibbonDepartmentID`),
    ADD CONSTRAINT `FK_7D8DF16EA18098BC` FOREIGN KEY (`library`) REFERENCES `gibbonlibrary` (`id`),
    ADD CONSTRAINT `FK_7D8DF16EC4597887` FOREIGN KEY (`gibbonPersonIDOwnership`) REFERENCES `gibbonperson` (`gibbonPersonID`),
    ADD CONSTRAINT `FK_7D8DF16ECCCD7B64` FOREIGN KEY (`gibbonPersonIDStatusRecorder`) REFERENCES `gibbonperson` (`gibbonPersonID`),
    ADD CONSTRAINT `FK_7D8DF16ED8D64BA0` FOREIGN KEY (`gibbonSpaceID`) REFERENCES `gibbonspace` (`gibbonSpaceID`),
    ADD CONSTRAINT `FK_7D8DF16EDE12AB56` FOREIGN KEY (`created_by`) REFERENCES `gibbonperson` (`gibbonPersonID`),
    ADD CONSTRAINT `FK_7D8DF16EE0330702` FOREIGN KEY (`gibbonPersonIDStatusResponsible`) REFERENCES `gibbonperson` (`gibbonPersonID`),
    ADD CONSTRAINT `FK_7D8DF16EFA1AE1AB` FOREIGN KEY (`gibbonPersonIDReturnAction`) REFERENCES `gibbonperson` (`gibbonPersonID`);
ALTER TABLE `gibbonLibraryItemEvent`
    ADD CONSTRAINT `FK_91CB12251EBCE61E` FOREIGN KEY (`gibbonPersonIDIn`) REFERENCES `gibbonperson` (`gibbonPersonID`),
    ADD CONSTRAINT `FK_91CB122520179A14` FOREIGN KEY (`gibbonLibraryItemID`) REFERENCES `gibbonlibraryitem` (`gibbonLibraryItemID`),
    ADD CONSTRAINT `FK_91CB1225D2C06C05` FOREIGN KEY (`gibbonPersonIDOut`) REFERENCES `gibbonperson` (`gibbonPersonID`),
    ADD CONSTRAINT `FK_91CB1225E0330702` FOREIGN KEY (`gibbonPersonIDStatusResponsible`) REFERENCES `gibbonperson` (`gibbonPersonID`),
    ADD CONSTRAINT `FK_91CB1225FA1AE1AB` FOREIGN KEY (`gibbonPersonIDReturnAction`) REFERENCES `gibbonperson` (`gibbonPersonID`);
