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


namespace Mirasvit\LayeredNavigation\Plugin\Frontend\Elasticsearch\SearchAdapter\Mapper;


use Magento\Elasticsearch7\SearchAdapter\Mapper;
use Mirasvit\LayeredNavigation\Api\Data\AttributeConfigInterface;
use Mirasvit\LayeredNavigation\Repository\AttributeConfigRepository;


class MultiselectAndLogicPlugin
{
    private $attributeConfigRepository;

    public function __construct(AttributeConfigRepository $attributeConfigRepository)
    {
        $this->attributeConfigRepository = $attributeConfigRepository;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param mixed $subject
     * @param array $result
     *
     * @return array
     *
     */
    public function afterBuildQuery($subject, array $result): array
    {
        $multiselectAndFilters = [];

        if (!isset($result['body']['query']['bool']) || !isset($result['body']['query']['bool']['must'])) {
            return $result;
        }

        $filterQuery = $result['body']['query']['bool']['must'];

        foreach ($filterQuery as $idx => $filterItem) {
            if (!isset($filterItem['terms'])) {
                continue;
            }

            $attributeCode   = array_keys($filterItem['terms'])[0];
            $attributeConfig = $this->attributeConfigRepository->getByAttributeCode($attributeCode);

            if (!$attributeConfig) {
                continue;
            }

            if (
                $attributeConfig->getMultiselectLogic() === AttributeConfigInterface::MULTISELECT_LOGIC_AND
                && count($filterItem['terms'][$attributeCode]) > 1
            ) {
                $multiselectAndFilters[$attributeCode] = $filterItem['terms'][$attributeCode];

                unset($filterQuery[$idx]);
            }
        }

        foreach ($multiselectAndFilters as $attrCode => $values) {
            foreach ($values as $value) {
                $filterQuery[] = ['term' => [$attrCode => $value]];
            }
        }

        $result['body']['query']['bool']['must'] = array_values($filterQuery);

        return $result;
    }
}
