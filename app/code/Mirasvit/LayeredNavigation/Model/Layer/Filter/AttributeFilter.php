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

namespace Mirasvit\LayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Framework\App\RequestInterface;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Helper\ArrayHelper;
use Mirasvit\LayeredNavigation\Model\Config\SizeLimiterConfigProvider;
use Mirasvit\LayeredNavigation\Repository\AttributeConfigRepository;
use Mirasvit\LayeredNavigation\Repository\GroupRepository;

class AttributeFilter extends AbstractFilter
{
    private $attributeConfigRepository;

    private $groupRepository;

    private $sizeLimiterConfigProvider;

    public function __construct(
        AttributeConfigRepository $attributeConfigRepository,
        GroupRepository $groupRepository,
        SizeLimiterConfigProvider $sizeLimiterConfigProvider,
        Layer $layer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($layer, $context, $data);

        $this->attributeConfigRepository = $attributeConfigRepository;
        $this->groupRepository           = $groupRepository;
        $this->sizeLimiterConfigProvider = $sizeLimiterConfigProvider;
    }

    public function apply(RequestInterface $request): self
    {
        $attributeValue = (string)$request->getParam($this->_requestVar);
        if (empty($attributeValue) && $attributeValue !== '0') {
            return $this;
        }

        $attributeValue = explode(',', (string)$attributeValue);

        // resolve grouped options
        $resolvedValue = $attributeValue;

        foreach ($resolvedValue as $value) {
            if ($group = $this->groupRepository->getByCode($value)) {
                $key = array_search($value, $resolvedValue);
                unset($resolvedValue[$key]);

                $resolvedValue = array_merge($resolvedValue, $group->getAttributeValueIds());
            }
        }

        $resolvedValue = array_values(array_unique($resolvedValue));

        $attribute = $this->getAttributeModel();

        // apply
        $this->getLayer()->getProductCollection()
            ->addFieldToFilter($attribute->getAttributeCode(), $resolvedValue);

        // add state
        if ($this->stateBarConfigProvider->isFilterClearBlockInOneRow()) {
            $labels = array_map(function ($value) {
                return $this->getOptionText($value);
            }, $attributeValue);

            $optionText = implode(', ', $labels);
            $this->addStateItem(
                $this->_createItem($optionText, $attributeValue)
            );
        } else {
            foreach ($attributeValue as $value) {
                $this->addStateItem(
                    $this->_createItem($this->getOptionText($value), $value)
                );
            }
        }

        $this->_items = null;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getItemsData(): array
    {
        $attribute = $this->getAttributeModel();

        /** @var \Mirasvit\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $collection */
        $collection = $this->getLayer()->getProductCollection();

        $optionsFacetedData = $collection->getExtendedFacetedData(
            $attribute->getAttributeCode(),
            $this->configProvider->isMultiselectEnabled($this->_requestVar)
        );

        $isAttributeFilterable = $this->getAttributeIsFilterable($attribute) === static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS;

        if (count($optionsFacetedData) === 0 && !$isAttributeFilterable) {
            return $this->itemDataBuilder->build();
        }

        $stateFilters = $this->getLayer()->getState()->getFilters();

        $stateAttributeCodes = [];

        foreach ($stateFilters as $filter) {
            if ($filter->getFilter()->getData('attribute_model')) {
                $stateAttributeCodes[] = $filter->getFilter()->getAttributeModel()->getAttributeCode();
            }
        }

        if (
            !$this->configProvider->isMultiselectEnabled($attribute->getAttributeCode())
            && in_array($attribute->getAttributeCode(), $stateAttributeCodes)
        ) {
            return $this->itemDataBuilder->build();
        }

        $productSize = $collection->getSize();
        $options     = $attribute->getFrontend()->getSelectOptions();

        if ($this->isSortByLabel()) {
            usort($options, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
        }

        if ($this->isSortByCounts()) {
            $options = $this->sortByCounts($options, $optionsFacetedData);
        }

        $groupAtrributesList = $this->groupRepository->getAttributesList();
        $groups = [];

        if (in_array($attribute->getAttributeCode(), $groupAtrributesList)) {
            $groups = $this->groupRepository->getGroupsListByAttributeCode($attribute->getAttributeCode());
        }
        if (count($groups)) {
            $presentGroups = [];
            // remove options without results
            foreach ($options as $key => $option) {
                $value = (string)$this->getOptionValue($option);

                if (empty($value) && $value !== '0') {
                    unset($options[$key]);
                    continue;
                }

                if ($isAttributeFilterable && !$this->getOptionCount($value, $optionsFacetedData)) {
                    unset($options[$key]);
                    continue;
                }
            }

            // remove options that match groups and remember groups
            foreach ($groups as $group) {
                foreach ($options as $key => $option) {
                    if (in_array((int)$option['value'], $group->getAttributeValueIds())) {
                        unset($options[$key]);
                        $presentGroups[$group->getCode()] = $group;
                    }
                }
            }

            // insert groups according their positions
            foreach ($presentGroups as $group) {
                $groupedOption = [
                    $group->getCode() => [
                        'label' => $group->getLabelByStoreId((int)$this->getStoreId()),
                        'value' => $group->getCode(),
                        'group' => $group->getId()
                    ]
                ];
                $options = ArrayHelper::insertIntoPosition($options, $groupedOption, $group->getPosition());
            }
        }

        foreach ($options as $key => $option) {
            $value = (string)$this->getOptionValue($option);
            if ($this->configProvider->isHideUnusefulOptionEnabled() && $this->getOptionCount($value, $optionsFacetedData) == $productSize) {
                continue;
            }
            $this->buildOptionData($option, $isAttributeFilterable, $optionsFacetedData, $productSize);
        }
        return $this->itemDataBuilder->build();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function buildOptionData(array $option, bool $isAttributeFilterable, array $optionsFacetedData, int $productSize): void
    {
        $value = (string)$this->getOptionValue($option);

        if (empty($value) && $value !== '0') {
            return;
        }

        if (isset($option['group'])) {
            $countMax = 0;
            $count = 0;
            $group = $this->groupRepository->get($option['group']);

            foreach ($group->getAttributeValueIds() as $optionValue) {
                $optionCount = $this->getOptionCount($optionValue, $optionsFacetedData);

                $countMax = $optionCount > $countMax
                    ? $optionCount
                    : $countMax;

                $count += $optionCount;
            }

            $count = $count >= $productSize ? $productSize : $count;
            $count = $count < $countMax ? $countMax : $count;
        } else {
            $count = $this->getOptionCount($value, $optionsFacetedData);
        }

        if ($isAttributeFilterable && $count === 0) {
            return;
        }

        $this->itemDataBuilder->addItemData(
            strip_tags((string)$option['label']),
            $value,
            $count
        );
    }

    private function getOptionValue(array $option): ?string
    {
        if ((empty($option['value']) && $option['value'] !== 0) || (!is_numeric($option['value']) && !isset($option['group']))) {
            return null;
        }

        return (string)$option['value'];
    }

    private function getOptionCount(string $value, array $optionsFacetedData): int
    {
        return isset($optionsFacetedData[$value]['count'])
            ? (int)$optionsFacetedData[$value]['count']
            : 0;
    }

    /**
     * Resolve state labels for grouped options
     *
     * @param int|string $value
     * @return string|bool
     */
    protected function getOptionText($value)
    {
        if ($group = $this->groupRepository->getByCode($value)) {
            return $group->getLabelByStoreId((int)$this->getStoreId());
        }

        return parent::getOptionText($value);
    }

    private function getAttributeConfig(): AttributeConfigInterface
    {
        $attrConfig = $this->attributeConfigRepository->getByAttributeCode(
            $this->getAttributeModel()->getAttributeCode()
        );

        return $attrConfig ? $attrConfig : $this->attributeConfigRepository->create();
    }

    public function isSortByLabel(): bool
    {
        return $this->getAttributeConfig()->getOptionsSortBy() === AttributeConfigInterface::OPTION_SORT_BY_LABEL;
    }

    public function isSortByCounts(): bool
    {
        return $this->getAttributeConfig()->getOptionsSortBy() === AttributeConfigInterface::OPTION_SORT_BY_COUNT;
    }

    public function isUseAlphabeticalIndex(): bool
    {
        return $this->getAttributeConfig()->getUseAlphabeticalIndex();
    }

    public function getAlphabeticalLimit(): int
    {
        return $this->sizeLimiterConfigProvider->getAlphabeticalLimit();
    }

    public function isAlphabeticalIndexAllowedByLimit(): bool
    {
        return $this->getAlphabeticalLimit() <= count($this->getItems());
    }
}
