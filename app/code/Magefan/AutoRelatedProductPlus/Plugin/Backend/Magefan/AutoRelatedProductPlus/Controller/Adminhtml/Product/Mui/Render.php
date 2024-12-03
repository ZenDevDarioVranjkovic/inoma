<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Plugin\Backend\Magefan\AutoRelatedProductPlus\Controller\Adminhtml\Product\Mui;

use Magefan\AutoRelatedProductPlus\Controller\Adminhtml\Product\Mui\Render as RenderController;
use Magento\Store\Model\StoreManagerInterface;
/**
 * Class Render
 * @package Magefan\AutoRelatedProductPlus\Plugin\Mui
 */
class Render
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magefan\AutoRelatedProduct\Model\AutoRelatedProductPlusAction
     */
    protected $autoRelatedProductAction;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $catalogRuleFactory;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magefan\AutoRelatedProduct\Model\AutoRelatedProductAction $autoRelatedProductAction
     * @param \Magento\CatalogRule\Model\RuleFactory $catalogRuleFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\CatalogRule\Model\RuleFactory  $catalogRuleFactory,
        \Magefan\AutoRelatedProduct\Model\AutoRelatedProductAction $autoRelatedProductAction,
        StoreManagerInterface $storeManager
    ) {

        $this->autoRelatedProductAction = $autoRelatedProductAction;
        $this->registry = $registry;
        $this->catalogRuleFactory = $catalogRuleFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param RenderController $renderController
     */
    public function beforeExecute(RenderController $renderController)
    {
        $request = $renderController->getRequest();

        if ($conditions = $request->getParam('rule', null)) {
            $catalogRule = $this->catalogRuleFactory->create();
            $catalogRule->loadPost($conditions);

            $ids = $request->getParam('website_ids', null);
            $websiteIds = [];

            if ($ids) {
                if (($key = array_search(0, $ids)) !== false) {
                    unset($ids[$key]);
                }

                foreach ($ids as $id) {
                    $websiteIds[] = $this->storeManager->getStore($id)->getWebsiteId();
                }
            }

            $catalogRule->setWebsiteIds($websiteIds);

            $this->registry->register(
                \Magefan\AutoRelatedProductPlus\Ui\Component\DataProvider\Product\ProductDataProvider::PRODUCTS_KEY,
                $this->autoRelatedProductAction->getListProductIds($catalogRule)
            );
        }
    }
}
