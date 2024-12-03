<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\DynamicProductAttributes\Plugin\CatalogRule\Model\Rule\Condition;

use Magento\CatalogRule\Model\Rule\Condition\MappableConditionsProcessor;
use Magefan\DynamicProductAttributes\Model\Rule\Condition\Product;

class MappableConditionsProcessorPlugin
{
    /**
     * @param MappableConditionsProcessor $subject
     * @param $conditions
     * @return array
     */
    public function beforeRebuildConditionsTree(MappableConditionsProcessor $subject, $conditions)
    {

        foreach ($conditions->getConditions() as $condition) {
            if ($condition->getType() === Product::class) {
                $condition->setType(\Magento\CatalogRule\Model\Rule\Condition\Product::class);
            }
        }
        return  [$conditions];
    }
}
