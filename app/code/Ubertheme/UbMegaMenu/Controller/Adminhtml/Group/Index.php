<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Controller\Adminhtml\Group;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbMegaMenu::group';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        //unset menu group id from session
        $this->_objectManager->get('Magento\Backend\Model\Session')->unsMenuGroupId();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ubertheme_UbMegaMenu::group');
        $resultPage->addBreadcrumb(__('UbMegaMenu'), __('UbMegaMenu'));
        $resultPage->addBreadcrumb(__('Manage Menu Groups'), __('Manage Menu Groups'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Menu Groups'));

        return $resultPage;
    }
}
