<?php

require_once __DIR__ . '/../../gmadridmodule/classes/IExtendedObjectValidation.php';

class GMadridRestrictionsAttachmentRuleDefinition extends ObjectModel implements IExtendedObjectValidation
{
    public $id_definition;
    public $date_change;
    public $id_group;
    public $id_attachment;
    /** @var bool GMadridRestrictionsPageRuleDefinition status */
    public $active;

    public static $definition = [
      'table' => 'gmadridrestrictions_attachment_rule_definition',
        'primary' => 'id_definition',
        'fields' => [
            'id_group' =>  ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required'=>true],
            'id_attachment' =>  ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required'=>true],
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
        $group_for_attachment = new PrestaShopCollection('GMadridRestrictionsAttachmentRuleDefinition');
        $group_for_attachment->where('id_group', '=', $this->id_group);
        $group_for_attachment->where('id_attachment', '=', $this->id_attachment);

        // 1. Assert there are not rules with the same product - group association
        if (! $this->isNewObject()) {
            $group_for_attachment->where('id_definition', '<>', $this->id_definition);
        }

        if ($group_for_attachment->count() > 0) {
                $errors["id_group"]=  $this->trans('Another rule is associating the same attachment to the same group already', [], 'Modules.gmadridRestrictions.Admin');
        }

        // 2. Check ID product and ID group do exist
        $group = new PrestaShopCollection('Group');
        $group->where('id_group', '=', $this->id_group);
        if($group->count() == 0) {
            $errors []= $this->trans('The group you specified does not exist', [], 'Modules.gmadridRestrictions.Admin');
        }

        $group = new PrestaShopCollection('AttachmentCore');
        $group->where('id_attachment', '=', $this->id_attachment);
        if($group->count() == 0) {
            $errors []= $this->trans('The attachment ID you specified does not exist', [], 'Modules.gmadridRestrictions.Admin');
        }

        return (empty($errors)) ? true : $errors;
    }
}
