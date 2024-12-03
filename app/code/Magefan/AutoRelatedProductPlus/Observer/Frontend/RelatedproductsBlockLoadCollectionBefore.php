<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Observer\Frontend;

use Magefan\AutoRelatedProduct\Api\Data\RuleInterface;
use Magefan\AutoRelatedProductPlus\Model\ResourceModel\RelatedProducts;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use Magefan\AutoRelatedProduct\Model\Config\Source\SortBy;

class RelatedproductsBlockLoadCollectionBefore implements ObserverInterface
{
    /**
     * @var RelatedProducts
     */
    protected $relatedProducts;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var Product
     */
    protected $productResource;

    /**
     * @var StoreManagerInterface|mixed
     */
    private $storeManager;

    protected $getCategoryByProduct;

    /**
     * @param RelatedProducts $relatedProducts
     * @param Json $serializer
     * @param Product $productResource
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        RelatedProducts $relatedProducts,
        Json $serializer,
        Product  $productResource,
        StoreManagerInterface $storeManager = null,
        \Magefan\Community\Api\GetCategoryByProductInterface $getCategoryByProduct = null
    ) {
        $this->serializer = $serializer;
        $this->relatedProducts = $relatedProducts;
        $this->productResource = $productResource;
        $this->storeManager = $storeManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);

        $this->getCategoryByProduct = $getCategoryByProduct ?:\Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magefan\Community\Api\GetCategoryByProductInterface::class);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rule = $observer->getRule();
        $collection = $observer->getCollection();

        switch ($rule->getData('sort_by')) {
            case SortBy::NAME:
                $collection->addAttributeToSort('name', 'ASC');
                break;
            case SortBy::NEWEST:
                $collection->addAttributeToSort('created_at', 'DESC');
                break;
            case SortBy::PRICE_DESC:
                $collection->addAttributeToSort('price', 'DESC');
            case SortBy::PRICE_ASC:
                $collection->addAttributeToSort('price', 'ASC');
                break;
            case SortBy::BEST_PR_WEEK:
                $collection->addAttributeToSort('mfdc_best_sellers_per_week','DESC');
                break;
            case SortBy::BEST_PR_MONTH:
                $collection->addAttributeToSort('mfdc_best_sellers_per_month','DESC');
                break;
            case SortBy::BEST_PR_QUARTER:
                $collection->addAttributeToSort('mfdc_best_sellers_per_quarter', 'DESC');
                break;
            case SortBy::BEST_PR_YEAR:
                $collection->addAttributeToSort('mfdc_best_sellers_per_year', 'DESC');
                break;
        }

        if (!($product = $observer->getProduct())) {
            return;
        }
        /* Logic only for product page */
        if ($cond = $rule->getData('same_as_conditions_serialized')) {
            $appliedConditions = 0;
            $uncond = $this->serializer->unserialize($cond);

            if (isset($uncond['conditions'])) {
                $aggr = $uncond['aggregator'] == 'any' ? 1 : 0;
                $equal = (int)$uncond['value'];

                foreach ($uncond['conditions'] as $co) {
                    if ($co["attribute"]) {
                        if ($co["attribute"] == 'category_ids') {
                            $catId = $this->getCurrentCategory($product);

                            if ($catId) {
                                $collection->getSelect()->join(
                                    ['ccproduct' => $this->productResource->getTable('catalog_category_product')],
                                    sprintf(
                                        'e.entity_id = ccproduct.product_id AND ccproduct.category_id %s(%s)',
                                        $equal ? 'IN' : 'NOT IN',
                                        $catId
                                    ),
                                    []
                                );

                                $collection->getSelect()->group('e.entity_id');
                                $appliedConditions++;
                            }
                        } else {
                            $attrValue = $product->getData($co["attribute"]);

                            if ($attrValue) {
                                if ($product->getResource()->getAttribute($co["attribute"])->getFrontendInput() == 'multiselect') {
                                    $filter = [];

                                    foreach (explode(',', $attrValue) as $val) {
                                        $filter[] = [
                                            'attribute' => $co["attribute"],
                                            'finset' => $val
                                        ];
                                    }
                                    if (!empty($filter)) {
                                        $collection->addAttributeToFilter($filter);

                                        if (!$equal) {
                                            $where = $collection->getSelect()->getPart(Select::WHERE);
                                            $fInSetCondition = array_pop($where);
                                            $fInSetCondition = str_replace('FIND_IN_SET', 'NOT FIND_IN_SET', $fInSetCondition);
                                            $fInSetCondition = str_replace(
                                                Select::SQL_OR,
                                                Select::SQL_AND,
                                                $fInSetCondition
                                            );
                                            $where[] = $fInSetCondition;
                                            $collection->setPart(Select::WHERE, $where);
                                        }
                                        $appliedConditions++;
                                    }
                                } else {
                                    $collection->addAttributeToFilter(
                                        $co["attribute"],
                                        [$equal ? 'eq' : 'neq' => $attrValue]
                                    );
                                    $appliedConditions++;
                                }
                            }
                        }
                    } else {
                        $price = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
                        switch ($co["operator"]) {
                            case '==':
                                $type = 'eq';
                                break;
                            case '!=':
                                $type = 'neq';
                                break;
                            case '<':
                                $type = 'lt';
                                break;
                            case '<=':
                                $type = 'lteq';
                                break;
                            case '>':
                                $type = 'gt';
                                break;
                            case '>=':
                                $type = 'gteq';
                                break;
                        }

                        if ($type) {
                            $collection->addFieldToFilter('price', [$type => $price]);
                            $appliedConditions++;
                        }
                    }
                }

                if ($aggr) {
                    $where = $collection->getSelect()->getPart(Select::WHERE);
                    $sameConditions = array_slice($where, -1 * $appliedConditions, null, true);
                    $sameWhere = '';
                    $andRegexp = '@' . Select::SQL_AND . '@';

                    foreach ($sameConditions as $key => $sameCondition) {
                        if (empty($sameWhere)) {
                            if (count($sameConditions) != count($where)) {
                                $sameWhere .=   Select::SQL_AND;
                            }
                            $sameWhere .= '(' .
                                preg_replace($andRegexp, '', $sameCondition, 1);
                        } else {
                            $sameWhere .= ' ' . preg_replace($andRegexp, Select::SQL_OR, $sameCondition, 1);
                        }

                        unset($where[$key]);
                    }

                    if ($sameWhere) {
                        $sameWhere .= ')';
                        $where[] = $sameWhere;
                        $collection->getSelect()->setPart(Select::WHERE, $where);
                    }
                }
            }
        }

