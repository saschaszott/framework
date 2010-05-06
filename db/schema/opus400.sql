SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `opus400` 
DEFAULT CHARACTER SET = utf8 
DEFAULT COLLATE = utf8_general_ci;
USE `opus400`;

-- -----------------------------------------------------
-- Table to store schema versioning information
-- -----------------------------------------------------
DROP TABLE IF EXISTS `schema_version`;
CREATE TABLE `schema_version` (
    `last_changed_date` VARCHAR(100) ,
    `revision` VARCHAR(20) ,
    `author` VARCHAR(100)
)
ENGINE = InnoDB
COMMENT = 'Holds revision information from subversion properties.';
-- -----------------------------------------------------
-- Insert revision information
-- 
-- The values are generated through svn checkin.
-- Do not edit here.
-- -----------------------------------------------------
INSERT INTO `schema_version` (last_changed_date, revision, author) VALUES ('$LastChangedDate$', '$Rev$', '$Author$');

-- -----------------------------------------------------
-- Table `documents`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `range_id` INT NULL COMMENT 'Foreign key: ?.? .' ,
  `completed_date` VARCHAR(50) NULL COMMENT 'Date of completion of the publication.' ,
  `completed_year` YEAR NOT NULL COMMENT 'Year of completion of the publication, if the \"completeted_date\" (exact date) is unknown.' ,
  `contributing_corporation` TEXT NULL COMMENT 'Contribution corporate body.' ,
  `creating_corporation` TEXT NULL COMMENT 'Creating corporate body.' ,
  `date_accepted` VARCHAR(50) NULL COMMENT 'Date of final exam (date of the doctoral graduation).' ,
  `type` VARCHAR(100) NOT NULL COMMENT 'Document type.' ,
  `edition` VARCHAR(25) NULL COMMENT 'Edition.' ,
  `issue` VARCHAR(25) NULL COMMENT 'Issue.' ,
  `language` VARCHAR(255) NULL COMMENT 'Language(s) of the document.' ,
  `non_institute_affiliation` TEXT NULL COMMENT 'Institutions, which are not officialy part of the university.' ,
  `page_first` INT NULL COMMENT 'First page of a publication.' ,
  `page_last` INT NULL COMMENT 'Last page of a pbulication.' ,
  `page_number` INT NULL COMMENT 'Total page numbers.' ,
  `publication_version` ENUM('draft', 'accepted', 'submitted', 'published', 'updated') NOT NULL COMMENT 'Version of publication.' ,
  `published_date` VARCHAR(50) NULL COMMENT 'Exact date of publication. Could differ from \"server_date_published\".' ,
  `published_year` YEAR NULL COMMENT 'Year of the publication, if the \"published_date\" (exact date) is unknown.  Could differ from \"server_date_published\".' ,
  `publisher_name` VARCHAR(255) NOT NULL COMMENT 'Name of an external publisher' ,
  `publisher_place` VARCHAR(255) NULL COMMENT 'City/State of extern. publisher' ,
  `publisher_university` VARCHAR(255) NULL COMMENT 'Name of ext. publishing university' ,
  `reviewed` ENUM('peer', 'editorial', 'open') NOT NULL COMMENT 'Style of the review process.' ,
  `server_date_modified` VARCHAR(50) NULL COMMENT 'Last modification of the document (is generated by the system).' ,
  `server_date_published` VARCHAR(50) NOT NULL COMMENT 'Date of publication on the repository (is generated by the system).' ,
  `server_date_unlocking` VARCHAR(50) NULL COMMENT 'Expiration date of a embargo.' ,
  `server_date_valid` VARCHAR(50) NULL COMMENT 'Expiration date of the validity of the document.' ,
  `server_state` ENUM('published', 'unpublished','deleted') NOT NULL COMMENT 'Status of publication process in the repository.' ,
  `source` VARCHAR(255) NULL COMMENT 'Bibliographic date from OPUS 3.x (formerly in OPUS 3.x \"source_text\").' ,
  `volume` VARCHAR(25) NULL COMMENT 'Volume.' ,
  `workflow` VARCHAR(100) NOT NULL DEFAULT 'repository' COMMENT 'Document publication workflow.' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'Document related data (monolingual, unreproducible colums).';


