<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\DynamicProductAttributes\Api;

/**
 * Return category by product
 *
 * @api
 * @since 2.1.10
 */
interface AddCustomValidationFiltersInterface
{

    /**
     * @param $conditions
     * @return mixed
     */
    public function processCustomValidator($conditions);

    /**
     * @param $collection
     * @return mixed
     */
    public function addCustomValidationFilters($collection);

    /**
     * @return mixed
     */
    public function _getWebsitesMap();

    /**
     * @param array $productIds
     * @return array
     */
    public function getParentProductIds(array $productIds): array;
}
