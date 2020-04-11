<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
* In some cases you should not drop the tables.
* Maybe the merchant will just try to reset the module
* but does not want to loose all of the data associated to the module.
*/

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'gmadridrestrictions_page_rule_definition`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'gmadridrestrictions_attachment_rule_definition`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
