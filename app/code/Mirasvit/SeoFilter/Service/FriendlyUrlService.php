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

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Mirasvit\SeoFilter\Model\ConfigProvider;
use Mirasvit\SeoFilter\Model\Context;
use Mirasvit\SeoFilter\Service\MatchService\Splitting;
use Magento\Store\Model\StoreManagerInterface;

class FriendlyUrlService
{
    const QUERY_FILTERS = ['cat'];

    private $rewriteService;

    private $urlService;

    private $splitting;

    private $context;

    private $configProvider;

    private $storeManager;

    private $attrAliasInstance = [
        'attrCode'  => null,
        'attrAlias' => null
    ];

    /** @var \Magento\Framework\App\Request\Http */
    private $request;

    private $urlInstance;

    private $urlFinder;

    public function __construct(
        RequestInterface      $request,
        RewriteService        $rewrite,
        UrlService            $urlService,
        Splitting             $splitting,
        UrlInterface          $urlInstance,
        UrlFinderInterface    $urlFinder,
        ConfigProvider        $configProvider,
        Context               $context,
        StoreManagerInterface $storeManager
    ) {
        $this->request        = $request;
        $this->rewriteService = $rewrite;
        $this->urlService     = $urlService;
        $this->splitting      = $splitting;
        $this->context        = $context;
        $this->configProvider = $configProvider;
        $this->urlInstance    = $urlInstance;
        $this->urlFinder      = $urlFinder;
        $this->storeManager   = $storeManager;
    }

    public function getUrl(string $attributeCode, string $filterValue, bool $remove = false, ?string $currentUrl = null, ?int $storeId = null): string
    {
        $values = explode(ConfigProvider::SEPARATOR_FILTER_VALUES, $filterValue);

        $requiredFilters[$attributeCode] = [];
        if ($attributeCode != '') {
            foreach ($values as $value) {
                $requiredFilters[$attributeCode][$value] = $value;
            }
        }

        // merge with previous filters
        foreach ($this->rewriteService->getActiveFilters() as $attr => $filters) {
            if (!$this->configProvider->isMultiselectEnabled(($attributeCode == 'cat') ? 'category_ids' : $attributeCode) && $attr == $attributeCode) {
                continue;
            }

            foreach ($filters as $filter) {
                if ($filter == $filterValue) {
                    unset($requiredFilters[$attr][$filter]);
                    continue;
                }

                $requiredFilters[$attr][$filter] = $filter;
            }
        }

        if (isset($requiredFilters['q']) && $this->request->getParam('q', false)) {
            unset($requiredFilters['q']);
        }
        // remove filter
        if ($attributeCode != '') {
            if ($remove && isset($requiredFilters[$attributeCode])) {
                foreach ($values as $value) {
                    if ($value == 'all') {
                        unset($requiredFilters[$attributeCode]);
                        break;
                    }

                    unset($requiredFilters[$attributeCode][$value]);
                }
            }
        }
        // merge all filters on one line f1-f2-f3-f4
        $filterLines = [];
        $queryParams = [];

        foreach ($requiredFilters as $attrCode => $filters) {
            if ($this->attrAliasInstance['attrCode'] === $attrCode) {
                $attrAlias = $this->attrAliasInstance['attrAlias'];
            } else {
                $attrAlias  = $this->getAttributeRewrite($attrCode);
                $this->attrAliasInstance['attrAlias'] = $attrAlias;
                $this->attrAliasInstance['attrCode'] = $attrCode;
            }
            $filterLine = [];
            $queryParam = [];

            foreach ($filters as $filter) {
                if (in_array($attrCode, self::QUERY_FILTERS)) {
                    $queryParam[] = $filter;
                } else {
                    $filterLine[] = $this->rewriteService->getOptionRewrite($attrCode, $filter, $storeId)->getRewrite();
                }
            }

            if (in_array($attrCode, self::QUERY_FILTERS) && count($filters) == 0) {
                $queryParam[] = '';
            }

            if (count($queryParam)) {
                $queryParams[$attrAlias] = implode(',', $queryParam);
            }

            if (count($filterLine)) {
                $filterLine = $this->sortOptions($filterLine);
                $filterLines[$attrAlias] = implode(ConfigProvider::SEPARATOR_FILTERS, $filterLine);
            }
        }

        if ($this->configProvider->getUrlFormat() === ConfigProvider::URL_FORMAT_ATTR_OPTIONS) {
            foreach ($filterLines as $attr => $options) {
                $filterLines[$attr] = $attr . '/' . $options;
            }
            ksort($filterLines);

            $filterString = implode('/', $filterLines);
        } else {
            $filterLines = implode(ConfigProvider::SEPARATOR_FILTERS, $filterLines);

            //sort filters
            $values = $this->splitting->splitFiltersString($filterLines, $storeId);

            if (is_array($values)) {
                $values = $this->sortOptions($values);
            }

            $filterString = implode(ConfigProvider::SEPARATOR_FILTERS, $values);
        }

        //add extra query params
        foreach ($this->urlService->getGetParams() as $param => $value) {
            if (!array_key_exists($param, $requiredFilters)) {
                if ($param === 'p') {
                    continue;
                }

                $queryParams[$param] = $value;
            }
        }

        return $this->getPreparedCurrentUrl($filterString, $queryParams, $currentUrl);
    }

