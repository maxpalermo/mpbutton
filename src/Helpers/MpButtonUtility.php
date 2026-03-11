<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace MpSoft\MpButton\Helpers;

use MpSoft\MpButton\Models\ModelMpButton;
use \Context;
use \Db;
use \DbQuery;
use \Module;
use \Tools;

class MpButtonUtility
{
    public static function getProductsByIdCategory($but)
    {
        if (!$but->id_categories) {
            return [];
        }

        $db = Db::getInstance();
        $sql = new DbQuery();
        $category_default = (int) $but->category_default;
        $products = [];
        $id_list = implode(',', $but->id_categories);

        if ($category_default) {
            $sql
                ->select('id_product')
                ->from('product')
                ->where('id_category_default in (' . pSQL($id_list) . ')');
        } else {
            $sql
                ->select('id_product')
                ->from('category_product')
                ->where('id_category in (' . pSQL($id_list) . ')');
        }

        $res = $db->executeS($sql);
        if (!$res) {
            return [];
        }

        foreach ($res as $id) {
            $products[] = $id['id_product'];
        }
        $list = array_unique($products);

        return $list;
    }

    public static function getProductsByIdSupplier($but)
    {
        if (!$but->id_suppliers) {
            return [];
        }

        $db = Db::getInstance();
        $sql = new DbQuery();
        $products = [];
        $id_list = implode(',', $but->id_suppliers);

        $sql
            ->select('id_product')
            ->from('product_supplier')
            ->where('id_supplier in (' . pSQL($id_list) . ')');
        $res = $db->executeS($sql);
        if (!$res) {
            return [];
        }

        foreach ($res as $id) {
            $products[] = $id['id_product'];
        }
        $list = array_unique($products);

        return $list;
    }

    public static function getIdProducts($button)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql
            ->select('distinct id_product')
            ->from('product_supplier')
            ->where('id_supplier in (' . implode(',', $button->id_suppliers) . ')');
        $result = $db->executeS($sql);
        $output = [];
        if (count($button->id_products) == 0) {
            $button->id_products = [];
        }
        if ($result) {
            foreach ($result as $row) {
                $output[] = $row['id_product'];
            }
            $button->id_products = array_unique(array_merge($button->id_products, $output));
        }

        return $button->id_products;
    }

    public static function setFieldsToUpdate()
    {
        $boxes = Tools::getValue('mp_buttonBox', []);
        $action = 0;
        $db = Db::getInstance();
        if (Tools::isSubmit('submitBulkenableSelectionmp_button')) {
            $action = 1;
        }
        foreach ($boxes as $box) {
            $db->execute(
                'update ' . _DB_PREFIX_ . "mp_button set active = $action where id_mp_button=" . (int) $box
            );
        }
    }

    protected static function l($string)
    {
        $module = Module::getInstanceByName('mpbutton');
        return $module->l($string, 'MpButtonUtility');
    }

    public static function getButtonsLayer()
    {
        return [
            [
                'id' => ModelMpButton::POSITION_TOP,
                'value' => self::l('Alto'),
            ],
            [
                'id' => ModelMpButton::POSITION_BOTTOM,
                'value' => self::l('Basso'),
            ],
            [
                'id' => ModelMpButton::POSITION_LEFT,
                'value' => self::l('Sinistra'),
            ],
            [
                'id' => ModelMpButton::POSITION_RIGHT,
                'value' => self::l('Destra'),
            ],
            [
                'id' => ModelMpButton::POSITION_POPUP,
                'value' => self::l('Centro'),
            ],
            [
                'id' => ModelMpButton::POSITION_AFTER_CART,
                'value' => self::l('Dopo il carrello'),
            ],
            [
                'id' => ModelMpButton::POSITION_DESC,
                'value' => self::l('Descrizione'),
            ],
        ];
    }

    public static function getPageControllers()
    {
        return [
            [
                'id' => 'category',
                'value' => self::l('Categorie'),
            ],
            [
                'id' => 'product',
                'value' => self::l('Prodotti'),
            ],
            [
                'id' => 'index',
                'value' => self::l('Home page'),
            ],
            [
                'id' => 'authentication',
                'value' => self::l('Autenticazione'),
            ],
            [
                'id' => 'cms',
                'value' => self::l('CMS'),
            ],
        ];
    }

    public static function getCustomerGroups()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql
            ->select('id_group')
            ->select('name')
            ->from('group_lang')
            ->where('id_lang=' . (int) Context::getContext()->language->id)
            ->orderBy('name');
        $res = $db->executeS($sql);
        if ($res) {
            $res[] = [
                'id_group' => -1,
                'name' => self::l('Utente non abilitato'),
            ];

            return $res;
        }

        return [];
    }

    public static function getCategories()
    {
        $db = Db::getInstance();
        $id_shop = (int) Context::getContext()->shop->id;
        $id_lang = (int) Context::getContext()->language->id;
        $sql = new DbQuery();
        $sql
            ->select('id_category')
            ->select('name')
            ->from('category_lang')
            ->where('id_shop = ' . (int) $id_shop)
            ->where('id_lang = ' . (int) $id_lang)
            ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return [];
        }

        return $result;
    }

    public static function getProducts()
    {
        $db = Db::getInstance();
        $id_shop = (int) Context::getContext()->shop->id;
        $id_lang = (int) Context::getContext()->language->id;
        $sql = new DbQuery();
        $sql
            ->select('p.id_product')
            ->select("concat(p.reference, ' - ', pl.name) as name")
            ->from('product', 'p')
            ->innerJoin('product_lang', 'pl', 'pl.id_product=p.id_product')
            ->where('pl.id_shop = ' . (int) $id_shop)
            ->where('pl.id_lang = ' . (int) $id_lang)
            ->where('p.active=1')
            ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return [];
        }

        return $result;
    }

    public static function getManufacturers()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $output = [];
        $sql
            ->select('id_manufacturer as id')
            ->select('name')
            ->from('manufacturer')
            ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return [];
        }
        $selected = explode(',', Tools::getValue('input_select_manufacturers', ''));
        foreach ($result as $row) {
            $is_selected = in_array($row['id'], $selected);
            $output[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'selected' => $is_selected,
            ];
        }

        return $output;
    }

    public static function getSuppliers()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $output = [];
        $sql
            ->select('id_supplier as id')
            ->select('name')
            ->from('supplier')
            ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return [];
        }
        $selected = explode(',', Tools::getValue('input_select_suppliers', ''));
        foreach ($result as $row) {
            $is_selected = in_array($row['id'], $selected);
            $output[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'selected' => $is_selected,
            ];
        }

        return $output;
    }
}
