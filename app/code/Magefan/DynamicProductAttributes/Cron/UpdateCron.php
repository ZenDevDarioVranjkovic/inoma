<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\DynamicProductAttributes\Cron;

use Magefan\DynamicProductAttributes\Model\Config;
use Magefan\DynamicProductAttributes\Model\UpdateProductAttributes;

class UpdateCron
{
    /**
     * @var UpdateProductAttributes
     */
    private $updateProductAttributes;

    /**
     * @var Config
     */
    private $config;

    const UPDATE_FOR_ALL_PRODUCTS = 0;

    /**
     * @param Config $config
     * @param UpdateProductAttributes $updateProductAttributes
     */
    public function __construct(
        Config                  $config,
        UpdateProductAttributes $updateProductAttributes
    ) {
        $this->config = $config;
        $this->updateProductAttributes = $updateProductAttributes;
    }

    /**
     * @return void
     */
    public function updateBestSellers()
    {
        if ($this->config->isEnabled() && $this->config->isBestSellersEnabled()) {
            $this->updateProductAttributes->updateBestSellerRating(self::UPDATE_FOR_ALL_PRODUCTS);
        }
    }

    /**
     * @return void
     */
    public function updateIsNew()
    {
        if ($this->config->isEnabled() && $this->config->isNewEnabled()) {
            $this->updateProductAttributes->updateIsNew(self::UPDATE_FOR_ALL_PRODUCTS);
        }
    }

    /**
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    public function updateIsOnSale()
    {
        if ($this->config->isEnabled() && $this->config->isOnSaleEnabled()){
            $this->updateProductAttributes->updateIsOnSale(self::UPDATE_FOR_ALL_PRODUCTS);
        }
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateQty()
    {
        if ($this->config->isEnabled() &&  $this->config->isQtyEnabled()){
            $this->updateProductAttributes->updateQty(self::UPDATE_FOR_ALL_PRODUCTS);
        }
    }

    /**
     * @return void
     */
    public function updateRatingAttribute()
    {
        if ($this->config->isEnabled() && $this->config->isReviewAndRatingEnabled()) {
            $this->updateProductAttributes->updateRatingAttribute(self::UPDATE_FOR_ALL_PRODUCTS);
        }
    }
}
