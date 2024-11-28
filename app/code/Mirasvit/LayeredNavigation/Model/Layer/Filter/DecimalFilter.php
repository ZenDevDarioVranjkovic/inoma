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

namespace Mirasvit\LayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Repository\AttributeConfigRepository;
use Mirasvit\LayeredNavigation\Service\SliderService;
use Mirasvit\SeoFilter\Service\RewriteService;

/**
 * @SuppressWarnings(PHPMD)
 */
class DecimalFilter extends AbstractFilter
{

    /** Price delta for filter  */
    const PRICE_DELTA = 0.001;

    /**
     * @var array
     */
    protected static $isStateAdded = [];

    /**
     * @var bool
     */
    protected static $isAdded;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

    private $priceCurrency;

    /**
     * @var array
     */
    private $facetedData;

    private $sliderService;

    private $rewriteService;

    private $attributeConfigRepository;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        PriceFactory $dataProviderFactory,
        SliderService $sliderService,
        AttributeConfigRepository $attributeConfigRepository,
        RewriteService $rewriteService,
        Layer $layer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($layer, $context, $data);

        $this->priceCurrency             = $priceCurrency;
        $this->dataProvider              = $dataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->sliderService             = $sliderService;
        $this->attributeConfigRepository = $attributeConfigRepository;
        $this->rewriteService            = $rewriteService;
    }

    public function apply(RequestInterface $request): self
    {
        $attributeCode  = $this->getRequestVar();
        $attributeValue = $request->getParam($this->getRequestVar());

        if (!$attributeValue && $attributeCode == 'price') {
            $rewrite        = $this->rewriteService->getAttributeRewrite($this->getRequestVar());
            $attributeAlias = $rewrite ? $rewrite->getRewrite() : $this->getRequestVar();
            $attributeValue = $request->getParam($attributeAlias);
        }

        if (!$attributeValue || !is_string($attributeValue)) {
            return $this;
        }

        $maxPrice = 1000000;

        $facetedData = $this->getFacetedData();

        if ($facetedData && isset($facetedData['max'])) {
            $maxPrice = $facetedData['max']['price'];
        }

        $fromArray    = [];
        $toArray      = [];
        $filterParams = explode(',', $attributeValue);

        $productCollection = $this->getProductCollection();

        foreach ($filterParams as $filterParam) {
            $filterParamArray = preg_split('/[\-:]/', $filterParam);

            $idx = 0;
            while ($idx < count($filterParamArray)) {
                $from = isset($filterParamArray[$idx]) ? (float)$filterParamArray[$idx] : null;
                $to   = isset($filterParamArray[$idx + 1]) && $filterParamArray[$idx + 1] != '' ? (float)$filterParamArray[$idx + 1] : null;

                $fromArray[] = $from ? $from : 0;
                $toArray[]   = $to ? $to : $maxPrice;

                $this->addStateItem(
                    $this->_createItem(
                        $this->renderRangeLabel($from, $to),
                        implode('-', [$from, $to])
                    )
                );

                $idx += 2;
            }
        }

        $from = min($fromArray);
        $to   = max($toArray);

        $attributeConfig = $this->getAttributeConfig($attributeCode);

        $displayMode = $attributeConfig
            ? $attributeConfig->getDisplayMode()
            : AttributeConfigInterface::DISPLAY_MODE_RANGE;

        if ($displayMode == AttributeConfigInterface::DISPLAY_MODE_RANGE) {
            $to -= self::PRICE_DELTA;
        }

        $this->setFromToData(['from' => $from, 'to' => $to]);

        $productCollection->addFieldToFilter($attributeCode, ['from' => $from, 'to' => $to]);

        return $this;
    }

    public function getFacetedData(): array
    {
        if ($this->facetedData === null) {
            /** @var \Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
            $productCollection = $this->getLayer()->getProductCollection();
            $attribute         = $this->getAttributeModel();

            $facets = $productCollection->getExtendedFacetedData($attribute->getAttributeCode(), true);

            $this->facetedData = $facets;
        }

        return $this->facetedData;
    }

    public function getSliderData(string $url): array
    {
        return $this->sliderService->getSliderData(
            $this->getFacetedData(),
            $this->getRequestVar(),
            (array)$this->getFromToData(),
            $url,
            $this->getAttributeConfig($this->_requestVar)->getSliderStep()
        );
    }

    public function getCurrencyRate(): float
    {
        $rate = $this->_getData('currency_rate');

        if ($rate === null) {
            $rate = $this->_storeManager->getStore($this->getStoreId())
                ->getCurrentCurrencyRate();
        }

        if (!$rate) {
            $rate = 1;
        }

        return (float)$rate;
    }

    protected function _getItemsData(): array
    {
        $facets = $this->getFacetedData();

        $data = [];

        if (count($facets) >= 1) {
            foreach ($facets as $key => $aggregation) {
                $count = $aggregation['count'];
                if (strpos($key, '_') === false) {
                    continue;
                }

                $data[] = $this->prepareData($key,(int) $count);
            }
        }

        return $data;
    }

    protected function prepareData(string $key, int $count): array
    {
        [$from, $to] = explode('_', $key);

        $from = $from == '*' ? $this->getFrom((float)$to) : (float)$from;
        $to   = $to == '*' || $to == '' ? null : (float)$to; 

        $label = $this->renderRangeLabel(empty($from) ? 0 : $from, $to);

        $value = $from . '-' . $to . $this->dataProvider->getAdditionalRequestData();

        return [
            'label' => $label,
            'value' => $value,
            'count' => $count,
            'from'  => $from,
            'to'    => $to
        ];
    }

    private function renderRangeLabel(?float $fromPrice, ?float $toPrice): ?string
    {
        if (strpos($fromPrice . $toPrice, ',') !== false) {
            return null;
        }

        $attributeConfig = $this->getAttributeConfig($this->_requestVar);
        $displayMode     = $attributeConfig->getDisplayMode();
        $valueTemplate   = $attributeConfig->getValueTemplate();

        if ($this->_requestVar === 'price') {
            $fromPrice = $fromPrice === null ? 0 : $fromPrice * $this->getCurrencyRate();
            $toPrice   = $toPrice === null ? '' : $toPrice * $this->getCurrencyRate();
        } else {
            $fromPrice = $fromPrice === null ? 0 : $fromPrice;
            $toPrice   = $toPrice === null ? '' : $toPrice;
        }

        if ($displayMode == AttributeConfigInterface::DISPLAY_MODE_RANGE && $toPrice !== '') {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }
        }

        if ($this->_requestVar === 'price') {
            $fromText = $this->priceCurrency->format($fromPrice);
            $toText   = $this->priceCurrency->format($toPrice);
        } else {
            $valueTemplate = $valueTemplate ? $valueTemplate : '{value}';

            $fromText = str_replace('{value}', (string)round((float)$fromPrice), $valueTemplate);
            $toText   = str_replace('{value}', (string)round((float)$toPrice), $valueTemplate);
        }

        if ($toPrice === '') {
            return (string)__('%1 and above', $fromText);
        } elseif ($fromPrice == $toPrice && $this->dataProvider->getOnePriceIntervalValue()) {
            return $fromText;
        } else {
            return (string)__('%1 - %2', $fromText, $toText);
        }
    }


    private function getFrom(float $from): float
    {
        $to       = 0.0;
        $interval = $this->dataProvider->getInterval();
        if ($interval && is_numeric($interval[0]) && $interval[0] < $from) {
            $to = (float)$interval[0];
        }

        return $to;
    }

    public function getAttributeConfig(string $attributeCode): AttributeConfigInterface
    {
        $attributeConfig = $this->attributeConfigRepository->getByAttributeCode($attributeCode);

        return $attributeConfig ? $attributeConfig : $this->attributeConfigRepository->create();
    }
}
