<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Model\ResourceModel;

use Magefan\AutoRelatedProductPlus\Model\ResourceModel\UpdateRelated\Viewed;
use Magefan\AutoRelatedProductPlus\Model\ResourceModel\UpdateRelated\Bought;

/**
 * Class RelatedUpdater
 * @package Magefan\AutoRelatedProductPlus\Model
 */
class UpdateRelated
{
    /**
     * @var Viewed
     */
    private $viewed;

    /**
     * @var Bought
     */
    private $bought;

    /**
     * @param Viewed $viewed
     * @param Bought $bought
     */
    public function __construct(
        Viewed $viewed,
        Bought $bought
    ) {
        $this->viewed = $viewed;
        $this->bought = $bought;
    }

    /**
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    public function update()
    {
        $this->viewed->execute();
        $this->bought->execute();
    }
}
