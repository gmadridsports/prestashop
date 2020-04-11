<?php

class Product extends ProductCore
{
    public static function checkAccessStatic($id_product, $id_customer)
    {
        if (!Group::isFeatureActive()) {
            return true;
        }

        $cache_id = 'Product::checkAccess_'.(int)$id_product.'-'.(int)$id_customer.(!$id_customer ? '-'.(int)Group::getCurrent()->id : '');
        if (!Cache::isStored($cache_id)) {
            if (!$id_customer) {
                $result = (bool)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
				SELECT ctg.`id_group`
				FROM `'._DB_PREFIX_.'category_product` cp
				INNER JOIN `'._DB_PREFIX_.'category_group` ctg ON (ctg.`id_category` = cp.`id_category`)
				WHERE cp.`id_product` = '.(int)$id_product.' AND ctg.`id_group` = '.(int)Group::getCurrent()->id .
                    ' AND IF ((
                SELECT
      CASE total.total
        WHEN 0 THEN "ok"
		ELSE "to be checked"
    END AS a
        FROM (SELECT COUNT(cp.`id_category`) as `total`
FROM `ps_category_product` cp
INNER JOIN `'._DB_PREFIX_.'category_group` ctg ON (ctg.`id_category` = cp.`id_category`) 
WHERE cp.`id_category` NOT IN (1,2)
                AND cp.`id_product` = '.(int)$id_product.' AND ctg.`id_group` NOT IN (1,2)) as `total` ) = "to be checked", 2, 0)  < ctg.`id_category`');
            } else {
                $result = (bool)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
				SELECT cg.`id_group`
				FROM `'._DB_PREFIX_.'category_product` cp
				INNER JOIN `'._DB_PREFIX_.'category_group` ctg ON (ctg.`id_category` = cp.`id_category`)
				INNER JOIN `'._DB_PREFIX_.'customer_group` cg ON (cg.`id_group` = ctg.`id_group`)
				WHERE cp.`id_product` = '.(int)$id_product.' AND cg.`id_customer` = '.(int)$id_customer.
                    ' AND IF ((
                SELECT
      CASE total.total
        WHEN 0 THEN "ok"
		ELSE "to be checked"
    END AS a
        FROM (SELECT COUNT(cp.`id_category`) as `total`
FROM `ps_category_product` cp
INNER JOIN `'._DB_PREFIX_.'category_group` ctg ON (ctg.`id_category` = cp.`id_category`) 
WHERE cp.`id_category` NOT IN (1,2)
                AND cp.`id_product` ='.(int)$id_product.' AND ctg.`id_group` NOT IN (1,2)) as `total` ) = "to be checked", 2, 0)  < ctg.`id_category`'
                );
            }

            Cache::store($cache_id, $result);
            return $result;
        }
        return Cache::retrieve($cache_id);
    }
}