-- -----------------------------------------------------
-- Table `document_identifiers`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `document_identifiers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `document_id` INT UNSIGNED NOT NULL COMMENT 'Foreign key to: documents.documents_id.' ,
  `type` ENUM('doi', 'handle', 'urn', 'std-doi', 'url', 'cris-link', 'splash-url', 'isbn', 'issn', 'opus3-id', 'opac-id', 'uuid') NOT NULL COMMENT 'Type of the identifier.' ,
  `value` TEXT NOT NULL COMMENT 'Value of the identifier.' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_document_identifiers_documents` (`document_id` ASC) ,
  CONSTRAINT `fk_document_identifiers_documents`
    FOREIGN KEY (`document_id` )
    REFERENCES `documents` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Table for identifiers  related to the document.';

-- -----------------------------------------------------
-- Table `document_files`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `document_files` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `document_id` INT UNSIGNED NOT NULL COMMENT 'Foreign key to: documents.documents_id.' ,
  `path_name` TEXT NOT NULL COMMENT 'File and path name.' ,
  `sort_order` TINYINT(4) NOT NULL COMMENT 'Order of the files.' ,
  `label` TEXT NOT NULL COMMENT 'Display text of the file.' ,
  `file_type` VARCHAR(255) NOT NULL COMMENT 'Filetype according to dublin core.' ,
  `mime_type` VARCHAR(255) NOT NULL COMMENT 'Mime type of the file.' ,
  `language` VARCHAR(3) NULL COMMENT 'Language of the file.' ,
  `file_size` BIGINT UNSIGNED NOT NULL COMMENT 'File size in bytes.',
  PRIMARY KEY (`id`) ,
  INDEX `fk_document_files_documents` (`document_id` ASC) ,
  CONSTRAINT `fk_document_files_documents`
    FOREIGN KEY (`document_id` )
    REFERENCES `documents` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Table for file related data.';


-- -----------------------------------------------------
-- Table `file_hashvalues`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `file_hashvalues` (
  `file_id` INT UNSIGNED NOT NULL ,
  `type` VARCHAR(50) NOT NULL COMMENT 'Type of the hash value.' ,
  `value` TEXT NOT NULL COMMENT 'Hash value.' ,
  PRIMARY KEY (`type`, `file_id`) ,
  INDEX `fk_file_hashvalues_document_files` (`file_id` ASC) ,
  CONSTRAINT `fk_file_hashvalues_document_files`
    FOREIGN KEY (`file_id` )
    REFERENCES `document_files` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Table for hash values.';


-- -----------------------------------------------------
-- Table `document_subjects`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `document_subjects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `document_id` INT UNSIGNED NULL COMMENT 'Foreign key to: documents.documents_id.' ,
  `language` VARCHAR(3) NULL COMMENT 'Language of the subject heading.' ,
  `type` VARCHAR(30) NULL COMMENT 'Subject type, i. e. a specific authority file.' ,
  `value` VARCHAR(255) NOT NULL COMMENT 'Value of the subject heading, i. e. text, notation etc.' ,
  `external_key` VARCHAR(255) NULL COMMENT 'Identifier for linking the subject heading to external systems such as authority files.' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_document_subjects_documents` (`document_id` ASC) ,
  CONSTRAINT `fk_document_subjects_documents`
    FOREIGN KEY (`document_id` )
    REFERENCES `documents` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Table for subject heading related data.';


-- -----------------------------------------------------
-- Table `document_title_abstracts`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `document_title_abstracts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `document_id` INT UNSIGNED NULL COMMENT 'Foreign key to: documents.documents_id.' ,
  `type` ENUM('main', 'parent', 'abstract', 'sub', 'additional') NOT NULL COMMENT 'Type of title or abstract.' ,
  `value` TEXT NOT NULL COMMENT 'Value of title or abstract.' ,
  `language` VARCHAR(3) NOT NULL COMMENT 'Language of the title or abstract.' ,
  `sort_order` TINYINT UNSIGNED NULL COMMENT 'Sort order of the titles related to the document.' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_document_title_abstracts_documents` (`document_id` ASC) ,
  CONSTRAINT `fk_document_title_abstracts_documents`
    FOREIGN KEY (`document_id` )
    REFERENCES `documents` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Table with title and abstract related data.';


-- -----------------------------------------------------
-- Table `persons`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `persons` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `academic_title` VARCHAR(255) NULL COMMENT 'Academic title.' ,
  `date_of_birth` VARCHAR(50) NULL COMMENT 'Date of birth.' ,
  `email` VARCHAR(100) NULL COMMENT 'E-mail address.' ,
  `first_name` VARCHAR(255) NULL COMMENT 'First name.' ,
  `last_name` VARCHAR(255) NOT NULL COMMENT 'Last name.' ,
  `place_of_birth` VARCHAR(255) NULL COMMENT 'Place of birth.' ,
  PRIMARY KEY (`id`) ,
  INDEX `last_name` (`last_name` ASC) )
