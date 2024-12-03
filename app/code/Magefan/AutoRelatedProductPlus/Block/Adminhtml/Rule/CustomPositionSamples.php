<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Block\Adminhtml\Rule;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Context;

class CustomPositionSamples extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getElementHtml():string
    {
        return $this->getContentHtml();
    }

    /**
     * Prepares content block
     * @return string
     */
    public function getContentHtml(): string
    {
        return $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Template::class,
            'custom_position_samples',
            ['data' =>
                ['rule_id'=> $this->registry->registry('current_model')->getData('id')]
            ]
        )
            ->setTemplate('Magefan_AutoRelatedProductPlus::custom_position_samples.phtml')
            ->toHtml();
    }


    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->getElementHtml();
    }
}
