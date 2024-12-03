<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Cron;

use Magefan\AutoRelatedProduct\Api\ConfigInterface as Config;
use Magefan\AutoRelatedProductPlus\Model\ResourceModel\UpdateRelated;

class UpdateRelatedIds
{
    /**
     * @var UpdateRelated
     */
    protected $relatedUpdater;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param UpdateRelated $relatedUpdater
     * @param Config $config
     */
    public function __construct(
        UpdateRelated $relatedUpdater,
        Config        $config
    ) {
        $this->relatedUpdater = $relatedUpdater;
        $this->config = $config;
    }

    public function execute()
    {
        if ($this->config->isEnabled()) {
            $this->relatedUpdater->update();
        }
    }
}