ENGINE = InnoDB
COMMENT = 'Person related data.';


-- -----------------------------------------------------
-- Table `person_external_keys`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `person_external_keys` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `person_id` INT UNSIGNED NULL COMMENT 'Foreign key to: persons.persons_id.' ,
  `type` ENUM('pnd') NOT NULL COMMENT 'Type of the external identifer, i. e. PND-Number (Personennormdatei).' ,
  `value` TEXT NOT NULL COMMENT 'Value of the external identifier.' ,
  `resolver` VARCHAR(255) NULL COMMENT 'URI to external resolving machanism for this identifier type.' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_person_external_keys_persons` (`person_id` ASC) ,
  CONSTRAINT `fk_person_external_keys_persons`
    FOREIGN KEY (`person_id` )
    REFERENCES `persons` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Table for external identifiers related to a person.';


-- -----------------------------------------------------
-- Table `link_persons_documents`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `link_persons_documents` (
  `person_id` INT UNSIGNED NOT NULL COMMENT 'Primary key and foreign key to: persons.persons_id.' ,
  `document_id` INT UNSIGNED NOT NULL COMMENT 'Primary key and foreign key to: documents.documents_id.' ,
  `institute_id` INT UNSIGNED NULL COMMENT 'Foreign key to: institutes_contents.institutes_id.' ,
  `role` ENUM('advisor', 'author', 'contributor', 'editor', 'referee',  'other', 'translator', 'submitter') NOT NULL COMMENT 'Role of the person in the actual document-person context.' ,
  `sort_order` TINYINT UNSIGNED NOT NULL COMMENT 'Sort order of the persons related to the document.' ,
  `allow_email_contact` BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Is e-mail contact in the actual document-person context allowed? (1=yes, 0=no).' ,
  INDEX `fk_link_documents_persons_persons` (`person_id` ASC) ,
  PRIMARY KEY (`person_id`, `document_id`, `role`) ,
  INDEX `fk_link_persons_publications_institutes_contents` (`institute_id` ASC) ,
  INDEX `fk_link_persons_documents_documents` (`document_id` ASC) ,
  CONSTRAINT `fk_link_documents_persons_persons`
    FOREIGN KEY (`person_id` )
    REFERENCES `persons` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_link_persons_publications_institutes_contents`
    FOREIGN KEY (`institute_id` )
    REFERENCES `collections_contents_1` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_link_persons_documents_documents`
    FOREIGN KEY (`document_id` )
    REFERENCES `documents` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Relation table (documents, persons, institutes_contents).';


-- -----------------------------------------------------
-- Table `document_patents`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `document_patents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `document_id` INT UNSIGNED NOT NULL COMMENT 'Foreign key to: documents.documents_id.' ,
  `countries` TEXT NOT NULL COMMENT 'Countries in which the patent was granted.' ,
  `date_granted` VARCHAR(50) NULL COMMENT 'Date when the patent was granted.' ,
  `number` VARCHAR(255) NOT NULL COMMENT 'Patent number / Publication number.' ,
  `year_applied` YEAR NOT NULL COMMENT 'Year of the application.' ,
  `application` TEXT NOT NULL COMMENT 'Description of the patent.' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_patent_information_document` (`document_id` ASC) ,
  CONSTRAINT `fk_patent_information_document`
    FOREIGN KEY (`document_id` )
    REFERENCES `documents` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Table for patent related data.';


-- -----------------------------------------------------
-- Table `document_statistics`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `document_statistics` (
  `document_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to: documents.documents_id.',
  `count` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  `month` tinyint(1) NOT NULL,
  `type` enum('frontdoor','files') NOT NULL,
  PRIMARY KEY  (`document_id`,`year`,`month`,`type`),
  CONSTRAINT `fk_document_statistics_Document`
    FOREIGN KEY (`document_id` )
    REFERENCES `documents` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Table for statistic related data.';

