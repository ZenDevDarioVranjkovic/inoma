<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Controller\Adminhtml\Group;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbMegaMenu::group_delete';

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('group_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->_objectManager->create('Ubertheme\UbMegaMenu\Model\Group');
                $model->load($id);
                $title = $model->getTitle();
                $model->delete();

                // display success message
                $this->messageManager->addSuccess(__('The Menu Group has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_ubmegamenugroup_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_ubmegamenugroup_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a Menu Group to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
