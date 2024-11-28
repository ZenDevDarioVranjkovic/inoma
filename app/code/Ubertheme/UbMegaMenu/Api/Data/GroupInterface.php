<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Api\Data;

/**
 * UB Mega Menu Group interface.
 * @api
 */
interface GroupInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const GROUP_ID                 = 'group_id';
    const MENU_POSITION            = 'menu_position';
    const TITLE                    = 'title';
    const IDENTIFIER               = 'identifier';
    const ANIMATION_TYPE           = 'animation_type';
    const MENU_TYPE                = 'menu_type';
    const MOBILE_TYPE              = 'mobile_type';
    const DESCRIPTION              = 'description';
    const CREATION_TIME            = 'creation_time';
    const UPDATE_TIME              = 'update_time';
    const IS_ACTIVE                = 'is_active';
    const SORT_ORDER               = 'sort_order';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get menu position
     *
     * @return string|null
     */
    public function getMenuPosition();

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get animation type
     *
     * @return string|null
     */
    public function getAnimationType();

    /**
     * Get menu type
     *
     * @return string|null
     */
    public function getMenuType();

    /**
     * Get mobile type
     *
     * @return string|null
     */
    public function getMobileType();

    /**
     * Get description
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
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setId($id);

    /**
     * Set menu position
     *
     * @param string $menuPosition
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setMenuPosition($menuPosition);

    /**
     * Set title
     *
     * @param string $title
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setTitle($title);

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setIdentifier($identifier);

    /**
     * Set animation type
     *
     * @param string $animationType
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setAnimationType($animationType);

    /**
     * Set menu type
     *
     * @param string $menuType
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setMenuType($menuType);

    /**
     * Set mobile type
     *
     * @param string $mobileType
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setMobileType($mobileType);

    /**
     * Set description
     *
     * @param string $description
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setDescription($description);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     */
    public function setIsActive($isActive);
}
