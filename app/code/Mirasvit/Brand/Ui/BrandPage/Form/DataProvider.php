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

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\ImageUploader;
use Mirasvit\Brand\Model\Config\Source\DisplayModeSource;
use Magento\Catalog\Model\Category\Attribute\Source\Page as CmsBlockSource;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\FieldFactory;
use Magento\Ui\Component\Form\FieldsetFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Api\Data\BrandPageStoreInterface;
use Mirasvit\Brand\Model\Config\GeneralConfig;
use Mirasvit\Brand\Model\ResourceModel\BrandPage\CollectionFactory;
use Mirasvit\Brand\Repository\BrandPageRepository;
use Mirasvit\Brand\Service\ImageUrlService;
use Mirasvit\Brand\Ui\BrandPage\Form\Modifier\NewBrandModifier;
use Mirasvit\Brand\Ui\BrandPage\Form\Source\Robots;
use Mirasvit\Brand\Ui\BrandPage\Form\Source\SeoDescriptionPosition;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class DataProvider extends AbstractDataProvider
{
    private $imageUploader;

    private $mime;

    /**
     * @var ReadInterface
     */
    private $mediaDirectory;

    private $imageUrlService;

    private $dataPersistor;

    private $status;

    private $imageHelper;

    private $modifier;

    private $uiComponentFactory;

    private $fieldsetFactory;

    private $storeManager;

    private $brandPageRepository;

    private $context;

    private $displayModeSource;

    private $cmsBlockSource;

    private $productCollectionFactory;

    private $generalConfig;

    private $fieldFactory;

    private $robotsSource;

    private $seoPositionSource;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ImageUploader $imageUploader,
        Filesystem $filesystem,
        Mime $mime,
        CollectionFactory $collectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        Status $status,
        ContextInterface $context,
        ImageHelper $imageHelper,
        DataPersistorInterface $dataPersistor,
        ImageUrlService $imageUrlService,
        GeneralConfig $generalConfig,
        NewBrandModifier $modifier,
        UiComponentFactory $uiComponentFactory,
        FieldsetFactory $fieldsetFactory,
        FieldFactory $fieldFactory,
        StoreManagerInterface $storeManager,
        BrandPageRepository $brandPageRepository,
        DisplayModeSource $displayModeSource,
        CmsBlockSource $cmsBlockSource,
        Robots $robotsSource,
        SeoDescriptionPosition $seoPositionSource,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection               = $collectionFactory->create();
        $this->productCollectionFactory = $productCollectionFactory;
        $this->status                   = $status;
        $this->imageHelper              = $imageHelper;
        $this->dataPersistor            = $dataPersistor;
        $this->imageUrlService          = $imageUrlService;
        $this->imageUploader            = $imageUploader;
        $this->mediaDirectory           = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->mime                     = $mime;
        $this->context                  = $context;
        $this->generalConfig            = $generalConfig;
        $this->modifier                 = $modifier;
        $this->uiComponentFactory       = $uiComponentFactory;
        $this->fieldsetFactory          = $fieldsetFactory;
        $this->fieldFactory             = $fieldFactory;
        $this->storeManager             = $storeManager;
        $this->brandPageRepository      = $brandPageRepository;
        $this->displayModeSource        = $displayModeSource;
        $this->cmsBlockSource           = $cmsBlockSource;
        $this->robotsSource             = $robotsSource;
        $this->seoPositionSource        = $seoPositionSource;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getMeta()
    {
        $meta = parent::getMeta();

        $model = $this->getModel();

        $meta = $this->prepareForm($model);
        $meta = $this->modifier->modifyMeta($meta);

        return $meta;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function prepareForm(BrandPageInterface $brandPage = null): array
    {
        $fieldset = $this->fieldsetFactory->create();
        $fieldset->setData([
            'name'   => 'wrapper',
            'config' => [
                'componentType' => 'fieldset',
                'label'         => ' ',
            ]
        ]);

        $contentFieldset = $this->getContentFieldset($brandPage);

        $seoFieldset = $this->getSeoFieldset($brandPage);

        $fieldset->addComponent('content', $contentFieldset);
        $fieldset->addComponent('meta_data', $seoFieldset);
        

        $data = $this->prepareComponent($fieldset);

        return $data['children'];
    }

    private function resolveFieldComment(string $field, int $storeId): string
    {
        if ($storeId) {
            if (
                $field == BrandPageStoreInterface::BRAND_ROBOTS 
                || $field == BrandPageStoreInterface::BRAND_SEO_POSITION
            ) {
                return '';
            }

            return (string)__('If empty, the default value will be used');
        } elseif ($field == BrandPageStoreInterface::BRAND_TITLE) {
            return (string)__('If empty, Title will be generated automatically');
        }

        return '';
    }

    protected function prepareComponent(UiComponentInterface $component): array
    {
        $data = [];
        foreach ($component->getChildComponents() as $name => $child) {
            $data['children'][$name] = $this->prepareComponent($child);
        }

        $data['arguments']['data']  = $component->getData();
        $data['arguments']['block'] = $component->getBlock();

        return $data;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getData()
    {
        $result = [];

        /** @var BrandPageInterface $item */
        foreach ($this->collection->getItems() as $item) {
            $item->load($item->getId());

            $data = $item->getData();
            $data = $this->prepareImageData($data, 'logo');
            $data = $this->prepareImageData($data, 'banner');

            if (isset($data['store_ids'])) {
                $data['store_id'] = $data['store_ids'];
            } else {
                $data['store_id'] = '0';
            }

            if (isset($data['content'])) {
                foreach ($data['content'] as $store => $contentData) {
                    foreach ($contentData as $key => $value) {
                        $data['content[' . $store . '][' . $key . ']'] = $value;
                    }
                }

                unset($data['content']);
            }

   

            if (isset($data['meta_data'])) {
                foreach ($data['meta_data'] as $store => $seoData) {
                    foreach ($seoData as $key => $value) {
                        $data['meta_data[' . $store . '][' . $key . ']'] = $value;
                    }
                }

                unset($data['meta_data']);
            }

            if ($this->generalConfig->isShowProductsInForm()) {
                $productCollection = $this->productCollectionFactory->create();
                $productCollection->addAttributeToSelect('*')
                    ->addAttributeToFilter($this->generalConfig->getBrandAttribute(), $item->getAttributeOptionId())
                    ->setOrder('entity_id', 'ASC');

                $data['links']['products'] = [];
                $data['configured'] = true;

                foreach ($productCollection as $product) {
                    $data['links']['products'][] = [
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'status' => $this->status->getOptionText($product->getStatus()),
                        'thumbnail' => $this->imageHelper->init($product, 'product_listing_thumbnail')->getUrl(),
                    ];
                }
            }

            $result[$item->getId()] = $data;
        }

        return $result;
    }

    /**
     * @param array  $data
     * @param string $imageKey
     *
     * @return array
     */
    private function prepareImageData($data, $imageKey)
    {
        if (isset($data[$imageKey])) {
            $imageName = $data[$imageKey];
            unset($data[$imageKey]);
            if ($this->mediaDirectory->isExist($this->getFilePath($imageName))) {
                $data[$imageKey] = [
                    [
                        'name' => $imageName,
                        'url'  => $this->imageUrlService->getImageUrl($imageName),
                        'size' => $this->mediaDirectory->stat($this->getFilePath($imageName))['size'],
                        'type' => $this->getMimeType($imageName),
                    ],
                ];
            }
        }

        return $data;
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function getMimeType($fileName)
    {
        $absoluteFilePath = $this->mediaDirectory->getAbsolutePath($this->getFilePath($fileName));

        return $this->mime->getMimeType($absoluteFilePath);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function getFilePath($fileName)
    {
        return $this->imageUploader->getFilePath($this->imageUploader->getBasePath(), $fileName);
    }

    private function getModel(): ?BrandPageInterface
    {
        $id = $this->context->getRequestParam($this->getRequestFieldName(), null);

        return $id ? $this->brandPageRepository->get((int)$id) : null;
    }

    private function getContentFields()
    {
        return array(
            BrandPageStoreInterface::BRAND_TITLE => [
                "label"         => "Title",
                "dataType"      => "text",
                "formElement"   => "input",
                "componentType" => "field"
            ],
            BrandPageStoreInterface::BRAND_DESCRIPTION => [
                "label"         => "Description",
                "dataType"      => "text",
                "formElement"   => "wysiwyg",
                "componentType" => "field"
            ],
            BrandPageStoreInterface::BRAND_SHORT_DESCRIPTION => [
                "label"         => "Short Description",
                "dataType"      => "text",
                "formElement"   => "textarea",
                "componentType" => "field"
            ],
            BrandPageStoreInterface::BRAND_DISPLAY_MODE => [
                "label"         => "Display Mode",
                "dataType"      => "text",
                "formElement"   => "select",
                "componentType" => "field"
            ],
            BrandPageStoreInterface::BRAND_CMS_BLOCK => [
                "label"         => "CMS Block",
                "dataType"      => "text",
                "formElement"   => "select",
                "componentType" => "field"
            ],
        );
    }

    private function getSeoFields()
    {
        return array(
            BrandPageStoreInterface::BRAND_META_TITLE => [
                "label"         => "Meta title",
                "dataType"      => "text",
                "formElement"   => "input",
                "componentType" => "field"
            ],
            BrandPageStoreInterface::BRAND_META_KEYWORDS => [
                "label"         => "Meta keywords",
                "dataType"      => "text",
                "formElement"   => "textarea",
                "componentType" => "field"
            ],
            BrandPageStoreInterface::BRAND_META_DESCRIPTION => [
                "label"         => "Meta description",
                "dataType"      => "text",
                "formElement"   => "textarea",
                "componentType" => "field"
            ],
            BrandPageStoreInterface::BRAND_SEO_DESCRIPTION => [
                "label"         => "SEO description",
                "dataType"      => "text",
                "formElement"   => "wysiwyg",
                "componentType" => "field"
            ],
            BrandPageStoreInterface::BRAND_SEO_POSITION => [
                "label"         => "SEO description position",
                "dataType"      => "text",
                "formElement"   => "select",
                "componentType" => "field"
            ],
            BrandPageStoreInterface::BRAND_CANONICAL_URL => [
                "label"         => "Canonical url",
                "dataType"      => "text",
                "formElement"   => "input",
                "componentType" => "field"
            ],
            BrandPageStoreInterface::BRAND_ROBOTS => [
                "label"         => "Robots",
                "dataType"      => "text",
                "formElement"   => "select",
                "componentType" => "field"
            ],
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getContentFieldset($brandPage)
    {
        $contentFieldset = $this->fieldsetFactory->create();
        $contentFieldset->setData([
            'name'   => 'content',
            'config' => [
                'componentType' => 'fieldset',
                'collapsible'   => true,
                'label'         => 'Content',
                'sortOrder'     => 10
            ]
        ]);

        foreach ($this->storeManager->getStores(true) as $store) {
            if (
                $store->getId() != 0
                && $brandPage
                && $brandPage->getData('store_ids')
                && !in_array($store->getId(), explode(',', $brandPage->getData('store_ids')))
            ) {
                continue;
            }

            $contentComponent = $this->fieldsetFactory->create();
            $contentComponent->setData([
                'name'   => (string)$store->getId(),
                'config' => [
                    'componentType' => 'fieldset',
                    'collapsible'   => $store->getId() != 0,
                    'label'         => $store->getId() == 0 ? 'Defaul (' . $store->getName() . ')' : $store->getName(),
                ]
            ]);

            foreach ($this->getContentFields() as $field => $config) {
                $fieldInput = $this->fieldFactory->create();
                $fieldName  = 'content['.$store->getId().']['.$field.']';

                $config['notice'] = $this->resolveFieldComment($field, (int)$store->getId());

                $fieldConfig = [
                    'name'    => $fieldName,
                    'config'  => $config,
                ];

                if ($field == BrandPageStoreInterface::BRAND_DISPLAY_MODE) {
                    $fieldConfig['options'] = $this->displayModeSource->toOptionArray();
                }

                if ($field == BrandPageStoreInterface::BRAND_CMS_BLOCK) {
                    $fieldConfig['options'] = $this->cmsBlockSource->getAllOptions();
                }

                $fieldInput->setData($fieldConfig);

                $contentComponent->addComponent($fieldName, $fieldInput);
            }

            $contentFieldset->addComponent((string)$store->getId(), $contentComponent);
        }
        
        return $contentFieldset;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getSeoFieldset($brandPage)
    {
        $seoFieldset = $this->fieldsetFactory->create();
        $seoFieldset->setData([
            'name'   => 'meta_data',
            'config' => [
                'componentType' => 'fieldset',
                'collapsible'   => true,
                'label'         => 'Search Engine Optimization',
                'sortOrder'     => 20
            ]
        ]);

        foreach ($this->storeManager->getStores(true) as $store) {
            if (
                $store->getId() != 0
                && $brandPage
                && $brandPage->getData('store_ids')
                && !in_array($store->getId(), explode(',', $brandPage->getData('store_ids')))
            ) {
                continue;
            }

            $seoComponent = $this->fieldsetFactory->create();
            $seoComponent->setData([
                'name'   => (string)$store->getId(),
                'config' => [
                    'componentType' => 'fieldset',
                    'collapsible'   => $store->getId() != 0,
                    'label'         => $store->getId() == 0 ? 'Defaul (' . $store->getName() . ')' : $store->getName(),
                ]
            ]);

            foreach ($this->getSeoFields() as $field => $config) {
                $fieldInput = $this->fieldFactory->create();
                $fieldName  = 'meta_data[' . $store->getId() . '][' . $field . ']';

                $config['notice'] = $this->resolveFieldComment($field, (int)$store->getId());

                $fieldConfig = [
                    'name'    => $fieldName,
                    'config'  => $config,
                ];

                if ($field == BrandPageStoreInterface::BRAND_ROBOTS) {
                    $fieldConfig['options'] = $this->robotsSource->toOptionArray();
                }

                if ($field == BrandPageStoreInterface::BRAND_SEO_POSITION) {
                    $fieldConfig['options'] = $this->seoPositionSource->toOptionArray();
                    
                    if ($store->getId() == 0) {
                        array_shift($fieldConfig['options']);
                    }
                }

                $fieldInput->setData($fieldConfig);

                $seoComponent->addComponent($fieldName, $fieldInput);
            }

            $seoFieldset->addComponent((string)$store->getId(), $seoComponent);
        }
        
        return $seoFieldset;
    }
}
