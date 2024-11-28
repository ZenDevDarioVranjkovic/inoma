/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

define([
    'jquery',
    'mage/translate',
    'Ubertheme_Base/js/mobile-detect.min',
    'matchMedia'
], function($, $t, MobileDetect) {
    'use strict';

    $.widget('mage.ubMenu', {
        options: {
            mainWrapperSelector: ".sections.nav-sections",
            menuKey: null,
            menuPosition: null,
            isMainMenu: false,
            enableSticky: false,
            rootSelector: ".ub-mega-menu.level0",
            itemSelector: "li.mega",
            offCanvasBtnSelector: "[data-action=\"toggle-nav\"]",
            offCanvasBreakpoint: 1023,
            offCanvasShowDelay: 50,
            offCanvasHideDelay: 300,
            menuType: 'accordion',
            mobileType: 'accordion', //using in Mobile only when menuType = vertical
            drillOptions: {
                $container: null,
                container: 'drilldown-container',
                root: 'drilldown-root',
                sub: 'drilldown-sub',
                back: 'drilldown-back',
                speed: 200,
                _css: {
                    float: 'left',
                    width: null
                },
                _history: []
            },
            extraClass: "",
            mobileDetect: null
        },

        /**
         * Initialize widget
         */
        _create: function() {
            //init mobile detect object
            this.options.mobileDetect = new MobileDetect(window.navigator.userAgent);

            //update type of menu
            this._updateMenuType();

            //active current menu item
            this._active();

            //listing events on menu items
            this._listen();
        },

        _active: function() {
            const self = this;

            //reset active state
            $(this.element).find('.active').removeClass('active');

            //set active state for current selected menu item and all associated parent items
            let $activeItem = null;
            //check has clicked menu item id in session storage
            const currentItemId = (sessionStorage) ? sessionStorage.getItem('ubMenuItemId') : false;
            const currentUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
            const urlPath = window.location.pathname;
            const urlPathWithSearch = window.location.pathname + window.location.search;
            if (currentItemId) {
                $activeItem = $(this.element).find('#' + currentItemId);
                if ($activeItem.find('a[href="' + currentUrl + '"]').length ||
                    $activeItem.find('a[href="' + urlPath + '"]').length ||
                    $activeItem.find('a[href="' + urlPathWithSearch + '"]').length
                ) {
                    if ($activeItem.children('a.mega').length) {
                        $activeItem = $activeItem.children('a.mega');
                    }
                    if ($activeItem.children('span.mega').length) {
                        $activeItem = $activeItem.children('span.mega');
                    }
                } else {
                    $activeItem = null;
                }
            } else {
                $activeItem = $(this.element).find("a[href=\"" + currentUrl + "\"]");
                if (!$activeItem.length) {
                    $activeItem = $(this.element).find("a[href=\"" + urlPath + "\"]");
                }
                if (!$activeItem.length) {
                    $activeItem = $(this.element).find("a[href=\"" + urlPathWithSearch + "\"]");
                }
            }
            if ($activeItem && $activeItem.length) {
                if ($activeItem.length > 1) {
                    $activeItem = $activeItem.first();
                }
                $activeItem.addClass('active');
                $activeItem.parentsUntil(self.options.rootSelector).addClass('active');
                //active for related elements
                $(self.options.itemSelector + '.active').children().addClass('active');
                $(self.options.itemSelector + '.has-child.active').children().addClass('active');
            }
        },

        _listen: function() {
            const self = this;

            /**
             * update current clicked menu item id to using on other contexts
             */
            let menuId = null;
            const eventName = (this._isTablet()) ? 'touchstart' : 'click';
            const $menuItems = $(this.element).find(self.options.itemSelector);
            $menuItems.on(eventName, function(e) {
                const $clickedItem = $(e.target).closest(self.options.itemSelector);
                if ($clickedItem.length) {
                    menuId = $clickedItem.attr('id');
                    sessionStorage.setItem('ubMenuItemId', menuId);
                } else {
                    //reset status of all `A` tags
                    $(this.element).find("a.mega").data('status', '');
                }
            });

            let media = 'all';
            if (self.options.offCanvasBreakpoint !== 'all') {
                if (self._isTabletPro()) {
                    self.options.offCanvasBreakpoint = '1365';
                }
                media = '(max-width: ' + self.options.offCanvasBreakpoint + 'px)'
            }
            mediaCheck({
                media: media,
                entry: function() {
                    if (parseInt(self.options.isMainMenu)) { //is main menu
                        //listen click event on the off-canvas button
                        $(self.options.offCanvasBtnSelector).click(
                            function() {
                                self._toggleOffCanvasMenu();
                            }
                        );
                        self._offCanvasMenu();
                    } else {
                        self._onCanvasMenu();
                    }
                },
                exit: function() {
                    //off click event on the off-canvas button
                    $(self.options.offCanvasBtnSelector).off('click');

                    self._onCanvasMenu();
                }
            });
        },

        //off-canvas menu toggle
        _toggleOffCanvasMenu: function() {
            if ($('html').hasClass('nav-open')) {
                $('html').removeClass('nav-open');
                setTimeout(function() {
                    $('html').removeClass('nav-before-open');
                }, this.options.offCanvasHideDelay);
            } else {
                $('html').addClass('nav-before-open');
                setTimeout(function() {
                    $('html').addClass('nav-open');
                }, this.options.offCanvasShowDelay);
            }
        },

        _offCanvasMenu: function() {
            //add extra class 'nav-off-canvas'
            $(this.element).closest('.page-wrapper').addClass('nav-off-canvas');
            //off events binding on the menu items from _onCanvasMenu()
            $(this.element).find(this.options.itemSelector + ", a.mega, span.mega")
                .off('click touchstart mouseenter mouseleave');
            //apply vertical menu
            this._verticalMenu();
        },

        _onCanvasMenu: function() {
            if (this.options.menuType === 'vertical' ||
                this.options.menuType === 'drilldown' ||
                this.options.menuType === 'accordion') {
                this._verticalMenu();
            } else if (this.options.menuType === 'footer_menu') {
                this._footerMenu();
            } else {
                //remove extra class 'nav-off-canvas'
                $(this.element).closest('.page-wrapper').removeClass('nav-off-canvas');
                //apply Horizontal menu
                this._horizontalMenu();
            }
        },

        _updateMenuType: function() {
            //because vertical/horizontal types is not support in Mobile
            if ((this._isMobile() || this._isTabletPortrait()) &&
                (this.options.menuType === 'vertical' || this.options.menuType === 'horizontal')) {
                this.options.menuType = this.options.mobileType;
            }

            if (this.options.offCanvasBreakpoint !== 'all' && this._isTablet()) {
                $(window).on('orientationchange', function(e) {
                    document.location.reload();
                });
            }

            //add extra class by menu type
            $(this.element).addClass(this.options.menuType + "-root");

            //add extra class if sticky enabled
            if (parseInt(this.options.enableSticky) && parseInt(this.options.isMainMenu)) {
                const $pageWrapper = this.element.closest('div.page-wrapper');
                if ($pageWrapper.length) {
                    $pageWrapper.addClass('ub-nav-sticky');
                    //handle states of sticky element
                    const observer = new IntersectionObserver(function(entries) {
                        if (entries[0].intersectionRatio === 0) {
                            $("div.page-wrapper").addClass('sticky-fired');
                            if (!$("div.page-wrapper").hasClass('nav-off-canvas')) {
                                if (!$('div.page-wrapper > .header.content > .sections.nav-sections').length) {
                                    const $nav = $('div.page-wrapper > .sections.nav-sections').first();
                                    $nav.insertAfter('div.page-wrapper > .page-header > .header.content > .logo');
                                    $nav.data('pos-changed', 1);
                                }
                            }
                            // $('div.page-wrapper .panel.wrapper').slideUp(500);
                        } else if (entries[0].intersectionRatio === 1) {
                            $("div.page-wrapper").removeClass('sticky-fired');
                            if (!$("div.page-wrapper").hasClass('nav-off-canvas')) {
                                const $nav = $('div.page-wrapper > .page-header > .header.content > .sections.nav-sections').first();
                                if ($nav.length && $nav.data('pos-changed')) {
                                    $nav.insertAfter('div.page-wrapper > .page-header');
                                }
                            }
                            // $('div.page-wrapper .panel.wrapper').slideDown(500);
                        }
                    }, { threshold: [0, 1] });
                    observer.observe(document.querySelector("#ub-top-bar"));
                }
            }
        },

        _verticalMenu: function() {
            //apply selected menu type
            if (this.options.menuType === 'accordion') {
                this._accordionMenu();
            } else if (this.options.menuType === 'drilldown') {
                this._drillDownMenu();
            } else {
                //apply vertical style as default
                this._commonEvents();
            }
        },

        _horizontalMenu: function() {
            if (this._isTablet()) {
                if (this.options.menuType === 'drilldown') {
                    $(this.options.drillOptions.$container).css('min-height', '');
                }
            }

            this._commonEvents();
        },

        _commonEvents: function() {
            const self = this;

            //reset events
            $(this.element).find("li.has-child").off('touchstart click').children().off('touchstart click');

            //binding common events on tablet and desktop
            const $menuItems = $(this.element).find("a.mega, span.mega");
            const eventName = (this._isTablet()) ? 'click' : 'mouseenter';
            $menuItems.on(eventName, function(e) {
                if ($(e.target).prop('tagName') === 'A') {
                    e.preventDefault();
                    //e.stopPropagation();
                }
                /**
                 * if a menu item used extra css class 'style-tabs'
                 * we will apply tabs style for the children menu items
                 */
                if ($(this).hasClass('style-tabs') || $(this).hasClass('style-tabs-hz')) {
                    self._tabs($(this));
                }
                //if a menu item is a tab head (used extra class 'tab-head')
                if ($(this).hasClass('tab-head')) {
                    const $tabHead = $(this).parent('li.tab-head');
                    self._activeTab($tabHead);
                    sessionStorage.setItem('ubLastOpenedTabId', $tabHead.attr('id'));
                }

                //get current status of menu item
                const status = $(this).data('status');
                //reset status of all menu items
                $menuItems.data('status', '');
                if (self._isTablet()) {
                    if (!$(this).hasClass('has-child') ||
                        ($(this).hasClass('has-child') && status != undefined && status === 'touched')
                    ) {
                        const link = $(this).attr('href');
                        if (link !== undefined && link.length) {
                            window.location.href = link;
                        }
                        return true;
                    } else {
                        $(this).data('status', 'touched');
                    }
                } else {
                    return true;
                }

                return false;
            });

            if (this._isTablet()) {
                //close sub menu items when touched to outside of menu area
                $(document).on('click touchstart', function(e) {
                    //if touched to outside of menu
                    if (!$(e.target).closest(self.options.rootSelector).length) {
                        const $activeItems = $(self.element).find(self.options.itemSelector + '.active');
                        $activeItems.removeClass('active').children().removeClass('active');
                    }
                });
            }

            if (this._isDesktop() || this._isTabletLandscape()) {
                //add extra class 'mega-hover' when mouse hover on menu item on desktop
                $(self.element).find(self.options.itemSelector).each(function(i, el) {
                    if (!$(el).hasClass('tab-head')) {
                        $(el).mouseenter(function() {
                            $(this).addClass('mega-hover');
                        }).mouseleave(function() {
                            $(this).removeClass('mega-hover');
                        });
                    }
                });
            }
        },

        _footerMenu: function() {
            //footer menu
        },

        _tabs: function($item) {
            const self = this;
            //check and get the needed tab to active
            let $activeTab = null;
            const $tabsWrapper = $item.siblings('.child-content').find('ul.level2');

            const $tabHeads = $tabsWrapper.children('li.tab-head');
            $activeTab = $tabHeads.filter(function(k) {
                return ($(this).children('a.mega.active').length ||
                    $(this).children('span.mega.active').length);
            });
            if (!$activeTab.length) {
                $activeTab = $tabHeads.first();
            }

            const lastOpenedId = sessionStorage.getItem('ubLastOpenedTabId');
            if (lastOpenedId) {
                const $openedTab = $tabsWrapper.children('#' + lastOpenedId);
                if ($openedTab.length) {
                    $activeTab = $openedTab;
                }
            }

            if ($activeTab) {
                if (!$activeTab.hasClass('active')) {
                    self._activeTab($activeTab);
                } else {
                    self._resizeTab($activeTab);
                }
            }
        },

        _activeTab: function($tabHead) {
            $tabHead.addClass('active').siblings('li.tab-head').removeClass('active');
            this._resizeTab($tabHead);
        },

        _resizeTab: function($tabHead) {
            //auto set min-height for wrapper of current tabs
            const $tabContent = $tabHead.children('.child-content');
            const minHeight = parseInt($tabContent.outerHeight()) + parseInt($tabContent.css('top'));
            $tabHead.closest('div.child-content').first().css('min-height', minHeight);
        },

        _accordionMenu: function() {
            const self = this;

            //add extra class 'scroll-enabled'
            if (parseInt(self.options.isMainMenu) && (self._isTabletLandscape() || self._isDesktop())) {
                $(this.element).closest(self.options.mainWrapperSelector).addClass('scroll-enabled');
            }

            //binding events on items has sub items
            const eventName = (this._isTablet()) ? 'touchstart' : 'click';
            const $menuItems = $(this.element).find("a.has-child, span.has-child");
            $menuItems.on(eventName, function(e) {
                let preventDefault = false;
                if ($(e.target).prop('tagName') === 'A' || $(e.target).parent("a.mega").length) {
                    preventDefault = true;
                }
                //inactive all siblings elements
                $(this).parent().siblings('.has-child').children().removeClass('active');
                //toggle active for current element
                if (!$(this).hasClass('active')) {
                    $(this).addClass('active').siblings().addClass('active');
                } else {
                    $(this).removeClass('active').siblings().removeClass('active');
                }
                if (preventDefault) {
                    e.preventDefault();
                }
            });

            //bind click event on menu item group links (shop all item)
            const $shopAllItems = $(this.element).find("span.menu-group-link");
            if ($shopAllItems.length) {
                $shopAllItems.on('click', function(e) {
                    const url = $(this).siblings('a').attr('href');
                    if (url !== undefined && url.length && url !== '#') {
                        window.location.href = url;
                        sessionStorage.setItem('ubMenuItemId', $(this).parent(self.options.itemSelector).attr('id'));
                    }
                });
            }
        },

        _drillDownMenu: function() {
            const self = this;

            //wrapper more tags using for drilldown function
            if (!$(self.element).closest('.drilldown-container').length) {
                $(self.element).wrap("<div class='drilldown'><div class='drilldown-container'></div></div>");

                //append drilldown buttons
                let ddButtons = '<div class=\"btn-drilldown\" style="display: none;">';
                ddButtons += '<a class=\"btn-back\" href=\"javascript:void(0);\">' + $t('Back') + '</a>';
                ddButtons += '</div>';
                $(self.element).parent().parent().prepend(ddButtons);

                //set drilldown container element
                self.options.drillOptions.$container = $(self.element).parent("." + self.options.drillOptions.container);

                self.options.drillOptions.$container.closest(".drilldown").delegate(
                    '.btn-drilldown .btn-back',
                    'click',
                    function() {
                        self._up({});
                    }
                );
            }

            //binding events on items has sub items
            const eventName = (this._isTablet()) ? 'touchstart' : 'click';
            const $menuItems = $(this.element).find("a.has-child, span.has-child");
            $menuItems.on(eventName, function(e) {
                let preventDefault = false;
                if ($(e.target).prop('tagName') === 'A' || $(e.target).closest("a.mega").length) {
                    preventDefault = true;
                }

                const $next = $(this).nextAll('.' + self.options.drillOptions.sub);
                if ($next.length) {
                    self._down($next, {});
                } else {
                    preventDefault = false;
                }

                if (preventDefault) {
                    e.preventDefault();
                }
            });

            //fixed styles
            self._autoStyles();
        },

        _down: function($next, opts) {
            const self = this;

            if (!$next.length) {
                return;
            }

            //re-calculate width for drilldown container
            self.options.drillOptions._css.width = $(self.element).outerWidth();
            self.options.drillOptions.$container.width(self.options.drillOptions._css.width * 2);

            //mark parent of the opened node
            $next.parent().attr("data-is-parent", true);

            //get the parent item
            let $parentItem = $next.siblings('a.mega');
            if (!$parentItem.length) {
                $parentItem = $next.siblings('span.mega');
            }

            let $parentElement = null;
            if ($parentItem.attr("href") != undefined) {
                $parentElement = '<a class="parent-item" href="' + $parentItem.attr("href") +
                    '"><span>' + $parentItem.text() + '</span></a>';
            } else {
                $parentElement = '<span class="parent-item">' + $parentItem.text() + '</span>';
            }
            if (!$next.children('.parent-item').length) {
                $next.prepend($parentElement);
            }

            //update needed CSS classes
            $next = $next.removeClass('child-content')
                .removeClass(self.options.drillOptions.sub)
                .addClass(self.options.drillOptions.root)
                .addClass(self.options.extraClass);

            //append to drilldown container
            $next.css('left', (2 * self.options.drillOptions._css.width) + 'px');
            self.options.drillOptions.$container.append($next);

            const speed = (opts && opts.speed !== undefined) ? opts.speed : self.options.drillOptions.speed;
            self._drilling({ marginLeft: (-1 * self.options.drillOptions._css.width) + "px", speed: speed },
                function() {
                    $next.animate({ left: '0px' }, speed, null);
                    const $current = $next.prev();

                    self.options.drillOptions._history.push($current.detach());
                    self._restoreState($next);

                    self.options.drillOptions.$container.parent().parent().addClass('drilling');
                    self.options.drillOptions.$container.parent().find('.btn-drilldown').show();

                    //fixed styles
                    self._autoStyles();
                }
            );
        },

        _up: function(opts) {
            const self = this;

            //re-calculate width for drilldown container
            self.options.drillOptions._css.width = $(self.element).outerWidth();
            self.options.drillOptions.$container.width(self.options.drillOptions._css.width * 2);

            //get node element to backward and prepend in to container
            const $back = self.options.drillOptions._history.pop();

            if ($back === undefined) {
                return;
            }

            $back.css('left', (-1 * self.options.drillOptions._css.width) + 'px');
            self.options.drillOptions.$container.prepend($back);

            const speed = (opts && opts.speed !== undefined) ? opts.speed : self.options.drillOptions.speed;
            self._drilling({ marginLeft: '0px', speed: speed },
                function() {
                    $back.animate({ left: '0px' }, speed, null);

                    const $current = $back.next();
                    $current.addClass(self.options.drillOptions.sub)
                        .removeClass(self.options.drillOptions.root)
                        .removeClass(self.options.extraClass);

                    //restore to the node element at its initial position in the Menu DOM tree
                    self.options.drillOptions.$container.find('[data-is-parent]').last()
                        .removeAttr('data-is-parent')
                        .append($current);
                    $current.find('.menu-group-link').first().hide();
                    $current.find('.drilldown-back').first().hide();
                    self._restoreState($back);

                    if ($back.hasClass('level0')) {
                        self.options.drillOptions.$container.parent().parent().removeClass('drilling');
                        self.options.drillOptions.$container.parent().find('.btn-drilldown').hide();
                    }

                    //fixed styles
                    self._autoStyles();
                }
            );
        },

        _drilling: function(opts, callback) {
            const $menus = this.options.drillOptions.$container.children('.' + this.options.drillOptions.root);
            $menus.css(this.options.drillOptions._css);

            const $menu = $menus.first();
            $menu.animate({ left: opts.marginLeft }, opts.speed, callback);
        },

        _restoreState: function($node) {
            $node.css({
                float: '',
                width: '',
                left: '',
                right: ''
            });
            //reset width of drilldown container
            this.options.drillOptions.$container.css('width', '');
        },

        _isMobile: function() {
            return (this.options.mobileDetect.phone()) ? true : false;
        },

        _isTablet: function() {
            return (this.options.mobileDetect.tablet()) ? true : false;
        },

        _isTabletPro: function() {
            let rs = false;
            if (this._isTablet()) {
                const ratio = window.devicePixelRatio || 1;
                const screen = {
                    width: window.screen.width * ratio,
                    height: window.screen.height * ratio
                };
                rs = (screen.width === 2048 && screen.height === 2732) ||
                    (screen.width === 2732 && screen.height === 2048);
            }

            return rs;
        },

        _isTabletPortrait: function() {
            const rs = (this._isTablet() && matchMedia('all and (orientation:portrait)').matches) ?
                true :
                false;

            return rs
        },

        _isTabletLandscape: function() {
            const rs = (this._isTablet() && matchMedia('all and (orientation:landscape)').matches) ?
                true :
                false;

            return rs
        },

        _isDesktop: function() {
            return (!this._isMobile() && !this._isTablet()) ? true : false;
        },

        _autoStyles: function() {
            const $drillDownRoot = this.options.drillOptions.$container.find('.' + this.options.drillOptions.root);
            let h = $drillDownRoot.outerHeight(true);
            const $backBtn = this.options.drillOptions.$container.siblings('.btn-drilldown');
            if ($backBtn.is(":visible")) {
                h += $backBtn.outerHeight(true);
            }
            $(this.options.drillOptions.$container).css('min-height', h);
        }
    });

    return $.mage.ubMenu;
});