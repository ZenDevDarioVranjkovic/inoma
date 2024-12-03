<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AutoRelatedProductPlus\Controller\Adminhtml\Product\Mui;

use Magento\Framework\Exception\NotFoundException;

/**
 * Class Render
 */
class Render extends \Magento\Ui\Controller\Adminhtml\Index\Render
{
    /**
     * Authorization level of a basic admin session
     */
    public function execute()
    {
        try {
            return parent::execute();
        } catch (NotFoundException $e) {
            $e->getMessage();
        }
    }
}
