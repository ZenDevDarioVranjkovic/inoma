<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\DynamicProductAttributes\Controller\Adminhtml\Rule;

use Magento\Framework\Controller\ResultFactory;

class UpdateProductAttributes extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magefan_DynamicProductAttributes::mfdynamicproductattributes';

    /**
     * @var \Magefan\DynamicProductAttributes\Model\UpdateProductAttributes
     */
    protected $updateProductAttributes;

    /**
     * @var \Magefan\DynamicProductAttributes\Model\Config
     */
    protected $config;

    /**
     * UpdateProductAttributes constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magefan\DynamicProductAttributes\Model\Config $config
     * @param \Magefan\DynamicProductAttributes\Model\UpdateProductAttributes $updateProductAttributes
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magefan\DynamicProductAttributes\Model\Config $config,
        \Magefan\DynamicProductAttributes\Model\UpdateProductAttributes $updateProductAttributes
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

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        try {
            if (!$this->config->isEnabled()) {
                $this->messageManager->addErrorMessage(__('The extension is disabled'));
            } else {
                $this->updateProductAttributes->update();
                $this->messageManager->addSuccessMessage(__('Attributes have been updated.'));
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong. %1', $e->getMessage()));
        }

        return $resultRedirect;
    }
}

