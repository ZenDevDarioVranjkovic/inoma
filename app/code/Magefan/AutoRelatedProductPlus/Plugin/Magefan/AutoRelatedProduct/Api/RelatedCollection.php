<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Plugin\Magefan\AutoRelatedProduct\Api;

use Magefan\AutoRelatedProduct\Model\ResourceModel\Rule\Collection;
use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class RelatedCollection
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var TimezoneInterface
     */
    protected $date;

    /**
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession,
        TimezoneInterface $date
    ) {
        $this->customerSession = $customerSession;
        $this->date = $date;
    }
    /**
     * @param Collection $subject
     * @param $result
     * @return mixed
     */
    public function afterAddActiveFilter(Collection $subject, $result)
    {
        $date = $this->date->date()->format('Y-m-d H:i:s');

        return  $result->addGroupFilter($this->customerSession->getCustomerGroupId())
            ->addFieldToFilter(
                'start_date',
                [
                    ['lteq' => $date],
                    ['null' => true]
                ]
            )
            ->addFieldToFilter(
                'finish_date',
                [
                    ['gteq' => $date],
                    ['null' => true]
                ]
            );
    }
}
