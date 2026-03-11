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
 *
 * @update    2026-03-06
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use MpSoft\MpButton\Helpers\GetTwigEnvironment;
use MpSoft\MpButton\Models\ModelMpButton;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class MpButton extends Module
{
    protected $adminClassName = 'AdminMpButton';
    protected $id_lang;
    protected $id_shop;
    protected $adminClass = 'AdminMpButton';

    public function __construct()
    {
        $this->name = 'mpbutton';
        $this->tab = 'front_office_features';
        $this->version = '1.1.6';
        $this->author = 'Massimiliano Palermo';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('MP Button display');
        $this->description = $this->l('Whit this module you can display a button on the front office.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->ps_versions_compliancy = ['min' => '8.1', 'max' => '8.99'];
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('actionAdminControllerSetMedia') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            $this->registerHook('displayTop') &&
            $this->registerHook('displayAfterAddToCart') &&
            $this->installTab() &&
            ModelMpButton::install();
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->uninstallTab();
    }

    protected function installTab()
    {
        $parentClass = 'AdminOtherModulesMp';
        $tabRepository = static::getTabRepository();
        $parentId = (int) $tabRepository->findOneIdByClassName($parentClass);

        if (!$parentId) {
            $parentTab = new Tab();
            $parentTab->class_name = $parentClass;
            $parentTab->module = $this->name;
            $parentTab->id_parent = $parentId;
            $parentTab->active = 1;
            $parentTab->icon = 'extension';
            foreach (Language::getLanguages() as $language) {
                $parentTab->name[$language['id_lang']] = $this->l('ALTRI MODULI');
            }
            $parentTab->add();
            $parentId = (int) $parentTab->id;
        }

        $childClass = $this->adminClass;
        $childId = $tabRepository->findOneIdByClassName($childClass);
        $tab = $childId ? new Tab($childId) : new Tab();
        $tab->class_name = $childClass;
        $tab->module = $this->name;
        $tab->id_parent = $parentId;
        $tab->active = 1;
        $tab->icon = 'icon-note';
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = $this->l('Mp Mostra Popup');
        }

        return $childId ? $tab->update() : $tab->add();
    }

    protected function uninstallTab()
    {
        $childClass = $this->adminClass;
        $tabRepository = static::getTabRepository();
        $childId = $tabRepository->findOneIdByClassName($childClass);
        if ($childId) {
            $tab = new Tab($childId);
            $tab->delete();
        }

        return true;
    }

    protected static function getTabRepository()
    {
        $tabRepository = SymfonyContainer::getInstance()->get('prestashop.core.admin.tab.repository');

        return $tabRepository;
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        // nothing;
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        $this->context->controller->addCSS($this->getLocalPath() . 'views/assets/css/front.css');

        $this->context->controller->addJS([
            $this->getLocalPath() . 'views/assets/js/front.js',
            $this->getLocalPath() . 'views/assets/js/sticky-manager.js',
            $this->getLocalPath() . 'views/assets/js/sticky-positions.js',
        ]);
    }

    public function hookDisplayAfterAddToCart($params)
    {
        $html = ModelMpButton::getContentAfterAddToCart((int) $params['product']->id);
        if ($html) {
            return implode('', $html);
        }
    }

    public function hookDisplayTop()
    {
        $controller = Tools::getValue('controller');

        $buttons_top = ModelMpButton::getActiveButtonsByPosition(ModelMpButton::POSITION_TOP, $controller);
        $buttons_left = ModelMpButton::getActiveButtonsByPosition(ModelMpButton::POSITION_LEFT, $controller);
        $buttons_right = ModelMpButton::getActiveButtonsByPosition(ModelMpButton::POSITION_RIGHT, $controller);
        $buttons_center_page = ModelMpButton::getActiveButtonsByPosition(ModelMpButton::POSITION_POPUP, $controller);
        $buttons_bottom = ModelMpButton::getActiveButtonsByPosition(ModelMpButton::POSITION_BOTTOM, $controller);
        $buttons_after_cart = ModelMpButton::getActiveButtonsByPosition(ModelMpButton::POSITION_AFTER_CART, $controller);
        $buttons_desc = ModelMpButton::getActiveButtonsByPosition(ModelMpButton::POSITION_DESC, $controller);

        $params = [
            'frontPage' => $controller,
            'buttons_top' => $buttons_top,
            'buttons_left' => $buttons_left,
            'buttons_right' => $buttons_right,
            'buttons_center_page' => $buttons_center_page,
            'buttons_bottom' => $buttons_bottom,
            'buttons_after_cart' => $buttons_after_cart,
            'buttons_description' => $buttons_desc,
        ];

        $html = $this->renderTwig('Front/ShowButton', $params);

        return $html;
    }

    public static function renderTwig($path, $params)
    {
        $module = Module::getInstanceByName('mpbutton');
        $twig = new GetTwigEnvironment($module->name);
        $twig->load('@ModuleTwig/' . $path);

        return $twig->render($params);
    }
}
