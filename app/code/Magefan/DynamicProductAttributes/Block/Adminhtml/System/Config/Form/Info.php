<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\DynamicProductAttributes\Block\Adminhtml\System\Config\Form;

/**
 * Class Info
 */
class Info extends \Magefan\Community\Block\Adminhtml\System\Config\Form\Info
{
    /**
     * Return extension url
     * @return string
     */
    protected function getModuleUrl(): string
    {
        return 'https://mage' . 'fan.com/magento-2-dynamic-categories';
    }

    /**
     * Return extension title
     * @return string
     */
    protected function getModuleTitle(): string
    {
        return 'Dynamic Product Attributes Extension';
    }
}
