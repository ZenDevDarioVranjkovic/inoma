<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\Base\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Json\DecoderInterface;

class Modules extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_filesystem;

    /**
     * @var \Ubertheme\Base\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Filesystem\Driver\File $filesystem
     * @param \Ubertheme\Base\Helper\Data $moduleHelper
     * @param DecoderInterface $jsonDecoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Filesystem\Driver\File $filesystem,
        \Ubertheme\Base\Helper\Data $moduleHelper,
        DecoderInterface $jsonDecoder,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);

        $this->_moduleManager = $moduleManager;
        $this->_moduleList = $moduleList;
        $this->_layoutFactory = $layoutFactory;
        $this->_moduleReader = $moduleReader;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_filesystem = $filesystem;
        $this->_helper = $moduleHelper;
    }

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $result = new \Magento\Framework\DataObject($this->_moduleList->getNames());
        $enabledModuleNames = $result->toArray();
        sort($enabledModuleNames);
        foreach ($enabledModuleNames as $moduleName) {
            if ($moduleName != 'Ubertheme_Base' && substr($moduleName, 0, strlen('Ubertheme_')) == 'Ubertheme_') {
                $html .= $this->_getFieldHtml($element, $moduleName);
            }
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $layout = $this->_layoutFactory->create();
            $this->_fieldRenderer = $layout->createBlock(\Magento\Config\Block\System\Config\Form\Field::class);
        }

        return $this->_fieldRenderer;
    }

    /**
     * Read information about a extension defined in composer file
     * @param $moduleKey
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _getModuleInfo($moduleKey)
    {
        $info = [];
        $moduleDir = $this->_moduleReader->getModuleDir('', $moduleKey);
        if ($moduleDir) {
            $composerFile = $moduleDir . '/composer.json';
            if (file_exists($composerFile)) {
                $content = $this->_filesystem->fileGetContents($composerFile);
                $info = $this->_jsonDecoder->decode($content);
            }
        }

        return $info;
    }

    /**
     * @param $fieldset
     * @param $moduleKey
     * @return string
     */
    protected function _getFieldHtml($fieldset, $moduleName)
    {
        $moduleInfo = $this->_getModuleInfo($moduleName);

        $moduleDesc = '<h5 class="module-name">' . $moduleInfo['name'] . '</h5>'
            .'<span class="module-desc">' . $moduleInfo['description'] . '</span>';

        $field = $fieldset->addField($moduleName, 'label', [
            'name' => 'dummy',
            'label' => $moduleDesc,
            'value' => "ver.{$moduleInfo['version']}",
        ])->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }

}
