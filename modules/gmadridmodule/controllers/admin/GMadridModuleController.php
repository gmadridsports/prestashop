<?php

require_once __DIR__ . '/../../classes/GMadridMembershipDefinition.php';

class GMadridModuleController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true; // use Bootstrap CSS
        $this->table = 'gmadridmembership_definition'; // SQL table name, will be prefixed with _DB_PREFIX_
        $this->identifier = 'id_definition'; // SQL column to be used as primary key
        $this->className = 'GMadridMembershipDefinition'; // PHP class name
        $this->allow_export = true; // allow export in CSV, XLS..

        $this->_defaultOrderBy = 'a.id_definition'; // the table alias is always `a`
        $this->_defaultOrderWay = 'DESC';
        $this->fields_list = [
            'id_definition' => ['title' => 'ID','class' => 'fixed-width-xs'],
            'product_id' => ['title' => 'Product ID'],
            'id_group' => ['title' => 'Group ID'],
            'active' => [
                'title' => $this->trans('Active', array(), 'Admin.Global'),
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ],
            'defines_user_membership' => [
                'title' => $this->trans('Default membership', [], 'Modules.gmadridmodule.Admin'),
                'align' => 'center',
                'active' => 'defines_user_membership',
                'type' => 'bool',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ],
            'is_past_membership' => [
                'title' => $this->trans('Is past membership', [], 'Modules.gmadridmodule.Admin'),
                'align' => 'center',
                'active' => 'is_past_membership',
                'type' => 'bool',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            ]
        ];

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->fields_form = [
            'legend' => [
                'title' => $this->trans('User Memberships', [], 'Modules.gmadridmodule.Admin'),
                'icon' => 'icon-list-ul'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->trans('Is active?', [], 'Modules.gmadridmodule.Admin'),
                    'name' => 'active',
                    'is_bool' => true,
                    'desc' => $this->trans('This group is associated to the user if and only if this rule is active', [], 'Modules.gmadridmodule.Admin'),
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
                    'name'=>'product_id',
                    'type'=>'text',
                    'label'=>'Product ID',
                    'required'=>true
                ],
//                 Cannot be selected, because of the template :shrugs: Let's keep using a textarea
//                [
//                    'type' => 'select',
//                    'lang' => true,
//                    'label' => $this->l('Product ID'),
//                    'desc' => 'aaa',
//                    'name' => 'product_id',
//                    'options' => array(
//                        'query' => [
//                            ['id'=> 1, 'name'=>'test'],
//                            ['id'=> 2, 'name'=>'test 2']
//                        ],
//                        'id' => 'id',
//                        'name' => 'name'
//                    )
//                ],
                [
                    'name'=>'id_group',
                    'type'=>'text',
                    'label'=>'Group ID',
                    'required'=>true
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Does it define user membership?', [], 'Modules.gmadridmodule.Admin'),
                    'name' => 'defines_user_membership',
                    'is_bool' => true,
                    'desc' => $this->trans('It asserts if this group defines the annual subscription, and then, the subscription number', [], 'Modules.gmadridmodule.Admin'),
                    'values' => [
                        [
                            'id' => 'membership_active_on',
                            'value' => '1',
                            'label' => $this->trans('Enabled', [], 'Admin.Global'),
                        ],
                        [
                            'id' => 'membership_active_off',
                            'value' => '0',
                            'label' => $this->trans('Disabled', [], 'Admin.Global'),
                        ],
                    ],
                    'required' => true
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Does it define a past user membership?', [], 'Modules.gmadridmodule.Admin'),
                    'name' => 'is_past_membership',
                    'is_bool' => true,
                    'desc' => $this->trans('It asserts if this group defines a past annual subscription, and then, a past subscription number', [], 'Modules.gmadridmodule.Admin'),
                    'values' => [
                        [
                            'id' => 'past_membership_active_on',
                            'value' => '1',
                            'label' => $this->trans('Enabled', [], 'Admin.Global'),
                        ],
                        [
                            'id' => 'past_membership_active_off',
                            'value' => '0',
                            'label' => $this->trans('Disabled', [], 'Admin.Global'),
                        ],
                    ],
                    'required' => true
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
