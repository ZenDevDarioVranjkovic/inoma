# README #

# UB Mega Menu Extension for Magento2 #

### Features:

#### Back-End:
- Allow enable/disable module
- Allow add/edit/delete information of menu groups, menu items, 
- Allow mass delete, mass change status menu groups, 
- Allow duplicate a menu group and all menu items on it.
- Allow enable/disable a menu item in menu items tree via Ajax request
- Allow select and import categories to make menu items
- Allow drag/drop to re-sorting menu items and change menu items relation via Ajax request
- Allow create a UB Mega Menu Widget to show anywhere in front-end via widgets management module.
- Allow setting animation type of a Menu group (+13 animations)
- Allow multiple level menu items configurations
- Allow setting menu item content type: Custom link, CMS Page link, Categories link
- Allow setting menu item content: Text, Static Blocks, Custom Content (text, html, images...),
- Allow show/hide Qty of products label in case menu item link to a category
- Allow setting icon image of menu item
- [Allow setting icon with Font Awesome - v4.5.0](https://fortawesome.github.io/Font-Awesome/icons/)
- Allow show/hide menu item title
- Allow set a menu item is a group or no group
- Allow set number columns of submenu content
- Allow set column width of each column in submenu content.
- Allow set max width of submenu content
- Allow add an addition class CSS to a menu item (for customizing purpose)
- Allow setting target of menu item link: _blank, _self, _parent
- Allow add description for a menu item
- Allow settings to show/hide mega content (desc, custom content, static blocks content of menu items) by devices (desktop, tablet, mobile) (ready from v1.0.1)
- Allow settings to show/hide Menu Group Title (ready from v1.0.1)
- Support multiple websites, store views configuration, allow multiple languages in settings

#### Front-End:
- Show UB Mega Menu multiple level items by menu_id or menu_key and configuration in back-end
- Allow multiple Blocks Menus per page
- Can show a Menu anywhere in theme in front-end via Widgets module.
- Support multiple websites, Store Views Configuration
- Support multiple languages
   
### Compatible:
+ Magento CE 2.x

### How to Install:
#### Install via Composer:
Go to web root folder (your magento 2 installation folder) and run below commands:

- `composer config repositories.ubmegamenu vcs REPOSITOTY_URL_OF_THIS_MODULE` (Example: git@bitbucket.org:joomsolutions/ub-module-ubmegamenu.git)
- `composer require ubertheme/module-ubmegamenu` (*This is a private repository. So, you have to type your bitbucket credentials in this step.*)
- `php -f bin/magento module:enable --clear-static-content Ubertheme_UbMegaMenu`
- `php -f bin/magento setup:upgrade`

### How to use
- 1- Settings and show a UB Mega Menu via Widgets management module (Content/Widgets)
Let's do it like as wysiwyg:

![Create a UB Mega Menu Widget 1](http://i.imgur.com/7Eb0LZY.png)

![Create a UB Mega Menu Widget 2](http://i.prntscr.com/7d1d0ec7ab894998b8e96239744828c9.png)

![Create a UB Mega Menu Widget 3](https://image.prntscr.com/image/mDy-C0G7RfyOg_EAjLQ3gg.png)

- 2- Call in CMS Block: 
```
{{block class="Ubertheme\UbMegaMenu\Block\Menu" name="ub.mega.menu1" menu_key="YOUR_MENU_KEY_HERE"}}

```
- 3- Call in XML: 
```
<referenceContainer name="page.top">
<block class="Ubertheme\UbMegaMenu\Block\Menu" name="ub.megamenu.leftmenu1" ifconfig="ubmegamenu/general/enable">
    <arguments>
        <argument name="menu_key" xsi:type="string">top-menu</argument>
        <argument name="addition_class" xsi:type="string"></argument>
    </arguments>
</block>
</referenceContainer>

```

### Table option param:

Param Name    | Desc/Values
------------- | -------------
show_number_product  | 0,1 Default is 0
show_menu_title | On/Off Menu group title. Values: 0,1 Default is 0
default_mega_col_width | The width of a Mega Column (px). Default is 200
mega_col_margin | The margin value (margin-left + margin-right) of a Mega Column (px). Default is 0
mega_content_visible_in | Example: desktop,tablet,mobile (ready from v1.0.1)
start_level | The start level of menu item to show. Default is 0.
end_level | The max level of menu item to show. Default is 10.
addition_class | The addition class CSS support to customize purpose.

### Addition class:

+ style-default

![Style for addition class "style-default"](https://image.prntscr.com/image/vCZr0PwsR_iDIOTCiSW_0A.png)


+ style-1

![Style for addition class "style-1"](https://image.prntscr.com/image/PshvHa41RvCh5u3KQ_9aNA.png)


+ style-2

![Style for addition class "style-2"](https://image.prntscr.com/image/GfTZ7EHqTSijWzU1ki-31w.png)