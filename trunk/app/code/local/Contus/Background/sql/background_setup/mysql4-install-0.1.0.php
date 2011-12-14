<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('background')};
CREATE TABLE {$this->getTable('background')} (
  `background_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`background_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {$this->getTable('background')} (`background_id`, `title`, `filename`, `content`, `status`, `created_time`, `update_time`) VALUES
(1, 'Home page Image', 'imagename.jpg', '', 2, '2010-10-12 10:42:08', '2010-10-12 10:42:08');
    ");

$installer->endSetup(); 