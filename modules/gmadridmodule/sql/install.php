<?php

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gmadridmembership_definition` (
    `id_definition` int(11) NOT NULL AUTO_INCREMENT,
    `date_change` DATETIME NOT NULL,
    `id_group` INT NOT NULL,
    `product_id` INT NOT NULL,
    `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
    `defines_user_membership` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
    PRIMARY KEY  (`id_definition`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'gmadridmembership_definition`
    ADD `is_past_membership` tinyint(1) unsigned NOT NULL DEFAULT \'0\'
';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
