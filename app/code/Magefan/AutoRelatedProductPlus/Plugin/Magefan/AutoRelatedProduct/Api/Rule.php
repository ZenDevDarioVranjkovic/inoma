<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Plugin\Magefan\AutoRelatedProduct\Api;

use Magento\Customer\Model\Session;
use Magefan\AutoRelatedProduct\Model\ActionValidator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Rule
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
     * @var ActionValidator
     */
    protected $actionValidator;

    /**
     * @param Session $customerSession
     * @param TimezoneInterface $date
     * @param ActionValidator $actionValidator
     */
    public function __construct(
        Session $customerSession,
        TimezoneInterface $date,
        ActionValidator $actionValidator
    ) {
        $this->customerSession = $customerSession;
        $this->date = $date;
        $this->actionValidator = $actionValidator;
    }

    /**
     * @param \Magefan\AutoRelatedProduct\Model\Rule $subject
     * @param $result
     * @return bool
     */
    public function afterIsActive(\Magefan\AutoRelatedProduct\Model\Rule $subject, $result)
    {
        if ($result) {
            $isCustomerGroupAllowed = in_array(
                $this->customerSession->getCustomerGroupId(),
                explode(',', (string) $subject->getData('customer_group_ids'))
            );

            $isDateValid = $this->actionValidator->isInTimeFrame(
                $this->date->date()->format('Y-m-d H:i:s'),
                $subject->getData('start_date'),
                $subject->getData('finish_date')
            );

            $result = ($isCustomerGroupAllowed && $isDateValid);
        }

        return $result;
    }
}
