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
 * @package   mirasvit/module-seo-filter
 * @version   1.3.28
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Service;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\CacheInterface;

class CacheService
{
    private $serializer;

    private $cache;

    public function __construct(
        Json $serializer,
        CacheInterface $cache
    ) {
        $this->serializer   = $serializer;
        $this->cache        = $cache;
    }
    
    private function getCacheKey(string $instance, array $dataKey)
    {
        $key = mb_strtoupper('mst_'. $instance .'_'. implode('_', $dataKey));

        // Cache instance doesn't distinguish between '-' and '_' symbols in identifier of load($identifier) method
        return str_replace('-', '--', $key);
    }

    public function getCache(string $instance, array $dataKey): ?array
    {
        $cachedData = $this->cache->load($this->getCacheKey($instance, $dataKey));
        if (empty($cachedData)) {
            return null;
        } else {
            $cachedData = $this->serializer->unserialize($cachedData);
            $cachedData = array_values($cachedData)[0];
        }

        return is_array($cachedData)? $cachedData : [$cachedData];
    }

    public function setCache(string $instance, array $dataKey, array $dataValue): void
    {
        $this->cache->save($this->serializer->serialize($dataValue), $this->getCacheKey($instance, $dataKey));
    }
}
