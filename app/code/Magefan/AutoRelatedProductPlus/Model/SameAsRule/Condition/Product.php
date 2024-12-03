<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Model\SameAsRule\Condition;

use Magento\Catalog\Model\Product as ProductModel;

class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * @param ProductModel $product
     * @param string $attributeCode
     * @return mixed
     */
    private function getAttributeValue(ProductModel $product, $attributeCode)
    {
        return $product->getResource()->getAttributeRawValue(
            $product->getId(),
            $attributeCode,
            $product->getStoreId()
        );
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType(): array
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = [
                'string' => ['=='],
                'numeric' => ['=='],
                'date' => ['=='],
                'select' => ['=='],
                'boolean' => ['=='],
                'multiselect' => ['=='],
                'grid' => ['=='],
                'category' => ['=='],
                'sku' => ['=='],
            ];
            $this->_arrayInputTypes[] = 'category';
        }

        return $this->_defaultOperatorInputByType;
    }

    /**
     * @return string
     */
    public function getValueElementHtml()
    {
        return __('same as Current Product ') . $this->getAttributeObject()->getDefaultFrontendLabel();
    }

    /**
     * @return array
     */
    public function getAttributeSelectOptions(): array
    {
        return [
            [
                'value' => '1',
                'label' => __('same as Current Product ') . $this->getAttributeObject()->getDefaultFrontendLabel()
            ]
        ];
    }

    /**
     * @return array
     */
    public function getValueSelectOptions(): array
    {
        return $this->getAttributeSelectOptions();
    }

    /**
     * @return string
     */
    public function getValueElementType(): string
    {
        return 'select';
    }

    /**
     * @return string
     */
    public function getFormName(): string
    {
        return 'autorp_rule_form';
    }
}
