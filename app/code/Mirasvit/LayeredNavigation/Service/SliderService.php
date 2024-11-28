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

namespace Mirasvit\LayeredNavigation\Service;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\LayeredNavigation\Model\ConfigProvider;
use Mirasvit\SeoFilter\Model\ConfigProvider as SeoFilterConfig;
use Mirasvit\SeoFilter\Service\RewriteService;
use Mirasvit\SeoFilter\Service\UrlService as SeoUrlService;
use Magento\Framework\Module\Manager;

class SliderService
{
    const MATCH_PREFIX            = 'slider_match_prefix_';
    const SLIDER_DATA             = 'sliderdata';
    const SLIDER_URL_TEMPLATE     = self::SLIDER_REPLACE_VARIABLE . '_from-' . self::SLIDER_REPLACE_VARIABLE . '_to';
    const SLIDER_REPLACE_VARIABLE = '[attr]';

    protected static $sliderOptions;

    private          $configProvider;

    private          $rewriteService;

    private          $request;

    private          $storeId;

    private          $urlHelper;

    private          $urlBuilder;

    private          $moduleManager;

    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        SeoUrlService $urlHelper,
        RewriteService $rewriteService,
        Manager $moduleManager
    ) {
        $this->request        = $request;
        $this->urlBuilder     = $urlBuilder;
        $this->urlHelper      = $urlHelper;
        $this->rewriteService = $rewriteService;
        $this->configProvider = $configProvider;
        $this->moduleManager  = $moduleManager;
        $this->storeId        = (int)$storeManager->getStore()->getStoreId();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getSliderData(array $facetedData, string $requestVar, array $fromToData, string $url, int $sliderStep): array
    {
        // in some cases price ranges might not have from to values
        // so we generating them from keys
        // fixes the compatibility issue with Amasty ElasticSearch
        foreach ($facetedData as $key => $value) {
            $ranges = explode('_', (string)$key);

            if (count($ranges) != 2) {
                continue;
            }

            if (isset($value['from']) || isset($value['to'])) {
                continue;
            }

            $facetedData[$key]['from'] = (int)$ranges[0];
            $facetedData[$key]['to'] = (int)$ranges[1];
        }

        $min = null;
        $max = null;

        foreach ($facetedData as $item) {
            if (isset($item['from'])) {
                $min = $min > $item['from'] ? $item['from'] : $min;
            }

            if (isset($item['to'])) {
                $max = $max < $item['to'] ? $item['to'] : $max;
            }
        }

        if (isset($facetedData['min'])) {
            $min = $facetedData['min']['price'];
            unset($facetedData['min']);
        }

        if (isset($facetedData['max'])) {
            $max = $facetedData['max']['price'];
            unset($facetedData['max']);
        }

        $from = ($fromToData) ? $fromToData['from'] : $min;
        $to   = ($fromToData) ? $fromToData['to'] : $max;

        $sliderData = [
            'min'        => $min,
            'max'        => $max,
            'requestVar' => $requestVar,
            'from'       => $from,
            'to'         => $to,
            'url'        => $url,
            'step'       => $sliderStep
        ];


        return $sliderData;
    }

    public function getSliderUrl(FilterInterface $filter, string $template): string
    {
        if ($this->configProvider->isSeoFiltersEnabled()
            && in_array($this->request->getFullActionName(), [
                'catalog_category_view',
                'all_products_page_index_index',
                'brand_brand_view',
                'landing_landing_view'
            ])
        ) {
            return $this->getSliderSeoFriendlyUrl($filter, $template);
        }

        $query = [$filter->getRequestVar() => $template];

        return $this->urlBuilder->getUrl('*/*/*', [
            '_current'     => true,
            '_use_rewrite' => true,
            '_query'       => $query,
        ]);
    }

    public function getParamTemplate(FilterInterface $filter): string
    {
        $requestVar = $filter->getRequestVar();

        return str_replace(
            SliderService::SLIDER_REPLACE_VARIABLE,
            $requestVar,
            SliderService::SLIDER_URL_TEMPLATE
        );
    }

    /** @SuppressWarnings(PHPMD) */
    private function getSliderSeoFriendlyUrl(FilterInterface $filter, string $template): string
    {
        $activeFilters = $this->rewriteService->getActiveFilters();
        if (!$activeFilters || $this->isFilterCategoryOnly($activeFilters)) {
            $separator = '/';
        } else {
            $separator = SeoFilterConfig::SEPARATOR_FILTERS;
        }

        $priceSeparator = SeoFilterConfig::SEPARATOR_DECIMAL;
        if ($this->configProvider->getSeoFiltersUrlFormat() === 'attr_options') {
            $priceSeparator = '/';
        }

//        $price = $filter->getRequestVar() . $priceSeparator . $template;

        $currentUrl = $this->urlBuilder->getCurrentUrl();

        if ($currentUrl === $this->urlBuilder->getBaseUrl()) {
            $friendlyUrlService = ObjectManager::getInstance()->create('Mirasvit\SeoFilter\Service\FriendlyUrlService');
            $currentUrl         = $friendlyUrlService->getClearUrl();
        }

        $suffix = $this->urlHelper->getCategoryUrlSuffix($this->storeId);
        $suffix = $suffix && strpos($currentUrl, $suffix) !== false ? $suffix : '';

        if(
            $this->request->getModuleName() == 'brand' 
            && $this->moduleManager->isEnabled('Mirasvit_Brand') 
            && $this->configProvider->getBrandsUrlSuffixCategory() === ConfigProvider::USE_BRAND_SUFFIX
        ) {
            $suffix = $this->configProvider->getBrandsUrlSuffix();
        }

        $currentUrl = $this->removeCategorySuffix($currentUrl, $suffix);
        
        $rewrite        = $this->rewriteService->getAttributeRewrite($filter->getRequestVar());
        $attributeAlias = $rewrite ? $rewrite->getRewrite() : $filter->getRequestVar();

        $price = $attributeAlias . $priceSeparator . $template;

        if (isset($activeFilters[$attributeAlias]) || isset($activeFilters[$filter->getRequestVar()])) {
            $path = parse_url($currentUrl)['path'];
            $path = explode('/', $path);

            if ($this->configProvider->getSeoFiltersUrlFormat() === 'attr_options') {
                $entry = array_search($attributeAlias, $path);
                if ($entry !== false) {
                    unset($path[$entry + 1]);
                    unset($path[$entry]);
                }
            } else {
                if ($filter->getRequestVar() == 'price') {
                    $attributeAlias = 'price';
                }
                $key     = array_key_last($path);
                $filters = explode('-', $path[$key]);
                $needle  = str_replace('-', ':', array_key_last($activeFilters[$attributeAlias]));
                $needle  = str_replace($template, $needle, $price);
                $entry   = array_search($needle, $filters);

                if ($entry !== false) {
                    unset($filters[$entry]);
                }

                $filters = array_filter($filters, function ($filter) {
                    return !!trim($filter);
                });

                $path[$key] = implode('-', $filters);
            }
            $path       = implode('/', $path);
            $currentUrl = str_replace(parse_url($currentUrl)['path'], $path, $currentUrl);
        }

        $originPath = isset(parse_url($currentUrl)['path']) ? parse_url($currentUrl)['path'] : '';
        $path = explode('/', $originPath);
        
        if (empty($activeFilters) && ($prefix = $this->configProvider->getSeoFiltersPrefix())) {
            $path[] = trim($prefix, '/'); 
        }
        
        if ($this->configProvider->getSeoFiltersUrlFormat() === 'attr_options') {
            $path[] = $price;
        } else {
            if (empty($activeFilters)) {
                $path[] = $price;
            } else {
                $key     = array_key_last($path);
                $filters = explode('-', $path[$key]);
                $filters = array_filter($filters, function ($filter) {
                    return !!trim($filter);
                });
                $filters[]  = $price;
                $filters    = implode('-', $filters);
                $path[$key] = $filters;
            }
        }

        $path       = implode('/', $path);
        $currentUrl = str_replace($originPath, $path, $currentUrl);

        return $currentUrl . $suffix;
    }

    /**
     * @param array|null $activeFilters
     *
     * @return bool
     */
    private function isFilterCategoryOnly($activeFilters)
    {
        if (!is_array($activeFilters)) {
            return false;
        }
        if (count($activeFilters) == 1 && array_key_exists('cat', $activeFilters)) {
            return true;
        }

        return false;
    }

    private function removeCategorySuffix(string $path, string $categorySuffix): string
    {
        if (!$categorySuffix) {
            return $path;
        }

        $suffixPosition = strrpos($path, $categorySuffix);

        return $suffixPosition !== false
            ? substr($path, 0, $suffixPosition)
            : $path;
    }
}
