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


namespace Mirasvit\Brand\Ui\BrandPage\Form;


use Magento\Catalog\Model\ResourceModel\Product\Collection;

class ProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\Related\RelatedDataProvider
{
    public function getCollection()
    {
        /** @var Collection $collection */
        $collection = parent::getCollection();

        return $collection->addAttributeToSelect('status');
    }
}