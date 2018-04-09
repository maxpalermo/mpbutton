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
 *  @author    mpSOFT <info@mpsoft.it>
 *  @copyright 2017 mpSOFT Massimiliano Palermo
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of mpSOFT
 */

ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

class AdminMpButtonController extends ModuleAdminController
{
    public $link;
    protected $id_lang;
    protected $id_shop;
    protected $messages;
    protected $local_path;
    
    public function __construct()
    {   
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->className = 'AdminMpButton';
        $this->token = Tools::getValue('token', Tools::getAdminTokenLite($this->className));
        parent::__construct();
        $this->id_lang = (int)ContextCore::getContext()->language->id;
        $this->id_shop = (int)ContextCore::getContext()->shop->id;
    }

    public function initContent()
    {
        $this->smarty = Context::getContext()->smarty;
        $this->link = new LinkCore();
        $this->errors = array();
        $this->messages = array();
        $this->addJqueryUI('ui.progressbar');
        
        require_once $this->module->getPath().'classes/MpButtonObjectClass.php';
        
        if (Tools::isSubmit('ajax')) {
            $action = 'ajaxProcess'.ucfirst(Tools::getValue('action'));
            $this->$action();
            $this->content = $this->initList().$this->initScript();
        } elseif (Tools::isSubmit('submitButtonSave')) {
            if(!$this->validateForm()) {
                $this->errors[] = sprintf(
                    $this->l('Error saving data: %d, %s'),
                    Db::getInstance()->getNumberError(),
                    Db::getInstance()->getMsgError()
                );  
            } else {
                $this->confirmations[] = $this->l('Button saved');
            }
            $this->content = $this->initList().$this->initScript();
        } elseif (Tools::isSubmit('submitNewButton')) {
            $this->content = $this->initForm();
        } else {
            $this->content = $this->initList().$this->initScript();
        }
        
        parent::initContent();
    }
    
    protected function validateForm()
    {
        $id = (int)Tools::getValue('hidden_id_mp_button', 0);
        $title = Tools::getValue('input_text_title', '');
        $content = Tools::getValue('input_text_content', '');
        $is_active = (int)Tools::getValue('input_switch_is_active', 0);
        $position = Tools::getvalue('input_select_position', 'top');
        $offset = (int)Tools::getValue('input_text_offset', 0);
        require_once $this->module->getPath().'classes/MpButtonObjectClass.php';
        $MpButton = new MpButtonObjectClass($id);
        $MpButton->icon = 'fa-cube';
        $MpButton->is_active = $is_active;
        $MpButton->title = $title;
        $MpButton->name = '';
        $MpButton->content = htmlspecialchars($content);
        $MpButton->position = $position;
        $MpButton->offset = $offset;
        if ($id) {
            try {
                return $MpButton->update();
            } catch (Exception $ex) {
                $this->warnings[] = $ex->getMessage();
                return false;
            }
        } else {
            try {
                return $MpButton->add();
            } catch (Exception $ex) {
                $this->warnings[] = $ex->getMessage();
                return false;
            }
        }
    }
    
    private function initForm()
    {
        $form = new HelperFormCore();
        $form->table = 'mp_button';
        $form->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $form->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $form->submit_action = 'submit_form';
        $form->currentIndex = $this->context->link->getAdminLink($this->className, false);
        $form->token = Tools::getAdminTokenLite($this->className);
        $form->bootstrap = true;
        $form->context = $this->context;
        $form->fields_value = $this->getFormValues();
        $form->identifier = 'id_mp_button';
        $form->show_toolbar = true;
        $form->tpl_vars = array(
            'fields_value' => $this->getFormValues(),
            'languages' => $this->context->controller->getLanguages(),
        );
        return $form->generateForm(array($this->getFormFields()));
    }
    
