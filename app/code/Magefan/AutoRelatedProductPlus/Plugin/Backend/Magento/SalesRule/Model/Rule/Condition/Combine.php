<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Plugin\Backend\Magento\SalesRule\Model\Rule\Condition;

use Magento\Framework\App\RequestInterface;
use Magento\SalesRule\Model\Rule\Condition\Address;
use Magento\SalesRule\Model\Rule\Condition\Combine as SubjectCombine;
use Magefan\DynamicProductAttributes\Model\AddCustomConditions;

/**
 * Class CombinePlugin
 */
class Combine
{

    /**
     * @var Address
     */
    protected $ruleAddress;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var AddCustomConditions
     */
    protected $addCustomConditions;

    /**
     * @param Address $ruleAddress
     * @param RequestInterface $request
     * @param AddCustomConditions $addCustomConditions
     */
    public function __construct(
        Address $ruleAddress,
        RequestInterface $request,
        AddCustomConditions $addCustomConditions
    ) {
        $this->ruleAddress = $ruleAddress;
        $this->request = $request;
        $this->addCustomConditions = $addCustomConditions;
    }

    /**
     * @param Combine $subject
     * @param $result
     * @return array
     */
    public function afterGetNewChildSelectOptions(SubjectCombine $subject, $result)
    {
        if ($this->request->getModuleName() == 'autorp') {
            $conditions = $result;

            $cartAttributes = $this->ruleAddress->loadAttributeOptions()->getAttributeOption();
            $attributesCart = [];

            foreach ($cartAttributes as $code => $label) {
                $attributesCart[] = [
                    'value' => 'Magento\SalesRule\Model\Rule\Condition\Address|' . $code,
                    'label' => $label,
                ];
            }
            $conditions = array_merge_recursive(
                $conditions,
                [
                    ['label' => __('Cart Attribute (Plus)'), 'value' => $attributesCart]
                ]
            );

            $categoryAttributes[] = [
                'value' => 'Magefan\AutoRelatedProduct\Model\Condition\Product|catalog_category_ids',
                'label' => 'Category',
            ];

            $pageAttributes = [
                [
                    'value' => 'Magefan\AutoRelatedProduct\Model\Condition\Product|page_action_name',
                    'label' => 'Action Name'
                ],
                [
                    'value' => 'Magefan\AutoRelatedProduct\Model\Condition\Product|page_uri',
                    'label' => 'URI'
                ]
            ];

            $conditions = array_merge_recursive(
                $conditions,
                [
//                    ['label' => __('Category Attribute'), 'value' => $categoryAttributes], product already have this data
                    ['label' => __('Page Attribute (Plus)'), 'value' => $pageAttributes],
                ]
            );

            $conditions = $this->addCustomConditions->execute($conditions);

            $result = $conditions;
        }

        return $result;
    }
}
