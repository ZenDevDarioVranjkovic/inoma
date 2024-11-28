<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\Base\Plugin\Magento\Framework\App\Http;

use Magento\Framework\App\Http\Context as HttpContext;

class Context
{
    public function beforeGetVaryString(HttpContext $subject)
    {
//        $detect = new \Mobile_Detect();
        $detect = new \Detection\MobileDetect;
        $device = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'desktop');

        //Detect special conditions devices
        $iPhone = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
        $iPad = (stripos($_SERVER['HTTP_USER_AGENT'], "iPad")
            || stripos($_SERVER['HTTP_USER_AGENT'], "Macintosh"));
        if ($iPhone) {
            $device = 'mobile';
        } else if ($iPad) {
            $device = 'tablet';
        }

        $subject->setValue('user_device', $device, 'default');
    }
}
