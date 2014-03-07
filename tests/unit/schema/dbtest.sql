-- If you change any table please remember to change the xml file, too. You have to change the
-- stored cache, too, in order to reflect the correct state of the site while it is cached.
-- Please run the script named `recreateJoomlaCache.php` to do so

-- Main table used for tests
DROP TABLE IF EXISTS `jos_foftest_foobars`;
CREATE TABLE IF NOT EXISTS `jos_foftest_foobars` (
  `foftest_foobar_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  `ordering` tinyint(4) NOT NULL,
  `hits` int(4) NOT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_on` datetime NOT NULL,
  `locked_by` int(11) NOT NULL,
  `locked_on` datetime NOT NULL,
  PRIMARY KEY (`foftest_foobar_id`)
);

-- Table used for testing aliases
DROP TABLE IF EXISTS `jos_foftest_foobaraliases`;
CREATE TABLE IF NOT EXISTS `jos_foftest_foobaraliases` (
  `id_foobar_aliases` int(11) NOT NULL AUTO_INCREMENT,
  `fo_title` varchar(100) NOT NULL,
  `fo_slug` varchar(100) NOT NULL,
  `fo_enabled` tinyint(4) NOT NULL,
  `fo_ordering` tinyint(4) NOT NULL,
  `fo_hits` int(4) NOT NULL,
  `fo_asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `fo_access` int(10) unsigned NOT NULL DEFAULT '0',
  `fo_created_by` int(11) NOT NULL,
  `fo_created_on` datetime NOT NULL,
  `fo_modified_by` int(11) NOT NULL,
  `fo_modified_on` datetime NOT NULL,
  `fo_locked_by` int(11) NOT NULL,
  `fo_locked_on` datetime NOT NULL,
  PRIMARY KEY (`id_foobar_aliases`)
);

-- === THIS TABLE IS USED FOR TESTING THE OLD METHOD OF JOINS (ie no ORM) ===
-- Table used to test joins
-- We create a generic "key column" so we can test against different table (with aliases or not)
-- There are different columns (unique and not), so we can test it when we have to use alias
-- (column name not unique) or not (unique column name)
DROP TABLE IF EXISTS `jos_foftest_foobarjoins`;
CREATE TABLE IF NOT EXISTS `jos_foftest_foobarjoins` (
  `foftest_id_foobarjoin` int(11) NOT NULL AUTO_INCREMENT,
  `external_key` int(11) NOT NULL,
  `fj_title` varchar(50) NOT NULL,
  `fj_dummy` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  PRIMARY KEY (`foftest_id_foobarjoin`)
);

-- === ACTUAL TABLES USED FOR TESTING THE NEW ORM FEATURE ===
-- Parent table 1:n - 1:1 relation
DROP TABLE if exists `jos_foftest_parents`;
CREATE TABLE IF NOT EXISTS `jos_foftest_parents` (
  `foftest_parent_id` INT NOT NULL AUTO_INCREMENT,
  `dummy` varchar (50) NOT NULL ,
  PRIMARY KEY (`foftest_parent_id`)
);

-- Children table 1:n - 1:1 relation
DROP TABLE if exists `jos_foftest_children`;
CREATE TABLE IF NOT EXISTS `jos_foftest_children` (
  `foftest_child_id` INT NOT NULL AUTO_INCREMENT,
  `dummy` varchar (50) NOT NULL ,
  `foftest_parent_id` INT NOT NULL ,
  PRIMARY KEY (`foftest_child_id`)
);

-- Table without any "special" column (ordering, hits etc etc)
DROP TABLE IF EXISTS `jos_foftest_bares`;
CREATE TABLE IF NOT EXISTS `jos_foftest_bares` (
  `foftest_bare_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  PRIMARY KEY (`foftest_bare_id`)
);
