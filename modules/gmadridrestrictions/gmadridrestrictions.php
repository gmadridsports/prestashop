<?php
if (!defined('_PS_VERSION_'))
{
    exit;
}

require_once __DIR__ . '/upgrade/install-1.1.0.php';
require_once __DIR__ . '/classes/GMadridRestrictionsPageRuleDefinition.php';
require_once __DIR__ . '/classes/GMadridRestrictionsAttachmentRuleDefinition.php';

class GMadridRestrictions extends Module
{
    public function __construct()
    {
        $this->name = 'gmadridrestrictions';
        $this->tab = 'administration';
        $this->version = '1.1.0';
        $this->author = 'Matteo Bertamini';

        $this->bootstrap = true;
        parent::__construct();

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->displayName = $this->l('GMadrid Restrictions');
        $this->description = $this->l('Restringe el acceso a páginas y productos por grupos');
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install() ||
            !upgrade_module_1_1_0($this)) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        // Uninstall Tabs
        $tab = new Tab((int)Tab::getIdFromClassName('GMadridRestrictions'));
        $tab->delete();

        if (! $this->unregisterHook('filterCmsContent') ||
            ! $this->unregisterHook('actionDownloadAttachment') ||
            ! parent::uninstall()) {
            return false;
        }

        return true;
    }

    public function hookFilterCmsContent($params)
    {
        $id_cms_category = $params["object"]["id_cms_category"];
        $membership_for_cms_catergories = new PrestaShopCollection('GMadridRestrictionsPageRuleDefinition');
        $membership_for_cms_catergories->where('active', '=', true);
        $membership_for_cms_catergories->where('id_cms_category', '=', $id_cms_category);

        $gmadrid_restrictions_page_rule_definitions = $membership_for_cms_catergories->getResults();

        if (count($gmadrid_restrictions_page_rule_definitions) == 0) {
            return;
        }

        $customer_groups = $this->context->customer->getGroups();

        foreach ($gmadrid_restrictions_page_rule_definitions as $membership_for_product) {
            if (in_array($membership_for_product->id_group, $customer_groups)) {
                return;
            }

        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $params["object"]["content"] = $this->context->smarty->fetch($this->local_path . 'views/templates/pages/notAllowed.tpl');

        return $params;
    }

    public function hookActionDownloadAttachment($params)
    {
        $id_attachment = $params["attachment"]->id;
        $groups_for_attachment = new PrestaShopCollection('GMadridRestrictionsAttachmentRuleDefinition');
        $groups_for_attachment->where('active', '=', true);
        $groups_for_attachment->where('id_attachment', '=', $id_attachment);

        $gmadrid_restrictions_attachment_definitions = $groups_for_attachment->getResults();

        if (count($gmadrid_restrictions_attachment_definitions) == 0) {
            return;
        }

        $customer_groups = $this->context->customer->getGroups();

        foreach ($gmadrid_restrictions_attachment_definitions as $membership_for_product) {
            if (in_array($membership_for_product->id_group, $customer_groups)) {
                return;
            }
        }

        Tools::redirect('index.php');
    }
}
