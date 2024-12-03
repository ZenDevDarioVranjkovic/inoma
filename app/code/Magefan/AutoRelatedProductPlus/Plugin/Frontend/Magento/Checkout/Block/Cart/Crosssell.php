<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Plugin\Frontend\Magento\Checkout\Block\Cart;

use Magefan\AutoRelatedProduct\Api\RelatedItemsProcessorInterface;

class Crosssell
{
    /**
     * @param RelatedItemsProcessorInterface $relatedItemsProcessor
     */
    private $relatedItemsProcessor;

    /**
     * @param RelatedItemsProcessorInterface $relatedItemsProcessor
     */
    public function __construct(
        RelatedItemsProcessorInterface $relatedItemsProcessor
    ) {
        $this->relatedItemsProcessor = $relatedItemsProcessor;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetItems($subject, $result)
    {
        return $this->relatedItemsProcessor->execute($subject, $result, 'cart_into_crossSell');
    }
}
