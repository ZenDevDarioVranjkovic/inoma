<?php

/**
 * Copyright © 2017 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\Base\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;

/**
 * Initial OWL2's assets files
 */
class InitOwl2 extends Template
{
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        $pageConfig = $context->getPageConfig();

        $pageConfig->addPageAsset('Ubertheme_Base::css/owl.carousel2/owl.carousel.min.css');
        $pageConfig->addPageAsset('Ubertheme_Base::css/owl.carousel2/owl.theme.default.min.css');
        $pageConfig->addPageAsset('Ubertheme_Base::css/lazy.load/style.css');

        parent::__construct($context, $data);
    }
}
