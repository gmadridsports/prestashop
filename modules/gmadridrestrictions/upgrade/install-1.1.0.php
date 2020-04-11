<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($object)
{
    /**
     * @return bool
     */
    $installTabs = function() use ($object)
    {
        $lang = Language::getLanguages();
        $restrictionPagesTab = new Tab();
        $restrictionPagesTab->active = 1;
        $restrictionPagesTab->class_name = 'GMadridRestrictionsModule';
        $restrictionPagesTab->module = 'gmadridrestrictions';
        $restrictionPagesTab->id_parent = (int)Tab::getIdFromClassName('SELL');
        $restrictionPagesTab->position = 1;
        foreach ($lang as $l) {
            $restrictionPagesTab->name[$l['id_lang']] = $object->l('Restricciones páginas');
        }
        $restrictionPagesTab->icon = 'account_circle';

        $restrictionAttachmentsTab = new Tab();
        $restrictionAttachmentsTab->active = 1;
        $restrictionAttachmentsTab->class_name = 'GMadridRestrictionAttachmentsModule';
        $restrictionAttachmentsTab->module = 'gmadridrestrictions';
        $restrictionAttachmentsTab->id_parent = (int)Tab::getIdFromClassName('SELL');
        $restrictionAttachmentsTab->position = 1;
        foreach ($lang as $l) {
            $restrictionAttachmentsTab->name[$l['id_lang']] = $object->l('Restricciones adjuntos');
        }
        $restrictionAttachmentsTab->icon = 'account_circle';

        return $restrictionPagesTab->add() && $restrictionAttachmentsTab->add();
    };
    
    include(dirname(__FILE__) . '/../sql/install-1.1.0.php');

    
    return $object->registerHook('filterCmsContent') &&
        $object->registerHook('actionDownloadAttachment') &&
        $installTabs();
}
