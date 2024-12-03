<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\DynamicProductAttributes\Model;

use Magento\CatalogRule\Model\Rule\Condition\Combine;

class AddCustomConditions
{
    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magefan\DynamicProductAttributes\Model\Rule\Condition\ProductFactory
     */
    private $customProductFactory;

    /**
     * @var \Magefan\DynamicProductAttributes\Model\Rule\Condition\ProductDateFactory
     */
    private $customProductDateFactory;

    /**
     * CombinePlugin constructor.
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $productFactory
     * @param \Magefan\DynamicProductAttributes\Model\Rule\Condition\ProductFactory $customProductFactory
     * @param \Magefan\DynamicProductAttributes\Model\Rule\Condition\ProductDateFactory|null $customProductDateFactory
     */
    public function __construct(
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $productFactory,
        \Magefan\DynamicProductAttributes\Model\Rule\Condition\ProductFactory $customProductFactory,
        \Magefan\DynamicProductAttributes\Model\Rule\Condition\ProductDateFactory $customProductDateFactory = null
    ) {
        $this->productFactory = $productFactory;
        $this->customProductFactory = $customProductFactory;
        $this->customProductDateFactory = $customProductDateFactory ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magefan\DynamicProductAttributes\Model\Rule\Condition\ProductDateFactory::class
            );
    }

    /**
     * @param array $conditions
     * @return array
     */
    public function execute(array $conditions): array
    {
        $productAttributes = $this->customProductFactory->create()->loadAttributeOptions()->getAttributeOption();
        $dynamicAttributes = [];
        foreach ($productAttributes as $code => $label) {
            $dynamicAttributes[] = [
                'value' => 'Magefan\DynamicProductAttributes\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }

        $productAttributes = $this->customProductDateFactory->create()->loadAttributeOptions()->getAttributeOption();
        foreach ($productAttributes as $code => $label) {
            $dynamicAttributes[] = [
                'value' => 'Magefan\DynamicProductAttributes\Model\Rule\Condition\ProductDate|' . $code,
                'label' => $label,
            ];
        }

        $conditions = array_merge_recursive(
            $conditions,
            [
                ['label' => __('Custom Product Attributes'), 'value' => $dynamicAttributes],
            ]
        );

        return $conditions;
    }
}
