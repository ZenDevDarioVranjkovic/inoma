<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Helper;

use Magento\Framework\App\Helper;
use Magento\Framework\App\Cache as AppCache;
use Magento\Framework\App\Cache\State;
use Magento\Store\Model\StoreManagerInterface;

class Cache extends Helper\AbstractHelper
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'UBMEGAMENU';

    /**
     * Cache ID
     */
    const CACHE_ID = 'ubmegamenu';

    /**
     * Default cache lifetime
     */
    const CACHE_LIFETIME = 86400;

    protected $cache;
    protected $cacheState;
    protected $storeManager;
    private $storeId;

    /**
     * Cache constructor.
     * @param Helper\Context $context
     * @param AppCache $cache
     * @param State $cacheState
     * @param StoreManagerInterface $storeManager
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        Helper\Context $context,
        AppCache $cache,
        State $cacheState,
        StoreManagerInterface $storeManager
    ) {
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        $this->storeManager = $storeManager;
        $this->storeId = $storeManager->getStore()->getId();

        parent::__construct($context);
    }

    /**
     * @param $method
     * @param array $vars
     * @return string
     */
    public function getId($method, $vars = array())
    {
        return base64_encode($this->storeId . self::CACHE_ID . $method . implode('', $vars));
    }

    /**
     * @param $cacheId
     * @return bool|string
     */
    public function load($cacheId)
    {
        if ($this->cacheState->isEnabled(self::CACHE_ID)) {
            return $this->cache->load($cacheId);
        }

        return false;
    }

    /**
     * @param $data
     * @param $cacheId
     * @param int $cacheLifetime
     * @return bool
     */
    public function save($data, $cacheId, $cacheLifetime = self::CACHE_LIFETIME)
    {
        if ($this->cacheState->isEnabled(self::CACHE_ID)) {
            $this->cache->save($data, $cacheId, array(self::CACHE_TAG), $cacheLifetime);
            return true;
        }

        return false;
    }

}
