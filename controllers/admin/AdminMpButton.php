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
require_once _PS_MODULE_DIR_ . 'mpbutton/src/Models/ModelMpButton.php';
require_once _PS_MODULE_DIR_ . 'mpbutton/src/Helpers/MpButtonUtility.php';

use MpSoft\MpButton\Helpers\GetTwigEnvironment;
use MpSoft\MpButton\Models\ModelMpButton;

class AdminMpButtonController extends ModuleAdminController
{
    protected $id_button;
    protected $languages;
    protected $default_language;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->id_button = (int) Tools::getValue('id', 0);

        if (Tools::isSubmit('ajax') && Tools::getValue('action')) {
            $action = 'ajaxProcess' . Tools::ucfirst(Tools::getValue('action'));
            if (method_exists($this, $action)) {
                $response = $this->$action();
                if (isset($response['httpCode'])) {
                    $httpCode = $response['httpCode'];
                    unset($response['httpCode']);
                } else {
                    $httpCode = 200;
                }
                $this->response($response, $httpCode);
            }
        }
    }

    public static function renderTwig($path, $params)
    {
        $twig = new GetTwigEnvironment('mpbutton');
        $twig->load('@ModuleTwig/' . $path);

        return $twig->render($params);
    }

    protected function response($data, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);

        exit;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJqueryUI('ui.dialog');
        $this->addJqueryUI('ui.progressbar');
        $this->addJqueryUI('ui.draggable');
        $this->addJqueryUI('ui.effect');
        $this->addJqueryUI('ui.effect-slide');
        $this->addJqueryUI('ui.effect-fold');
        $this->addJqueryUI('ui.datepicker');
        $this->addJqueryPlugin('chosen');
        $this->addJS(_PS_JS_DIR_ . 'tiny_mce/tinymce.min.js');
    }

    public function getEditPage()
    {
        $id = (int) Tools::getValue('id');
        $path = 'Admin/PopupEditor';

        $model = new ModelMpButton($id);

        $params = [
            'model' => $model,
            'backUrl' => $this->context->link->getAdminLink($this->controller_name),
            'endpoint' => $this->context->link->getAdminLink($this->controller_name),
            'idPopup' => $id,
            'idLang' => $this->context->language->id,
            'categoriesTree' => json_encode($this->getCategories($id)),
            'featuresTree' => json_encode($this->getFeatures($id)),
            'attributesTree' => json_encode($this->getAttributes($id)),
        ];

        $page = self::renderTwig($path, $params);

        return $page;
    }

    public function postProcess()
    {
        return parent::postProcess();
    }

    public function initContent()
    {
        if (Tools::getValue('action') === 'edit') {
            $this->content = $this->getEditPage();
        } else {
            /** @var MpButton */
            $module = $this->module;
            $template = $module->renderTwig('Admin/AdminTable', [
                'endpoint' => $this->context->link->getAdminLink($this->controller_name),
            ]);

            $this->content = $template;
        }

        parent::initContent();
    }

    public function ajaxProcessFetchAllPopups()
    {
        $params = [
            'search' => Tools::getValue('search'),
            'id_order' => Tools::getValue('orderId'),
            'orderBy' => Tools::getValue('sort'),
            'sort' => Tools::getValue('order'),
            'limit' => Tools::getValue('limit'),
            'offset' => Tools::getValue('offset'),
            'type' => Tools::getValue('type'),
            'filter' => Tools::getValue('filter'),
        ];

        $data = ModelMpButton::getAllPopups($params);

        $this->response([
            'success' => true,
            'rows' => $data['rows'],
            'total' => $data['total'],
            'totalNotFiltered' => $data['totalNotFiltered'],
            'offset' => $data['offset'],
            'limit' => $data['limit'],
            'query' => $data['query']
        ]);
    }

    public function ajaxProcessGetContent()
    {
        $id = (int) Tools::getValue('id');
        $idLang = (int) Tools::getValue('idLang');
        $model = new ModelMpButton($id, $idLang);

        return [
            'active' => $model->active,
            'title' => $model->title,
            'content' => $model->content,
            'position' => $model->position,
            'priority' => $model->priority,
            'positions' => [
                0 => 'Centro',
                1 => 'Sinistra',
                2 => 'Destra',
                3 => 'Sopra',
                4 => 'Sotto',
                98 => 'Carrello',
                99 => 'Prodotto',
            ],
        ];
    }

    public function ajaxProcessShowSection()
    {
        $id = (int) Tools::getValue('id');
        $idLang = (int) Tools::getValue('idLang');
        $section = Tools::getValue('section');

        return $this->renderSection($id, $idLang, $section);
    }

    public function ajaxProcessDelete()
    {
        $id = (int) Tools::getValue('id');
        $model = new ModelMpButton($id);
        $error = '';

        try {
            if (Validate::isLoadedObject($model)) {
                $delete = $model->delete();
                if (!$delete) {
                    $error = 'Errore DB ' + Db::getInstance()->getMsgError();
                }
            } else {
                $error = 'Elemento non trovato.';
            }
        } catch (\Throwable $th) {
            $error = $th->getMessage();
        }

        return [
            'id' => $id,
            'success' => $error ? false : true,
            'error' => $error
        ];
    }

    public function ajaxProcessSaveSection()
    {
        $id = (int) Tools::getValue('id');
        $idLang = (int) Tools::getValue('idLang');
        $section = (string) Tools::getValue('section');
        $dataRaw = Tools::getValue('data');

        if (!$idLang) {
            $idLang = (int) $this->context->language->id;
        }

        $payload = [];
        if ($dataRaw) {
            $decoded = json_decode($dataRaw, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return [
                    'success' => false,
                    'message' => 'Payload non valido',
                ];
            }
            $payload = $decoded;
        }

        if ($id == 0 && $section != 'content') {
            return [
                'success' => false,
                'message' => 'Devi salvare il contenuto del banner prima di proseguire!',
            ];
        }

        $model = new ModelMpButton($id);

        $section = Tools::strtolower(trim($section));
        switch ($section) {
            case 'content':
                if (array_key_exists('active', $payload)) {
                    $model->active = (bool) $payload['active'];
                }
                if (array_key_exists('position', $payload) && $payload['position'] !== null && $payload['position'] !== '') {
                    $model->position = (int) $payload['position'];
                }
                if (array_key_exists('priority', $payload) && $payload['priority'] !== null && $payload['priority'] !== '') {
                    $model->priority = (int) $payload['priority'];
                }
                if (array_key_exists('title', $payload)) {
                    $model->title[$idLang] = (string) $payload['title'];
                }
                if (array_key_exists('content', $payload)) {
                    $model->content[$idLang] = (string) $payload['content'];
                }
                break;

            case 'timer':
                if (array_key_exists('delay', $payload) && $payload['delay'] !== null && $payload['delay'] !== '') {
                    $model->delay = (int) $payload['delay'];
                }
                if (array_key_exists('expire', $payload) && $payload['expire'] !== null && $payload['expire'] !== '') {
                    $model->expire = (int) $payload['expire'];
                }
                if (array_key_exists('date_start', $payload)) {
                    $model->date_start = $this->toMysqlDateTime($payload['date_start']);
                }
                if (array_key_exists('date_end', $payload)) {
                    $model->date_end = $this->toMysqlDateTime($payload['date_end']);
                }
                break;

            case 'visibility':
                if (array_key_exists('customer_groups', $payload)) {
                    $model->customer_groups = $this->arrayToString($payload['customer_groups']);
                }
                if (array_key_exists('pages', $payload)) {
                    $model->pages = $this->arrayToString($payload['pages']);
                }
                if (array_key_exists('manufacturers', $payload)) {
                    $model->manufacturers = $this->arrayToString($payload['manufacturers']);
                }
                if (array_key_exists('suppliers', $payload)) {
                    $model->suppliers = $this->arrayToString($payload['suppliers']);
                }
                if (array_key_exists('products', $payload)) {
                    $model->products = $this->arrayToString($payload['products']);
                }
                break;

            case 'categories':
                if (array_key_exists('categories', $payload)) {
                    $model->categories = is_array($payload['categories']) ? $payload['categories'] : [];
                }
                break;

            case 'features':
                if (array_key_exists('features', $payload)) {
                    $model->features = is_array($payload['features']) ? $payload['features'] : [];
                }
                break;

            case 'attributes':
                if (array_key_exists('attributes', $payload)) {
                    $model->attributes = is_array($payload['attributes']) ? $payload['attributes'] : [];
                }
                break;

            default:
                return [
                    'id' => $id,
                    'success' => false,
                    'message' => 'Sezione non valida',
                ];
        }

        try {
            $model->id_employee = (int) $this->context->employee->id;
            if (Validate::isLoadedObject($model)) {
                $model->date_upd = date('Y-m-d H:i:s');
                $ok = (bool) $model->update(true);
            } else {
                $model->date_add = date('Y-m-d H:i:s');
                $model->date_upd = null;
                $ok = (bool) $model->add(false, true);
            }
            $this->fixDates($model->id);
        } catch (Exception $e) {
            return [
                'id' => $model->id ?? 0,
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        return [
            'id' => $model->id ?? $id,
            'success' => $ok,
            'message' => $ok ? 'OK' : 'Errore salvataggio',
        ];
    }

    private function arrayToString($array, $delimitator = ',')
    {
        if (is_array($array)) {
            return implode($delimitator, $array);
        }

        return (string) $array;
    }

    private function fixDates($id)
    {
        $pfx = _DB_PREFIX_;
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql
            ->select('date_start,date_end,date_add,date_upd')
            ->from('mp_button')
            ->where('id_mp_button=' . (int) $id);
        $row = $db->getRow($sql);
        $blank_date = '0000-00-00 00:00:00';
        $set = '';

        if ($row['date_start'] == $blank_date) {
            $set .= '`date_start` = null,';
        }
        if ($row['date_end'] == $blank_date) {
            $set .= '`date_end` = null,';
        }
        if ($row['date_add'] == $blank_date) {
            $set .= '`date_add` = NOW(),';
        }
        if ($row['date_upd'] == $blank_date) {
            $set .= '`date_upd` = null,';
        }

        $set = rtrim($set, ',');
        if ($set) {
            $update = "update {$pfx}mp_button set {$set} where id_mp_button={$id}";
            try {
                return $db->execute($update);
            } catch (\Throwable $th) {
                return $th->getMessage();
            }
        }
    }

    public function ajaxProcessUploadMceImage()
    {
        if (!isset($_FILES['file'])) {
            return [
                'success' => false,
                'message' => 'Nessun file ricevuto',
                'httpCode' => 400,
            ];
        }

        $file = $_FILES['file'];
        if (!is_array($file) || empty($file['tmp_name']) || !empty($file['error'])) {
            return [
                'success' => false,
                'message' => 'Upload non valido',
                'httpCode' => 400,
            ];
        }

        $tmpName = (string) $file['tmp_name'];
        $originalName = (string) $file['name'];

        $ext = Tools::strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!$ext || !in_array($ext, $allowed, true)) {
            return [
                'success' => false,
                'message' => 'Estensione non consentita',
                'httpCode' => 400,
            ];
        }

        $uploadDir = _PS_ROOT_DIR_ . '/img/mpbutton/';
        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                return [
                    'success' => false,
                    'message' => 'Impossibile creare la cartella img/mpbutton',
                    'httpCode' => 500,
                ];
            }
        }

        $safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $safeBase = trim((string) $safeBase, '_');
        if ($safeBase === '') {
            $safeBase = 'image';
        }

        $targetName = $safeBase . '-' . date('YmdHis') . '-' . Tools::passwdGen(6) . '.' . $ext;
        $targetPath = $uploadDir . $targetName;

        if (!@move_uploaded_file($tmpName, $targetPath)) {
            return [
                'success' => false,
                'message' => 'Impossibile salvare il file',
                'httpCode' => 500,
            ];
        }

        @chmod($targetPath, 0644);

        $publicUrl = rtrim((string) Tools::getShopDomainSsl(true), '/') . __PS_BASE_URI__ . 'img/mpbutton/' . $targetName;

        return [
            'location' => $publicUrl,
        ];
    }

    private function toMysqlDateTime($value)
    {
        if ($value === null) {
            return null;
        }

        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }

        $s = str_replace('T', ' ', $s);
        if (strlen($s) === 16) {
            $s .= ':00';
        }

        return pSQL($s);
    }

    private function renderSection(int $id, int $idLang, string $section)
    {
        $path = "Admin/partials/{$section}";
        $back = $this->context->link->getAdminLink($this->controller_name);
        $model = new ModelMpButton($id, $idLang);
        $fields = $model->getFields();

        $params = [
            'positions' => [
                0 => 'Centro',
                1 => 'Sinistra',
                2 => 'Destra',
                3 => 'Sopra',
                4 => 'Sotto',
                98 => 'Carrello',
                99 => 'Prodotto',
            ],
            'customerGroups' => $this->getCustomerGroups(),
            'pages' => [
                'category' => 'Categorie',
                'product' => 'Prodotto',
                'index' => 'Home page',
                'authentication' => 'Login',
                'cms' => 'CMS'
            ],
            'manufacturers' => $this->getManufacturers(),
            'suppliers' => $this->getSuppliers(),
            'products' => $this->getProducts(),
            'idPopup' => $id,
            'idLang' => $this->context->language->id,
            'languages' => Language::getLanguages(),
            'fields' => $fields,
            'backUrl' => $back
        ];

        $html = self::renderTwig($path, $params);

        return [
            'html' => $html,
        ];
    }

    private function getCustomerGroups()
    {
        $id_lang = (int) Context::getContext()->language->id;
        $items = Group::getGroups($id_lang);
        $out = [];
        foreach ($items as $item) {
            $out[$item['id_group']] = $item['name'];
        }

        return $out;
    }

    private function getManufacturers()
    {
        $id_lang = (int) Context::getContext()->language->id;
        $items = Manufacturer::getManufacturers(false, $id_lang, false);
        $out = [];
        foreach ($items as $item) {
            $out[$item['id_manufacturer']] = $item['name'];
        }

        return $out;
    }

    private function getSuppliers()
    {
        $id_lang = (int) Context::getContext()->language->id;
        $items = Supplier::getSuppliers(false, $id_lang, false);
        $out = [];
        foreach ($items as $item) {
            $out[$item['id_supplier']] = $item['name'];
        }

        return $out;
    }

    private function getProducts()
    {
        $id_lang = (int) Context::getContext()->language->id;
        $items = Product::getProducts($id_lang, 0, 99999, 'id_product', 'asc');
        $out = [];
        foreach ($items as $item) {
            $out[$item['id_product']] = "({$item['reference']}) {$item['name']}";
        }

        return $out;
    }

    private function getCategories($id)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $model = new ModelMpButton((int) $id);
        $categories = Category::getCategories($id_lang, false, true);
        $selected = array_map('intval', $model->categories ?: []);

        $buildTree = function ($parentId) use (&$buildTree, $categories, $selected) {
            $out = [];
            if (!isset($categories[$parentId]) || !is_array($categories[$parentId])) {
                return $out;
            }

            foreach ($categories[$parentId] as $idCategory => $category) {
                $idCategory = (int) $idCategory;
                $name = '';
                if (isset($category['infos']['name'])) {
                    $name = (string) $category['infos']['name'];
                }
                if ($name === '') {
                    $name = '#' . $idCategory;
                }

                $children = $buildTree($idCategory);
                if (!empty($children)) {
                    $node = [
                        '(seleziona)' => [
                            'value' => $idCategory,
                            'selected' => in_array($idCategory, $selected, true) ? 1 : 0,
                        ],
                    ];
                    foreach ($children as $childKey => $childValue) {
                        $node[$childKey] = $childValue;
                    }
                    $out[$name] = $node;
                } else {
                    $out[$name] = [
                        'value' => $idCategory,
                        'selected' => in_array($idCategory, $selected, true) ? 1 : 0,
                    ];
                }
            }

            return $out;
        };

        return $buildTree(0);
    }

    public function getFeatures($id)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $features = Feature::getFeatures($id_lang);
        $model = new ModelMpButton((int) $id);
        $selected = array_map('intval', $model->features ?: []);

        $tree = [];
        foreach ($features as $feature) {
            $idFeature = isset($feature['id_feature']) ? (int) $feature['id_feature'] : 0;
            $featureName = isset($feature['name']) ? (string) $feature['name'] : '';
            if ($featureName === '') {
                $featureName = '#' . $idFeature;
            }

            $values = FeatureValue::getFeatureValuesWithLang($id_lang, $idFeature);
            $children = [];
            foreach ($values as $value) {
                $idFeatureValue = isset($value['id_feature_value']) ? (int) $value['id_feature_value'] : 0;
                $label = isset($value['value']) ? (string) $value['value'] : '';
                if ($label === '') {
                    $label = '#' . $idFeatureValue;
                }

                if (isset($children[$label])) {
                    $label .= ' (#' . $idFeatureValue . ')';
                }

                $children[$label] = [
                    'value' => $idFeatureValue,
                    'selected' => in_array($idFeatureValue, $selected, true) ? 1 : 0,
                ];
            }

            $tree[$featureName] = $children;
        }

        return $tree;
    }

    public function getAttributes($id)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $attributeGroups = AttributeGroup::getAttributesGroups($id_lang);
        $model = new ModelMpButton((int) $id);
        $selected = array_map('intval', $model->attributes ?: []);

        $tree = [];
        foreach ($attributeGroups as $attributeGroup) {
            $idAttributeGroup = isset($attributeGroup['id_attribute_group']) ? (int) $attributeGroup['id_attribute_group'] : 0;
            $attributeGroupName = isset($attributeGroup['name']) ? (string) $attributeGroup['name'] : '';
            if ($attributeGroupName === '') {
                $attributeGroupName = '#' . $idAttributeGroup;
            }

            $values = AttributeGroup::getAttributes($id_lang, $idAttributeGroup);
            $children = [];
            foreach ($values as $value) {
                $idAttribute = isset($value['id_attribute']) ? (int) $value['id_attribute'] : 0;
                $label = isset($value['name']) ? (string) $value['name'] : '';
                if ($label === '') {
                    $label = '#' . $idAttribute;
                }

                if (isset($children[$label])) {
                    $label .= ' (#' . $idAttribute . ')';
                }

                $children[$label] = [
                    'value' => $idAttribute,
                    'selected' => in_array($idAttribute, $selected, true) ? 1 : 0,
                ];
            }

            $tree[$attributeGroupName] = $children;
        }

        return $tree;
    }
}
