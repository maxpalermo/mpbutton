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

namespace MpSoft\MpButton\Models;

use \Context;
use \Db;
use \DbQuery;
use \Product;
use \Tools;
use \Validate;

class ModelMpButton extends \ObjectModel
{
    const DEBUG_SQL = false;
    const POSITION_POPUP = '0';
    const POSITION_LEFT = '1';
    const POSITION_RIGHT = '2';
    const POSITION_TOP = '3';
    const POSITION_BOTTOM = '4';
    const POSITION_AFTER_CART = '98';
    const POSITION_DESC = '99';

    public $id_employee;
    public $position;
    public $priority;
    public $delay;
    public $expire;
    public $date_start;
    public $date_end;
    public $active;
    /** @var array */
    public $customer_groups;
    /** @var array */
    public $pages;
    /** @var array */
    public $categories;
    /** @var array */
    public $suppliers;
    /** @var array */
    public $manufacturers;
    /** @var array */
    public $products;
    /** @var array */
    public $features;
    /** @var array */
    public $attributes;
    public $date_add;
    public $date_upd;
    // Lang fields
    public $title;
    public $content;
    public $codeblock;

    /**
     * Object definitions
     */
    public static $definition = [
        'table' => 'mp_button',
        'primary' => 'id_mp_button',
        'multilang' => true,
        'multishop' => true,
        'fields' => [
            'id_employee' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ],
            'position' => [
                'type' => self::TYPE_INT,  // 1:left, 2:right, 3:top, 4:bottom, 0:center, 98: after_cart, 99: desc
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'priority' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'delay' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'expire' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'date_start' => [
                'type' => self::TYPE_DATE,
                'datetime' => true,
                'validate' => 'isDateOrNull',
                'required' => false,
            ],
            'date_end' => [
                'type' => self::TYPE_DATE,
                'datetime' => true,
                'validate' => 'isDateOrNull',
                'required' => false,
            ],
            'customer_groups' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => false,
            ],
            'pages' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => false,
            ],
            'categories' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => false,
            ],
            'manufacturers' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => false,
            ],
            'suppliers' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => false,
            ],
            'products' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => false,
            ],
            'features' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => false,
            ],
            'attributes' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => false,
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'datetime' => true,
                'validate' => 'isDate',
                'required' => false,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'datetime' => true,
                'validate' => 'isDateOrNull',
                'required' => false,
            ],
            // Lang fields
            'title' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'size' => 64,
                'lang' => true,
            ],
            'content' => [
                'type' => self::TYPE_HTML,
                'validate' => 'isAnything',
                'required' => false,
                'size' => 3999999999999,
                'lang' => true,
            ],
            'codeblock' => [
                'type' => self::TYPE_HTML,
                'validate' => 'isAnything',
                'required' => false,
                'size' => 3999999999999,
                'lang' => true,
            ],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        if ($id) {
            $this->explode($this->customer_groups);
            $this->explode($this->pages);
            $this->explode($this->categories);
            $this->explode($this->suppliers);
            $this->explode($this->manufacturers);
            $this->explode($this->products);
            $this->explode($this->features);
            $this->explode($this->attributes);
        }
    }

    public static function install()
    {
        $pfx = _DB_PREFIX_;
        $engine = _MYSQL_ENGINE_;
        $QUERY = "
            CREATE TABLE IF NOT EXISTS `{$pfx}mp_button` (
                `id_mp_button` int(11) NOT NULL AUTO_INCREMENT,
                `id_shop` int(11) DEFAULT NULL,
                `id_employee` int(11) NOT NULL DEFAULT 0,
                `position` int(11) NOT NULL COMMENT 'centro=0, sinistra=1, destra=2, sopra=3, sotto=4, carrello=98, prodotto=99',
                `priority` int(11) NOT NULL DEFAULT 1 COMMENT 'Indica la priorità di visualizzazione nella stessa posizione',
                `delay` int(11) DEFAULT 0 COMMENT 'Il popup appare dopo x secondi',
                `expire` int(11) DEFAULT 0 COMMENT 'Il popup scompare dopo x secondi (0 non scompare)',
                `date_start` datetime DEFAULT NULL COMMENT 'Il popup si attiva dopo questa data',
                `date_end` datetime DEFAULT NULL COMMENT 'Il popup si disattiva dopo questa data',
                `active` tinyint(1) NOT NULL DEFAULT 1,
                `customer_groups` text DEFAULT NULL COMMENT 'Mostra ai gruppi di clienti selezionate',
                `pages` text DEFAULT NULL COMMENT 'Mostra nelle pagine selezionate',
                `manufacturers` text DEFAULT NULL COMMENT 'Mostra ai prodotti appartenenti a questi produttori',
                `suppliers` text DEFAULT NULL COMMENT 'Mostra ai prodotti appartenenti a questi fornitori',
                `products` text DEFAULT NULL COMMENT 'Mostra a questi prodotti',
                `categories` text DEFAULT NULL COMMENT 'Mostra ai prodotti appartenenti a queste categorie',
                `features` text DEFAULT NULL COMMENT 'Mostra ai prodotti appartenenti a queste caratteristiche',
                `attributes` text DEFAULT NULL COMMENT 'Mostra ai prodotti appartenenti a questi attributi',
                `date_add` datetime NOT NULL,
                `date_upd` datetime DEFAULT NULL,
                PRIMARY KEY (`id_mp_button`)
            ) ENGINE={$engine}
        ";

        $QUERY_LANG = "
            CREATE TABLE IF NOT EXISTS `{$pfx}mp_button_lang` (
                `id_mp_button` INT(11) NOT NULL,
                `id_lang` INT(11) NOT NULL,
                `id_shop` INT(11) NOT NULL,
                `title` VARCHAR(64) NOT NULL,
                `content` TEXT NULL DEFAULT NULL,
                `codeblock` TEXT NULL DEFAULT NULL,
                PRIMARY KEY (`id_mp_button`, `id_lang`) USING BTREE
            ) ENGINE={$engine}
        ";

        return Db::getInstance()->execute($QUERY) && Db::getInstance()->execute($QUERY_LANG);
    }

    private function explode(&$value)
    {
        if ($value) {
            $value = explode(',', $value);
        } else {
            $value = [];
        }
    }

    private function implode(&$value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        } else {
            $value = (string) $value;
        }
    }

    protected function getProductsAssociated($table)
    {
        $tbl_prod = _DB_PREFIX_ . 'product';
        $sql = '';
        switch ($table) {
            case 'category':
                $category_default = false;
                if (!$this->categories) {
                    break;
                }
                $tbl = _DB_PREFIX_ . $table . '_product';
                $sql = '';
                if ($category_default) {
                    $sql = "SELECT `id_product` FROM {$tbl_prod} WHERE `id_category_default` IN (" . implode(',', $this->categories) . ')';
                } else {
                    $sql = "SELECT DISTINCT `id_product` FROM {$tbl} WHERE `id_category` IN (" . implode(',', $this->categories) . ')';
                }

                break;
            case 'manufacturer':
                if (!$this->manufacturers) {
                    break;
                }
                $sql = "SELECT `id_product` FROM {$tbl_prod} WHERE `id_manufacturer` IN (" . implode(',', $this->manufacturers) . ')';

                break;
            case 'supplier':
                if (!$this->suppliers) {
                    break;
                }
                $tbl = _DB_PREFIX_ . 'product_' . $table;
                $sql = "SELECT DISTINCT `id_product` FROM {$tbl} WHERE `id_supplier` IN (" . implode(',', $this->suppliers) . ')';

                break;
        }

        if ($sql) {
            $rows = Db::getInstance()->executeS($sql);
            $ids = [];
            if ($rows) {
                foreach ($rows as $row) {
                    $ids[] = $row['id_product'];
                }
            }

            if ($ids) {
                return $ids;
            }
        }

        return [];
    }

    public function getProductsList()
    {
        $products = array_merge(
            $this->getProductsAssociated('category'),
            $this->getProductsAssociated('supplier'),
            $this->getProductsAssociated('manufacturer')
        );

        $merged = array_merge($this->products, $products);
        $unique = array_unique($merged);
        asort($unique);

        return $unique;
    }

    private function fixFields()
    {
        $this->implode($this->customer_groups);
        $this->implode($this->categories);
        $this->implode($this->suppliers);
        $this->implode($this->manufacturers);
        $this->implode($this->pages);
        $this->implode($this->products);
        $this->implode($this->features);
        $this->implode($this->attributes);
    }

    public function add($auto_date = true, $null_values = false)
    {
        $this->fixFields();
        $null_values = true;

        return parent::add($auto_date, $null_values);
    }

    public function update($null_values = false)
    {
        $this->fixFields();
        $null_values = true;

        return parent::update($null_values);
    }

    public function save($null_values = false, $auto_date = true)
    {
        $this->fixFields();
        $null_values = true;

        return parent::save($null_values, $auto_date);
    }

    public static function getActiveButtonsByPosition($position, $controller)
    {
        if ($controller == 'pagenotfound') {
            return [];
        }

        $id_lang = (int) Context::getContext()->language->id;
        $controller = pSQL($controller);
        $id = 0;
        switch ($controller) {
            case 'category':
                $id = Tools::getValue('id_category');
                break;
            case 'product':
                $id = Tools::getValue('id_product');
                $product = new Product($id, false, $id_lang);
                if (!Validate::isLoadedObject($product)) {
                    return [];
                }

                $attributes = $product->getAttributeCombinations($id_lang);
                $id_attribute = [];
                foreach ($attributes as $attribute) {
                    $id_attribute[] = $attribute['id_attribute'];
                }
                $id_attribute = array_unique($id_attribute);

                $features = $product->getFeatures();
                $id_feature_value = [];
                foreach ($features as $feature) {
                    $id_feature_value[] = $feature['id_feature_value'];
                }

                $id_categories = $product->getCategories();

                break;
            default:
                $id = 0;
        }

        $id_lang = (int) Context::getContext()->language->id;
        $db = Db::getInstance();
        $pfx = _DB_PREFIX_;
        $primary = self::$definition['primary'];
        $table = self::$definition['table'];

        $sql = "
            select
                {$primary} 
            from
                {$pfx}{$table} 
            where
                active=1 
            and
                (date_start is null or date_start < NOW() or date_start = NOW())
            and
                (date_end is null or date_end > NOW() or date_start = NOW())
            and
                position={$position}
        ";

        switch ($controller) {
            case 'category':
                $sql .= "
                    and
                        (FIND_IN_SET('{$id}', categories) > 0 or categories = '' or categories is null)
                ";
                break;
            case 'product':
                $sql .= "
                    and
                        (FIND_IN_SET('{$id}', products) > 0 or products = '' or products is null)
                ";
                break;
            default:
                break;
        }

        $OR = '';
        if (isset($id_attribute) && $id_attribute) {
            $OR .= self::findInSet($id_attribute, 'attributes');
        }

        if (isset($id_feature_value) && $id_feature_value) {
            $OR .= self::findInSet($id_attribute, 'features');
        }

        if (isset($id_categories) && $id_categories) {
            $OR .= self::findInSet($id_attribute, 'categories');
        }

        if ($OR) {
            $OR = rtrim($OR, 'OR ');
            $sql .= "
                and 
                ({$OR})
            ";
        }

        if ($controller) {
            $sql .= "
                and
                    (FIND_IN_SET('{$controller}', pages) > 0 or pages = '' or pages is null)
            ";
        }

        $sql .= '
            order by 
                position, priority
        ';

        if (self::DEBUG_SQL) {
            \PrestaShopLogger::addLog($sql, 1, 0, 'ModelMpButton', 0);
        }

        try {
            $result = $db->executeS($sql);
        } catch (\Throwable $th) {
            // \PrestaShopLogger::addLog('[' . $th->getMessage() . ',' . $th->getFile() . ',' . $th->getLine() . ']: ' . $sql);
            // \PrestaShopLogger::addLog('ATTR:' . $pattern_attributes . ', FEAT: ' . $pattern_features . ', CAT: ' . $pattern_categories);
            $result = [];
            exit($sql);
        }
        if (!$result) {
            return [];
        }

        $output = [];
        foreach ($result as $id) {
            $but = new ModelMpButton($id[$primary], $id_lang);
            $output[$but->id] = $but;
        }

        return $output;
    }

    private static function findInSet($set, $field)
    {
        $out = '';
        foreach ($set as $item) {
            $out .= "FIND_IN_SET('{$item}', `{$field}`) > 0 OR ";
        }

        $out = rtrim($out, 'OR ');

        return "({$out} or `{$field}` = '' OR `{$field}` is null) OR ";
    }

    public static function getByPosition($position)
    {
        $pfx = _DB_PREFIX_;
        $primary = self::$definition['primary'];
        $table = self::$definition['table'];
        $sql = "
            SELECT 
                {$primary} 
            FROM 
                {$pfx}{$table} 
            WHERE 
                position = $position 
            AND 
                active = 1
            ORDER BY 
                position, priority
        ";
        $rows = Db::getInstance()->executeS($sql);
        if ($rows) {
            return $rows;
        }

        return false;
    }

    public static function getContentAfterAddToCart($id_product)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $buttons = self::getByPosition(ModelMpButton::POSITION_AFTER_CART);
        $products = [];
        $content = [];

        if ($buttons) {
            foreach ($buttons as $button) {
                $model = new ModelMpButton((int) $button['id_mp_button'], $id_lang);
                if (!Validate::isLoadedObject($model)) {
                    continue;
                }
                $products = $model->getProductsList();
                if (in_array($id_product, $products)) {
                    if ($model->date_start == '0000-00-00 00:00:00' && $model->date_end == '0000-00-00 00:00:00') {
                        $content[] = $model->content;

                        continue;
                    }
                    if ($model->date_start == '0000-00-00 00:00:00' && $model->date_end != '0000-00-00 00:00:00') {
                        if (strtotime(date('Y-m-d H:i:s')) <= strtotime($model->date_end)) {
                            $content[] = $model->content;

                            continue;
                        }
                    }
                    if ($model->date_start != '0000-00-00 00:00:00' && $model->date_end == '0000-00-00 00:00:00') {
                        if (strtotime(date('Y-m-d H:i:s')) >= strtotime($model->date_start)) {
                            $content[] = $model->content;

                            continue;
                        }
                    }
                    if ($model->date_start != '0000-00-00 00:00:00' && $model->date_end != '0000-00-00 00:00:00') {
                        if (
                            (strtotime(date('Y-m-d H:i:s')) >= strtotime($model->date_start)) &&
                            (strtotime(date('Y-m-d H:i:s')) <= strtotime($model->date_end))
                        ) {
                            $content[] = $model->content;

                            continue;
                        }
                    }
                }
            }
        }

        return $content;
    }

    public static function getAllPopups($params)
    {
        /*
         * $params = [
         *     'search' => Tools::getValue('search'),
         *     'orderBy' => Tools::getValue('sort'),
         *     'sort' => Tools::getValue('order'),
         *     'limit' => Tools::getValue('limit'),
         *     'offset' => Tools::getValue('offset'),
         *     'filter' => []
         * ];
         */

        $id_lang = (int) Context::getContext()->language->id;
        $db = Db::getInstance();

        $queryCount = new DbQuery();
        $queryCount
            ->select('COUNT(a.id_mp_button)')
            ->from('mp_button', 'a')
            ->innerJoin('mp_button_lang', 'bl', 'a.id_mp_button=bl.id_mp_button and bl.id_lang=' . $id_lang);
        $totalRows = (int) $db->getValue($queryCount);

        $query = new DbQuery();
        $query
            ->select('a.*, bl.title, bl.content, bl.codeblock')
            ->from('mp_button', 'a')
            ->innerJoin('mp_button_lang', 'bl', 'a.id_mp_button=bl.id_mp_button and bl.id_lang=' . $id_lang)
            ->orderBy($params['orderBy'] . ' ' . $params['sort']);

        if ($params['filter']) {
            $filters = json_decode($params['filter'], 1);

            if (isset($filters['id_mp_button'])) {
                $value = (int) $filters['id_mp_button'];
                $query->where("a.id_mp_button = {$value}");
                $queryCount->where("a.id_mp_button = {$value}");
            }

            if (isset($filters['title'])) {
                $value = pSQL($filters['title']);
                $query->where("bl.title LIKE '{$value}%'");
                $queryCount->where("bl.title LIKE '{$value}%'");
            }

            if (isset($filters['delay'])) {
                $value = (int) $filters['date_start'];
                $query->where("a.delay >= {$value}");
                $queryCount->where("a.delay >= {$value}");
            }

            if (isset($filters['expire'])) {
                $value = (int) $filters['expire'];
                $query->where("a.expire >= {$value}");
                $queryCount->where("a.expire >= {$value}");
            }

            if (isset($filters['date_start'])) {
                $value = pSQL($filters['date_start']);
                $query->where("a.date_start >= '{$value}'");
                $queryCount->where("a.date_start >= '{$value}'");
            }

            if (isset($filters['date_end'])) {
                $value = pSQL($filters['date_end']);
                $query->where("a.date_end <= '{$value}'");
                $queryCount->where("a.date_end <= '{$value}'");
            }

            if (isset($filters['active'])) {
                $value = (int) $filters['active'];
                $query->where("a.active = {$value}");
                $queryCount->where("a.active = {$value}");
            }

            if (isset($filters['date_add'])) {
                $value = pSQL($filters['date_end']);
                $query->where("a.date_add >= '{$value}'");
                $queryCount->where("a.date_add >= '{$value}'");
            }

            if (isset($filters['date_upd'])) {
                $value = pSQL($filters['date_end']);
                $query->where("a.date_add >= '{$value}'");
                $queryCount->where("a.date_upd >= '{$value}'");
            }
        }

        if ($params['limit']) {
            $query->limit((int) $params['limit'], (int) $params['offset']);
        }

        $notes = $db->executeS($query);
        $filtered = $db->getValue($queryCount);

        return [
            'rows' => $notes,
            'total' => $filtered,
            'offset' => $params['offset'],
            'limit' => $params['limit'],
            'totalNotFiltered' => $totalRows,
            'query' => $query->build(),
        ];
    }

    public function getFields()
    {
        $this->fixFields();

        return parent::getFields();
    }
}
