<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\DynamicProductAttributes\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 */
class Config
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Extension enabled config path
     */
    const XML_PATH_EXTENSION_ENABLED = 'mfdynamicproductattributes/general/enabled';

    const XML_PATH_REVIEW_AND_RATING_ENABLED = 'mfdynamicproductattributes/update_schedule/review_and_rating/enabled';
    const XML_PATH_QTY_ENABLED = 'mfdynamicproductattributes/update_schedule/quantity/enabled';
    const XML_PATH_IS_ON_SALE_ENABLED = 'mfdynamicproductattributes/update_schedule/is_on_sale/enabled';
    const XML_PATH_IS_NEW_ENABLED = 'mfdynamicproductattributes/update_schedule/is_new/enabled';
    const XML_PATH_BEST_SELLERS_ENABLED = 'mfdynamicproductattributes/update_schedule/best_sellers/enabled';

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve true if dynamic product attributes module is enabled
     *
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        return (bool)$this->getConfig(self::XML_PATH_EXTENSION_ENABLED, $storeId);
    }

    /**
     * @return bool
     */
    public function isManageStock(): bool
    {
        return (bool) $this->getConfig(\Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK);
    }

    /**
     * Retrieve store config value
     * @param string $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function isReviewAndRatingEnabled($storeId = null): bool
    {
        return (bool)$this->getConfig(self::XML_PATH_REVIEW_AND_RATING_ENABLED, $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function isQtyEnabled($storeId = null): bool
    {
        return (bool)$this->getConfig(self::XML_PATH_QTY_ENABLED, $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function isOnSaleEnabled($storeId = null): bool
    {
        return (bool)$this->getConfig(self::XML_PATH_IS_ON_SALE_ENABLED, $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function isNewEnabled($storeId = null): bool
    {
        return (bool)$this->getConfig(self::XML_PATH_IS_NEW_ENABLED, $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function isBestSellersEnabled($storeId = null): bool
    {
        return (bool)$this->getConfig(self::XML_PATH_BEST_SELLERS_ENABLED, $storeId);
    }
}
