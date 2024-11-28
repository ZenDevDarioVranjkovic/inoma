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

namespace Mirasvit\Brand\Model\Brand\PostData;


use Mirasvit\Brand\Api\Data\BrandPageStoreInterface;
use Mirasvit\Brand\Api\Data\PostData\ProcessorInterface;
use Mirasvit\Brand\Repository\BrandRepository;

class StoresProcessor implements ProcessorInterface
{
    private $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function preparePostData(array $data): array
    {
        if (isset($data['use_config']['stores']) && $data['use_config']['stores'] == 'true') {
            $data['stores'][0] = [
                BrandPageStoreInterface::STORE_ID    => 0,
                BrandPageStoreInterface::BRAND_TITLE => $this->generateBrandTitle((int)$data['attribute_option_id'])
            ];

            $data['store_ids'] = [0];
        } elseif (isset($data[BrandPageStoreInterface::STORE_ID])) {
            foreach ($data[BrandPageStoreInterface::STORE_ID] as $storeId) {
                $data['stores'][$storeId] = [
                    BrandPageStoreInterface::STORE_ID => $storeId
                ];
            }

            asort($data[BrandPageStoreInterface::STORE_ID]);
            $data['store_ids'] = $data[BrandPageStoreInterface::STORE_ID];
        }

        if (isset($data['content'])) {
            // to ensure that label always has default title
            if (
                !isset($data['content'][0])
                || !isset($data['content'][0][BrandPageStoreInterface::BRAND_TITLE])
                || !trim($data['content'][0][BrandPageStoreInterface::BRAND_TITLE])
            ) {
                $data['content'][0][BrandPageStoreInterface::BRAND_TITLE] = $this->generateBrandTitle((int)$data['attribute_option_id']);
                $data['content'][0][BrandPageStoreInterface::STORE_ID]    = 0;
            }

            foreach ($data['content'] as $storeId => $contentData) {
                if (
                    !$contentData[BrandPageStoreInterface::BRAND_TITLE]
                    && !$contentData[BrandPageStoreInterface::BRAND_DESCRIPTION]
                    && !$contentData[BrandPageStoreInterface::BRAND_SHORT_DESCRIPTION]
                    && isset($contentData[BrandPageStoreInterface::BRAND_DISPLAY_MODE])
                    && !$contentData[BrandPageStoreInterface::BRAND_DISPLAY_MODE]
                    && !$contentData[BrandPageStoreInterface::BRAND_CMS_BLOCK]
                    && !isset($data['stores'][$storeId])
                ) {
                    continue;
                }

                if ($storeId != 0 && !in_array(0, $data['store_ids']) && !in_array($storeId, $data['store_ids'])) {
                    continue;
                }

                $data['stores'][$storeId] = [
                    BrandPageStoreInterface::STORE_ID                => $storeId,
                    BrandPageStoreInterface::BRAND_TITLE             => $contentData[BrandPageStoreInterface::BRAND_TITLE] ?? null,
                    BrandPageStoreInterface::BRAND_DESCRIPTION       => $contentData[BrandPageStoreInterface::BRAND_DESCRIPTION] ?? null,
                    BrandPageStoreInterface::BRAND_SHORT_DESCRIPTION => $contentData[BrandPageStoreInterface::BRAND_SHORT_DESCRIPTION] ?? null,
                    BrandPageStoreInterface::BRAND_DISPLAY_MODE      => $contentData[BrandPageStoreInterface::BRAND_DISPLAY_MODE] ?? null,
                    BrandPageStoreInterface::BRAND_CMS_BLOCK         => $contentData[BrandPageStoreInterface::BRAND_CMS_BLOCK] ?? null,
                ];
            }
        }
    
        if (isset($data['meta_data'])) {
            foreach ($data['meta_data'] as $storeId => $contentData) {
                if (
                    !$contentData[BrandPageStoreInterface::BRAND_META_TITLE]
                    && !$contentData[BrandPageStoreInterface::BRAND_META_KEYWORDS]
                    && !$contentData[BrandPageStoreInterface::BRAND_META_DESCRIPTION]
                    && !$contentData[BrandPageStoreInterface::BRAND_SEO_DESCRIPTION]
                    && !$contentData[BrandPageStoreInterface::BRAND_SEO_POSITION]
                    && !$contentData[BrandPageStoreInterface::BRAND_CANONICAL_URL]
                    && !$contentData[BrandPageStoreInterface::BRAND_ROBOTS]
                    && !isset($data['stores'][$storeId])
                ) {
                    continue;
                }

                if ($storeId != 0 && !in_array(0, $data['store_ids']) && !in_array($storeId, $data['store_ids'])) {
                    continue;
                }

                $storeMetaData = [
                    BrandPageStoreInterface::STORE_ID               => $storeId,
                    BrandPageStoreInterface::BRAND_META_TITLE       => $contentData[BrandPageStoreInterface::BRAND_META_TITLE] ?? null,
                    BrandPageStoreInterface::BRAND_META_KEYWORDS    => $contentData[BrandPageStoreInterface::BRAND_META_KEYWORDS] ?? null,
                    BrandPageStoreInterface::BRAND_META_DESCRIPTION => $contentData[BrandPageStoreInterface::BRAND_META_DESCRIPTION] ?? null,
                    BrandPageStoreInterface::BRAND_SEO_DESCRIPTION  => $contentData[BrandPageStoreInterface::BRAND_SEO_DESCRIPTION] ?? null,
                    BrandPageStoreInterface::BRAND_SEO_POSITION     => $contentData[BrandPageStoreInterface::BRAND_SEO_POSITION] ?? null,
                    BrandPageStoreInterface::BRAND_CANONICAL_URL    => $contentData[BrandPageStoreInterface::BRAND_CANONICAL_URL] ?? null,
                    BrandPageStoreInterface::BRAND_ROBOTS           => $contentData[BrandPageStoreInterface::BRAND_ROBOTS] ?? null,
                ];

                $data['stores'][$storeId] = !isset($data['stores'][$storeId]) ? $storeMetaData
                    : array_merge($data['stores'][$storeId], $storeMetaData);
            }
        }

        $data['store_ids'] = implode(',', $data['store_ids']);

        ksort($data['stores']);

        return $data;
    }

    private function generateBrandTitle(int $optionId): string
    {
        $brand = $this->brandRepository->get($optionId);

        return $brand ? $brand->getLabel() : '';
    }
}
