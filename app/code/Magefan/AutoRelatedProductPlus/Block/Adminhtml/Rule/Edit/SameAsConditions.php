<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Block\Adminhtml\Rule\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magefan\AutoRelatedProductPlus\Block\Adminhtml\Rule\Edit\SameAsConditions\SameAs;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magefan\AutoRelatedProductPlus\Model\SameAsRuleFactory;

class SameAsConditions extends Generic implements TabInterface
{
    /**
     * @var Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var SameAs
     */
    protected $actions;

    /**
     * @var string
     */
    protected $_nameInLayout = 'same_as_conditions_apply_to';

    /**
     * @var SameAsRuleFactory
     */
    protected $ruleFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param SameAs $actions
     * @param Fieldset $rendererFieldset
     * @param SameAsRuleFactory $ruleFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        SameAs $actions,
        Fieldset $rendererFieldset,
        SameAsRuleFactory $ruleFactory,
        array $data = []
    ) {
        $this->rendererFieldset = $rendererFieldset;
        $this->actions = $actions;
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Same As Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Same As Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_model');
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param $model
     * @param $fieldsetId
     * @param $formName
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addTabToForm($model, $fieldsetId = 'same_as_conditions_fieldset', $formName = 'autorp_rule_form')
    {
        if (!$this->getRequest()->getParam('js_form_object')) {
            $this->getRequest()->setParam('js_form_object', $formName);
        }
        $rule = $this->ruleFactory->create();

        $rule->setData('conditions_serialized', $model->getData('same_as_conditions_serialized'));
        $model = $rule;
        $fieldSetId ='autorp_rule_formrule_same_as_conditions_fieldset_';
        $newChildUrl = $this->getUrl('mfautorp/product_group/newSameAsConditionHtml/form/' . $fieldSetId, ['form_namespace' => $formName]);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_same_as');
        $renderer = $this->rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $newChildUrl
        )->setFieldSetId(
            $fieldSetId
        );
        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' => __(
                    'Same As (leave blank for all items).'
                )
            ]
        )->setRenderer(
            $renderer
        );
        $fieldset->addField(
            'same_as_conditions',
            'text',
            [
                'name'           => 'same_as_conditions',
                'label'          => __('same_as_conditions'),
                'title'          => __('same_as_conditions'),
                'required'       => true,
                'data-form-part' => $formName
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->actions
        );
        $form->setValues($rule->getData());
        $this->setConditionFormName($model->getConditions(), $formName, $fieldSetId);

        return $form;
    }

    /**
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param $formName
     * @param $jsFormName
     * @return void
     */
    private function setConditionFormName(\Magento\Rule\Model\Condition\AbstractCondition $conditions, $formName, $jsFormName)
    {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($jsFormName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }
}
