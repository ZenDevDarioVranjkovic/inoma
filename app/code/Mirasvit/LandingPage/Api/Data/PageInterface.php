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

namespace Mirasvit\LandingPage\Api\Data;

interface PageInterface
{
    const PAGE_ID            = 'page_id';
    const NAME               = 'name';
    const STORE_IDS          = 'store_ids';
    const IS_ACTIVE          = 'is_active';
    const URL_KEY            = 'url_key';
    const PAGE_TITLE         = 'page_title';
    const META_TITLE         = 'meta_title';
    const META_TAGS          = 'meta_tags';
    const META_DESCRIPTION   = 'meta_description';
    const DESCRIPTION        = 'description';
    const TOP_BLOCK          = 'top_block';
    const BOTTOM_BLOCK       = 'bottom_block';
    const LAYOUT_UPDATE      = 'layout_update';
    const CATEGORIES         = 'categories';
    const SEARCH_TERM        = 'search_term';
    const IS_DISPLAY_FILTERS = 'is_display_filters';
    const MAIN_TABLE         = 'mst_landing_page';
    const FILTERS_TABLE      = 'mst_landing_page_filter';


    public function getName(): string;

    public function setName(string $value): self;

    public function getStoreIds(): string;

    public function setStoreIds(string $value): self;

    public function getIsActive(): bool;

    public function setIsActive(bool $value): self;

    public function getUrlKey(): string;

    public function setUrlKey(string $value): self;

    public function getStoreId(): int;

    public function setStoreId(int $value): self;

    public function getPageTitle(): string;

    public function setPageTitle(string $value): self;

    public function getMetaTitle(): string;

    public function setMetaTitle(string $value): self;

    public function getMetaTags(): string;

    public function setMetaTags(string $value): self;

    public function getMetaDescription(): string;

    public function setMetaDescription(string $value): self;

    public function getDescription(): string;

    public function setDescription(string $value): self;

    public function getTopBlock(): int;

    public function setTopBlock(int $value): self;

    public function getBottomBlock(): int;

    public function setBottomBlock(int $value): self;

    public function getLayoutUpdate(): string;

    public function setLayoutUpdate(string $value): self;

    public function getCategories(): string;

    public function setCategories(string $value): self;

    public function getSearchTerm(): string;

    public function setSearchTerm(string $value): self;
}
