<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Block\Adminhtml\Rule\Edit\SameAsConditions;

use Magento\Framework\Data\Form\Element\AbstractElement;

class SameAs implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $html = '';

        if ($element->getRule() && $element->getRule()->getConditions()) {
            $html = str_replace('conditions', 'same_as_conditions', $element->getRule()->getConditions()->asHtmlRecursive());
        }

        return $html;
    }
}
