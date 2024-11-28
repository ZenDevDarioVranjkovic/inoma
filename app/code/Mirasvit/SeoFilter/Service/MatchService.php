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
 * @package   mirasvit/module-seo-filter
 * @version   1.3.28
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Service;

use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Mirasvit\SeoFilter\Api\Data\RewriteInterface;
use Mirasvit\SeoFilter\Model\ConfigProvider;
use Mirasvit\SeoFilter\Model\Context;
use Mirasvit\SeoFilter\Repository\RewriteRepository;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class MatchService
{
    const DECIMAL_FILTERS = 'decimalFilters';
    const STATIC_FILTERS  = 'staticFilters';

    private $splitting;

    private $rewriteRepository;

    private $urlRewrite;

    private $urlService;

    private $context;

    private $configProvider;

    private $objectManager;

    private $rewriteService;

    private $moduleManager;

    private $cacheService;

    private $storeManager;

    private $rewritesWithDash = [];

    public function __construct(
        MatchService\Splitting      $splitting,
        RewriteRepository           $rewriteRepository,
        RewriteService              $rewriteService,
        UrlRewriteCollectionFactory $urlRewrite,
        UrlService                  $urlService,
        ConfigProvider              $configProvider,
        ObjectManagerInterface      $objectManager,
        Manager                     $moduleManager,
        Context                     $context,
        CacheService                $cacheService,
        StoreManagerInterface       $storeManager
    ) {
        $this->splitting         = $splitting;
        $this->rewriteRepository = $rewriteRepository;
        $this->rewriteService    = $rewriteService;
        $this->urlRewrite        = $urlRewrite;
        $this->urlService        = $urlService;
        $this->configProvider    = $configProvider;
        $this->objectManager     = $objectManager;
        $this->moduleManager     = $moduleManager;
        $this->context           = $context;
        $this->cacheService      = $cacheService;
        $this->storeManager      = $storeManager;
    }

    public function getParams(): ?array
    {
        if ($this->isNativeRewrite()) {
            return null;
        }


        $categoryId       = 0;
        $isBrandPage      = false;
        $isAllProductPage = false;
        $isLandingPage    = false;

        //        $currentUrl = $this->context->getUrlBuilder()->getCurrentUrl();
        //        $urlPath    = parse_url($currentUrl, PHP_URL_PATH);

        $urlPath = $this->context->getRequest()->getOriginalPathInfo();

        $baseUrlPathAll = 'all';

        if ($this->moduleManager->isEnabled('Mirasvit_AllProducts')) {
            $allProductConfig = $this->objectManager->get('\Mirasvit\AllProducts\Config\Config');

            $baseUrlPathAll = $allProductConfig->isEnabled() ? $allProductConfig->getUrlKey() : $baseUrlPathAll;
        }

        $brandRouteParams   = $this->getBaseBrandUrlPath();
        $baseUrlPathBrand   = $brandRouteParams['brandPath'];

        $landingRouteParams = $this->getLandingPageUrlPath();
        $landingUrl         = $landingRouteParams['page_url'];

        $baseUrlPathCategory = '';

        if (preg_match('~^/' . $baseUrlPathAll . '/\S+~', $urlPath)) {
            $isAllProductPage = true;
        } elseif ($landingUrl && strpos($urlPath, $landingUrl) !== false) {
            $isLandingPage = true;
        } elseif (preg_match('~^/' . $baseUrlPathBrand . '/\S+~', $urlPath)) {
            $isBrandPage = true;
        } else {
            $categoryId = $this->getCategoryId();
        }
        if (!$categoryId && !$isBrandPage && !$isAllProductPage && !$isLandingPage) {
            return null;
        }

        if ($categoryId) {
            $baseUrlPathCategory = $this->getCategoryBaseUrlPath($categoryId);
        }

        if ($isBrandPage) {
            $baseUrlPath = $baseUrlPathBrand;
        } elseif ($isLandingPage) {
            $baseUrlPath = $landingUrl;
        } elseif ($isAllProductPage) {
            $baseUrlPath = $baseUrlPathAll;
        } else {
            $baseUrlPath = $baseUrlPathCategory;
        }


        $filterData = $baseUrlPath ? $this->splitting->getFiltersString($baseUrlPath) : [];

        $staticFilters  = [];
        $decimalFilters = [];

        $decimalFilters = $this->handleDecimalFilters($filterData, $decimalFilters);

        $staticFilters = $this->handleStockFilters($filterData, $staticFilters);
        $staticFilters = $this->handleRatingFilters($filterData, $staticFilters);
        $staticFilters = $this->handleSaleFilters($filterData, $staticFilters);
        $staticFilters = $this->handleNewFilters($filterData, $staticFilters);
        $staticFilters = $this->handleAttributeFilters($filterData, $staticFilters);

        $params = [];

        foreach ($decimalFilters as $attr => $values) {
            $params[$attr] = implode(ConfigProvider::SEPARATOR_FILTER_VALUES, $values);
        }

        foreach ($staticFilters as $attr => $values) {
            $params[$attr] = implode(ConfigProvider::SEPARATOR_FILTER_VALUES, $values);
        }

        $match = count($filterData) == 0;

        $result = [
            'all_products_route'      => $isAllProductPage ? $baseUrlPathAll : null,
            'landing_page_route_data' => $isLandingPage ? $landingRouteParams : [],
            'brand_page_route_data'   => $isBrandPage ? $brandRouteParams : [],
            'category_id'             => $categoryId,
            'params'                  => $params,
            'match'                   => $match,
        ];

        $result = $this->checkIfRouteExists($result);

        return $result;
    }

    private function getLandingPageUrlPath(): array
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $urlPath = parse_url($this->context->getUrlBuilder()->getCurrentUrl(), PHP_URL_PATH);

        if (class_exists('Mirasvit\LandingPage\Repository\PageRepository')
            && $this->moduleManager->isEnabled('Mirasvit_LandingPage')
        ) {
            $pageRepository = $this->objectManager->get('Mirasvit\LandingPage\Repository\PageRepository');
            foreach ($pageRepository->getCollection() as $landing) {
                if (!$landing->getIsActive()) {
                    continue;
                }
                if (
                    $this->startsWith($urlPath, trim($landing->getUrlKey(), '/') . '/')
                    || (trim($urlPath, '/') === trim($landing->getUrlKey(), '/')) 
                    && ($landing->getStoreId() === 0 || $storeId === $landing->getStoreId())
                ) {               
                    return ['page_url' => $landing->getUrlKey(), 'page_id' => intval($landing->getData('page_id'))];
                }
            }
        }

        return ['page_url' => null, 'page_id' => null];
    }

    private function startsWith(string $urlPath, string $landingUrlKey): bool 
    {
        $storeCode = '/' . $this->storeManager->getStore()->getCode();

        if ($storeCode !== '/' && substr($urlPath, 0, strlen($storeCode . '/')) == $storeCode . '/') {
            $urlPath = substr($urlPath, strlen($storeCode));
        } 
        
        $length = strlen($landingUrlKey);
        return substr(trim($urlPath, '/'), 0, $length ) === $landingUrlKey;
    }    

    private function getBaseBrandUrlPath(): array
    {
        $brandPath   = 'brand';
        $brandId     = null;
        $brandUrlKey = null;

        $urlPath = parse_url($this->context->getUrlBuilder()->getCurrentUrl(), PHP_URL_PATH);

        if (!class_exists('Mirasvit\Brand\Model\Config\GeneralConfig')) {
            return [
                'brandPath'        => $brandPath,
                'brandId'          => $brandId,
                'brandUrlKey'      => $brandUrlKey,
                'isShortFormatUrl' => true
            ];
        }

        /** @var \Mirasvit\Brand\Model\Config\GeneralConfig|object $brandConfig */
        $brandConfig = $this->objectManager->get('Mirasvit\Brand\Model\Config\GeneralConfig');

        $brandPath = $brandConfig->getAllBrandUrl();
        $isShortFormatBrandUrl = $brandConfig->getFormatBrandUrl() == 1 ? true : false;

        /** @var \Mirasvit\Brand\Repository\BrandRepository|object $brandRepository */
        $brandRepository = $this->objectManager->get('Mirasvit\Brand\Repository\BrandRepository');
        foreach ($brandRepository->getList() as $brand) {
            if (preg_match('/\/' . $brand->getUrlKey() . '\/\S*/', rtrim($urlPath, '/') . '/')) {
                $brandId     = $brand->getId();
                $brandUrlKey = $brand->getUrlKey();
                if ($brandConfig->getFormatBrandUrl() == 1) {
                    $brandPath = $brand->getUrlKey();
                    break;
                } else {
                    $brandPath = $brandConfig->getAllBrandUrl() . '/' . $brand->getUrlKey();
                    break;
                }
            }
        }

        return [
            'brandPath'        => $brandPath,
            'brandId'          => $brandId,
            'brandUrlKey'      => $brandUrlKey,
            'isShortFormatUrl' => $isShortFormatBrandUrl
        ];
    }

    private function getCategoryId(): ?int
    {
        $requestPath  = trim($this->context->getRequest()->getOriginalPathInfo(), '/');
        $originalPath = $requestPath . '-' . $this->context->getStoreId();
        if ($categoryId = $this->cacheService->getCache('getCategoryId', [$originalPath])) {
            $categoryId = array_values($categoryId)[0];

            return (int)$categoryId;
        }

        if ($categoryId = $this->getCategoryIdByPath($requestPath)) {
            $this->cacheService->setCache('getCategoryId', [$originalPath], [$categoryId]);

            return (int)$categoryId;
        }

        $categoryRewriteCollection = $this->urlRewrite->create()
            ->addFieldToFilter('entity_type', 'category')
            ->addFieldToFilter('store_id', $this->context->getStoreId())
            ->setOrder('request_path', 'DESC');

        $categorySuffix = $this->urlService->getCategoryUrlSuffix();

        $categoryBasePath = '';

        foreach ($categoryRewriteCollection as $categoryRewrite) {
            $path = $this->removeCategorySuffix($categoryRewrite->getRequestPath());
            if(substr($path, -1) == '/') {
                $path = substr($path, 0, -1);
            }
            if (strpos($requestPath, $path . '/') === 0 && strlen($path) > strlen($categoryBasePath)) {
                $categoryBasePath = $path;
                break;
            }
        }

        if (empty($categoryBasePath) && strpos($requestPath, 'catalog/category/view') !== false) {
            if (preg_match('/id\/(\d*)/', $requestPath, $match)) {
                return (int)$match[1];
            }
        }

        if (empty($categoryBasePath)) {
            return null;
        }

        $filtersData = $this->splitting->getFiltersString($categoryBasePath);
        $rewrites    = $this->rewriteRepository->getCollection();
        $requestPath = $this->removeCategorySuffix($requestPath);
        $prefix      = $this->configProvider->getPrefix();

        if ($prefix) {
            if (strripos($requestPath, '/' . $prefix . '/') !== false) {
                $requestPath = str_replace('/' . $prefix . '/', '/', $requestPath);
            } else {
                return null;
            }
        }

        if (isset($filtersData['*'])) {
            $filtersData = $filtersData['*'];
        }

        $filterOptions = [];
        $staticFilters = [];

        if ($this->configProvider->getUrlFormat() == ConfigProvider::URL_FORMAT_ATTR_OPTIONS) {
            $fData = $filtersData;

            $staticFilters = $this->handleStockFilters($fData, $staticFilters);
            $staticFilters = $this->handleRatingFilters($fData, $staticFilters);
            $staticFilters = $this->handleSaleFilters($fData, $staticFilters);
            $staticFilters = $this->handleNewFilters($fData, $staticFilters);
        }

        foreach ($filtersData as $attribute => $filter) {
            if ($this->configProvider->getUrlFormat() == ConfigProvider::URL_FORMAT_ATTR_OPTIONS) {
                $requestData = explode('/', $requestPath);

                $rewrites = $this->rewriteRepository->getCollection()
                    ->addFieldToFilter(\Mirasvit\SeoFilter\Api\Data\RewriteInterface::STORE_ID, $this->context->getStoreId())
                    ->addFieldToFilter(\Mirasvit\SeoFilter\Api\Data\RewriteInterface::ATTRIBUTE_CODE, $attribute)
                    ->addFieldToFilter(\Mirasvit\SeoFilter\Api\Data\RewriteInterface::OPTION, ['null' => true]);

                foreach ($rewrites as $rewrite) {
                    $attributeKey = array_search($rewrite->getRewrite(), $requestData);
                    unset($requestData[$attributeKey + 1]);
                    unset($requestData[$attributeKey]);
                }


                if (isset($staticFilters[$attribute])) {
                    $attributeKey = array_search($attribute, $requestData);
                    unset($requestData[$attributeKey + 1]);
                    unset($requestData[$attributeKey]);
                }

                $requestPath = implode('/', $requestData);
            } else {
                $filterOptions[] = $filter;
            }
        }

        if (count($filterOptions)) {
            $filterString = implode('-', $filterOptions);

            if (strrpos($requestPath, $filterString) !== false) {
                // substr_replace because category path can include option alias
                $requestPath = substr_replace(
                    $requestPath,
                    '',
                    strrpos($requestPath, $filterString),
                    strlen($filterString)
                );
            }
        }

        $requestPath = trim($requestPath, '/-');
        $requestPath .= $categorySuffix;

        // category rewrites can be with / at the end of the path
        $catId = $this->getCategoryIdByPath($requestPath) ? : $this->getCategoryIdByPath($requestPath . '/');

        $this->cacheService->setCache('getCategoryId', [$originalPath], [$catId]);

        return $catId;
    }

    private function removeCategorySuffix(string $path): string
    {
        $categorySuffix = $this->urlService->getCategoryUrlSuffix();

        if (!$categorySuffix || substr_compare($path, $categorySuffix, -strlen($categorySuffix)) !== 0) {
            return $path;
        }

        $suffixPosition = strrpos($path, $categorySuffix);

        return $suffixPosition !== false
            ? substr($path, 0, $suffixPosition)
            : $path;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    // private function collectCorrectFilterOptions(array $filter, string $attribute = null): array
    // {
    //     $found = [];

    //     $rewrites = $this->rewriteRepository->getCollection();

    //     $isRange = true;

    //     if ($attribute && !$this->context->isDecimalAttribute($attribute)) {
    //         $isRange = false;
    //     }

    //     foreach ($filter as $value) {
    //         if ($this->isStaticFilterRewrite($value) || ($attribute && $isRange) || is_numeric($value)) {
    //             $found[] = $value;
    //         } else {
    //             foreach ($rewrites as $rewrite) {
    //                 if ($value === $rewrite->getRewrite()) {
    //                     $found[] = $value;
    //                 }
    //             }
    //         }
    //     }

    //     sort($found);

    //     return $found;
    // }

    // private function ensureAttributeRewrite(string $alias): ?string
    // {
    //     $staticFilterLables = [
    //         ConfigProvider::FILTER_RATING,
    //         ConfigProvider::FILTER_NEW,
    //         ConfigProvider::FILTER_SALE,
    //         ConfigProvider::FILTER_STOCK
    //     ];

    //     return $this->rewriteService->getAttributeRewriteByAlias($alias, $this->context->getStoreId()) || in_array($alias, $staticFilterLables)
    //         ? $alias
    //         : null;
    // }

    private function getCategoryIdByPath(string $requestPath): ?int
    {
        $categoryRewrite = $this->urlRewrite
            ->create()
            ->addFieldToFilter('store_id', $this->context->getStoreId())
            ->addFieldToFilter('entity_type', 'category')
            ->addFieldToFilter('request_path', $requestPath)
            ->getFirstItem();

        return $categoryRewrite && $categoryRewrite->getEntityId() ? (int)$categoryRewrite->getEntityId() : null;
    }

    // private function isStaticFilterRewrite(string $value): bool
    // {
    //     $staticFilters = [
    //         ConfigProvider::FILTER_SALE,
    //         ConfigProvider::FILTER_NEW,
    //         ConfigProvider::LABEL_RATING_1,
    //         ConfigProvider::LABEL_RATING_2,
    //         ConfigProvider::LABEL_RATING_3,
    //         ConfigProvider::LABEL_RATING_4,
    //         ConfigProvider::LABEL_RATING_5,
    //         ConfigProvider::LABEL_STOCK_IN,
    //         ConfigProvider::LABEL_STOCK_OUT,
    //     ];

    //     return in_array($value, $staticFilters);
    // }

    private function getCategoryBaseUrlPath(int $categoryId): string
    {
        /** @var \Magento\UrlRewrite\Model\UrlRewrite $item */
        $item = $this->urlRewrite->create()
            ->addFieldToFilter('entity_type', 'category')
            ->addFieldToFilter('redirect_type', 0)
            ->addFieldToFilter('store_id', $this->context->getStoreId())
            ->addFieldToFilter('entity_id', $categoryId)
            ->getFirstItem();

        $url = (string)$item->getData('request_path');

        if (!$url) {
            $urlPath = trim($this->context->getRequest()->getOriginalPathInfo(), '/');

            if (
                strpos($urlPath, 'catalog/category/view') !== false
                && strpos($urlPath, (string)$categoryId) !== false
            ) {
                $categoryId = (string)$categoryId;

                $url = substr($urlPath, 0, strpos($urlPath, $categoryId) + strlen($categoryId));
            }
        }


        return $this->removeCategorySuffix($url);
    }

    private function isNativeRewrite(): bool
    {
        $requestString = trim($this->context->getRequest()->getPathInfo(), '/');

        $requestPathRewrite = $this->urlRewrite->create()
            ->addFieldToFilter('entity_type', 'category')
            ->addFieldToFilter('redirect_type', 0)
            ->addFieldToFilter('store_id', $this->context->getStoreId())
            ->addFieldToFilter('request_path', $requestString);

        return $requestPathRewrite->getSize() > 0;
    }

    private function handleStockFilters(array &$filterData, array $staticFilters): array
    {
        $options = [
            1 => ConfigProvider::LABEL_STOCK_OUT,
            2 => ConfigProvider::LABEL_STOCK_IN,
        ];

        return $this->processBuiltInFilters(ConfigProvider::FILTER_STOCK, $options, $filterData, $staticFilters);
    }

    private function handleRatingFilters(array &$filterData, array $staticFilters): array
    {
        $options = [
            1 => ConfigProvider::LABEL_RATING_1,
            2 => ConfigProvider::LABEL_RATING_2,
            3 => ConfigProvider::LABEL_RATING_3,
            4 => ConfigProvider::LABEL_RATING_4,
            5 => ConfigProvider::LABEL_RATING_5,
        ];

        return $this->processBuiltInFilters(ConfigProvider::FILTER_RATING, $options, $filterData, $staticFilters);
    }

    private function handleSaleFilters(array &$filterData, array $staticFilters): array
    {
        $options = [
            0 => ConfigProvider::FILTER_SALE . '_no',
            1 => ConfigProvider::FILTER_SALE . '_yes',
        ];

        return $this->processBuiltInFilters(ConfigProvider::FILTER_SALE, $options, $filterData, $staticFilters);
    }

    private function handleNewFilters(array &$filterData, array $staticFilters): array
    {
        $options = [
            0 => ConfigProvider::FILTER_NEW . '_no',
            1 => ConfigProvider::FILTER_NEW . '_yes',
        ];

        return $this->processBuiltInFilters(ConfigProvider::FILTER_NEW, $options, $filterData, $staticFilters);
    }

    private function handleAttributeFilters(array &$filterData, array $staticFilters): array
    {
        foreach ($filterData as $attrCode => $filterValues) {
            $rewriteCollection = $this->rewriteRepository->getCollection()
                ->addFieldToFilter(RewriteInterface::REWRITE, ['in' => $filterValues])
                ->addFieldToFilter(RewriteInterface::STORE_ID, $this->context->getStoreId());

            if ($attrCode != '*') {
                $rewriteCollection->addFieldToFilter(RewriteInterface::ATTRIBUTE_CODE, $attrCode);
            }

            if ($rewriteCollection->getSize() == count($filterValues)) {
                /** @var RewriteInterface $rewrite */
                foreach ($rewriteCollection as $rewrite) {
                    $rewriteAttributeCode = $rewrite->getAttributeCode();
                    $optionId             = $rewrite->getOption();

                    $staticFilters[$rewriteAttributeCode][] = $optionId;
                    $this->checkIfRewriteHasDash($rewrite);
                }

                unset($filterData[$attrCode]);
            } else {
                $rewriteCollection = $this->rewriteRepository->getCollection()
                    ->addFieldToFilter(RewriteInterface::ATTRIBUTE_CODE, $attrCode)
                    ->addFieldToFilter(RewriteInterface::STORE_ID, $this->context->getStoreId())
                    ->addFieldToFilter(RewriteInterface::OPTION, ['notnull' => true]);

                $rewrites = [];
                foreach ($rewriteCollection as $rewrite) {
                    $rewrites[$rewrite->getOption()] = $rewrite->getRewrite();
                    $this->checkIfRewriteHasDash($rewrite);
                }
                $filterString = implode('-', $filterValues);

                foreach ($rewrites as $optionId => $rew) {
                    $str = str_replace($rew, '', $filterString);
                    if ($filterString != $str) {
                        $filterString = $str;

                        $staticFilters[$attrCode][] = $optionId;
                    }
                }
                unset($filterData[$attrCode]);
            }
        }

        return $staticFilters;
    }

    private function handleDecimalFilters(array &$filterData, array $decimalFilters): array
    {
        foreach ($filterData as $attrCode => $filterValues) {
            if ($attrCode != '*') {
                if ($this->context->isDecimalAttribute($attrCode)) {
                    $option = implode(ConfigProvider::SEPARATOR_FILTERS, $filterValues);

                    $decimalFilters[$attrCode][] = $option;

                    unset($filterData[$attrCode]);
                }
            } else {
                foreach ($filterValues as $key => $filterValue) {
                    if (strpos($filterValue, ConfigProvider::SEPARATOR_DECIMAL) !== false) {
                        $exploded = explode(ConfigProvider::SEPARATOR_DECIMAL, $filterValue);
                        $attrCode = $exploded[0];
                        unset($exploded[0]);

                        $option                      = implode(ConfigProvider::SEPARATOR_FILTERS, $exploded);
                        $decimalFilters[$attrCode][] = $option;

                        unset($filterData['*'][$key]);
                    }
                }
            }
        }

        return $decimalFilters;
    }

    private function processBuiltInFilters(string $attrCode, array $options, array &$filterData, array $staticFilters): array
    {
        foreach ($options as $key => $label) {
            foreach ($filterData as $fKey => $value) {
                if (in_array($label, $value)) {
                    $staticFilters[$attrCode][] = $key;

                    $vKey = array_search($label, $filterData[$fKey]);
                    unset($filterData[$fKey][$vKey]);
                }
            }
        }

        return $staticFilters;
    }
    
    private function checkIfRouteExists(array $result): array
    {
        if ($result['category_id']) {
            return $result;
        }

        if (empty($result['params'])) {
            $result['match'] = false;
            return $result;
        }

        $requestPath  = trim($this->context->getRequest()->getOriginalPathInfo(), '/');
        $allParts     = explode('/', $requestPath);

        // define url parts count without filters
        if (!empty($result['brand_page_route_data'])) {
            $basePartsCount = count(explode('/', $result['brand_page_route_data']['brandUrlKey'] ?? ''));
            $basePartsCount = $result['brand_page_route_data']['isShortFormatUrl'] ? $basePartsCount : $basePartsCount + 1;
        } elseif ($result['all_products_route']) {
            $basePartsCount = count(explode('/', $result['all_products_route']));
        } elseif (!empty($result['landing_page_route_data'])) {
            $basePartsCount = count(explode('/', trim($result['landing_page_route_data']['page_url'], '/')));
        } else {
            $basePartsCount = 0;
        }

        if ($this->configProvider->getPrefix()) {
            $basePartsCount++;
        }

        $validFiltersCount = 0;

        if ($this->configProvider->getUrlFormat() == ConfigProvider::URL_FORMAT_OPTIONS) {
            $filtersCountInUrl = isset($allParts[$basePartsCount]) ? count(explode('-', $allParts[$basePartsCount])) : 0;

            foreach ($result['params'] as $attr => $item) {
                $options = explode(',', $item);
                $validFiltersCount += count($options);

                if (!empty($this->rewritesWithDash)) {
                    $validFiltersCount += $this->handleRewritesWithDash($options, $attr);
                }
            }

            $result['match'] = $basePartsCount + 1 == count($allParts) && $filtersCountInUrl == $validFiltersCount;
        } else {
            $validFilters = true;

            // initial position of the first attribute in long-url
            $attributeIndex = $basePartsCount + 1;

            foreach ($allParts as $key => $part) {
                if ($key != $attributeIndex) {
                    continue;
                }
                $validFiltersCount = 0;

                $filtersCountInUrl = isset($allParts[$attributeIndex]) ? count(explode('-', $allParts[$attributeIndex])) : 0;

                $attributeValue = isset($result['params'][$allParts[$key - 1]]) ? $result['params'][$allParts[$key - 1]] : null;

                $attributeCode = isset($allParts[$key - 1]) ? $allParts[$key - 1] : null;

                if ($filtersCountInUrl > 1 && $attributeValue) {

                    $separator = $attributeCode && $this->context->isDecimalAttribute($attributeCode) ? '-' : ',';

                    $options = explode($separator, $attributeValue);

                    $validFiltersCount += count($options);

                    if ($separator === ',' && !empty($this->rewritesWithDash)) {
                        $validFiltersCount += $this->handleRewritesWithDash($options, $attributeCode);
                    }

                    if ($filtersCountInUrl != $validFiltersCount) {
                        $validFilters = false;
                        break;
                    }
                }

                $attributeIndex += 2;
            }

            $result['match'] = count($allParts) - $basePartsCount == count($result['params']) * 2 && $validFilters;
        }

        return $result;
    }

    private function checkIfRewriteHasDash(RewriteInterface $rewrite): void
    {
        $alias = trim($rewrite->getRewrite(), '-');
        if (strpos($alias, '-') !== false) {
            $this->rewritesWithDash[$rewrite->getAttributeCode()][$rewrite->getOption()] = count(explode('-', $alias));
        }
    }

    private function handleRewritesWithDash(array $options, string $attr): int
    {
        if (!empty($this->rewritesWithDash && isset($this->rewritesWithDash[$attr]))) {
            $dashesCount = 0;
            foreach ($options as $option) {
                if (isset($this->rewritesWithDash[$attr][$option])) {
                    $dashesCount += $this->rewritesWithDash[$attr][$option] - 1;
                }
            }
            return $dashesCount;
        }
        return 0;
    }
}