<?php
/**
 * 2017 mpSOFT
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    Massimiliano Palermo <info@mpsoft.it>
 *  @copyright 2018 Digital Solutions®
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of mpSOFT
 */

class MpButtonObjectClass extends ObjectModelCore
{
    const POSITION_TOP = 'top';
    const POSITION_LEFT = 'left';
    const POSITION_RIGHT = 'right';
    const POSITION_BOTTOM = 'bottom';
    const POSITION_POPUP = 'center';
    
    public $name;
    public $position;
    public $offset;
    public $icon;
    public $title;
    public $content;
    public $is_active;
    public $id_shop;
    public $id_lang;
    
    /**
     * Object definitions
     */
    public static $definition = array(
        'table' => 'mp_button',
        'primary' => 'id_mp_button',
        'multilang' => false,
        'fields' => array(
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ),
            'id_lang' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ),
            'position' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'size' => 255,
            ),
            'offset' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ),
            'icon' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'size' => 255,
            ),
            'title' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'size' => 255,
            ),
            'content' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => true,
            ),
            'is_active' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ),
        ),
    );
    
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        $context = ContextCore::getContext();
        if ((int)$id_shop == 0) {
            $context->shop->id;
            $id_shop = (int)$context->shop->id;
        }
        if ((int)$id_lang == 0) {
            $id_lang = (int)$context->language->id;
        }
        $this->id_shop = $id_shop;
        $this->id_lang = $id_lang;
        parent::__construct($id, $id_lang, $id_shop);
        if ($id) {
            $this->content = htmlspecialchars_decode($this->content);
        }
    }
    
    public function createTable()
    {
        $sql = array();

        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mp_button` (
            `id_mp_button` int(11) NOT NULL AUTO_INCREMENT,
            `id_shop` int(11) NOT NULL DEFAULT 1,
            `id_lang` int(11) NOT NULL,
            `position` varchar(255) NOT NULL,
            `offset` int(11) NOT NULL DEFAULT 0,
            `icon` varchar(255) NOT NULL DEFAULT \'\',
            `title` varchar(255) NOT NULL,
            `content` text NOT NULL,
            `is_active` bool NOT NULL,
            PRIMARY KEY  (`id_mp_button`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'mp_button` ' .
          'ADD UNIQUE INDEX `idx_title` (`title`);';

        foreach ($sql as $query) {
            try {
                if (Db::getInstance()->execute($query) == false) {
                    return Db::getInstance()->getMsgError();
                }
            } catch (Exception $ex) {
                PrestaShopLoggerCore::addLog($ex->getMessage());
                return true;
            }
        }
        
        return true;
    }
    
    public static function getActiveButtons()
    {
        $db = Db::getInstance();
        $sql = "select id_mp_button from "._DB_PREFIX_."mp_button where is_active=1";
        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        $output = array();
        foreach ($result as $id) {
            $but = new MpButtonObjectClass($id);
            $output[] = $but;
        }
        return $output;
    }
}
