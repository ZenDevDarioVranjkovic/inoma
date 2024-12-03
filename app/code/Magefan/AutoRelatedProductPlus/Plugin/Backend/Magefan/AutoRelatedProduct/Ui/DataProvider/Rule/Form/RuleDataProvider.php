<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Plugin\Backend\Magefan\AutoRelatedProduct\Ui\DataProvider\Rule\Form;

use Magefan\AutoRelatedProduct\Ui\DataProvider\Rule\Form\RuleDataProvider as SubjectRuleDataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class RuleDataProviderPlugin
 * @package Magefan\AutoRelatedProductPlus\Plugin\Ui\DataProvider\Rule
 */
class RuleDataProvider extends AbstractDataProvider
{

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * RuleDataProviderPlugin constructor.
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param RuleDataProvider $subject
     * @param $result
     * @return array
     */
    public function afterGetMeta(SubjectRuleDataProvider $subject, $result)
    {
        if ($currentRuleId = $this->request->getParam('id')) {
            $meta = [
                'what_to_display'  => [
                    'children' => [
                        'products_grid' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'rule_id' => $currentRuleId
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } else {
            $meta = parent::getMeta();
        }
        return $meta;
    }

    public function afterGetData($subject, $result)
    {
        return $result;
    }
}
