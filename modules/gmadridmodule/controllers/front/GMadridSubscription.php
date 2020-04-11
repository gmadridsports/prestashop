<?php

class GMadridModuleGMadridSubscriptionModuleFrontController extends ModuleFrontController {
    public $ssl = true;

    public function init() {
        parent::init();
    }
    public function setMedia() {
        parent::setMedia();
        $this->registerJavascript(
            'aabb',
            _PS_JS_DIR_.'vendor/spin.js',
            [
                'priority' => 200]
        );
        $this->registerJavascript(
            'aa',
            _PS_JS_DIR_.'vendor/ladda.js',
            [
                'priority' => 204]
        );


//        die('here');
        $this->registerJavascript(
            'module-gmadridsubscription-subscription-lib',
            _THEME_JS_DIR_.'/gmadridSubscription.js',
            [
                'priority' => 300,
                'attributes' => 'defer',
            ]
        );
    }

    public function initContent() {
        parent::initContent();

        $cartUrl = $this->context->link->getPageLink(
            'order',
            null,
            $this->context->language->id,
            ['action' => 'show']
        );
        $membership = array(
            'membership_product_id' => GMadridMembershipDefinition::getAnnualMembershipRule()->product_id,
            'checkout_url' => $cartUrl
        );
        $this->context->smarty->assign(array('gmadridMembership' => $membership));
        $this->setTemplate('module:gmadridmodule/views/templates/front/subscribe.tpl');
    }
}
