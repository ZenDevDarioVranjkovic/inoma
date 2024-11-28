<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Block\Adminhtml\Group;

/**
 * Admin UB Menu Group
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize menu group edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'group_id';
        $this->_blockGroup = 'Ubertheme_UbMegaMenu';
        $this->_controller = 'adminhtml_group';

        parent::_construct();

        //add button manage items
        $groupId = $this->_coreRegistry->registry('ubmegamenu_group')->getId();
        if ($groupId) {
            $this->buttonList->add(
                'manageitems',
                [
                    'label' => __('Manage Menu Items'),
                    'class' => 'manage__items',
                    'onclick' => 'window.location.href=\''
                        . $this->getUrl('ubmegamenu/item/index', ['group_id' => $groupId ]) . '\'',
                ],
                -1,
                10
            );
        }

        if ($this->_isAllowedAction('Ubertheme_UbMegaMenu::group_save')) {
            $this->buttonList->update('save', 'label', __('Save Menu Group'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Ubertheme_UbMegaMenu::group_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Menu Group'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('ubmegamenu_group')->getId()) {
            return __(
                "Edit Menu Group '%1'",
                $this->escapeHtml($this->_coreRegistry->registry('ubmegamenu_group')->getTitle())
            );
        } else {
            return __('New Menu Group');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'ubmegamenu/*/save',
            ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']
        );
    }
}