-- -----------------------------------------------------
-- Table `document_notes`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `document_notes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `document_id` INT UNSIGNED NOT NULL COMMENT 'Foreign key to: documents.documents_id.' ,
  `message` TEXT NOT NULL COMMENT 'Message text.' ,
  `creator` TEXT NOT NULL COMMENT 'Crator of the message.' ,
  `scope` ENUM('private', 'public', 'reference') NOT NULL COMMENT 'Visibility: private, public, reference to another document version.' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_document_notes_document` (`document_id` ASC) ,
  CONSTRAINT `fk_document_notes_document`
    FOREIGN KEY (`document_id` )
    REFERENCES `documents` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Table for notes to documents.';

-- -----------------------------------------------------
-- Table `document_enrichments`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `document_enrichments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `document_id` INT UNSIGNED NOT NULL COMMENT 'Foreign key to: documents.documents_id.' ,
  `key` TEXT NOT NULL COMMENT 'Key of the enrichment.' ,
  `value` TEXT NOT NULL COMMENT 'Value of the enrichment.' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_document_enrichment_document` (`document_id` ASC) ,
  CONSTRAINT `fk_document_enrichment_document`
    FOREIGN KEY (`document_id` )
    REFERENCES `documents` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Key-value table for database scheme enhancements.';


-- -----------------------------------------------------
-- Table `document_licences`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `document_licences` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `active` TINYINT NOT NULL COMMENT 'Flag: can authors choose this licence (0=no, 1=yes)?' ,
  `comment_internal` MEDIUMTEXT NULL COMMENT 'Internal comment.' ,
  `desc_markup` MEDIUMTEXT NULL COMMENT 'Description of the licence in a markup language (XHTML etc.).' ,
  `desc_text` MEDIUMTEXT NULL COMMENT 'Description of the licence in short and pure text form.' ,
  `language` VARCHAR(3) NOT NULL COMMENT 'Language of the licence.' ,
  `link_licence` MEDIUMTEXT NOT NULL COMMENT 'URI of the licence text.' ,
  `link_logo` MEDIUMTEXT NULL COMMENT 'URI of the licence logo.' ,
  `link_sign` MEDIUMTEXT NULL COMMENT 'URI of the licence contract form.' ,
  `mime_type` VARCHAR(30) NOT NULL COMMENT 'Mime type of the licence text linked in \"link_licence\".' ,
  `name_long` VARCHAR(255) NOT NULL COMMENT 'Full name of the licence as displayed to users.' ,
  `pod_allowed` TINYINT(1) NOT NULL COMMENT 'Flag: is print on demand allowed. (1=yes, 0=yes).' ,
  `sort_order` TINYINT NOT NULL COMMENT 'Sort order (00 to 99).' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'Table for licence related data.';

-- -----------------------------------------------------
-- Table `accounts`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `accounts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `login` VARCHAR(45) NOT NULL COMMENT 'Login name.' ,
  `password` VARCHAR(45) NOT NULL COMMENT 'Password.' ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `UNIQUE_LOGIN` (`login` ASC) )
ENGINE = InnoDB
COMMENT = 'Table for system user accounts.';


-- -----------------------------------------------------
-- Table `ipranges`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ipranges` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `startingip` INT UNSIGNED NOT NULL COMMENT 'IP address the range starts with. Use MYSQL functions INET_ATON and INET_NTOA.' ,
  `endingip` INT UNSIGNED NOT NULL COMMENT 'IP address the range end with. Use MYSQL function INET_ATON and INET_NTOA.' ,
  `name` VARCHAR(255) COMMENT 'Name of the range f.e. university or administration.',
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `UNIQUE_IP_RANGE` (startingip, endingip) )
ENGINE = InnoDB
COMMENT = 'Table for ranges of ip addresses.';

-- -----------------------------------------------------
-- Table `roles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `privileges`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `privileges` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` INT UNSIGNED NOT NULL COMMENT 'Role that has some privilege.',
  `privilege` enum('administrate', 'publish', 'readMetadata', 'readFile') NOT NULL COMMENT 'Privilege somone has.',
  `document_server_state` ENUM('published', 'unpublished', 'deleted') COMMENT 'Status of publication process of a document in the repository.' ,
  `file_id` INT UNSIGNED COMMENT 'Necessary if privilege ist readFile, else set null.',
  PRIMARY KEY (`id`),
  INDEX `fk_privilege_has_role` (`role_id` ASC) ,
  INDEX `fk_privilege_has_document_file` (`file_id` ASC) ,
  UNIQUE INDEX `unique_privileges_lookup_index` (`role_id`, `privilege`, `document_server_state`, `file_id`),
  CONSTRAINT `fk_privilege_has_role`
    FOREIGN KEY (`role_id` )
    REFERENCES `roles` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE ,
  CONSTRAINT `fk_privilege_has_document_file`
    FOREIGN KEY (`file_id` )
    REFERENCES `document_files` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE )
