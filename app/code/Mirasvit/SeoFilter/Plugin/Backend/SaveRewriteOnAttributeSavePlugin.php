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
 * @package   mirasvit/module-seo-filter
 * @version   1.3.28
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Plugin\Backend;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Message\ManagerInterface;
use Mirasvit\SeoFilter\Api\Data\RewriteInterface;
use Mirasvit\SeoFilter\Repository\RewriteRepository;
use Mirasvit\SeoFilter\Service\LabelService;
use Mirasvit\SeoFilter\Service\RewriteService;
use Mirasvit\SeoFilter\Model\ConfigProvider;

/**
 * @see \Magento\Catalog\Model\ResourceModel\Eav\Attribute::save()
 * @SuppressWarnings(PHPMD)
 */
class SaveRewriteOnAttributeSavePlugin
{
    private $rewriteService;

    private $rewriteRepository;

    private $labelService;

    private $messageManager;

    private $configProvider;

    public function __construct(
        RewriteService    $rewriteService,
        RewriteRepository $rewriteRepository,
        LabelService      $labelService,
        ConfigProvider    $configProvider,
        ManagerInterface  $messageManager
    ) {
        $this->rewriteService    = $rewriteService;
        $this->rewriteRepository = $rewriteRepository;
        $this->labelService      = $labelService;
        $this->messageManager    = $messageManager;
        $this->configProvider    = $configProvider;
    }

    /**
     * @param Attribute $subject
     * @param \Closure  $proceed
     *
     * @return Attribute
     */
    public function aroundSave($subject, \Closure $proceed)
    {
        $attributeCode = (string)$subject->getAttributeCode();

        if (!$attributeCode) {
            return $proceed();
        }

        $seoFilterData = $subject->getData('seo_filter');

        if (isset($seoFilterData['attribute'])) {
            foreach ($seoFilterData['attribute'] as $storeId => $urlRewrite) {
                $storeId    = (int)$storeId;
                $urlRewrite = $urlRewrite ? (string)$urlRewrite : $attributeCode;

                $urlRewrite = $this->labelService->uniqueLabel($urlRewrite, $storeId, 0, $attributeCode);

                $rewrite = $this->rewriteService->getAttributeRewrite(
                    $attributeCode,
                    $storeId,
                    false
                );

                if ($rewrite) {
                    $rewrite->setRewrite($urlRewrite);
                    $this->rewriteRepository->save($rewrite);
                }
            }
        }

        if (isset($seoFilterData['options'])) {
            foreach ($seoFilterData['options'] as $optionId => $item) {
                $optionId = (string)$optionId;
                foreach ($item as $storeId => $urlRewrite) {
                    $storeId    = (int)$storeId;
                    $urlRewrite = trim((string)$urlRewrite);

                    if (!$urlRewrite) {
                        continue;
                    }

                    $existing = $rewrite = $this->rewriteRepository->getCollection()
                        ->addFieldToFilter(RewriteInterface::REWRITE, $urlRewrite)
                        ->addFieldToFilter(RewriteInterface::OPTION, ['notnull' => true])
                        ->addFieldToFilter(RewriteInterface::STORE_ID, $storeId)
                        ->addFieldToFilter(RewriteInterface::OPTION, ['neq' => $optionId]);
                    
                    if ($this->configProvider->getUrlFormat() == ConfigProvider::URL_FORMAT_ATTR_OPTIONS) {
                        $existing->addFieldToFilter(RewriteInterface::ATTRIBUTE_CODE, $attributeCode);
                    }

                    if ($existing->getSize()) {
                        $oldRewrite = $urlRewrite;
                        $urlRewrite = $this->labelService->uniqueLabel($urlRewrite, $storeId);
                        $message = 'The rewrite "' . $oldRewrite . '" already exists for another option with id: ' 
                            . $optionId . '. New rewrite "' . $urlRewrite . '" was generated.';
                        $this->messageManager->addWarningMessage(__($message));
                    }

                    $rewrite = $this->rewriteService->getOptionRewrite(
                        $attributeCode,
                        $optionId,
                        $storeId,
                        false
                    );

                    if ($rewrite) {
                        $rewrite->setRewrite($urlRewrite);
                        $this->rewriteRepository->save($rewrite);
                    }

                }
            }

        }

        return $proceed();
    }
}
