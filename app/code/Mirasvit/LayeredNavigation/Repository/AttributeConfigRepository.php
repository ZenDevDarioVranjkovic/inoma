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

namespace Mirasvit\LayeredNavigation\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterfaceFactory;
use Mirasvit\LayeredNavigation\Model\ResourceModel\AttributeConfig\Collection;
use Mirasvit\LayeredNavigation\Model\ResourceModel\AttributeConfig\CollectionFactory;
use Mirasvit\LayeredNavigation\Service\CacheService;

class AttributeConfigRepository
{
    private $factory;

    private $collectionFactory;

    private $entityManager;

    private $cacheService;

    private $attrbuteConfig = [];

    public function __construct(
        AttributeConfigInterfaceFactory $factory,
        CollectionFactory $collectionFactory,
        EntityManager $entityManager,
        CacheService $cacheService
    ) {
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
        $this->entityManager     = $entityManager;
        $this->cacheService      = $cacheService;
    }

    public function create(): AttributeConfigInterface
    {
        return $this->factory->create();
    }

    /**
     * @return Collection|AttributeConfigInterface[]
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }


    public function get(int $id): ?AttributeConfigInterface
    {
        $model = $this->create();

        $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }


    public function getByAttributeCode(string $code, bool $useCache = true): ?AttributeConfigInterface
    {
        if (isset($this->attrbuteConfig[$code])) {
            $attribute = $this->create();
            $attribute->setData($this->attrbuteConfig[$code]);
            
            return $attribute;
        }

        if ($useCache && !is_null($attributeData = $this->cacheService->getCache('getAttributeByCode', $code))) {
            $this->attrbuteConfig[$code] = $attributeData;
            $attribute = $this->create();
            $attribute->setData($attributeData);

            return $attribute;
        }

        /** @var AttributeConfigInterface $model */
        $model = $this->getCollection()
            ->addFieldToFilter(AttributeConfigInterface::ATTRIBUTE_CODE, $code)
            ->getFirstItem();
        
        $this->cacheService->setCache('getAttributeByCode', $code, [$model->getData()]);
        $this->attrbuteConfig[$code] = $model->getData();
        return $model->getId() ? $model : null;
    }

    public function getByAttributeId(int $id): ?AttributeConfigInterface
    {
        /** @var AttributeConfigInterface $model */
        $model = $this->getCollection()
            ->addFieldToFilter(AttributeConfigInterface::ATTRIBUTE_ID, $id)
            ->getFirstItem();

        return $model->getId() ? $model : null;
    }

    public function save(AttributeConfigInterface $model): AttributeConfigInterface
    {
        return $this->entityManager->save($model);
    }

    public function delete(AttributeConfigInterface $model): void
    {
        $this->entityManager->delete($model);
    }
}
