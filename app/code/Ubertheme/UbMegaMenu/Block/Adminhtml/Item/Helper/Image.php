<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Block\Adminhtml\Item\Helper;

use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\Data\Form\Element\Image as ImageField;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Ubertheme\UbMegaMenu\Model\Item\Image as ImageModel;

/**
 * Class Image
 * @package Ubertheme\UbMegaMenu\Block\Adminhtml\Item\Helper
 */
class Image extends ImageField
{
    /**
     * @var ImageModel
     */
    protected $imageModel;

    /**
     * @param ImageModel $imageModel
     * @param ElementFactory $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        ImageModel $imageModel,
        ElementFactory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        Escaper $escaper,
        UrlInterface $urlBuilder,
        $data = []
    ) {
        $this->imageModel = $imageModel;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $urlBuilder, $data);
    }

    /**
     * @return bool|string
     */
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = $this->imageModel->getBaseUrl().$this->getValue();
        }

        return $url;
    }
}
