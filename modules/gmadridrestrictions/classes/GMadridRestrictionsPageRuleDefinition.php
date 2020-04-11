<?php

require_once __DIR__ . '/../../gmadridmodule/classes/IExtendedObjectValidation.php';

class GMadridRestrictionsPageRuleDefinition extends ObjectModel implements IExtendedObjectValidation
{
    public $id_definition;
    public $date_change;
    public $id_group;
    public $id_cms_category;
    /** @var bool GMadridRestrictionsPageRuleDefinition status */
    public $active;

    public static $definition = [
      'table' => 'gmadridrestrictions_page_rule_definition',
        'primary' => 'id_definition',
        'fields' => [
            'id_group' =>  ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required'=>true],
            'id_cms_category' =>  ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required'=>true],
            'active' =>  ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required'=>true]
        ]
    ];

    public function switchActive() {
        $this->active = ($this->active == '0')? '1' : '0';
    }

    public function isNewObject()
    {
        return $this->id_definition == NULL;
    }

    /**
     * @return bool|array true if ok, string of the error if any
     */
    public function validateObject() {
        $errors = [];
        $membership_for_products = new PrestaShopCollection('GMadridRestrictionsPageRuleDefinition');
        $membership_for_products->where('id_group', '=', $this->id_group);
        $membership_for_products->where('id_cms_category', '=', $this->id_cms_category);

        // 1. Assert there are not rules with the same product - group association
        if (! $this->isNewObject()) {
            $membership_for_products->where('id_definition', '<>', $this->id_definition);
        }

        if ($membership_for_products->count() > 0) {
                $errors["id_group"]=  $this->trans('Another rule is associating the same CMS category to the same group already', [], 'Modules.gmadridRestrictions.Admin');
        }

        // 2. Check ID product and ID group do exist
        $group = new PrestaShopCollection('Group');
        $group->where('id_group', '=', $this->id_group);
        if($group->count() == 0) {
            $errors []= $this->trans('The group you specified does not exist', [], 'Modules.gmadridRestrictions.Admin');
        }

        $group = new PrestaShopCollection('CMSCategoryCore');
        $group->where('id_cms_category', '=', $this->id_cms_category);
        if($group->count() == 0) {
            $errors []= $this->trans('The CMS category ID you specified does not exist', [], 'Modules.gmadridRestrictions.Admin');
        }

        return (empty($errors)) ? true : $errors;
    }

//    /**
//     * @return GMadridMembershipDefinition
//     */
//    public static function getAnnualMembershipRule()
//    {
//        $membership_for_products = new PrestaShopCollection('GMadridMembershipDefinition');
//        $membership_for_products->where('defines_user_membership', '=', true);
//        $membership_for_products->where('active', '=', true);
//        $membership_for_products_result = $membership_for_products->getResults();
//
//        if (empty($membership_for_products_result)) {
//            return null;
//        }
//
//        return $membership_for_products_result[0];
//    }
}
