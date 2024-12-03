<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Controller\Adminhtml\Product\Group;

use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Backend\App\Action;

class NewSameAsConditionHtml extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magefan_AutoRelatedProduct::rule';

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Generate Condition HTML form. Ajax
     */
    public function execute()
    {
        $id = (string)$this->getRequest()->getParam('id');
        $form = $this->getRequest()->getParam('form');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        /* use object manager for factory like magento controller*/
        $model = $this->_objectManager->create($type)
            ->setId($id)
            ->setType($type)
            ->setRule($this->_objectManager->create(\Magento\SalesRule\Model\Rule::class))
            ->setPrefix('same_as_conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            if (strpos($form, 'where_conditions') !== false) {
                $model->setPrefix('where_conditions');
                $model->setWhereConditions([]);
            }

            if (strpos($form, 'same_as_conditions') !== false) {
                $model->setPrefix('same_as_conditions');
            }

            $model->setJsFormObject($form);
            $model->setFormName($this->getRequest()->getParam('form_namespace'));
            $html = $model->asHtmlRecursive();

        } else {
            $html = '';
        }

        $this->getResponse()->setBody($html);
    }
}
