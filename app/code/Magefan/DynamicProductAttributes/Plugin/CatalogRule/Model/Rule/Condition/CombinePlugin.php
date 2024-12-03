<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\DynamicProductAttributes\Plugin\CatalogRule\Model\Rule\Condition;

use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magefan\DynamicProductAttributes\Model\AddCustomConditions;

class CombinePlugin
{
    /**
     * @var AddCustomConditions
     */
    private $addCustomConditions;

    /**
     * @param AddCustomConditions $addCustomConditions
     */
    public function __construct(
        AddCustomConditions $addCustomConditions
    ) {
        $this->addCustomConditions = $addCustomConditions;
    }

    /**
     * @param Combine $subject
     * @param $result
     * @return mixed
     */
    public function afterGetNewChildSelectOptions(Combine $subject, $result)
    {
        return $this->addCustomConditions->execute($result);
    }
}
