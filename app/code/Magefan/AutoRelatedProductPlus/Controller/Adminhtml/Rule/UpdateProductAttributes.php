<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AutoRelatedProductPlus\Controller\Adminhtml\Rule;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class UpdateProductAttributes
 * @package Magefan\DynamicCategory\Controller\Adminhtml\Rule
 */
class UpdateProductAttributes extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magefan_AutoRelatedProduct::rule';

    /**
     * @var \Magefan\AutoRelatedProductPlus\Model\UpdateProductAttributes
     */
    protected $updateProductAttributes;

    /**
     * @var \Magefan\AutoRelatedProductPlus\Model\Config
     */
    protected $config;

    /**
     * UpdateProductAttributes constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magefan\AutoRelatedProduct\Model\Config $config
     * @param \Magefan\AutoRelatedProductPlus\Model\UpdateProductAttributes $updateProductAttributes
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magefan\AutoRelatedProduct\Model\Config $config,
        \Magefan\AutoRelatedProductPlus\Model\UpdateProductAttributes $updateProductAttributes
    ) {
        $this->updateProductAttributes = $updateProductAttributes;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Action execute
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->getUrl('autorp/rule/index'));
        try {
            if (!$this->config->isEnabled()) {
                $this->messageManager->addError(__('The Dynamic Product Attributes extension is disabled. You can enable it in Stores > Configuration > Magefan Extensions > Dynamic Product Attributes.'));
            } else {
                $this->updateProductAttributes->update();
                $this->messageManager->addSuccess(__('Attributes have been updated.'));
            }

        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong. %1', $e->getMessage()));
        }

        return $resultRedirect;
    }
}
