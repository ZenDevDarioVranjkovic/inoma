<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\DynamicProductAttributes\Model;

use Magefan\DynamicProductAttributes\Api\AddCustomValidationFiltersInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magefan\Community\Api\GetParentProductIdsInterface;
use Magefan\Community\Api\GetWebsitesMapInterface;

class AddCustomValidationFilters implements AddCustomValidationFiltersInterface
{
    /**
     * @var DateTime|mixed
     */
    protected $dateTime;
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var GetParentProductIdsInterface
     */
    protected $getParentProductIds;

    /**
     * @var GetWebsitesMapInterface
     */
    protected $getWebsitesMap;

    /**
     * @var array
     */
    protected $customValidators = [];

    /**
     * AddCustomValidationFilters constructor.
     * @param DateTime $dateTime
     * @param Config $config
     * @param GetParentProductIdsInterface $getParentProductIds
     * @param GetWebsitesMapInterface $getWebsitesMap
     */
    public function __construct(
        DateTime $dateTime,
        Config $config,
        GetParentProductIdsInterface $getParentProductIds,
        GetWebsitesMapInterface $getWebsitesMap
    ) {
        $this->dateTime = $dateTime;
        $this->config = $config;
        $this->getWebsitesMap = $getWebsitesMap;
        $this->getParentProductIds = $getParentProductIds;

    }

    /**
     * @param $conditions
     * @return mixed
     */
    public function processCustomValidator($conditions)
    {
        $this->customValidators = [];

        foreach ($conditions['conditions'] as $key => $condition) {
            if ($condition['attribute'] == 'mfdc_created_in_days') {
                $date = date('Y-m-d 00:00:00', strtotime($this->dateTime->gmtDate('Y/m/d')) - (int)$condition['value'] * 86400);

                $this->customValidators[$condition['attribute']] = $this->buildCustomCondition($condition['operator'], $date);

                unset($conditions['conditions'][$key]);
                break;
            }

            if ($condition['attribute'] == 'quantity_and_stock_status') {
                // in stock - 1 || out of stock - 0
                $stockFilterValue = (int)$condition['value'];

                $this->customValidators[$condition['attribute']] = $stockFilterValue;

                unset($conditions['conditions'][$key]);
                break;
            }
        }

        return $conditions;
    }

    /**
     * @param $collection
     * @return void
     */
    public function addCustomValidationFilters($collection)
    {
        if (isset($this->customValidators['mfdc_created_in_days'])) {
            $collection->getSelect()->where('created_at ' . $this->customValidators['mfdc_created_in_days']);
        }

        if (isset($this->customValidators['quantity_and_stock_status'])) {
            $this->addStockFilter($collection, $this->customValidators['quantity_and_stock_status']);
        }
    }

    /**
     * @param $collection
     * @param int $is_salable
     * @return mixed
     */
    private function addStockFilter($collection, int $is_salable)
    {
        $stockFlag = 'has_stock_status_filter';

        if ($collection->hasFlag($stockFlag)) {
            return $collection;
        }

        $cond = [
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock='. $is_salable,
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=0'
        ];

        if ($this->config->isManageStock()) {
            $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.is_in_stock=' . $is_salable;
        } else {
            $cond[] = '{{table}}.use_config_manage_stock = ' . $is_salable;
        }

        $collection->joinField(
            'inventory_in_stock',
            'cataloginventory_stock_item',
            'is_in_stock',
            'product_id=entity_id',
            '(' . join(') OR (', $cond) . ')'
        );

        $collection->setFlag($stockFlag, true);

        return $collection;
    }

    /**
     * @param string $operator
     * @param string $value
     * @return string
     */
    private function buildCustomCondition(string $operator, string $value): string
    {
        switch ($operator) {
            case '==':
                $value = str_replace(' 00:00:00', '', $value);
                $valueCondition = 'like "%' . $value . '%"';
                break;
            case '!=':
                $value = str_replace(' 00:00:00', '', $value);
                $valueCondition = 'not like "%' . $value . '%"';
                break;
            case '>=':
                $valueCondition = '<= "' . $value . '"';
                break;
            case '<=':
                $valueCondition = '>= "' . $value . '"';
                break;
            case '>':
                $valueCondition = '< "' . $value . '"';
                break;
            case '<':
                $valueCondition = '> "' . $value . '"';
                break;
            case '()':
                $valueCondition = 'in ("' . str_replace(',', '","', $value) . '")';
                break;
            case '!()':
                $valueCondition = 'not in ("' . str_replace(',', '","', $value) . '")';
                break;
            default:
                $value = str_replace(' 00:00:00', '', $value);
                $valueCondition = 'like "%' . $value . '%"';
                break;
        }

        return $valueCondition;
    }

    /**
     * @return array
     */
    public function _getWebsitesMap()
    {
        return $this->getWebsitesMap->execute();
    }

    /**
     * @param array $productIds
     * @return array
     */
    public function getParentProductIds(array $productIds): array
    {
        return $this->getParentProductIds->execute($productIds);
    }
}
