CREATE TABLE gibbonLibrary (id INT(3) UNSIGNED ZEROFILL AUTO_INCREMENT, facility INT(10) UNSIGNED ZEROFILL, department INT(4) UNSIGNED ZEROFILL, name VARCHAR(50) NOT NULL COMMENT 'The library name should be unique.', abbr VARCHAR(6) NOT NULL COMMENT 'The library Abbreviation should be unique.', active TINYINT(1) NOT NULL, lending_period SMALLINT NOT NULL COMMENT 'Lending period default for this library in days.', INDEX IDX_A14FB50BCD1DE18A (department), INDEX facility (facility), UNIQUE INDEX name (name), UNIQUE INDEX abbr (abbr), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1;
CREATE TABLE gibbonLibraryItem (gibbonLibraryItemID INT(10) UNSIGNED ZEROFILL AUTO_INCREMENT, library INT(3) UNSIGNED ZEROFILL, item_type VARCHAR(32) NOT NULL, id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL COMMENT 'Name for book, model for computer, etc.', producer VARCHAR(255) NOT NULL COMMENT 'Author for book, manufacturer for computer, etc', vendor VARCHAR(100) DEFAULT NULL, fields LONGTEXT NOT NULL COMMENT '(DC2Type:array)', purchaseDate DATE DEFAULT NULL, invoiceNumber VARCHAR(50) DEFAULT NULL, imageType VARCHAR(4) NOT NULL COMMENT 'Type of image. Image should be 240px x 240px, or smaller.', imageLocation VARCHAR(255) DEFAULT NULL COMMENT 'URL or local FS path of image.', comment LONGTEXT NOT NULL, locationDetail VARCHAR(255) NOT NULL, ownershipType VARCHAR(12) DEFAULT 'School' NOT NULL, replacement VARCHAR(1) DEFAULT 'Y' NOT NULL, replacementCost NUMERIC(10, 2) DEFAULT NULL, physicalCondition VARCHAR(16) NOT NULL, bookable VARCHAR(1) DEFAULT 'N' NOT NULL, borrowable VARCHAR(1) DEFAULT 'Y' NOT NULL, status VARCHAR(16) DEFAULT 'Available' NOT NULL COMMENT 'The current status of the item.', timestampStatus DATETIME DEFAULT NULL COMMENT 'The time the status was recorded', returnExpected DATE DEFAULT NULL COMMENT 'The time when the event expires.', returnAction VARCHAR(16) DEFAULT NULL COMMENT 'What to do when the item is returned?', gibbonSpaceID INT(10) UNSIGNED ZEROFILL, gibbonPersonIDOwnership INT(10) UNSIGNED ZEROFILL, gibbonDepartmentID INT(4) UNSIGNED ZEROFILL, gibbonSchoolYearIDReplacement INT(3) UNSIGNED ZEROFILL, gibbonPersonIDStatusResponsible INT(10) UNSIGNED ZEROFILL, gibbonPersonIDStatusRecorder INT(10) UNSIGNED ZEROFILL, gibbonPersonIDReturnAction INT(10) UNSIGNED ZEROFILL, INDEX IDX_7D8DF16EA18098BC (library), INDEX IDX_7D8DF16ED8D64BA0 (gibbonSpaceID), INDEX IDX_7D8DF16EC4597887 (gibbonPersonIDOwnership), INDEX IDX_7D8DF16E6DFE7E92 (gibbonDepartmentID), INDEX IDX_7D8DF16E2797629C (gibbonSchoolYearIDReplacement), INDEX IDX_7D8DF16EE0330702 (gibbonPersonIDStatusResponsible), INDEX IDX_7D8DF16ECCCD7B64 (gibbonPersonIDStatusRecorder), INDEX IDX_7D8DF16EFA1AE1AB (gibbonPersonIDReturnAction), INDEX item_type (item_type), UNIQUE INDEX id (id), PRIMARY KEY(gibbonLibraryItemID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1;
CREATE TABLE gibbonLibraryItemEvent (gibbonLibraryItemEventID INT(14) UNSIGNED ZEROFILL AUTO_INCREMENT, type VARCHAR(12) DEFAULT 'Other' NOT NULL COMMENT 'This is maintained even after the item is returned, so we know what type of event it was.', status VARCHAR(16) DEFAULT 'Available' NOT NULL, timestampOut DATETIME DEFAULT NULL COMMENT 'The time the event was recorded', returnExpected DATE DEFAULT NULL COMMENT 'The time when the event expires.', returnAction VARCHAR(16) DEFAULT NULL COMMENT 'What to do when the item is returned?', timestampReturn DATETIME DEFAULT NULL, gibbonLibraryItemID INT(10) UNSIGNED ZEROFILL, gibbonPersonIDStatusResponsible INT(10) UNSIGNED ZEROFILL, gibbonPersonIDOut INT(10) UNSIGNED ZEROFILL, gibbonPersonIDReturnAction INT(10) UNSIGNED ZEROFILL, gibbonPersonIDIn INT(10) UNSIGNED ZEROFILL, INDEX IDX_91CB122520179A14 (gibbonLibraryItemID), INDEX IDX_91CB1225E0330702 (gibbonPersonIDStatusResponsible), INDEX IDX_91CB1225D2C06C05 (gibbonPersonIDOut), INDEX IDX_91CB1225FA1AE1AB (gibbonPersonIDReturnAction), INDEX IDX_91CB12251EBCE61E (gibbonPersonIDIn), PRIMARY KEY(gibbonLibraryItemEventID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB AUTO_INCREMENT = 1;
ALTER TABLE gibbonLibrary ADD CONSTRAINT FK_A14FB50B105994B2 FOREIGN KEY (facility) REFERENCES gibbonSpace (gibbonSpaceID);
ALTER TABLE gibbonLibrary ADD CONSTRAINT FK_A14FB50BCD1DE18A FOREIGN KEY (department) REFERENCES gibbonDepartment (gibbonDepartmentID);
ALTER TABLE gibbonLibraryItem ADD CONSTRAINT FK_7D8DF16EA18098BC FOREIGN KEY (library) REFERENCES gibbonLibrary (id);
ALTER TABLE gibbonLibraryItem ADD CONSTRAINT FK_7D8DF16ED8D64BA0 FOREIGN KEY (gibbonSpaceID) REFERENCES gibbonSpace (gibbonSpaceID);
ALTER TABLE gibbonLibraryItem ADD CONSTRAINT FK_7D8DF16EC4597887 FOREIGN KEY (gibbonPersonIDOwnership) REFERENCES gibbonPerson (gibbonPersonID);
ALTER TABLE gibbonLibraryItem ADD CONSTRAINT FK_7D8DF16E6DFE7E92 FOREIGN KEY (gibbonDepartmentID) REFERENCES gibbonDepartment (gibbonDepartmentID);
ALTER TABLE gibbonLibraryItem ADD CONSTRAINT FK_7D8DF16E2797629C FOREIGN KEY (gibbonSchoolYearIDReplacement) REFERENCES gibbonSchoolYear (gibbonSchoolYearID);
ALTER TABLE gibbonLibraryItem ADD CONSTRAINT FK_7D8DF16EE0330702 FOREIGN KEY (gibbonPersonIDStatusResponsible) REFERENCES gibbonPerson (gibbonPersonID);
ALTER TABLE gibbonLibraryItem ADD CONSTRAINT FK_7D8DF16ECCCD7B64 FOREIGN KEY (gibbonPersonIDStatusRecorder) REFERENCES gibbonPerson (gibbonPersonID);
ALTER TABLE gibbonLibraryItem ADD CONSTRAINT FK_7D8DF16EFA1AE1AB FOREIGN KEY (gibbonPersonIDReturnAction) REFERENCES gibbonPerson (gibbonPersonID);
ALTER TABLE gibbonLibraryItemEvent ADD CONSTRAINT FK_91CB122520179A14 FOREIGN KEY (gibbonLibraryItemID) REFERENCES gibbonLibraryItem (gibbonLibraryItemID);
ALTER TABLE gibbonLibraryItemEvent ADD CONSTRAINT FK_91CB1225E0330702 FOREIGN KEY (gibbonPersonIDStatusResponsible) REFERENCES gibbonPerson (gibbonPersonID);
ALTER TABLE gibbonLibraryItemEvent ADD CONSTRAINT FK_91CB1225D2C06C05 FOREIGN KEY (gibbonPersonIDOut) REFERENCES gibbonPerson (gibbonPersonID);
ALTER TABLE gibbonLibraryItemEvent ADD CONSTRAINT FK_91CB1225FA1AE1AB FOREIGN KEY (gibbonPersonIDReturnAction) REFERENCES gibbonPerson (gibbonPersonID);
ALTER TABLE gibbonLibraryItemEvent ADD CONSTRAINT FK_91CB12251EBCE61E FOREIGN KEY (gibbonPersonIDIn) REFERENCES gibbonPerson (gibbonPersonID);