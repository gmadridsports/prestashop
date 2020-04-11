<?php
if (!defined('_PS_VERSION_'))
{
    exit;
}

require_once __DIR__ . '/classes/GMadridMembershipDefinition.php';

class GMadridModule extends Module
{
    public function __construct()
    {
        $this->name = 'gmadridmodule';
        $this->tab = 'administration';
        $this->version = '1.3.4';
        $this->author = 'Matteo Bertamini';

        $this->bootstrap = true;
        parent::__construct();

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->displayName = $this->l('GMadrid Membership');
        $this->description = $this->l('Gestional de suscripciones de socios');
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        include(dirname(__FILE__) . '/sql/install.php');

        if (!parent::install() ||
            ! $this->installTabs() ||
            ! $this->registerHook('actionPaymentConfirmation') ||
            ! $this->registerHook('displayCustomerAccount') ||
            ! $this->registerHook('actionCartSave')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        // Uninstall Tabs
        $tab = new Tab((int)Tab::getIdFromClassName('GMadridModule'));
        $tab->delete();

        if (! $this->unregisterHook('actionPaymentConfirmation') ||
            ! $this->unregisterHook('displayCustomerAccount') ||
            ! $this->unregisterHook('actionCartSave') ||
            ! parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function installTabs()
    {
        $lang = Language::getLanguages();
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'GMadridModule';
        $tab->module = 'gmadridmodule';
        $tab->id_parent = (int)Tab::getIdFromClassName('SELL');
        $tab->position = 1;
        foreach ($lang as $l) {
            $tab->name[$l['id_lang']] = $this->l('Suscripciones socios');
        }
        $tab->icon = 'account_circle';

        return $tab->add();
    }

    public function hookActionCartSave()
    {
        // hack: if we update the quantity it would pass one more time on this hook.
        // we want this to be run just once.
        static $runningHook = false;

        if ($runningHook) {
            return;
        } else {
            $runningHook = true;
        }

        $cart = \Context::getContext()->cart;

        if($cart == null) {
            $cart = new Cart(\Context::getContext()->cookie->id_cart, \Context::getContext()->customer->id_lang);

            if($cart == null || $cart->id == null) {
                return;
            }
        }

        $products = $cart->getProducts(false, false, null, false);
        $annualMembershipRule = GMadridMembershipDefinition::getAnnualMembershipRule();

        if (null == $annualMembershipRule) {
            return;
        }

        foreach ($products as $product) {
            $idProduct = (int)$product['id_product'];

            if ($idProduct != $annualMembershipRule->product_id) {
                continue;
            }
            if ($this->isAMember()) {
                $cart->delete();
            } elseif ($product['cart_quantity'] > 1) {
                if ($this->context->customer->id == NULL) {
                    $cart->updateQty(
                        $product['cart_quantity'] - 1,
                        $product['id_product'],
                        $product['id_product_attribute'],
                        $product['id_customization'],
                        'down',
                        $product['id_address_delivery']);
                } else {
                    try {
                        $cart->delete();
                    } catch (Exception $e) {
                        // it happens for sure. Weird PS behavior (shrug).
                    }
                }
            }

            break;
        }

        if (Tools::getValue('id_product', false) == $annualMembershipRule->product_id) {
            if ($this->isAMember() || (Tools::getValue('qty', 0) > 1)) {
                $_POST['qty'] = 0;
                $_GET['qty'] = 1;
            }
        }
    }

    public function hookActionPaymentConfirmation($params)
    {
        $id_order = ($params["id_order"]);
        $order_details = new PrestaShopCollection('OrderDetail');
        $order_details->where('id_order', '=', $id_order);
        $order_details_result = $order_details->getResults();

        $order_product_ids = array_map(function($order_detail) { return $order_detail->product_id; }, $order_details->getResults());

        $membership_for_products = new PrestaShopCollection('GMadridMembershipDefinition');
        $membership_for_products->where('product_id', 'in', $order_product_ids);
        $membership_for_products->where('active', '=', true);
        $membership_for_products_result = $membership_for_products->getResults();

        $actual_customer_groups = $this->context->customer->getGroups();
        $customer_groups_to_add = [];

        foreach ($membership_for_products_result as $membership_for_product) {
            if (! in_array($membership_for_product->id_group, $actual_customer_groups)) {
                $customer_groups_to_add []= $membership_for_product->id_group;
            }
        }

        $this->context->customer->addGroups($customer_groups_to_add);
    }

    public function hookDisplayCustomerAccount()
    {
        $cartUrl = $this->context->link->getPageLink(
            'order',
            null,
            $this->context->language->id,
            ['action' => 'show']
        );

        $membership = array(
            'is_member' => $this->isAMember(),
            'membership_number' => $this->context->customer->id,
            'is_past_membership' => $this->isAPastMember(),
            'membership_product_id' => GMadridMembershipDefinition::getAnnualMembershipRule()->product_id,
            'checkout_url' => $cartUrl
        );
        $this->context->smarty->assign(array('gmadridMembership' => $membership));
        return $this->display(__FILE__, 'displayBadgeMembership.tpl');
    }

    public function isAMember() {
        return in_array(
            GMadridMembershipDefinition::getAnnualMembershipRule()->id_group,
            $this->context->customer->getGroups());
    }

    public function isAPastMember() {
        return GMadridMembershipDefinition::isPastMembership($this->context->customer->getGroups());
    }
}
