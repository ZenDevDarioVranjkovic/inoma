<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Model\SameAsRule;

use Magento\Rule\Model\Condition\Context;
use Magento\CatalogRule\Model\Rule\Condition\ProductFactory;

class SameAsCombine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @param Context $context
     * @param ProductFactory $conditionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductFactory $conditionFactory,
        array $data = []
    ) {
        $this->_productFactory = $conditionFactory;
        parent::__construct($context, $data);
        $this->setType(\Magento\CatalogRule\Model\Rule\Condition\Combine::class);
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions(): array
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];

        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Magefan\AutoRelatedProductPlus\Model\SameAsRule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }
        $conditions = [['value' => '', 'label' => __('Please choose a condition to add.')]];
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'label' => __('Custom Fields'),
                    'value' => [
                        [
                            'label' => __('Price'),
                            'value' => \Magefan\AutoRelatedProductPlus\Model\SameAsRule\Condition\Price::class,
                        ],
                    ]
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );

        return $conditions;
    }
}