        if ($rule->getData('who_bought_this_also_bought')) {
            $alsoBoughtIds = $this->relatedProducts->getRelatedIds((int)$product->getId(), 'magefan_autorp_index_also_bought');
            $alsoBoughtIds = $alsoBoughtIds ? : [-1];
            if ($alsoBoughtIds) {
                $collection->addFieldToFilter('entity_id', ['in' => $alsoBoughtIds]);
            }
        }
        if ($rule->getData('who_viewed_this_also_viewed')) {
            $alsoViewedIds = $this->relatedProducts->getRelatedIds((int)$product->getId(), 'magefan_autorp_index_also_viewed');
            $alsoViewedIds = $alsoViewedIds ? : [-1];
            if ($alsoViewedIds) {
                $collection->addFieldToFilter('entity_id', ['in' => $alsoViewedIds]);
            }
        }

        if ($rule->getData('from_one_category_only')) {
            $this->addProductsFromTheSameCategoryFilter($product, $collection);
        }

        if (($higher = $rule->getData('only_with_higher_price')) || $rule->getData('only_with_lower_price')) {
            $this->addPriceFilter($higher, $product->getFinalPrice(), $collection);
        }
    }

    /**
     * @param $product
     * @return null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCurrentCategory($product)
    {
        $category = $this->getCategoryByProduct->execute($product);
        return $category ? $category->getId() : null;
    }

    /**
     * @param $currentProduct
     * @param $collection
     * @return void
     * @throws NoSuchEntityException
     */
    private function addProductsFromTheSameCategoryFilter($currentProduct, $collection): void
    {
        $productCategoryId = $this->getCurrentCategory($currentProduct);

        if ($productCategoryId) {
            $collection->addCategoriesFilter(['in' => [$productCategoryId]]);
        }
    }

    /**
     * @param $higher
     * @param $price
     * @param $collection
     * @return void
     */
    private function addPriceFilter($higher, $price, $collection): void
    {
        if (is_array($price)) {
            $price = array_shift($price);
        }

        $where = $higher ? "price_index.final_price > ?" : "price_index.final_price < ?";
        $collection->getSelect()->where($where, $price);
    }
}
