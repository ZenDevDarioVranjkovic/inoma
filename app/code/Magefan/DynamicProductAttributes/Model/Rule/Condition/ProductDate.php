<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\DynamicProductAttributes\Model\Rule\Condition;

use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\Rule\Model\Condition\Product\AbstractProduct;

/**
 * Class ProductDate
 */
class ProductDate extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $attrCode = $this->getAttribute();

        $oldAttrValue = $model->getData($attrCode);

        if ($oldAttrValue === null) {
            if ($this->getOperator() === '<=>') {
                return true;
            }
            return false;
        }

        $attrValue = $model->getData($attrCode);
        $attrValue = ($attrValue && is_string($attrValue) && !is_numeric($attrValue)) ? strtotime($attrValue) : $attrValue;

        $this->_setAttributeValue($model);
        $result = $this->validateAttribute($attrValue);
        $this->_restoreOldAttrValue($model, $oldAttrValue);

        return (bool)$result;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $productAttributes = $this->_productResource->loadAllAttributes()->getAttributesByCode();

        $attributes = [];
        foreach ($productAttributes as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            if (!$attribute->isAllowedForRuleCondition() || !$attribute->getDataUsingMethod(
                    $this->_isUsedForRuleProperty
                )
            ) {
                continue;
            }

            if ('date' == $attribute->getFrontendInput()) {
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel() . __(' (extended)');
            }
        }
        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'string';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * @return mixed
     */
    public function getValueParsed()
    {
        if (!$this->hasValueParsed()) {
            $value = $this->getData('value');
            $value = (is_string($value)) ? trim($value) : 0;
            $this->setValueParsed($this->getDateObject()->date('Y-m-d 00:00:00', strtotime('today +' . $value . ' day')));
            //$this->setValueParsed(strtotime('today +' . $value . ' day'));
        }

        return $this->getData('value_parsed');
    }

    /**
     * @return array
     */
    public function getDefaultOperatorOptions()
    {
        if (null === $this->_defaultOperatorOptions) {
            $this->_defaultOperatorOptions = [
                '==' => __('is (today +/- n days)'),
                '>=' => __('equals or greater than (today +/- n days)'),
                '<=' => __('equals or less than (today +/- n days)'),
            ];
        }
        return $this->_defaultOperatorOptions;
    }

    /**
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            $this->_defaultOperatorInputByType['string'] = ['==', '>=', '<='];
        }
        return $this->_defaultOperatorInputByType;
    }

    public function getValueElementHtml()
    {
        return str_replace('type="text"', 'type="number"', $this->getValueElement()->getHtml());
    }

    /**
     * @return \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private function getDateObject()
    {
        if (null === $this->date) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->date = $objectManager->create(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        }

        return  $this->date;
    }
}
