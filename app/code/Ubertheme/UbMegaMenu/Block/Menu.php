<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Block;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context;
use Ubertheme\Base\Helper\Data as BaseHelper;
use Ubertheme\UbMegaMenu\Helper\Data as DataHelper;
use Ubertheme\UbMegaMenu\Helper\Mega as MegaHelper;
use Ubertheme\UbMegaMenu\Helper\Cache as CacheHelper;

class Menu extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    protected $_configs = [
        'is_mega_menu' => 1,
        'is_main_menu' => 0,
        'is_mobile_menu' => 0,
        'show_menu_title' => 0,
        'show_number_product' => 0,
        'mega_style' => 1,
        'default_mega_col_width' => 200,
        'mega_col_margin' => 20,
        'mega_content_visible_option' => null,
        'mega_content_visible_in' => null,
        'start_level' => 0,
        'end_level' => 10,
        'menu_position' => null,
        'menu_group_id' => null,
        'menu_key' => null,
        'animation' => null,
        'addition_class' => null,
        'cache_lifetime' => 86400,
    ];

    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var MegaHelper
     */
    protected $megaHelper;

    /**
     * @var CacheHelper
     */
    protected $cacheHelper;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Menu constructor.
     * @param TemplateContext $context
     * @param HttpContext $httpContext
     * @param BaseHelper $baseHelper
     * @param DataHelper $dataHelper
     * @param MegaHelper $megaHelper
     * @param CacheHelper $cacheHelper
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        HttpContext $httpContext,
        BaseHelper $baseHelper,
        DataHelper $dataHelper,
        MegaHelper $megaHelper,
        CacheHelper $cacheHelper,
        array $data = []
    ) {
        $this->baseHelper = $baseHelper;
        $this->httpContext = $httpContext;
        $this->dataHelper = $dataHelper;
        $this->megaHelper = $megaHelper;
        $this->cacheHelper = $cacheHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _beforeToHtml()
    {
        //initial configs
        $this->initialConfig($this->getData());

        //get menu group id
        if ($this->hasData('menu_id')) {
            $menuGroupId = $this->getData('menu_id');
            /** @var \Ubertheme\UbMegaMenu\Model\Group $menuGroup */
            $menuGroup = $this->dataHelper->getMenuGroup($menuGroupId);
            $menuKey = $menuGroup->getIdentifier();
        } else {
            //get menu key from config
            $menuKey = ($this->hasData('menu_key')) ? trim($this->getData('menu_key')) : null;
            $customerGroupId = $this->httpContext->getValue(Context::CONTEXT_GROUP);
            //get menu group id by menu key and customer group id
            $menuGroup = $this->dataHelper->getMenuGroup(0, $menuKey, $customerGroupId);
            $menuGroupId = $menuGroup->getId();
        }

        //update some other configs
        $this->_configs['menu_title'] = $menuGroup->getTitle();
        $this->_configs['menu_key'] = $menuKey;
        $this->_configs['menu_group_id'] = $menuGroupId;
        $this->_configs['menu_position'] = $menuGroup->getMenuPosition();
        if ($this->_configs['menu_position'] === 'main') {
            $this->_configs['is_main_menu'] = 1;
        }
        $this->_configs['menu_type'] = $menuGroup->getMenuType();
        if ($this->_configs['menu_type'] == \Ubertheme\UbMegaMenu\Model\Group::TYPE_VERTICAL
            || $this->_configs['menu_type'] == \Ubertheme\UbMegaMenu\Model\Group::TYPE_HORIZONTAL ) {
            $mobileType = $menuGroup->getMobileType();
        } else {
            $mobileType = $menuGroup->getMenuType();
        }
        $this->_configs['mobile_type'] = $mobileType;

        $this->_configs['animation'] = ($this->hasData('animation'))
            ? trim($this->getData('animation'))
            : $menuGroup->getAnimationType();

        //set config params for mega helper
        $this->megaHelper->setParams($this->_configs);

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        //assign template
        if (!$this->getTemplate()) {
            $this->setTemplate("Ubertheme_UbMegaMenu::menu.phtml");
        }

        //get menu items and generate menu items tree html
        if ($this->_configs['menu_group_id'] && $this->_configs['menu_key']) {
            $menuHtml = $this->_generateMenuHtml($this->_configs['menu_group_id']);
        } else {
            if ($this->_configs['menu_key']) {
                $menuHtml = '<div class="no-menu">'
                    . __(
                        'The menu with the "%1" key does not exist or has not been assigned to this store view.',
                        $this->_configs['menu_key']
                    ) . '</div>';
            } else {
                $menuHtml = '<span class="no-menu">'
                    . __('You have not set the menu to show in this store view yet.')
                    . '</span>';
            }
        }

        //assign data to template
        $this->assign('menuHtml', $menuHtml);
        $this->assign('config', $this->_configs);

        return $this->fetchView($this->getTemplateFile());
    }

    protected function _generateMenuHtml($menuGroupId)
    {
        $html = null;
        $cacheVars = $this->getCacheVars();
        $cacheId = $this->cacheHelper->getId(
            '_generateMenuHtml',
            $cacheVars
        );
        $html = $this->cacheHelper->load($cacheId);
        if (!$html) {
            //get menu items and build menu markup html
            $items = $this->dataHelper->getMenuItems($menuGroupId, $this->_configs);
            if ($items) {
                //build menu items data
                $this->megaHelper->rebuildData($items);
                //generate menu
                $html = $this->megaHelper->genMenu();
            } else {
                $html = '<span class="no-menu">' . __('There are not menu items found.') . '</span>';
            }
            //save to cache
            $this->cacheHelper->save(
                $this->baseHelper->getSerializer()->serialize($html),
                $cacheId, $this->_configs['cache_lifetime']
            );
        } else {
            $html = $this->baseHelper->getSerializer()->unserialize($html);
        }

        return $html;
    }

    protected function initialConfig($data)
    {
        foreach ($this->_configs as $key => $val) {
            $this->_configs[$key] = $this->baseHelper->getConfigValueByKey($key, ['ubmegamenu'], $data);
        }

        //init cache lifetime for custom cache
        $this->_configs['cache_lifetime'] = ($this->getData('cache_lifetime'))
            ? $this->getData('cache_lifetime')
            : $this->_configs['cache_lifetime'];

        return $this;
    }

    public function getIdentities()
    {
        return [
            \Magento\Store\Model\Store::CACHE_TAG,
            \Ubertheme\UbMegaMenu\Model\Group::CACHE_TAG,
            \Ubertheme\UbMegaMenu\Model\Item::CACHE_TAG,
            $this->_configs['menu_group_id'],
            $this->_configs['menu_key'],
            $this->httpContext->getVaryString()
        ];
    }

    public function getCacheVars() {
        $vars = [
            $this->_design->getDesignTheme()->getId(),
            $this->getTemplate(),
            $this->getNameInLayout(),
            $this->_configs['menu_group_id'],
            $this->httpContext->getVaryString()
        ];

        return $vars;
    }

    public function getCacheKeyInfo()
    {
        return $this->getCacheVars();
    }

}
