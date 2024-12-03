<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Plugin\Magefan\AutoRelatedProduct\Model\Config;

use Magento\Framework\App\RequestInterface;

class ActionValidator
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param \Magefan\AutoRelatedProduct\Model\ActionValidator $subject
     * @param $result
     * @return bool
     */
    public function afterIsRestricted(\Magefan\AutoRelatedProduct\Model\ActionValidator $subject, $result, $rule): bool
    {
        if ($result || !is_string($rule->getData('category_ids'))) {
            return $result;
        }

        if ($this->request->getFullActionName() == 'catalog_category_view') {
            return !$this->isCurrentCategoryAllowedInRule($rule);
        }

        return false;
    }

    /**
     * @param $rule
     * @return bool
     */
    private function isCurrentCategoryAllowedInRule($rule): bool
    {
        $allowedCategoriesIds = explode(',', $rule->getData('category_ids'));
        $currentCategoryId = (int) $this->request->getParam('id');

        return (bool) in_array($currentCategoryId, $allowedCategoriesIds);
    }
}
