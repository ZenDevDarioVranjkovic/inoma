<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Controller\Adminhtml\Group;

use Ubertheme\UbMegaMenu\Model\Group;

class MassEnable extends MassAction
{
    /**
     * @var string success message
     */
    protected $successMessage = 'A total of %1 record(s) have been enabled';

    /**
     * @var string error message
     */
    protected $errorMessage = 'An error occurred while enabling record(s).';

    /**
     * @var bool
     */
    protected $isActive = true;

    /**
     * @param Group $group
     * @return $this|mixed
     * @throws \Exception
     */
    protected function runAction(Group $group)
    {
        $group->setIsActive($this->isActive);
        $group->save();

        return $this;
    }
}
