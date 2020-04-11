<?php

require_once(__DIR__ . '/IExtendedObjectValidation.php');

class GMadridMembershipDefinition extends ObjectModel implements IExtendedObjectValidation
{
    public $id_definition;
    public $date_change;
    public $id_group;
    public $product_id;
    /** @var bool GMadridMembershipDefinition status */
    public $active;
    public $defines_user_membership;
    public $is_past_membership;

    public static $definition = [
      'table' => 'gmadridmembership_definition',
        'primary' => 'id_definition',
        'fields' => [
            'id_group' =>  ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required'=>true],
            'product_id' =>  ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required'=>true],
            'active' =>  ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required'=>true],
            'defines_user_membership' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required'=>true],
            'is_past_membership' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required'=>true]
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
        $membership_for_products = new PrestaShopCollection('GMadridMembershipDefinition');
        $membership_for_products->where('id_group', '=', $this->id_group);
        $membership_for_products->where('product_id', '=', $this->product_id);

        // 1. Assert there are not rules with the same product - group association
        if (! $this->isNewObject()) {
            $membership_for_products->where('id_definition', '<>', $this->id_definition);
        }

        if ($membership_for_products->count() > 0) {
                $errors["id_group"]=  $this->trans('Another rule is associating the same product to the same group already', [], 'Modules.gmadridmodule.Admin');
        }

        // 2. It cannot be both an active and a past membership
        if ($this->defines_user_membership == '1' && $this->is_past_membership == '1') {
            $errors["is_past_membership"]=  $this->trans('It cannot be a past membership and the actual one at the same time', [], 'Modules.gmadridmodule.Admin');
        }

        // 3. Check ID product and ID group do exist
        $group = new PrestaShopCollection('Group');
        $group->where('id_group', '=', $this->id_group);
        if($group->count() == 0) {
            $errors []= $this->trans('The group you specified does not exist', [], 'Modules.gmadridmodule.Admin');
        }

        $group = new PrestaShopCollection('Product');
        $group->where('id_product', '=', $this->product_id);
        if($group->count() == 0) {
            $errors []= $this->trans('The product ID you specified does not exist', [], 'Modules.gmadridmodule.Admin');
        }

        // 4. Assert there's only an active membership rule
        if ($this->active == '0' || $this->defines_user_membership == '0') {
            return (empty($errors)) ? true : $errors;
        }

        $membership_for_products = new PrestaShopCollection('GMadridMembershipDefinition');
        $membership_for_products->where('defines_user_membership', '=', true);
        $membership_for_products->where('active', '=', true);
        if (! $this->isNewObject()) {
            $membership_for_products->where('id_definition', '<>', $this->id_definition);
        }

        if ($membership_for_products->count() == 0) {
            return (empty($errors)) ? true : $errors;
        }

        $errors["defines_user_membership"] = $this->trans('Another membership rule is active already', [], 'Modules.gmadridmodule.Admin');

        return (empty($errors)) ? true : $errors;
    }

    /**
     * @return GMadridMembershipDefinition
     */
    public static function getAnnualMembershipRule()
    {
        $membership_for_products = new PrestaShopCollection('GMadridMembershipDefinition');
        $membership_for_products->where('defines_user_membership', '=', true);
        $membership_for_products->where('active', '=', true);
        $membership_for_products_result = $membership_for_products->getResults();

        if (empty($membership_for_products_result)) {
            return null;
        }

        return $membership_for_products_result[0];
    }

    public static function isPastMembership(array $userGroups = []) {
        $membership_for_products = new PrestaShopCollection('GMadridMembershipDefinition');
        $membership_for_products->where('is_past_membership', '=', true);
        $membership_for_products_result = $membership_for_products->getResults();

        if (empty($membership_for_products_result)) {
            return false;
        }

        $extractGroupIds = function($membershipForProductResult) {
            return $membershipForProductResult->id_group;
        };
        $pastMembershipGroupIds = array_map($extractGroupIds, $membership_for_products_result);

        foreach ($userGroups as $userGroup) {
            if(in_array($userGroup, $pastMembershipGroupIds)) {
                return true;
            }
        }

        return false;
    }
}
