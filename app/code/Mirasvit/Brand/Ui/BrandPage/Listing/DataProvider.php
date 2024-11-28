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
 * @package   mirasvit/module-navigation
 * @version   2.7.34
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Ui\BrandPage\Listing;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as UiDataProvider;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Api\Data\BrandPageStoreInterface;
use Mirasvit\Brand\Service\ImageUrlService;

class DataProvider extends UiDataProvider
{
    private $imageUrlService;

    private $filterGroupBuilder;

    public function __construct(
        ImageUrlService $imageUrlService,
        FilterGroupBuilder $filterGroupBuilder,
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->imageUrlService = $imageUrlService;
        $this->filterGroupBuilder = $filterGroupBuilder;

        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];

        $arrItems['items'] = [];
        /** @var BrandPageInterface|\Magento\Framework\DataObject $item */
        foreach ($searchResult->getItems() as $item) {
            $itemData = $item->getData();

            if ($item->getData(BrandPageInterface::LOGO)) {
                $itemData[BrandPageInterface::LOGO . '_src'] = $this->imageUrlService->getImageUrl($item->getLogo());
            }

            $itemData[BrandPageInterface::BRAND_TITLE] = $item->getDataFromGroupedField(BrandPageStoreInterface::BRAND_TITLE, 'content', 0);

            if (isset($itemData['store_ids'])) {
                $itemData['store_id'] = $itemData['store_ids'];
            } else {
                $itemData['store_id'] = '0';
            }

            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == BrandPageInterface::ID) {
            $filter->setField('main_table.' . BrandPageInterface::ID);
        }

        if ($filter->getField() == BrandPageInterface::BRAND_TITLE) {
            $filter->setField('brand_store_data.' . BrandPageInterface::BRAND_TITLE);
        }

        if ($filter->getField() == BrandPageStoreInterface::STORE_ID) {
            $filter->setField('main_table.store_ids');
            $filter->setConditionType('finset');

            // if a particular store selected in filter
            // we display brands configured for all stores and that particular store
            if ($filter->getValue() != 0) {
                $storeIds = array_unique(array_merge([0], explode(',', $filter->getValue())));

                foreach ($storeIds as $storeId) {
                    $f = $this->filterBuilder->setField($filter->getField())
                        ->setValue($storeId)
                        ->setConditionType($filter->getConditionType())
                        ->create();

                    $this->filterGroupBuilder->addFilter($f);
                }

                $filterGroup = $this->filterGroupBuilder->create();

                $this->getSearchCriteria()->setFilterGroups(
                    array_merge($this->getSearchCriteria()->getFilterGroups(), [$filterGroup])
                );

                return;
            }
        }

        parent::addFilter($filter);
    }
}
