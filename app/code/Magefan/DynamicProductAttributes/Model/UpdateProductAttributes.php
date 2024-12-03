<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\DynamicProductAttributes\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Class UpdateProductAttributes
 */
class UpdateProductAttributes extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var StockRegistryInterface
     */
    protected $registryInterface;

    /**
     * @var Customer
     */
    protected $customerResource;

    /**
     * @var AttributeFactory
     */
    protected $eavAttributeFactory;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $entityIdColumns;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var array
     */
    private $allStoreIds;

    /**
     * @var Config
     */
    private $config;

    /**
     * UpdateProductAttributes constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ProductRepository $productRepository
     * @param StockRegistryInterface $registryInterface
     * @param Customer $customerResource
     * @param AttributeFactory $eavAttributeFactory
     * @param DateTime $date
     * @param Configurable $configurable
     * @param Config $config
     * @param AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param StoreRepositoryInterface|null $storeRepository
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        ProductRepository $productRepository,
        StockRegistryInterface $registryInterface,
        Customer $customerResource,
        AttributeFactory $eavAttributeFactory,
        DateTime $date,
        Configurable $configurable,
        Config $config,
        AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        StoreRepositoryInterface $storeRepository = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->productRepository = $productRepository;
        $this->registryInterface = $registryInterface;
        $this->customerResource = $customerResource;
        $this->eavAttributeFactory = $eavAttributeFactory;
        $this->configurable = $configurable;
        $this->date = $date;
        $this->config = $config;
        $this->storeRepository = $storeRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Store\Api\StoreRepositoryInterface::class);
    }

    /**
     * @param null $productId
     * @throws \Zend_Db_Statement_Exception
     */
    public function update($productId = null): void
    {

        $productId = (int)$productId;
        $this->updateRatingAttribute($productId);
        $this->updateQty($productId);
        $this->updateIsOnSale($productId); //run is on sale after qty update
        $this->updateIsNew($productId);
        $this->updateBestSellerRating($productId);
    }

    /**
     * @param int $productId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateQty(int $productId): void
    {
        $attr = $this->getAttribute('mfdc_stock_qty');

        $productEntityTable = $this->getTable('catalog_product_entity');
        $attrEntityTable = $this->getTable('catalog_product_entity_' . $attr->getData('backend_type'));
        $productSuperLinkTable = $this->getTable('catalog_product_super_link');

        $entityIdColumn = $this->getEntityIdColumn($attrEntityTable);

        $useMSI = $this->config->getConfig('mfdynamicproductattributes/general/msi');

        if ($this->getConnection()->isTableExists('inventory_source_item') && $useMSI) {
            $allWebsiteIds = [];
            foreach ($this->storeRepository->getList() as $store) {
                $allWebsiteIds[$store->getWebsite()->getCode()] = (int)$store->getWebsiteId();
            }

            $stockTable = $this->getTable('inventory_source_item');

            foreach ($allWebsiteIds as $webSiteCode => $websiteId) {
                $selectSource = $this->getConnection()->select()
                    ->from(['l' => $this->getTable('inventory_source_stock_link')], 'source_code')
                    ->joinLeft(
                        ['c'=> $this->getTable('inventory_stock_sales_channel')],
                        'l.stock_id = c.stock_id',
                        []
                    )
                    ->where('c.type = ?', 'website');

                if ($websiteId) {
                    /* If not admin website */
                    $selectSource->where('c.code = ?', $webSiteCode);
                }

                $allWebSiteStockSourceCodes = $this->getConnection()->fetchCol($selectSource);

                if (!count($allWebSiteStockSourceCodes)) {
                    continue;
                }

                $allWebsiteStoreIds = [];
                foreach ($this->storeRepository->getList() as $store) {
                    if ($store->getWebsiteId() == $websiteId) {
                        $allWebsiteStoreIds[] = $store->getId();
                    }
                }

                foreach ($allWebsiteStoreIds as $storeId) {
                    $qtyCase = '(
                               CASE
                                WHEN (pet.type_id = "configurable" OR pet.type_id = "grouped")
                                THEN (SELECT SUM(quantity) FROM ' . $stockTable . ' tmpst
                                    LEFT JOIN ' . $productEntityTable . ' tmppet
                                    ON tmpst.sku = tmppet.sku
                                    WHERE tmppet.entity_id IN (
                                        SELECT product_id FROM ' . $productSuperLinkTable . ' WHERE `parent_id` = pet.' . $entityIdColumn . '
                                    ) AND source_code IN ("' . implode('","', $allWebSiteStockSourceCodes) . '")
                                )
                                ELSE (SELECT SUM(quantity) FROM ' . $stockTable . ' WHERE sku = pet.sku
                                    AND source_code IN ("' . implode('","', $allWebSiteStockSourceCodes) . '")
                                )
                               end
                            )';
                    $selectSql = 'SELECT
                            null as value_id, ' .
                        $attr->getId() . ' as attribute_id,
                            ' . $storeId . ' as store_id,
                            pet.' . $entityIdColumn . ', ' .
                        $qtyCase . ' as value
                        FROM ' .
                        $stockTable . ' st
                        LEFT JOIN ' .
                        $productEntityTable . ' pet ON st.sku = pet.sku
                        WHERE pet.' . $entityIdColumn . ' IS NOT NULL
                            AND st.source_code IN ("' . implode('","', $allWebSiteStockSourceCodes) . '")';

                    if ($productId) {
                        $selectSql .= ' AND pet.entity_id = ' . $productId;
                    }

                    $this->createTmpTableWithClearedAttributeValues($attr, $storeId);
                    $insertSql = 'INSERT INTO ' . $attrEntityTable . '_mf_tmp (value_id, attribute_id, store_id,' . $entityIdColumn . ', value ) ' . $selectSql . ' ' .
                        'ON DUPLICATE KEY UPDATE value = ' . $qtyCase;
                    $this->getConnection()->query($insertSql);
                    $this->moveDataFromTmpToPermanentTable($attrEntityTable, $storeId);
                }
            }
        } else {
            $stockTable = $this->getTable('cataloginventory_stock_item');

            $qtyCase = '(
                       CASE
                        WHEN (pet.type_id = "configurable" OR pet.type_id = "grouped")
                        THEN (SELECT sum(qty) FROM ' . $stockTable . '
                            WHERE `product_id` IN (SELECT product_id FROM ' . $productSuperLinkTable . ' WHERE `parent_id` = pet.' . $entityIdColumn . '
                            )
                        )
                        ELSE st.qty
                       end
                    )';

            $selectSql = 'SELECT
                    null as value_id, ' .
                $attr->getId() . ' as attribute_id,
                    0 as store_id,
                    pet.' . $entityIdColumn . ', ' .
                $qtyCase . ' as value
                FROM ' .
                $stockTable . ' st
                LEFT JOIN ' .
                $productEntityTable . ' pet ON st.product_id = pet.entity_id
                WHERE pet.' . $entityIdColumn . ' IS NOT NULL
                ';
            //stock_status = 1'; - do not add stock status otherwise will not update outofstock products
            if ($productId) {
                $selectSql .= ' AND pet.entity_id = ' . $productId;
            }

            $this->createTmpTableWithClearedAttributeValues($attr);
            $insertSql = 'INSERT INTO ' . $attrEntityTable . '_mf_tmp (value_id, attribute_id, store_id,' . $entityIdColumn . ', value ) ' . $selectSql . ' ' .
                'ON DUPLICATE KEY UPDATE value = ' . $qtyCase;
            $this->getConnection()->query($insertSql);
            $this->moveDataFromTmpToPermanentTable($attrEntityTable);
        }
    }

    /**
     * @param int $productId
     * @throws \Zend_Db_Statement_Exception
     */
    public function updateIsOnSale(int $productId): void
    {
        $toDate = $this->getAttribute('special_to_date');
        $fromDate = $this->getAttribute('special_from_date');
        $onSale = $this->getAttribute('mfdc_is_on_sale');

        if ($toDate->getId() && $fromDate->getId() && $onSale->getId()) {
            $specialPrice = $this->getAttribute('special_price');
            $dateTimeTable = $this->getTable('catalog_product_entity_datetime');
            $decimalTable = $this->getTable('catalog_product_entity_decimal');
            $intTable = $this->getTable('catalog_product_entity_int');
            $productEntityTable = $this->getTable('catalog_product_entity');

            $entityIdColumn = $this->getEntityIdColumn($decimalTable);

            $this->populateAttributeData($onSale);

            $date = $this->date->gmtDate("Y-m-d 00:00:00");

            foreach ($this->getStoreIdsForAttribute($onSale) as $storeId) {
                $allConditionsSql =
                    'SELECT
                        null as value_id, ' .
                    $onSale->getId() . ' as attribute_id,
                        ' . $storeId . ' as store_id,
                        pet.entity_id as id,
                        pet.' . $entityIdColumn . ',
                        1 as value
                    FROM ' .
                    $this->getTable('catalog_product_entity') . ' pet ' .
                    ' LEFT JOIN ' .
                    $decimalTable . ' pv ON pet.' . $entityIdColumn . ' = pv.' . $entityIdColumn . '
                    AND
                        pv.attribute_id = ' . $specialPrice->getId() . ' AND pv.store_id = ' . $storeId .
                    ' LEFT JOIN ' .
                    $dateTimeTable . ' fv ON pet.' . $entityIdColumn . ' = fv.' . $entityIdColumn . '
                    AND
                        fv.attribute_id = ' . $fromDate->getId() . ' AND fv.store_id = ' . $storeId .
                    ' LEFT JOIN ' .
                    $dateTimeTable . ' tv ON pet.' . $entityIdColumn . ' = tv.' . $entityIdColumn . '
                    AND
                        tv.attribute_id = ' . $toDate->getId() . ' AND tv.store_id = ' . $storeId;

                if (0 != $storeId) {
                    $allConditionsSql .=
                        ' LEFT JOIN ' .
                        $decimalTable . ' pvo ON pet.' . $entityIdColumn . ' = pvo.' . $entityIdColumn . '
                        AND
                        pvo.attribute_id = ' . $specialPrice->getId() . ' AND pvo.store_id = 0' .
                        ' LEFT JOIN ' .
                        $dateTimeTable . ' fvo ON pet.' . $entityIdColumn . ' = fvo.' . $entityIdColumn . '
                        AND
                        fvo.attribute_id = ' . $fromDate->getId() . ' AND fvo.store_id = 0' .
                        ' LEFT JOIN ' .
                        $dateTimeTable . ' tvo ON pet.' . $entityIdColumn . ' = tvo.' . $entityIdColumn . '
                        AND
                        tvo.attribute_id = ' . $toDate->getId() . ' AND tvo.store_id = 0';
                }

                $allConditionsSql .= ($storeId != 0) ?
                    ' WHERE
                        (pvo.value > 0 OR pv.value > 0)
                        AND CASE WHEN (fv.value_id IS NOT NULL)
                            THEN
                                (fv.value IS NULL OR fv.value <= "' . $date . '")
                            ELSE
                                (fvo.value_id IS NOT NULL AND (fvo.value IS NULL OR fvo.value <= "' . $date . '")) OR fvo.value_id IS NULL
                            END
                        AND CASE WHEN (tv.value_id IS NOT NULL)
                            THEN
                                (tv.value IS NULL OR tv.value >= "' . $date . '")
                            ELSE
                                (tvo.value_id IS NOT NULL AND (tvo.value IS NULL OR tvo.value >= "' . $date . '")) OR tvo.value_id IS NULL
                            END;
                           ' :
                    ' WHERE
                        pv.value > 0
                          AND (
                              (fv.value_id IS NOT NULL AND (fv.value IS NULL OR fv.value <= "' . $date . '"))
                              OR fv.value_id IS NULL
                          )
                          AND (
                              (tv.value_id IS NOT NULL AND (tv.value IS NULL OR tv.value >= "' . $date . '"))
                              OR tv.value_id IS NULL
                          ) ';

                if ($productId) {
                    $allConditionsSql .= ' AND pet.entity_id = ' . $productId;
                }

                $items = $this->getConnection()->query($allConditionsSql)->fetchAll();

                $onSaleProductIds = [0];
                if ($items) {
                    $productIds = [];
                    foreach ($items as $item) {
                        $productIds[] = $item['id'];
                    }


                    $select = $this->getConnection()->select()
                        ->from(['main_table' => $this->getTable('catalog_product_relation')])
                        ->where('main_table.child_id IN (' . $this->getEnbledInStockProductSelectSql($productIds, $storeId) . ')')
                        ->joinLeft(
                            ['ee' => $productEntityTable],
                            'main_table.parent_id = ee.' . $entityIdColumn,
                            []
                        )->where('ee.entity_id IS NOT NULL');


                    $parents = [];
                    foreach ($this->getConnection()->fetchAll($select) as $related) {
                        if (!isset($parents[$related['child_id']])) {
                            $parents[$related['child_id']] = [];
                        }
                        $parents[$related['child_id']][] = $related['parent_id'];
                    }

                    foreach ($items as $item) {
                        $this->insertEntities($intTable, $item);
                        $onSaleProductIds[] = (int)$item['id'];

                        if (isset($parents[$item['id']])) {
                            foreach ($parents[$item['id']] as $parentId) {
                                $item[$entityIdColumn] = $parentId;
                                $this->insertEntities($intTable, $item);
                                $onSaleProductIds[] = (int)$parentId;
                            }
                        }
                    }
                }

                if ('entity_id' != $entityIdColumn) {
                    $select = 'SELECT ' . $entityIdColumn . ' FROM ' . $productEntityTable . '
                        WHERE entity_id IN (' . implode(',', $onSaleProductIds) . ')';

                    $onSaleProductIds = [0];
                    foreach ($this->getConnection()->fetchAll($select) as $item) {
                        $onSaleProductIds[] = $item[$entityIdColumn];
                    }
                }

                $this->getConnection()->update(
                    $intTable,
                    ['value' => 0],
                    $d = [
                        'store_id = ?' => (int)$storeId,
                        'attribute_id = ?' => (int)$onSale->getId(),
                        'value = ?' => 1,
                        $entityIdColumn . ' NOT IN (?)' => $onSaleProductIds
                    ]
                );
            }
        }
    }

    private function getEnbledInStockProductSelectSql(array $productIds, int $storeId): string
    {
        $intTable = $this->getTable('catalog_product_entity_int');
        $entityIdColumn = $this->getConnection()->tableColumnExists($intTable, 'row_id') ? 'row_id' : 'entity_id';

        $productEntityTable = $this->getTable('catalog_product_entity');

        $stockAttr = $this->getAttribute('mfdc_stock_qty');
        $statusAttr = $this->getAttribute('status');

        $sql = 'SELECT
            `e`.entity_id
            FROM
            `' . $productEntityTable . '` AS `e`
            INNER JOIN `' . $intTable . '` AS `at_mfdc_stock_qty_default` ON (
            `at_mfdc_stock_qty_default`.`' . $entityIdColumn . '` = `e`.`' . $entityIdColumn . '`
            )
            AND (
            `at_mfdc_stock_qty_default`.`attribute_id` = "' . (int)$stockAttr->getId() . '"
            )
            AND (
            `at_mfdc_stock_qty_default`.`store_id` = 0
            )';

        if ($storeId) {
            $sql .= '
                LEFT JOIN `' . $intTable . '` AS `at_mfdc_stock_qty` ON (
                `at_mfdc_stock_qty`.`' . $entityIdColumn . '` = `e`.`' . $entityIdColumn . '`
                )
                AND (
                `at_mfdc_stock_qty`.`attribute_id` = "' . (int)$stockAttr->getId() . '"
                )
                AND (
                `at_mfdc_stock_qty`.`store_id` = "' . $storeId . '"
                )';
        }

        $sql .= '
              INNER JOIN `' . $intTable . '` AS `at_status_default` ON (
                `at_status_default`.`' . $entityIdColumn . '` = `e`.`' . $entityIdColumn . '`
              )
              AND (
                `at_status_default`.`attribute_id` = "' . (int)$statusAttr->getId() . '"
              )
              AND (`at_status_default`.`store_id` = 0)';

        if ($storeId) {
            $sql .= '
                LEFT JOIN `' . $intTable . '` AS `at_status` ON (
                `at_status`.`' . $entityIdColumn . '` = `e`.`' . $entityIdColumn .  '`
                )
                AND (
                `at_status`.`attribute_id` = "' . (int)$statusAttr->getId() . '"
                )
                AND (
                `at_status`.`store_id` = "' . $storeId . '"
                )';
        }

        $productIdConditions = [];
        foreach ($productIds as $id) {
            $productIdConditions[] = '(`e`.`' . $entityIdColumn . '` = ' . (int)$id . ')';
        }

        $sql .= '
            WHERE
              (
                ' . implode(' OR ', $productIdConditions) . '
              )';

        if ($storeId) {
            $sql .= '
              AND (
                IF(
                  at_mfdc_stock_qty.value_id > 0, at_mfdc_stock_qty.value,
                  at_mfdc_stock_qty_default.value
                ) > 0
              )
              AND (
                IF(
                  at_status.value_id > 0, at_status.value,
                  at_status_default.value
                ) = 1
              )
            ';
        } else {
            $sql .= '
                  AND (at_mfdc_stock_qty_default.value > 0)
                AND (at_status_default.value = 1)
            ';
        }

        return $sql;
    }

    /**
     * @param int $productId
     */
    public function updateRatingAttribute(int $productId): void
    {
        $map = [
            /* review_entity_summary.field => product_attr_code */
            'reviews_count' => 'mfdc_reviews_count',
            'rating_summary' => 'mfdc_reviews_score',
        ];

        foreach ($map as $field => $attrCode) {
            $attr = $this->getAttribute($attrCode);

            if (!$attr->getId()) {
                continue;
            }

            $productEntityTable = $this->getTable('catalog_product_entity');
            $attrEntityTable = $this->getTable('catalog_product_entity_' . $attr->getData('backend_type'));
            $reviewTable = $this->getTable('review_entity_summary');

            if ($field == 'rating_summary') {
                $fieldTerm = '';
                $smallest = 100;
                $smallestId = null;

                $values = [1, 2, 3, 4, 5];
                foreach ($values as $value) {
                    $range = [];
                    switch ($value) {
                        case 1:
                            $range[] = 0;
                            $range[] = 20;
                            break;
                        case 2:
                            $range[] = 21;
                            $range[] = 40;
                            break;
                        case 3:
                            $range[] = 41;
                            $range[] = 60;
                            break;
                        case 4:
                            $range[] = 61;
                            $range[] = 80;
                            break;
                        case 5:
                            $range[] = 81;
                            $range[] = 100;
                            break;
                    }
                    $fieldTerm .= ' WHEN ( ' . $field . ' >= ' . ((int)$range[0]) . ' AND  ' . $field . ' <= ' . ((int)$range[1]) . ' ) THEN ' . $value . ' ';

                    if ($smallest > (int)$range[0]) {
                        $smallest = (int)$range[0];
                        $smallestId = $value;
                    }
                }

                if (!$smallestId) {
                    $smallestId = 0;
                }

                if ($fieldTerm) {
                    $fieldTerm = 'CASE ' . $fieldTerm . ' ELSE ' . $smallestId . ' END ';
                }

                $field = '(' . $fieldTerm . ')';
            } else {
                $field = 't2.' . $field;
            }

            $entityIdColumn = $this->getEntityIdColumn($attrEntityTable);

            $selectSql = 'SELECT
                    null  as value_id,
                    ' . $attr->getId() . ' as attribute_id,
                    t2.store_id,
                    ' . ((('row_id' == $entityIdColumn) ? 'pet.' . $entityIdColumn : 'entity_pk_value') . ' as ' . $entityIdColumn) . ',
                    ' . $field . ' as value
                FROM ' . $reviewTable . ' t2
                    LEFT JOIN ' . $productEntityTable . ' as pet ON t2.entity_pk_value = pet.entity_id AND t2.entity_type = 1
                    LEFT JOIN ' . $this->getTable('store') . ' as s ON t2.store_id = s.store_id
                    WHERE pet.entity_id IS NOT NULL and s.store_id IS NOT NULL
                ';

            if ($productId) {
                $selectSql .= 'AND t2.entity_pk_value = ' . $productId;
            }

            $this->createTmpTableWithClearedAttributeValues($attr);
            $insertSql = 'INSERT INTO ' . $attrEntityTable . '_mf_tmp (value_id, attribute_id, store_id, ' . $entityIdColumn . ', value) ' . $selectSql . '
                ON DUPLICATE KEY UPDATE value = ' . $field;
            $this->getConnection()->query($insertSql);
            $this->moveDataFromTmpToPermanentTable($attrEntityTable);
        }
    }

    /**
     * @param int $productId
     */
    public function updateIsNew(int $productId): void
    {
        $toDate = $this->getAttribute('news_to_date');
        $fromDate = $this->getAttribute('news_from_date');
        $isNew = $this->getAttribute('mfdc_is_new');

        if ($toDate->getId() && $fromDate->getId() && $isNew->getId()) {
            $date = $this->date->gmtDate("Y-m-d 00:00:00");
            $dateTimeTable = $this->getTable('catalog_product_entity_datetime');
            $intTable = $this->getTable('catalog_product_entity_int');
            $entityIdColumn = $this->getEntityIdColumn($intTable);

            foreach ($this->getStoreIdsForAttribute($isNew) as $storeId) {
                $selectSql =
                    'SELECT
                        null as value_id, ' .
                    $isNew->getId() . ' as attribute_id,
                        ' . $storeId . ' as store_id,
                        pet.' . $entityIdColumn . ',
                        1 as value
                    FROM ' .
                    $this->getTable('catalog_product_entity') . ' pet
                    LEFT JOIN ' .
                    $dateTimeTable . ' fv ON pet.' . $entityIdColumn . ' = fv.' . $entityIdColumn . '
                    AND
                        fv.attribute_id = ' . $fromDate->getId() . ' AND fv.store_id = ' . $storeId .
                    ' LEFT JOIN ' .
                    $dateTimeTable . ' tv ON pet.' . $entityIdColumn . ' = tv.' . $entityIdColumn . '
                    AND
                        tv.attribute_id = ' . $toDate->getId() . ' AND tv.store_id = ' . $storeId;

                if (0 != $storeId) {
                    $selectSql .=
                        ' LEFT JOIN ' .
                        $dateTimeTable . ' fvo ON pet.' . $entityIdColumn . ' = fvo.' . $entityIdColumn . '
                        AND
                            fvo.attribute_id = ' . $fromDate->getId() . ' AND fvo.store_id = 0' .
                        ' LEFT JOIN ' .
                        $dateTimeTable . ' tvo ON pet.' . $entityIdColumn . ' = tvo.' . $entityIdColumn . '
                        AND
                            tvo.attribute_id = ' . $toDate->getId() . ' AND tvo.store_id = 0';
                }

                $fv = (0 != $storeId)
                    ?  ' CASE
                            WHEN fv.value_id IS NOT NULL THEN fv.value ELSE fvo.value
                         END '
                    :  ' fv.value ';

                $tv = (0 != $storeId)
                    ?  ' CASE
                            WHEN tv.value_id IS NOT NULL THEN tv.value ELSE tvo.value
                         END '
                    :  ' tv.value ';

                $selectSql .=
                    ' WHERE
                        CASE
                            WHEN '.$fv.' IS NOT NULL OR '.$tv.' IS NOT NULL
                            THEN
                                ('.$fv.' <= "'.$date.'" OR '.$fv.' IS NULL)
                                AND
                                ('.$tv.' >= "'.$date.'" OR '.$tv.' IS NULL)
                        END
                    ';

                if ($productId) {
                    $selectSql .= ' AND pet.entity_id = ' . $productId;
                }

                $this->createTmpTableWithClearedAttributeValues($isNew, $storeId);
                $insertSql = 'INSERT INTO ' . $intTable . '_mf_tmp (value_id, attribute_id, store_id,' . $entityIdColumn . ', value ) ' . $selectSql . ' ' .
                    'ON DUPLICATE KEY UPDATE value = 1';

                $this->getConnection()->query($insertSql);
                $this->moveDataFromTmpToPermanentTable($intTable, $storeId);
                /* No need to update this for configurable, grouped, bundle products, they have own attributes */
            }
        }
    }

    /**
     * @param int $productId
     */
    public function updateBestSellerRating(int $productId): void
    {
        $data = [
            'mfdc_best_sellers_per_week' => 7,
            'mfdc_best_sellers_per_month' => 30,
            'mfdc_best_sellers_per_quarter' => 91,
            'mfdc_best_sellers_per_year' => 365
        ];

        $productEntityTable = $this->getTable('catalog_product_entity');
        $intTable = $this->getTable('catalog_product_entity_int');
        $attrEntityTable = $intTable;

        $salesOrderItemTable = $this->getTable('sales_order_item');
        $salesOrderTable = $this->getTable('sales_order');

        $entityIdColumn = $this->getEntityIdColumn($attrEntityTable);

        foreach ($data as $attrCode => $days) {
            $attr = $this->getAttribute($attrCode);
            $attrId = $attr->getId();

            if (!$attrId) {
                continue;
            }

            foreach ($this->getStoreIdsForAttribute($attr) as $storeId) {

                $selectSql = '
                SELECT  null as value_id, ' . $attrId . ' as attribute_id, '. $storeId .' as store_id, pet.' . $entityIdColumn . ', COUNT(product_id) as new_value
                FROM ' . $salesOrderItemTable . '
                LEFT JOIN ' . $salesOrderTable . '
                ON ' . $salesOrderTable . '.entity_id = ' . $salesOrderItemTable . '.order_id

                LEFT JOIN ' . $productEntityTable . ' pet
                ON pet.entity_id = ' . $salesOrderItemTable . '.product_id

                WHERE ' . $salesOrderItemTable . '.created_at > DATE_SUB(CURDATE(), INTERVAL ' . $days . ' DAY)
                AND ' . $salesOrderTable . '.status IN ("complete", "processing", "pending")'
                    . ((!$attr->isScopeStore())  ? ' ' : ' AND ' . $salesOrderItemTable . '.store_id = ' . $storeId ) .
                    ' AND ' . $salesOrderItemTable . '.qty_refunded < 1
                AND pet.' . $entityIdColumn . ' IS NOT NULL';
                if ($productId) {
                    $selectSql .= ' AND pet.entity_id = ' . $productId;
                }

                $selectSql .= ' GROUP BY product_id';

                $this->createTmpTableWithClearedAttributeValues($attr, !$attr->isScopeStore() ? null : $storeId);
                $insertSql = 'INSERT INTO ' . $intTable . '_mf_tmp (value_id, attribute_id, store_id,' . $entityIdColumn . ', value )
                SELECT * FROM (' . $selectSql . ') as slt ' .
                    'ON DUPLICATE KEY UPDATE value = new_value';
                $this->getConnection()->query($insertSql);
                $this->moveDataFromTmpToPermanentTable($intTable);

                /* No need to add support of configurable products as they also included in sales_items table */
            }
        }
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->customerResource->getConnection();
        }
        return $this->connection;
    }

    /**
     * @param string $tableName
     * @return string
     */
    private function getTable(string $tableName):string
    {
        return $this->customerResource->getTable($tableName);
    }

    /**
     * @param string $table
     * @param $entity
     */
    private function insertEntities(string $table, $entity): void
    {
        $entityIdColumn = $this->getEntityIdColumn($table);

        $insertSql = 'INSERT INTO ' . $table .
            ' (value_id, attribute_id, store_id, ' . $entityIdColumn . ', value)
            VALUES (
                null, ' .
            $entity['attribute_id'] . ', ' .
            $entity['store_id'] . ', ' .
            $entity[$entityIdColumn] . ', ' .
            $entity['value'] .
            ') ON DUPLICATE KEY UPDATE value =  ' . $entity['value'];
        $this->getConnection()->query($insertSql);
    }

    /**
     * @param string $attrCode
     * @return mixed
     */
    private function getAttribute(string $attrCode)
    {
        if (!isset($this->attributes[$attrCode])) {
            $this->attributes[$attrCode] = $this->eavAttributeFactory->create()->load($attrCode, 'attribute_code');
        }

        return $this->attributes[$attrCode];
    }

    /**
     * @param string $table
     * @return string
     */
    private function getEntityIdColumn(string $table): string
    {
        if (!isset($this->entityIdColumns[$table])) {
            $this->entityIdColumns[$table] = $this->getConnection()->tableColumnExists($table, 'row_id') ? 'row_id' : 'entity_id';
        }

        return $this->entityIdColumns[$table];
    }

    /**
     * Retrieve all store IDs
     * @return array
     */
    private function getAllStoreIds(): array
    {
        if (null === $this->allStoreIds) {
            $storeIds = [];
            foreach ($this->storeRepository->getList() as $store) {
                $storeIds[] = (int)$store->getId();
            }
            if (!in_array(0, $storeIds)) {
                array_unshift($storeIds, 0);
            }

            $this->allStoreIds = $storeIds;
        }

        return $this->allStoreIds;
    }

    /**
     * @param $attribute
     * @return array|int[]
     */
    private function getStoreIdsForAttribute($attribute): array
    {
        return $attribute->isScopeStore() ? $this->getAllStoreIds() : [0];
    }

    /**
     * @param $attribute
     * @param null $storeId
     */
    private function createTmpTableWithClearedAttributeValues($attribute, $storeId = null): void
    {
        $productEntityTable = $this->getTable('catalog_product_entity');
        $attrEntityTable = $this->getTable('catalog_product_entity_' . $attribute->getData('backend_type'));
        $attrEntityTableTmp = $this->getTable($attrEntityTable . '_mf_tmp');
        $entityIdColumn = $this->getEntityIdColumn($attrEntityTable);

        $this->getConnection()->dropTable($attrEntityTableTmp);
        $this->getConnection()->query(
            'CREATE TEMPORARY TABLE ' . $attrEntityTableTmp . ' AS SELECT * FROM ' . $attrEntityTable . ' WHERE
            attribute_id = ' . (int)$attribute->getId() .
            ((null !== $storeId) ? (' AND store_id = ' . (int)$storeId) : '')
        );
        $this->getConnection()->query(
            'ALTER TABLE ' . $attrEntityTableTmp . ' ADD UNIQUE( `attribute_id`, `store_id`, `' . $entityIdColumn . '`)'
        );

        $selectSql = 'SELECT
            null AS value_id, ' .
            $attribute->getId() . ' AS attribute_id,
            ' . ((null !== $storeId) ? (int)$storeId : '0') . ' AS store_id,
            ' . $entityIdColumn . ',
            0 AS value
            FROM ' . $productEntityTable;

        /*
        if ($productId) {
            $selectSql .= ' WHERE entity_id = ' . $productId;
        }
        */

        $insertSql = 'INSERT INTO ' . $attrEntityTableTmp . ' (value_id, attribute_id, store_id, ' . $entityIdColumn . ', value) ' . $selectSql . '
            ON DUPLICATE KEY UPDATE value = 0';
        $this->getConnection()->query($insertSql);

        /*
        $this->getConnection()->delete(
            $attrEntityTableTmp,
            [
                'store_id <> ?' => 0,
                'attribute_id = ?' => $attribute->getId()
            ]
        );
        */
    }

    /**
     * @param string $attrEntityTable
     * @param null $storeId
     */
    private function moveDataFromTmpToPermanentTable(string $attrEntityTable, $storeId = null): void
    {
        $entityIdColumn = $this->getEntityIdColumn($attrEntityTable);

        $insertSql = 'INSERT INTO ' . $attrEntityTable . '
            SELECT
                null as value_id,
                attribute_id,
                store_id,'
                . ($entityIdColumn == 'row_id' ? 'value, ' . $entityIdColumn : $entityIdColumn . ', value')
            . ' FROM ' . $attrEntityTable . '_mf_tmp' .
            ((null !== $storeId) 
                ? (' WHERE store_id = ' . (int)$storeId)
                : '') .
            ' ON DUPLICATE KEY UPDATE value = ' . $attrEntityTable . '_mf_tmp.value';

        $this->getConnection()->query($insertSql);

        $this->getConnection()->dropTable($attrEntityTable . '_mf_tmp');
    }

    /**
     * @param $attribute
     */
    private function populateAttributeData($attribute): void
    {
        $productEntityTable = $this->getTable('catalog_product_entity');
        $attrEntityTable = $this->getTable('catalog_product_entity_' . $attribute->getData('backend_type'));
        $entityIdColumn = $this->getEntityIdColumn($attrEntityTable);

        $selectSql = 'SELECT
            null AS value_id, ' .
            $attribute->getId() . ' AS attribute_id,
            0 AS store_id,
            ' . $entityIdColumn . ',
            0 AS value
            FROM ' . $productEntityTable;

        $insertSql = 'INSERT INTO ' . $attrEntityTable . ' (value_id, attribute_id, store_id, ' . $entityIdColumn . ', value) ' . $selectSql . '
            ON DUPLICATE KEY UPDATE value = value';
        $this->getConnection()->query($insertSql);
    }
}
