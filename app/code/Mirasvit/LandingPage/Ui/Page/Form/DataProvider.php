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

namespace Mirasvit\LandingPage\Ui\Page\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\LandingPage\Api\Data\FilterInterface;
use Mirasvit\LandingPage\Api\Data\PageInterface;
use Mirasvit\LandingPage\Repository\FilterRepository;
use Mirasvit\LandingPage\Repository\PageRepository;

class DataProvider extends AbstractDataProvider
{
    protected $collection;

    private   $pageRepository;

    private   $filterRepository;

    private   $context;

    public function __construct(
        FilterRepository $filterRepository,
        PageRepository   $pageRepository,
        string           $name,
        string           $primaryFieldName,
        string           $requestFieldName,
        ContextInterface $context,
        array            $meta = [],
        array            $data = []
    ) {
        $this->filterRepository = $filterRepository;
        $this->collection       = $pageRepository->getCollection();
        $this->pageRepository   = $pageRepository;
        $this->context          = $context;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        $result     = [];
        $filterData = [];

        if ($model = $this->getModel()) {
            $pageData         = $model->getData();
            $filterCollection = $this->filterRepository->getByPageId((int)$model->getId());

            foreach ($filterCollection->getItems() as $filter) {
                $data              = $filter->getData();
                $data['attribute'] = $data[FilterInterface::ATTRIBUTE_ID];
                $data['options']   = explode(',', $data[FilterInterface::OPTION_IDS]);
                $filterData[]      = $data;
            }

            if (isset($pageData[PageInterface::CATEGORIES])) {
                $pageData[PageInterface::CATEGORIES] = explode(',', $pageData[PageInterface::CATEGORIES]);
            }

            $pageData[PageInterface::STORE_IDS] = explode(',', $pageData[PageInterface::STORE_IDS]);
            $pageData['filters']                = $filterData;

            $result[$model->getId()] = $pageData;
        }

        return $result;
    }

    private function getModel(): ?PageInterface
    {
        $id = $this->context->getRequestParam(PageInterface::PAGE_ID, null);

        return $id ? $this->pageRepository->get((int)$id) : null;
    }

}
