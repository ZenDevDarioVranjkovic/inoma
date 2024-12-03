<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Observer\Backend\Magefan;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogRule\Model\RuleFactory;
use Magefan\AutoRelatedProduct\Model\Config\Source\RelatedTemplate;
use Magento\Framework\Exception\LocalizedException;

class ArpRuleSaveBefore implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var RuleFactory
     */
    protected $catalogRuleFactory;

    /**
     * @param RequestInterface $request
     * @param RuleFactory $catalogRuleFactory
     */
    public function __construct(
        RequestInterface $request,
        RuleFactory $catalogRuleFactory
    ) {
        $this->request=$request;
        $this->catalogRuleFactory = $catalogRuleFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rule = $observer->getRule();

        if (!$rule->getData('duplicated')) {
            /* Same As Conditions */
            if ($rule->getData('apply_same_as_condition') == 'true' && $rule->getRule('same_as_conditions')) {
                $catalogRule = $this->catalogRuleFactory->create();
                $catalogRule->loadPost(['conditions' => $rule->getRule('same_as_conditions')]);
                $catalogRule->beforeSave();
                $rule->setData('same_as_conditions_serialized', $catalogRule->getConditionsSerialized());
            } else {
                $rule->setData('same_as_conditions_serialized', null);
            }

            if ($this->request->getParam('category_ids') === null) {
                $rule->setData('category_ids', '');
            }
        }

        if (RelatedTemplate::CUSTOM == $rule->getTemplate() && $rule->getCustomTemplate()) {
            if (strpos($rule->getCustomTemplate(), '.phtml') !== false) {
                $rule->setTemplate($rule->getCustomTemplate());
            } else {
                throw new LocalizedException(__('Invalid template, please use the next format Vendor_Module::myTemplate.phtml'));
            }
        }
    }
}
