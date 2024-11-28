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

namespace Mirasvit\LayeredNavigation\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Mirasvit\Core\Service\SerializeService;

class HorizontalBarConfigProvider
{
    const STATE_BLOCK_NAME            = 'catalog.navigation.state';
    const STATE_SEARCH_BLOCK_NAME     = 'catalogsearch.navigation.state';
    const STATE_HORIZONTAL_BLOCK_NAME = 'm.catalog.navigation.horizontal.state';

    const POSITION_SIDEBAR    = 'sidebar';
    const POSITION_HORIZONTAL = 'horizontal';
    const POSITION_BOTH       = 'both';

    private $scopeConfig;

    private $filterNavPositions = [];

    private $hasSidebarFilters = true;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getFilterPosition(string $attributeCode): string
    {
        $filters = $this->getFilters();

        foreach ([$attributeCode, '*'] as $filter) {
            if (isset($filters[$filter])) {
                return $filters[$filter];
            }
        }

        return self::POSITION_SIDEBAR;
    }

    public function getHideHorizontalFiltersValue(): int
    {
        return (int)$this->scopeConfig->getValue(
            'mst_nav/horizontal_bar/horizontal_filters_hide',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getFilters(): array
    {
        $filters = $this->scopeConfig->getValue(
            'mst_nav/horizontal_bar/filters',
            ScopeInterface::SCOPE_STORE
        );

        $filters = SerializeService::decode($filters);

        if (!$filters) {
            return [];
        }

        $result = [];

        foreach ($filters as $item) {
            $result[$item['attribute_code']] = $item['position'];
        }

        return $result;
    }

    public function setFilterNavPosition(string $filter, string $position): void
    {
        $this->filterNavPositions[$filter] = $position;
    }

    public function getFilterNavPositions(): array
    {
        return $this->filterNavPositions;
    }

    public function getHasSidebarFilters(): bool
    {
        return $this->hasSidebarFilters;
    }

    public function setHasSidebarFilters(bool $value): void
    {
        $this->hasSidebarFilters = $value;
    }
}
