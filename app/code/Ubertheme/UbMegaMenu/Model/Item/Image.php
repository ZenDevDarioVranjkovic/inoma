<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Model\Item;

use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Image
{
    /**
     * media folder to save images
     * @var string
     */
    protected $subDir = 'ubmegamenu';

    /**
     * url builder object
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @param UrlInterface $urlBuilder
     * @param Filesystem $fileSystem
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Filesystem $fileSystem
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->fileSystem = $fileSystem;
    }

    /**
     * get base url of image
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).$this->subDir.'/images';
    }

    /**
     * get base directory of image
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->fileSystem->getDirectoryWrite(
            DirectoryList::MEDIA)->getAbsolutePath($this->subDir.'/images');
    }
}
