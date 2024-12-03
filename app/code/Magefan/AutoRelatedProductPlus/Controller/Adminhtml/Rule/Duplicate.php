<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magefan\AutoRelatedProduct\Api\RuleRepositoryInterface;

class Duplicate extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magefan_AutoRelatedProduct::rule';

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * Duplicate constructor.
     * @param Context $context
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(
        Context $context,
        RuleRepositoryInterface $ruleRepository
    ) {
        parent::__construct($context);
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $ruleId = $this->_request->getParam('id');
            $rule = $this->ruleRepository->get($ruleId);
            $model = clone $rule;
            $model
                ->unsetData('id')
                ->setName($model->getName() . ' (' . __('Duplicated') . ')')
                ->setData('status', 0)
                ->setData('duplicated', 1);


            $this->ruleRepository->save($model);

            $this->messageManager->addSuccessMessage(__('%1 has been duplicated.', $model->getOwnTitle()));
            $resultRedirect->setPath('autorp/*/edit', ['id' => $model->getId()]);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __(
                    'Something went wrong while saving this %1. %2',
                    strtolower(isset($model) ? $model->getOwnTitle() : 'item'),
                    $e->getMessage()
                )
            );
            $resultRedirect->setPath('autorp/*/edit', ['_current' => true]);
        }

        return $resultRedirect;
    }
}