ENGINE = InnoDB,
COMMENT = 'Contains privileges to access and change files and metadata.';

-- -----------------------------------------------------
-- Table `link_accounts_roles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `link_accounts_roles` (
  `account_id` INT UNSIGNED NOT NULL ,
  `role_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`account_id`, `role_id`) ,
  INDEX `fk_accounts_roles_link_accounts` (`account_id` ASC) ,
  INDEX `fk_accounts_roles_link_roles` (`role_id` ASC) ,
  CONSTRAINT `fk_accounts_roles_link_accounts`
    FOREIGN KEY (`account_id` )
    REFERENCES `accounts` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_accounts_roles_link_roles`
    FOREIGN KEY (`role_id` )
    REFERENCES `roles` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `link_ipranges_roles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `link_ipranges_roles` (
  `role_id` INT UNSIGNED NOT NULL COMMENT 'Primary key and foreign key to: roles.id.' ,
  `iprange_id` INT UNSIGNED NOT NULL COMMENT 'Primary key and foreign key to: ipranges.id.' ,
  PRIMARY KEY (`role_id`, `iprange_id`) ,
  INDEX `fk_iprange_has_roles` (`role_id` ASC) ,
  INDEX `fk_role_has_ipranges` (`iprange_id` ASC) ,
  CONSTRAINT `fk_iprange_has_role`
    FOREIGN KEY (`role_id` )
    REFERENCES `roles` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_role_has_ipranges`
    FOREIGN KEY (`iprange_id` )
    REFERENCES `ipranges` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Relation table (roles, ipranges).';


-- -----------------------------------------------------
-- Table `collections_roles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `collections_roles` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `name` VARCHAR(255) NOT NULL COMMENT 'Name, label or type of the collection role, i.e. a specific classification or conference.' ,
  `oai_name` VARCHAR(255) NOT NULL COMMENT 'Shortname identifying role in oai context.' ,
  `position` INT(11) UNSIGNED NOT NULL COMMENT 'Position of this collection tree (role) in the sorted list of collection roles for browsing and administration.' ,
  `link_docs_path_to_root` ENUM('none', 'count', 'display', 'both') default 'none' COMMENT 'Every document belonging to a collection C automatically belongs to every collection on the path from C to the root of the collection tree for document counting, document diplaying, none or both.',
  `visible` TINYINT(1) UNSIGNED NOT NULL COMMENT 'Deleted collection trees are invisible. (1=visible, 0=invisible).' ,
  `visible_browsing_start` 	TINYINT(1) UNSIGNED NOT NULL 	COMMENT 'Show tree on browsing start page. (1=yes, 0=no).' ,
  `display_browsing` 		VARCHAR(512) NULL 		COMMENT 'Comma separated list of collection_contents_x-fields to display in browsing list context.' ,
  `visible_frontdoor` 		TINYINT(1) UNSIGNED NOT NULL 	COMMENT 'Show tree on frontdoor. (1=yes, 0=no).' ,
  `display_frontdoor` 		VARCHAR(512) NULL 		COMMENT 'Comma separated list of collection_contents_x-fields to display in frontdoor context.' ,
  `visible_oai` 		TINYINT(1) UNSIGNED NOT NULL 	COMMENT 'Show tree in oai output. (1=yes, 0=no).' ,
  `display_oai` 		VARCHAR(512) NULL 		COMMENT 'collection_contents_x-field to display in oai context.' ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `UNIQUE_NAME` (`name` ASC) ,
  UNIQUE INDEX `UNIQUE_OAI_NAME` (`oai_name` ASC) )
ENGINE = InnoDB
COMMENT = 'Administration table for the indivdual collection trees.';


