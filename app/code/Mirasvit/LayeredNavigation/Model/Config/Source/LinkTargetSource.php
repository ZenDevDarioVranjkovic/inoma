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


namespace Mirasvit\LayeredNavigation\Model\Config\Source;


use Magento\Framework\Data\OptionSourceInterface;

class LinkTargetSource implements OptionSourceInterface
{
    const TARGET_SELF  = '_self';
    const TARGET_BLANK = '_blank';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::TARGET_SELF,
                'label' => self::TARGET_SELF,
            ],
            [
                'value' => self::TARGET_BLANK,
                'label' => self::TARGET_BLANK,
            ],
        ];
    }
}