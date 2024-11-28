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

namespace Mirasvit\Brand\Repository;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Api\Data\BrandPageInterfaceFactory;
use Mirasvit\Brand\Model\Config\GeneralConfig;
use Mirasvit\Brand\Model\ResourceModel\BrandPage\Collection;
use Mirasvit\Brand\Model\ResourceModel\BrandPage\CollectionFactory;
use Magento\Framework\Filter\FilterManager;

class BrandPageRepository
{
    private $factory;

    private $collectionFactory;

    private $entityManager;

    private $productAction;

    private $config;

    private $productCollectionFactory;

    private $filter;

    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ProductAction $productAction,
        GeneralConfig $config,
        BrandPageInterfaceFactory $factory,
        CollectionFactory $collectionFactory,
        EntityManager $entityManager,
        FilterManager $filter
    ) {
        $this->productAction            = $productAction;
        $this->config                   = $config;
        $this->factory                  = $factory;
        $this->collectionFactory        = $collectionFactory;
        $this->entityManager            = $entityManager;
        $this->filter                   = $filter;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function create(): BrandPageInterface
    {
        return $this->factory->create();
    }

    /** @return Collection|BrandPageInterface[] */
    public function getCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    public function get(int $id): ?BrandPageInterface
    {
        $model = $this->create();

        $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }

    public function save(BrandPageInterface $brandPage): BrandPageInterface
    {
        if ($brandPage->getData('products')) {
            $this->updateProductsBrand($brandPage);
        }

        // additional check for lowercase brand urlkey
        if ($brandUrlKey = $brandPage->getData(BrandPageInterface::URL_KEY)) {
            $brandPage->setData(BrandPageInterface::URL_KEY, $this->filter->translitUrl($brandUrlKey));
        }

        return $this->entityManager->save($brandPage);
    }

    public function delete(BrandPageInterface $brandPage): void
    {
        $this->entityManager->delete($brandPage);
    }

    private function updateProductsBrand(BrandPageInterface $brandPage): void
    {
        $attributeCode = $this->config->getBrandAttribute();
        $ids           = $brandPage->getData('products');
        $brandId       = $brandPage->getAttributeOptionId();

        // Set brand
        $this->productAction->updateAttributes(
            $ids,
            [$attributeCode => $brandId],
            0
        );

        // Unset brand
        $collection = $this->productCollectionFactory->create()
            ->addAttributeToFilter($attributeCode, ['eq' => $brandId])
            ->addFieldToFilter('entity_id', ['nin' => $ids]);

        $idsToUnset = [];

        foreach ($collection as $item) {
            $idsToUnset[] = $item->getId();
        }

        if (count($idsToUnset)) {
            $this->productAction->updateAttributes(
                $idsToUnset,
                [$attributeCode => ''],
                0
            );
        }
    }
}