    public function getPreparedCurrentUrl(string $filterUrlString, array $queryParams, ?string $currentUrl = null): string
    {
        $suffix = $this->getSuffix((bool)$currentUrl);
        $url    = $currentUrl ? : $this->getClearUrl();
        $url    = preg_replace('/\?.*/', '', $url);
        $url    = ($suffix && $suffix !== '/') ? str_replace($suffix, '', $url) : $url;
        if (!empty($filterUrlString)) {
            if ($separator = $this->configProvider->getPrefix()) {
                $url .= (substr($url, -1, 1) === '/' ? '' : '/') . $separator;
            }

            $url .= (substr($url, -1, 1) === '/' ? '' : '/') . $filterUrlString;
        }

        $url = $url . $suffix;
        $url = preg_replace("@//$@", "/", $url);

        $query = '';
        if (count($queryParams)) {
            $query = '?' . http_build_query($queryParams);
        }

        return $url . $query;
    }

    public function getClearUrl(): string
    {
        $url = '';

        $fullActionName = $this->request->getFullActionName();
        switch ($fullActionName) {
            case 'catalog_category_view':
                $category = $this->context->getCurrentCategory();

                // need this because $category->getUrl() can return not direct URL (rewrite with redirect)
                // we need to be sure that we get the direct category URL
                $rewrite = $this->urlFinder->findOneByData(
                    [
                        'entity_id'     => $category->getId(),
                        'entity_type'   => 'category',
                        'store_id'      => $this->context->getStoreId(),
                        'redirect_type' => 0,
                    ]
                );

                if ($rewrite) {
                    $category->setData('url', $this->urlInstance->getDirectUrl($rewrite->getRequestPath()));
                }

                $url = $category->getUrl();
                break;

            case 'all_products_page_index_index':
                $url = ObjectManager::getInstance()->get('\Mirasvit\AllProducts\Service\UrlService')->getClearUrl();
                break;

            case 'brand_brand_view':
                $url = ObjectManager::getInstance()->get('Mirasvit\Brand\Service\BrandUrlService')->getBaseBrandUrl();

                $currentUrl  = parse_url($this->request->getRequestString(), PHP_URL_PATH);
                $brandConfig = ObjectManager::getInstance()->get('Mirasvit\Brand\Model\Config\GeneralConfig');

                /** @var \Mirasvit\Brand\Repository\BrandRepository|object $brandRepository */
                $brandRepository = ObjectManager::getInstance()->get('Mirasvit\Brand\Repository\BrandRepository');

                foreach ($brandRepository->getFullList() as $brand) {
                    if (preg_match('/\/' . $brand->getUrlKey() . '\//', rtrim($currentUrl, '/') . '/')) {
                        $url = $brand->getUrl();
                        break;
                    }
                }

                break;
            case 'landing_landing_view':
                $landingId = $this->request->getParam('landing');
                if (!$landingId) {
                    return $url;
                }

                $landing = ObjectManager::getInstance()->get('Mirasvit\LandingPage\Repository\PageRepository')->get((int)$landingId);

                if (!$landing && !$landing->getUrlKey()) {
                    return $url;
                }

                $storeId = (int)$this->storeManager->getStore()->getId();

                $landingStoreCode = '/';
                if ($storeId > 1) {
                    $landingStoreCode = $this->storeManager->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_LINK, true);
                }

                $url = $landingStoreCode . $landing->getUrlKey();
                break;
        }

        return $url;
    }

    public function getSuffix(bool $isCategory = false): string
    {
        $suffix = '';
        if ($this->request->getFullActionName() == 'catalog_category_view' || $isCategory) {
            $suffix = $this->urlService->getCategoryUrlSuffix();
        }

        if ($this->request->getFullActionName() == 'brand_brand_view') {
            $suffix = $this->urlService->getBrandUrlSuffix();
        }

        return $suffix;
    }

    public function getAttributeRewrite(string $attributeCode): string
    {
        $attrRewrite = $this->rewriteService->getAttributeRewrite($attributeCode);

        return $attrRewrite ? $attrRewrite->getRewrite() : $attributeCode;
    }

    public function getUrlWithFilters(string $url, array $filters): string
    {
        $filterString = '';
        $filterLines  = [];

        foreach ($filters as $code => $options) {
            $attrAlias = $this->getAttributeRewrite($code);

            $filterLine = [];

            foreach (explode(',', $options) as $option) {
                $optionRewrite = $this->rewriteService->getOptionRewrite($code, $option);

                if ($optionRewrite) {
                    $filterLine[] = $optionRewrite->getRewrite();
                }
            }

            $filterLines[$attrAlias] = implode(ConfigProvider::SEPARATOR_FILTERS, $filterLine);
        }

        if ($this->configProvider->getUrlFormat() === ConfigProvider::URL_FORMAT_ATTR_OPTIONS) {
            foreach ($filterLines as $attr => $options) {
                $filterLines[$attr] = $attr . '/' . $options;
            }
            ksort($filterLines);

            $filterString = implode('/', $filterLines);
        } else {
            $filterLines = implode(ConfigProvider::SEPARATOR_FILTERS, $filterLines);

            //sort filters
            $values = $this->splitting->splitFiltersString($filterLines);
            asort($values);

            $filterString = implode(ConfigProvider::SEPARATOR_FILTERS, $values);
        }

        return $this->getPreparedCurrentUrl($filterString, [], $url);
    }

    private function sortOptions(array $filterOptions): array
    {
        uasort($filterOptions, function ($a, $b) {
            if ($a == $b) {
                return 0;
            }
            // sorting for options like a-b and a-b-a with dash separator
            if (substr($a, 0, strlen($b)) === $b) {
                return -1;
            }
            if (substr($b, 0, strlen($a)) === $a) {
                return 1;
            }
            return ($a < $b) ? -1 : 1;
            }
        );

        return $filterOptions;
    }
}
