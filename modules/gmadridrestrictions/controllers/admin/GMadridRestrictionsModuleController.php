<?php

require_once __DIR__ . '/../../classes/GMadridRestrictionsPageRuleDefinition.php';

class GMadridRestrictionsModuleController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true; // use Bootstrap CSS
        $this->table = 'gmadridrestrictions_page_rule_definition'; // SQL table name, will be prefixed with _DB_PREFIX_
        $this->identifier = 'id_definition'; // SQL column to be used as primary key
        $this->className = 'GMadridRestrictionsPageRuleDefinition'; // PHP class name
        $this->allow_export = true; // allow export in CSV, XLS..

        $this->_defaultOrderBy = 'a.id_definition'; // the table alias is always `a`
        $this->_defaultOrderWay = 'DESC';
        $this->fields_list = [
            'id_definition' => ['title' => 'ID','class' => 'fixed-width-xs'],
            'id_cms_category' => ['title' => 'CMS Category ID'],
            'id_group' => ['title' => 'Group ID'],
            'active' => [
                'title' => $this->trans('Active', array(), 'Admin.Global'),
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ]
        ];

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->fields_form = [
            'legend' => [
                'title' => $this->trans('User Memberships', [], 'Modules.gmadridRestrictionsmodule.Admin'),
                'icon' => 'icon-list-ul'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->trans('Is active?', [], 'Modules.gmadridRestrictionsmodule.Admin'),
                    'name' => 'active',
                    'is_bool' => true,
                    'desc' => $this->trans('If active, the pages belonging to the specified CMS group are restricted to the specified user group', [], 'Modules.gmadridRestrictionsmodule.Admin'),
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => '1',
                            'label' => $this->trans('Enabled', [], 'Admin.Global'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => '0',
                            'label' => $this->trans('Disabled', [], 'Admin.Global'),
                        ],
                    ],
                    'required' => true
                ],
                [
                    'name'=>'id_cms_category',
                    'type'=>'text',
                    'label'=>'CMS category ID',
                    'required'=>true
                ],
                [
                    'name'=>'id_group',
                    'type'=>'text',
                    'label'=>'Group ID',
                    'required'=>true
                ]
            ],
            'submit' => [
                'title' => $this->trans('Save', [], 'Admin.Actions'),
            ]
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selection'),
                'icon' => 'icon-trash text-danger'
            ]
        ];
    }

    public function validateRules($class_name = false) {
        parent::validateRules($class_name);

        if (!$class_name) {
            $class_name = $this->className;
        }

        /** @var $object ObjectModel */
        $object = new $class_name();
        $this->copyFromPost($object, $this->table);
        $this->validateObject($object);
    }

    protected function validateObject($object)
    {
        $errors = $object->validateObject();

        if ($errors === true) {
            return;
        }

        foreach ($errors as $error_field => $error_message) {
            $this->errors[$error_field] = $error_message;
        }
    }

    public function processStatus()
    {
        $object = $this->loadObject();
        $object->switchActive();
        $this->validateObject($object);
        $object->switchActive();

        if (!empty($this->errors)) {
            return;
        }

        parent::processStatus();
    }

    public function viewAccess($disable = false)
    {
        return true;
    }
}
