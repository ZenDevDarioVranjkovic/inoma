<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-navigation
 * @version   2.7.34
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LayeredNavigation\Block\Renderer;


use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Model\Config\SeoConfigProvider;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;
use Mirasvit\LayeredNavigation\Service\SliderService;

class SliderRenderer extends AbstractRenderer
{
    protected $_template = 'Mirasvit_LayeredNavigation::renderer/sliderRenderer.phtml';

    protected $configProvider;

    private $sliderService;

    private $priceCurrency;

    public function __construct(
        ConfigProvider $configProvider,
        SliderService $sliderService,
        PriceCurrencyInterface $priceCurrency,
        SeoConfigProvider $seoConfigProvider,
        Template\Context $context,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->sliderService  = $sliderService;
        $this->priceCurrency  = $priceCurrency;

        parent::__construct($seoConfigProvider, $configProvider, $context, $data);
    }

    public function isSlider(): bool
    {
        return in_array($this->attributeConfig->getDisplayMode(), [
            AttributeConfigInterface::DISPLAY_MODE_SLIDER,
            AttributeConfigInterface::DISPLAY_MODE_SLIDER_FROM_TO,
        ]);
    }

    public function isFromTo(): bool
    {
        return in_array($this->attributeConfig->getDisplayMode(), [
            AttributeConfigInterface::DISPLAY_MODE_FROM_TO,
            AttributeConfigInterface::DISPLAY_MODE_SLIDER_FROM_TO,
        ]);
    }

    public function getSeparator(): string
    {
        return $this->configProvider->getSeoFiltersUrlFormat() === 'attr_options' ? '-' : ':';
    }

    public function getValueTemplate(): string
    {
        if ($this->getAttributeCode() === 'price') {
            $currency = $this->_storeManager->getStore()->getCurrentCurrency();

            $price = $this->priceCurrency->format(
                1,
                true,
                0,
                $this->_storeManager->getStore(),
                $currency
            );

            $price = str_replace('1', '{value.2}', $price);

            return $price;
        }

        return $this->attributeConfig->getValueTemplate() ? $this->attributeConfig->getValueTemplate() : '{value}';
    }

    public function getRate(): float
    {
        if ($this->getAttributeCode() !== 'price') {
            return 1;
        }

        $cc = $this->_storeManager->getStore()->getCurrentCurrency();

        return (float)$this->_storeManager->getStore()->getBaseCurrency()->getRate($cc->getCode());
    }

    public function getSliderData(): array
    {
        return $this->filter->getSliderData($this->getSliderUrl());
    }

    public function getSliderUrl(): string
    {
        return $this->sliderService->getSliderUrl($this->filter, $this->getSliderParamTemplate());
    }

    public function getSliderParamTemplate(): string
    {
        return $this->sliderService->getParamTemplate($this->filter);
    }
}
