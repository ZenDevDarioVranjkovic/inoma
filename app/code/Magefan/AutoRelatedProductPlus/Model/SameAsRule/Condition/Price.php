<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Model\SameAsRule\Condition;

class Price extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * @return array
     */
    public function getDefaultOperatorInputByType(): array
    {
        $this->_defaultOperatorInputByType = parent::getDefaultOperatorInputByType();
        $this->_arrayInputTypes[] = 'price';
        $this->_defaultOperatorInputByType['price'] = ['==', '!=', '>=', '>', '<=', '<'];

        return $this->_defaultOperatorInputByType;
    }

    /**
     * @return string
     */
    public function getInputType() :string
    {
        return 'price';
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getAttributeElementHtml()
    {
        return __('Price');
    }

    /**
     * @return string
     */
    public function getValueElementHtml()
    {
        return __(' Current Product Price');
    }

    /**
     * @return string
     */
    protected function _getAttributeCode(): string
    {
        return 'price';
    }

    /**
     * @return array
     */
    public function getAttributeSelectOptions(): array
    {
        return [
            [
                'value' => '1',
                'label' => __(' Current Product Price')
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