-- -----------------------------------------------------
-- Table `link_documents_licences`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `link_documents_licences` (
  `document_id` INT UNSIGNED NOT NULL COMMENT 'Primary key and foreign key to: documents.documents_id.' ,
  `licence_id` INT UNSIGNED NOT NULL COMMENT 'Primary key and foreign key to: licences.licences_id.' ,
  PRIMARY KEY (`document_id`, `licence_id`) ,
  INDEX `fk_documents_has_document_licences_documents` (`document_id` ASC) ,
  INDEX `fk_documents_has_document_licences_document_licences` (`licence_id` ASC) ,
  CONSTRAINT `fk_documents_has_document_licences_documents`
    FOREIGN KEY (`document_id` )
    REFERENCES `documents` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_documents_has_document_licences_document_licences`
    FOREIGN KEY (`licence_id` )
    REFERENCES `document_licences` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Relation table (documents, document_licences).';


-- -----------------------------------------------------
-- Table `document_references`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `document_references` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `document_id` INT UNSIGNED NOT NULL COMMENT 'Foreign key to referencing document.' ,
  `type` ENUM('doi', 'handle', 'urn', 'std-doi', 'url', 'cris-link', 'splash-url', 'isbn', 'issn') NOT NULL COMMENT 'Type of the identifier.' ,
  `value` TEXT NOT NULL COMMENT 'Value of the identifier.' ,
  `label` TEXT NOT NULL COMMENT 'Display text of the identifier.' ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_document_references_documents` (`document_id` ASC) ,
  CONSTRAINT `fk_document_references_documents`
    FOREIGN KEY (`document_id` )
    REFERENCES `documents` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Table for identifiers referencing to related documents.';


-- -----------------------------------------------------
-- Table `metis_pixel`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `metis_pixel` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key.' ,
  `public_id` VARCHAR(50) NOT NULL COMMENT 'public identification code' ,
  `private_id` VARCHAR(50) NOT NULL COMMENT 'private identification code' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'Table for VG Wort-pixel.';


-- -----------------------------------------------------
-- Table `link_documents_metis_pixel`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `link_documents_metis_pixel` (
  `document_id` INT UNSIGNED NOT NULL COMMENT 'Primary key and foreign key to: documents.documents_id.' ,
  `pixel_id` INT UNSIGNED NOT NULL COMMENT 'Primary key and foreign key to: metis_pixel.id.' ,
  PRIMARY KEY (`document_id`, `pixel_id`) ,
  INDEX `fk_documents_has_metispixel_documents` (`document_id` ASC) ,
  INDEX `fk_documents_has_metispixel_document_pixels` (`pixel_id` ASC) ,
  CONSTRAINT `fk_documents_has_metispixel_documents`
    FOREIGN KEY (`document_id` )
    REFERENCES `documents` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_documents_has_metispixel_document_pixels`
    FOREIGN KEY (`pixel_id` )
    REFERENCES `metis_pixel` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
COMMENT = 'Relation table (documents, metis_pixel).';


-- -----------------------------------------------------
-- Table `translations`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `translations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `context` VARCHAR(15) NOT NULL,
    `locale` VARCHAR(10) NOT NULL,    
    `translation_key` VARCHAR(15) NOT NULL,
    `translation_msg` VARCHAR(15) NOT NULL,    
    PRIMARY KEY (`id`))
ENGINE = InnoDB;


--
-- Table `languages`
-- Based on http://sil.org/iso639-3/download.asp
--
CREATE  TABLE IF NOT EXISTS `languages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `part2_b` char(3) default NULL COMMENT 'Equivalent 639-2 identifier of the bibliographic applications code set, if there is one',
  `part2_t` char(3) default NULL COMMENT 'Equivalent 639-2 identifier of the terminology applications code set, if there is one',
  `part1` char(2) default NULL COMMENT 'Equivalent 639-1 identifier, if there is one',
  `scope` ENUM('I', 'M', 'S') NOT NULL COMMENT 'I(ndividual), M(acrolanguage), S(pecial)',
  `type` ENUM('A', 'C', 'E', 'H', 'L', 'S') NOT NULL COMMENT 'A(ncient), C(onstructed), E(xtinct), H(istorical), L(iving), S(pecial)',
  `ref_name` varchar(150) NOT NULL COMMENT 'Reference language name',
  `comment` varchar(150) default NULL COMMENT 'Comment relating to one or more of the columns',
  `active` TINYINT UNSIGNED NOT NULL default 0 COMMENT 'Is the institute visible? (1=yes, 0=no).' ,
  PRIMARY KEY (`id`)
)
ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table `collections_themes`
-- -----------------------------------------------------
CREATE  TABLE `collections_themes` (
  `role_id` INT UNSIGNED NOT NULL,
  `collection_id` INT UNSIGNED NOT NULL,
  `theme` TEXT NOT NULL,
  PRIMARY KEY (`role_id`, `collection_id`)
)
ENGINE=InnoDB;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
