<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Controller\Adminhtml\Group;

use Ubertheme\UbMegaMenu\Model\Group;

class MassDelete extends MassAction
{
    /**
     * @var string success message
     */
    protected $successMessage = 'A total of %1 record(s) have been deleted';

    /**
     * @var string error message
     */
    protected $errorMessage = 'An error occurred while deleting record(s).';

    /**
     * @param Group $group
     * @return $this|mixed
     * @throws \Exception
     */
    protected function runAction(Group $group)
    {
        $group->delete();
        return $this;
    }
}
