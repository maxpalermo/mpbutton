<?php
/**
* 2007-2018 PrestaShop
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
*  @author    Massimiliano Palermo <mpsoft.it>
*  @copyright 2018 Digital Solution®
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/classes/MpButtonObjectClass.php';

class MpButton extends Module
{
    protected $config_form = false;
    protected $adminClassName = 'AdminMpButton';
    protected $id_lang;
    protected $id_shop;
    public $link;

    public function __construct()
    {
        $this->name = 'mpbutton';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Digital Solutions®';
        $this->need_instance = 0;
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('MP Button display');
        $this->description = $this->l('Whit this module you can dispaly a button on the front office.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
        $this->link = new LinkCore();
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        $obj = new MpButtonObjectClass();
        $result =  $obj->createTable();
        if ($result !== true) {
            $this->_errors[] = sprintf($this->l('Error creating table: %s'), $result);
            return false;
        }

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayTop') &&
            $this->installTab('MpModules', $this->adminClassName, $this->l('MP Button'));
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->uninstallTab($this->adminClassName);
    }
    
    /**
     * Install Main Menu
     * @return int Main menu id
     */
    public function installMainMenu()
    {
        $id_mp_menu = (int) TabCore::getIdFromClassName('MpModules');
        if ($id_mp_menu == 0) {
            $tab = new TabCore();
            $tab->active = 1;
            $tab->class_name = 'MpModules';
            $tab->id_parent = 0;
            $tab->module = null;
            $tab->name = array();
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $this->l('MP Modules');
            }
            $id_mp_menu = $tab->add();
            if ($id_mp_menu) {
                PrestaShopLoggerCore::addLog('id main menu: '.(int)$id_mp_menu);
                return (int)$tab->id;
            } else {
                PrestaShopLoggerCore::addLog('id main menu error');
                return false;
            }
        }
    }
    
    /**
     * Get id of main menu
     * @return int Main menu id
     */
    public function getMainMenuId()
    {
        $id_menu = (int)Tab::getIdFromClassName('MpModules');
        return $id_menu;
    }
    
    /**
     *
     * @param string $parent Parent tab name
     * @param type $class_name Class name of the module
     * @param type $name Display name of the module
     * @param type $active If true, Tab menu will be shown
     * @return boolean True if successfull, False otherwise
     */
    public function installTab($parent, $class_name, $name, $active = 1)
    {
        // Create new admin tab
        $tab = new Tab();
        $id_parent = (int)Tab::getIdFromClassName($parent);
        PrestaShopLoggerCore::addLog('Install main menu: id=' . (int)$id_parent);
        if (!$id_parent) {
            $id_parent = $this->installMainMenu();
            if (!$id_parent) {
                $this->_errors[] = $this->l('Unable to install main module menu tab.');
                return false;
            }
            PrestaShopLoggerCore::addLog('Created main menu: id=' . (int)$id_parent);
        }
        $tab->id_parent = (int)$id_parent;
        $tab->name      = array();
        
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }
        
        $tab->class_name = $class_name;
        $tab->module     = $this->name;
        $tab->active     = $active;
        
        if (!$tab->add()) {
            $this->_errors[] = $this->l('Error during Tab install.');
            return false;
        }
        return true;
    }
    
    /**
     *
     * @param string pe $class_name Class name of the module
     * @return boolean True if successfull, False otherwise
     */
    public function uninstallTab($class_name)
    {
        $id_tab = (int)Tab::getIdFromClassName($class_name);
        if ($id_tab) {
            $tab = new Tab((int)$id_tab);
            $result = $tab->delete();
            if (!$result) {
                $this->_errors[] = $this->l('Unable to remove module menu tab.');
            }
            return $result;
        }
    }

    public function hookDisplayTop()
    {
        $mpButtons = MpButtonObjectClass::getActiveButtons();
        $html = array();
        foreach ($mpButtons as $but) {
            if ($but->position == MpButtonObjectClass::POSITION_BOTTOM) {
                $smarty = Context::getContext()->smarty;
                $smarty->assign(
                    array(
                        'content' => $but->content,
                        'offset' => $but->offset
                    )
                );
                $button = $smarty->fetch($this->getPath().'views/templates/front/bottom.tpl');
                $html[] = $button;
            }
        }
        
        return implode('<br>', $html);
    }
    
    /**
     * Get The URL path of this module
     * @return string The URL of this module
     */
    public function getUrl()
    {
        return $this->_path;
    }
    
    /**
     * Return the physical path of this module
     * @return string The path of this module
     */
    public function getPath()
    {
        return $this->local_path;
    }
}
