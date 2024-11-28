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
 * @package   mirasvit/module-landing-page
 * @version   1.0.13
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\LandingPage\Repository;

use Mirasvit\LandingPage\Api\Data\PageInterface;
use Mirasvit\LandingPage\Model\PageFactory;
use Mirasvit\LandingPage\Model\ResourceModel\Page\Collection;
use Mirasvit\LandingPage\Model\ResourceModel\Page\CollectionFactory;

class PageRepository
{
    private $pageFactory;

    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        PageFactory       $pageFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->pageFactory       = $pageFactory;
    }

    public function create(): PageInterface
    {
        return $this->pageFactory->create();
    }

    public function get(int $id): ?PageInterface
    {
        $model = $this->create();

        $model->load($id);

        return $model->getId() ? $model : null;
    }

    public function getCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    public function save(PageInterface $model): PageInterface
    {
        $model->save();

        return $model;
    }

    public function delete(PageInterface $model)
    {
        $model->delete();
    }


}
