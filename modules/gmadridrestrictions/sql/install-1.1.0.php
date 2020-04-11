<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gmadridrestrictions_page_rule_definition` (
    `id_definition` int(11) NOT NULL AUTO_INCREMENT,
    `date_change` DATETIME NOT NULL,
    `id_group` INT NOT NULL,
    `id_cms_category` INT NOT NULL,
    `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
    PRIMARY KEY  (`id_definition`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gmadridrestrictions_attachment_rule_definition` (
    `id_definition` int(11) NOT NULL AUTO_INCREMENT,
    `date_change` DATETIME NOT NULL,
    `id_group` INT NOT NULL,
    `id_attachment` INT NOT NULL,
    `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
    PRIMARY KEY  (`id_definition`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