    protected function getFormFields()
    {
        $popup_preview_link = '<div class="form-group"><button class="btn btn-default"><i class="icon fa-eye"></i></button></div>';
        $fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Button properties'),
					'icon' => 'icon-edit',
					'badge' => 'icon-edit',
				),
				'input' => array(
					array(
						'type' => 'hidden',
						'name' => 'hidden_id_mp_button',
					),
					array(
						'type' => 'html',
						'name' =>  $popup_preview_link,
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Is active?'),
						'name' => 'input_switch_is_active',
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Title'),
						'name' => 'input_text_title',
						'class' => 'text-strong',
					),
					array(
						'type' => 'textarea',
						'label' => $this->l('Content'),
						'name' => 'input_text_content',
						'cols' => 40,
						'rows' => 10,
						'class' => 'rte',
						'autoload_rte' => true,
					),
                    array(
						'type' => 'select',
						'label' => $this->l('Position'),
						'name' => 'input_select_position',
						'options' => array(
                            'query' => array(
                                array(
                                    'id' => MpButtonObjectClass::POSITION_TOP,
                                    'value' => $this->l('Top'),
                                ),
                                array(
                                    'id' => MpButtonObjectClass::POSITION_BOTTOM,
                                    'value' => $this->l('Bottom'),
                                ),
                                array(
                                    'id' => MpButtonObjectClass::POSITION_LEFT,
                                    'value' => $this->l('Left'),
                                ),
                                array(
                                    'id' => MpButtonObjectClass::POSITION_RIGHT,
                                    'value' => $this->l('Right'),
                                ),
                                array(
                                    'id' => MpButtonObjectClass::POSITION_POPUP,
                                    'value' => $this->l('Center'),
                                ),
                            ),
                            'id' => 'id',
                            'name' => 'value',
                        )
					),
                    array(
						'type' => 'text',
						'label' => $this->l('Offset'),
						'name' => 'input_text_offset',
						'class' => 'text-strong text-right fixed-width-sm',
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
					'name' => 'submitButtonSave',
					'class' => 'btn btn-default pull-right'
				),
				'buttons' => array(
					array(
						'href' => $this->context->link->getAdminLink($this->className),
						'title' => $this->l('Back to list'),
						'icon' => 'process-icon-back'
					),
				),
			),
		);
        
        return $fields_form;
    }
    
    protected function getFormValues()
    {
        $id = (int)Tools::getValue('id', 0);
        if ($id) {
            require_once $this->module->getPath().'classes/MpButtonObjectClass.php';
            $MpButton = new MpButtonObjectClass($id);
            return array(
                'hidden_id_mp_button' => (int)$MpButton->id,
                'input_switch_is_active' => (int)$MpButton->is_active,
                'input_text_title' => $MpButton->title,
                'input_text_content' => htmlspecialchars_decode($MpButton->content),
                'input_select_position' => $MpButton->position,
                'input_text_offset' => $MpButton->offset,
            );
        } else {
            return array(
                'hidden_id_mp_button' => (int)Tools::getValue('id', 0),
                'input_switch_is_active' => 0,
                'input_text_title' => '',
                'input_text_content' => '',
                'input_select_position' => MpButtonObjectClass::POSITION_TOP,
                'input_text_offset' => 0,
            );
        }
    }


    private function initList()
    {
        $currentIndex = $this->context->link->getAdminLink($this->className, false);
        $token = Tools::getAdminTokenLite($this->className);
        $list = new HelperListCore();
        $list->title = $this->l('Buttons list');
        $list->shopLinkType = '';
        $list->table = 'mp_button';
        $list->no_link = true;
        $list->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected elements?'),
                'icon' => 'process-icon-delete',
            ),
        );
        $list->show_toolbar = true;
        $list->identifier = 'id_mp_button';
        $list->token = $token;
        $list->currentIndex = $currentIndex;
        $list->simple_header = false;
        $list->toolbar_btn = array(
            'new' => array(
                'desc' => 'Add new button',
                'icon' => 'process-icon-add',
                'href' => $this->context->link->getAdminLink($this->className).'&submitNewButton',
                'token' => $token,
            ),
            'back' => array(
                'desc' => 'Back to dashboard',
                'icon' => 'process-icon-back',
                'href' => $this->context->link->getAdminLink('AdminDashboard'),
                'token' => '',
            ),
        );
        $values = $this->getListValues();
        $list->listTotal = count($values);
        return $list->generateList($values, $this->getListFields());
    }
    
    protected function getListValues()
    {
        $currentIndex = $this->context->link->getAdminLink($this->className).'&submitNewButton';
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('*')
            ->from('mp_button')
            ->orderBy('title');
        $result = $db->executeS($sql);
        if ($result) {
            foreach($result as &$row) {
                $id = (int)$row['id_mp_button'];
                $row['icon'] = '<i class="icon '.$row['icon'].'"></i>';
                $row['is_active'] = $row['is_active']==1?$this->toggle(true, $id):$this->toggle(false, $id);
                $row['button'] = '<a href="javascript:location.href=\''.$currentIndex.'&id='.$row['id_mp_button'].'\'">'
                    .'<button type="button" class="btn btn-default">'
                    .'<i class="icon icon-edit"></i> '
                    .$this->l('Edit')
                    .'</button>'
                    .'</a>';
            }
        } elseif (!$result && $db->getNumberError()) {
            $this->errors[] = sprintf(
                $this->l('Error getting values: %d, %s'),
                $db->getNumberError(),
                $db->getMsgError()
            );
            return array();
        }
        return $result;
    }
    
    protected function toggle($is_active, $value)
    {
        if ($is_active) {
            $btn = $this->createElement(
                'i',
                'icon icon-check',
                'color: #72C279; cursor: pointer;',
                $value,
                'javascript:mpbutton_ToggleActive(this)'
            );
        } else {
            $btn = $this->createElement(
                'i',
                'icon icon-times',
                'color: #C27279; cursor: pointer;',
                $value,
                'javascript:mpbutton_ToggleActive(this)'
            );
        }
        return $btn;
    }
    
    protected function createElement($element, $class, $style, $value, $onclick)
    {
        $el = "<".$element
            ." class='".$class
            ."' style='".$style
            ."' value='".$value
            ."' onclick='".$onclick
            ."'></".$element.">";
        return $el;
    }
    
    protected function getListFields()
    {
        $fields_list = array(
            'id_mp_button' => array(
                'title' => $this->l('Id'),
                'align' => 'text-right',
                'width' => '32',
                'search' => false,
            ),
            'icon' => array(
                'title' => $this->l('Icon'),
                'align' => 'text-center',
                'width' => '48',
                'type' => 'bool',
                'float' => true,
                'search' => false,
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'align' => 'text-left',
                'width' => 'auto',
                'search' => false,
            ),
            'is_active' => array(
                'title' => $this->l('Is active'),
                'align' => 'text-center',
                'width' => 32,
                'type' => 'bool',
                'float' => true,
                'hint' => $this->l('Click here to activate or deactivate button.'),
                'search' => false,
            ),
            'button' => array(
                'type' => 'bool',
                'float' => true,
                'align' => 'text-center',
                'search' => false,
                'title' => $this->l('Actions'),
            )
        );
        return $fields_list;
    }
    
    public function ajaxProcessToggleStatus()
    {
        $id = (int)Tools::getValue('id_mp_button', 0);
        if (!$id) {
            print Tools::jsonEncode(
                array(
                    'status' => false,
                    'message' => $this->l('Id button not valid'),
                )
            );
            exit();
        }
        $db = Db::getInstance();
        $result = $db->execute('update '._DB_PREFIX_.'mp_button set is_active=NOT is_active where id_mp_button='.(int)$id);
        if (!$result) {
            print Tools::jsonEncode(
                array(
                    'status' => false,
                    'message' => $db->getMsgError(),
                )
            );
            exit();
        }
        $sql = 'select is_active from '._DB_PREFIX_.'mp_button where id_mp_button='.(int)$id;
        $active = (int)$db->getValue($sql);
        print Tools::jsonEncode(
            array(
                'toggle' => $active,
            )
        );
        exit();
    }
    
    private function initScript()
    {
        $smarty = Context::getContext()->smarty;
        $smarty->assign(
            array(
                'ajax_url' => $this->context->link->getAdminLink($this->className, false),
                'ajax_token' => Tools::getAdminTokenLite($this->className),
            )
        );
        $script = $smarty->fetch($this->module->getPath().'views/templates/admin/script.tpl');
        return $script;
    }
    
    public function setMedia()
    {
        parent::setMedia();
        $this->addJqueryUI('ui.dialog');
        $this->addJqueryUI('ui.progressbar');
        $this->addJqueryUI('ui.draggable');
        $this->addJqueryUI('ui.effect');
        $this->addJqueryUI('ui.effect-slide');
        $this->addJqueryUI('ui.effect-fold');
        PrestaShopLoggerCore::addLog($this->module->name .  ': setMedia');
    }
    
    public function getCategories()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $output = array();
        $sql->select('id_category as id')
                ->select('name')
                ->from('category_lang')
                ->where('id_shop = ' . (int)$this->id_shop)
                ->where('id_lang = ' . (int)$this->id_lang)
                ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        $selected = explode(',',Tools::getValue('input_select_categories', ''));
        foreach ($result as $row) {
            $is_selected = in_array($row['id'], $selected);
            $output[] = array(
                'id' => $row['id'],
                'name' => $row['name'],
                'selected' => $is_selected,
            );
        }
        return $output;
    }
    
    public function getPositions()
    {
        $output = array(
            array(
                'id'=>'top',
                'name'=>'top'
            ),
            array(
                'id'=>'left',
                'name'=>'left'
            ),
            array(
                'id'=>'right',
                'name'=>'right'
            ),
            array(
                'id'=>'bottom',
                'name'=>'bottom'
            ),
            array(
                'id'=>'fixed-top',
                'name'=>'fixed-top'
            ),
            array(
                'id'=>'fixed-bottom',
                'name'=>'fixed-bottom'
            ),
            array(
                'id'=>'popup',
                'name'=>'popup'
            ),
        );
    }
    
    public function getManufacturers()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $output = array();
        $sql->select('id_manufacturer as id')
                ->select('name')
                ->from('manufacturer')
                ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        $selected = explode(',',Tools::getValue('input_select_manufacturers', ''));
        foreach ($result as $row) {
            $is_selected = in_array($row['id'], $selected);
            $output[] = array(
                'id' => $row['id'],
                'name' => $row['name'],
                'selected' => $is_selected,
            );
        }
        return $output;
    }
    
    public function getSuppliers()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $output = array();
        $sql->select('id_supplier as id')
                ->select('name')
                ->from('supplier')
                ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        $selected = explode(',',Tools::getValue('input_select_manufacturers', ''));
        foreach ($result as $row) {
            $is_selected = in_array($row['id'], $selected);
            $output[] = array(
                'id' => $row['id'],
                'name' => $row['name'],
                'selected' => $is_selected,
            );
        }
        return $output;
    }
}
