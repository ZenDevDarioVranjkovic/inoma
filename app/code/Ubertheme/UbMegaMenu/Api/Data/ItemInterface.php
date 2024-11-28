<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Api\Data;

/**
 * UB Mega Menu Item interface.
 * @api
 */
interface ItemInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ITEM_ID                 = 'item_id';
    const PARENT_ID               = 'parent_id';
    const GROUP_ID                = 'group_id';
    const TITLE                   = 'title';
    const SEO_TITLE               = 'seo_title';
    const IDENTIFIER              = 'identifier';
    const PATH                    = 'path';
    const LEVEL                   = 'level';
    const SHOW_TITLE              = 'show_title';
    const ICON_IMAGE              = 'icon_image';
    const FONT_AWESOME            = 'font_awesome';
    const LINK_TYPE               = 'link_type';
    const LINK                    = 'link';
    const LINK_TARGET             = 'link_target';
    const CATEGORY_ID             = 'category_id';
    const SHOW_NUMBER_PRODUCT     = 'show_number_product';
    const CMS_PAGE                = 'cms_page';
    const IS_GROUP                = 'is_group';
    const MEGA_COLS               = 'mega_cols';
    const MEGA_WIDTH              = 'mega_width';
    const MEGA_BASE_WIDTH_TYPE    = 'mega_base_width_type';
    const MEGA_COL_WIDTH          = 'mega_col_width';
    const MEGA_COL_X_WIDTH        = 'mega_col_x_width';
    const MEGA_SUB_CONTENT_TYPE   = 'mega_sub_content_type';
    const CUSTOM_CONTENT          = 'custom_content';
    const STATIC_BLOCKS           = 'static_blocks';
    const VISIBLE_OPTION          = 'visible_option';
    const VISIBLE_IN              = 'visible_in';
    const ADDITION_CLASS          = 'addition_class';
    const DESCRIPTION             = 'description';
    const CREATION_TIME           = 'creation_time';
    const UPDATE_TIME             = 'update_time';
    const IS_ACTIVE               = 'is_active';
    const SORT_ORDER              = 'sort_order';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Parent ID
     *
     * @return int|null
     */
    public function getParentId();

    /**
     * Get Group ID
     *
     * @return int|null
     */
    public function getGroupId();

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get SEO Title
     *
     * @return string|null
     */
    public function getSEOTitle();

    /**
     * Get Identifier
     *
     * @return string|null
     */
    public function getIdentifier();

    /**
     * Get Path
     *
     * @return string|null
     */
    public function getPath();

    /**
     * Get Level
     *
     * @return integer
     */
    public function getLevel();

    /**
     * Get Show Title
     *
     * @return bool|null
     */
    public function isShowTitle();

    /**
     * Get Icon Image
     *
     * @return string|null
     */
    public function getIconImage();

    /**
     * Get Font Awesome
     *
     * @return string|null
     */
    public function getFontAwesome();

    /**
     * Get Link Type
     *
     * @return string|null
     */
    public function getLinkType();

    /**
     * Get Link
     *
     * @return string|null
     */
    public function getLink();

    /**
     * Get Link Target
     *
     * @return string|null
     */
    public function getLinkTarget();

    /**
     * Get Category ID
     *
     * @return int|null
     */
    public function getCategoryId();

    /**
     * Get Show Number Product
     *
     * @return bool|null
     */
    public function isShowNumberProduct();

    /**
     * Get CMS Page
     *
     * @return string|null
     */
    public function getCmsPage();

    /**
     * Get Is Group
     *
     * @return bool|null
     */
    public function isGroup();

    /**
     * Get Mega Cols
     *
     * @return int|null
     */
    public function getMegaCols();

    /**
     * Get Mega Width
     *
     * @return int|null
     */
    public function getMegaWidth();

    /**
     * Get Mega Base Width Type
     *
     * @return int|null
     */
    public function getMegaBaseWidthType();

    /**
     * Get Mega Col Width
     *
     * @return int|null
     */
    public function getMegaColWidth();

    /**
     * Get Mega ColX Width
     *
     * @return string|null
     */
    public function getMegaColXWidth();

    /**
     * Get Mega Sub Content Type
     *
     * @return string|null
     */
    public function getMegaSubContentType();

    /**
     * Get Custom Content
     *
     * @return string|null
     */
    public function getCustomContent();

    /**
     * Get Static Blocks
     *
     * @return string|null
     */
    public function getStaticBlocks();

    /**
     * Get Visible Option
     *
     * @return string|null
     */
    public function getVisibleOption();

    /**
     * Get Visible In
     *
     * @return string|null
     */
    public function getVisibleIn();

    /**
     * Get Addition Class
     *
     * @return string|null
     */
    public function getAdditionClass();

    /**
     * Get Description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Get sort order
     *
     * @return string|null
     */
    public function getSortOrder();

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setId($id);

    /**
     * Set Parent ID
     *
     * @param int $parentId
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setParentId($parentId);

    /**
     * Set Group ID
     *
     * @param int $groupId
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setGroupId($groupId);

    /**
     * Set Title
     *
     * @param string $title
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setTitle($title);

    /**
     * Set SEO Title
     *
     * @param string $title
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setSEOTitle($title);

    /**
     * Set Identifier
     *
     * @param string $identifier
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setIdentifier($identifier);

    /**
     * Set Path
     *
     * @param string $path
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setPath($path);

    /**
     * Set Level
     *
     * @param integer $level
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setLevel($level);

    /**
     * Set Show Title
     *
     * @param int $isShowTitle
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setIsShowTitle($isShowTitle);

    /**
     * Set Icon Image
     *
     * @param string $iconImage
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setIconImage($iconImage);

    /**
     * Set Font Awesome
     *
     * @param string $fontAwesome
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setFontAwesome($fontAwesome);

    /**
     * Set Link Type
     *
     * @param string $linkType
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setLinkType($linkType);

    /**
     * Set Link
     *
     * @param string $link
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setLink($link);

    /**
     * Set Link Target
     *
     * @param string $linkTarget
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setLinkTarget($linkTarget);

    /**
     * Set Category ID
     *
     * @param string $categoryId
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setCategoryId($categoryId);

    /**
     * Set Show Number Product
     *
     * @param int $isShowNumberProduct
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setIsShowNumberProduct($isShowNumberProduct);

    /**
     * Set CMS page
     *
     * @param string $cmsPage
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setCmsPage($cmsPage);

    /**
     * Set Is Group
     *
     * @param string $isGroup
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setIsGroup($isGroup);

    /**
     * Set Mega Cols
     *
     * @param string $megaCols
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setMegaCols($megaCols);

    /**
     * Set Mega Width
     *
     * @param string $megaWidth
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setMegaWidth($megaWidth);

    /**
     * Set Mega Base Width Type
     *
     * @param string $baseWidthType
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setMegaBaseWidthType($baseWidthType);

    /**
     * Set Mega Col Width
     *
     * @param string $megaColWidth
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setMegaColWidth($megaColWidth);

    /**
     * Set Mega Col X Width
     *
     * @param string $megaColXWidth
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setMegaColXWidth($megaColXWidth);

    /**
     * Set Mega Sub Content Type
     *
     * @param string $megaSubContentType
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setMegaSubContentType($megaSubContentType);

    /**
     * Set Custom Content
     *
     * @param string $customContent
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setCustomContent($customContent);

    /**
     * Set Static Blocks
     *
     * @param string $staticBlocks
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setStaticBlocks($staticBlocks);

    /**
     * Set Visible Option
     *
     * @param string $visibleOption
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setVisibleOption($visibleOption);

    /**
     * Set Visible In
     *
     * @param string $visibleIn
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setVisibleIn($visibleIn);

    /**
     * Set Addition Class
     *
     * @param string $additionClass
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setAdditionClass($additionClass);

    /**
     * Set Description
     *
     * @param string $description
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setDescription($description);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     */
    public function setIsActive($isActive);
}
