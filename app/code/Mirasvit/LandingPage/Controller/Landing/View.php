<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-landing-page
 * @version   1.0.13
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LandingPage\Controller\Landing;

use Exception;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Response\HttpInterface;
use Mirasvit\LandingPage\Model\Page as LandingPage;
use Mirasvit\LandingPage\Repository\PageRepository;

class View implements HttpGetActionInterface
{

    private $storeManager;

    private $categoryRepository;

    private $catalogSession;

    private $pageRepository;

    private $queryFactory;

    private $registry;

    private $context;

    public function __construct(
        PageRepository              $pageRepository,
        StoreManagerInterface       $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        Session                     $catalogSession,
        QueryFactory                $queryFactory,
        Registry                    $registry,
        Context                     $context
    ) {
        $this->queryFactory       = $queryFactory;
        $this->pageRepository     = $pageRepository;
        $this->storeManager       = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->catalogSession     = $catalogSession;
        $this->registry           = $registry;
        $this->context            = $context;
    }

    /**
     * @return Page | HttpInterface
     */

    public function execute()
    {

        $landingId = $this->context->getRequest()->getParam('landing');

        /** @var Page $page */
        $page = $this->context->getResultFactory()
            ->create(ResultFactory::TYPE_PAGE);

        $landing = $this->pageRepository->get($landingId);

        if ($landing->getSearchTerm()) {
            $page->addHandle('catalogsearch_result_index');
            $page->getConfig()->addBodyClass('catalogsearch-result-index');
        } else {
            $this->initCategory();
            $page->addHandle('catalog_category_view');
        }

        $page->initLayout();
        $page->addHandle('landing_landing_view');
        $page->addPageLayoutHandles(['type' => 'layered'], null, false);
        $this->setMetaData($page, $landing);
        $this->setBreadCrumbs($page, $landing);

        return $page;
    }

    public function initCategory(): ?CategoryInterface
    {
        $categoryId = (int)$this->storeManager->getStore()->getRootCategoryId();

        if (!$categoryId) {
            return null;
        }

        try {
            $category = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
            $category->setData('is_anchor', 1);
        } catch (Exception $e) {
            return null;
        }

        $this->catalogSession->setLastVisitedCategoryId($category->getId());

        try {
            $this->context->getEventManager()->dispatch(
                'catalog_controller_category_init_after',
                ['category' => $category]
            );

            $this->registry->register('current_category', $category);
        } catch (Exception $e) {
            $this->context->getMessageManager()->addExceptionMessage($e);

            return null;
        }

        return $category;
    }

    public function setMetaData(Page $page, LandingPage $landing)
    {
        if ($landing->getLayoutUpdate()) {
            $page->addUpdate($landing->getLayoutUpdate());
        }

        $pageMainTitle = $page->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($landing->getPageTitle() ? : $landing->getName());
        }

        $page->getConfig()->setMetaTitle($landing->getMetaTitle() ? : 'Landing');
        $page->getConfig()->setRobots($landing->getMetaTags());
        $page->getConfig()->getTitle()->set($landing->getMetaTitle() ? :  $landing->getName());
        $page->getConfig()->setDescription($landing->getMetaDescription() ?? 'description');
    }

    public function setBreadCrumbs(Page $page, LandingPage $landing)
    {
        $breadcrumbs = $page->getLayout()->getBlock('breadcrumbs');

        if ($breadcrumbs) {
            $breadcrumbs->clearCrumbs();
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link'  => $this->storeManager->getStore()->getBaseUrl(),
                ]
            )->addCrumb(
                'landing',
                ['label' => $landing->getPageTitle() ? :  $landing->getName()]
            );
        }
    }
}
