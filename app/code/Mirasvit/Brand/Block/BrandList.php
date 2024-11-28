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

namespace Mirasvit\Brand\Block;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Brand\Api\Data\BrandInterface;
use Mirasvit\Brand\Service\BrandListService;
use Mirasvit\Brand\Model\Config\Config;

class BrandList extends Template
{
    private $config;

    private $brandListService;

    private $layerResolver;

    public function __construct(
        Config           $config,
        BrandListService $brandListService,
        LayerResolver    $layerResolver,
        Context          $context
    ) {
        $this->config           = $config;
        $this->brandListService = $brandListService;
        $this->layerResolver    = $layerResolver;

        parent::__construct($context);
    }

    public function getBrandsByLetters(): array
    {
        $usedBrands = $this->config->getGeneralConfig()->isShowBrandsWithoutProducts()
            ? null
            : $this->getUsedBrands();

        return $this->brandListService->getBrandsByLetters($usedBrands);
    }

    public function canShowImage(BrandInterface $brand): bool
    {
        return $this->config->getAllBrandPageConfig()->isShowBrandLogo() && $brand->getImage();
    }

    private function getUsedBrands(): array
    {
        $brandAttributeCode = $this->config->getGeneralConfig()->getBrandAttribute();

        if (!$brandAttributeCode) {
            return [];
        }

        $productCollection = $this->layerResolver->get()->getProductCollection();
        $facets            = $productCollection->getFacetedData($brandAttributeCode);

        $usedBrands = [];
        foreach ($facets as $facet) {
            $usedBrands[] = $facet['value'];
        }

        return $usedBrands;
    }
}
