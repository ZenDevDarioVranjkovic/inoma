define([
    'jquery',
    'Mirasvit_LayeredNavigation/js/config',
    'mage/cookies'
], function ($, config) {
    'use strict';

    return {
        leftnavUpdate: function (leftnav) {
            var navigation = '.sidebar.sidebar-main .block.filter, .block.filter, .block-content.filter-content';

            if (leftnav) {
                $(navigation).not(".mst-nav__horizontal-bar .block.filter").replaceWith(leftnav);
                $(navigation).not(".mst-nav__horizontal-bar .block.filter").trigger('contentUpdated');
            }
        },

        filtersUpdate: function (leftnav) {
            var navigation = '.sidebar.sidebar-main .filter-options';

            if (leftnav) {
                const filtersNewContent = $(leftnav).find('.filter-options').prop('outerHTML');
                $(navigation).replaceWith(filtersNewContent);
                $(navigation).trigger('contentUpdated');
            }
        },

        productsUpdate: function (products) {
            if (products) {
                $(config.getAjaxProductListWrapperId()).replaceWith(products);

                // trigger events
                $(config.getAjaxProductListWrapperId()).trigger('contentUpdated');
                $(config.getAjaxProductListWrapperId()).applyBindings();

                setTimeout(function () {
                    // execute after swatches are loaded
                    $(config.getAjaxProductListWrapperId()).trigger('amscroll_refresh');
                }, 500);

                if ($.fn.lazyload) {
                    // lazyload images for new content (Smartwave_Porto theme)
                    $(config.getAjaxProductListWrapperId() + ' .porto-lazyload').lazyload({
                        effect: 'fadeIn'
                    });
                }

                if ($('.lazyload').length && $('.lazyload').unveil !== undefined) {
                    $("img.lazyload").unveil(0, function () {
                        $(this).load(function () {
                            this.classList.remove("lazyload");
                        });
                    });
                }

                // update form_key
                let formKey = $.mage.cookies.get('form_key');

                $('input[name="form_key"]', $(config.getAjaxProductListWrapperId())).each(function (idx, elem) {
                    const $elem = $(elem);
                    if (!formKey) {
                        formKey = $elem.val();
                    }

                    if ($elem.val() !== formKey) {
                        $elem.val(formKey);
                    }
                });
                
                if ($('#mst_categorySearch').length) { // update search input value on filters state change (compatibility with ESU category search)
                    let url = new URL(window.location.href);
    
                    $('#mst_categorySearch').val(url.searchParams.get('q'));
                }
            }
        },

        pageTitleUpdate: function (pageTitle) {
            $('#page-title-heading').closest('.page-title-wrapper').replaceWith(pageTitle);
            $('#page-title-heading').trigger('contentUpdated');
        },

        breadcrumbsUpdate: function (breadcrumbs) {
            $('.wrapper-breadcrums, .breadcrumbs').replaceWith(breadcrumbs);
            $('.wrapper-breadcrums, .breadcrumbs').trigger('contentUpdated');
        },

        updateCategoryViewData: function (categoryViewData) {
            if (categoryViewData === '') {
                return
            }

            if ($(".category-view").length === 0) {
                $('<div class="category-view"></div>').insertAfter('.page.messages');
            } else {
                $(".category-view").replaceWith(categoryViewData);
            }
        },

        updateQuickNavigation: function (quickNavigation) {
            $(".mst-quick-nav__filterList").replaceWith(quickNavigation);
            $(".mst-quick-nav__filterList").trigger('contentUpdated');
        },

        horizontalNavigationUpdate: function (horizontalNav, isHorizontalByDefault) {
            const horizontalNavigation = '.mst-nav__horizontal-bar';

            if (horizontalNav) {
                if (isHorizontalByDefault == 1) {
                    $("#layered-filter-block").remove();
                }

                $(horizontalNavigation).first().replaceWith(horizontalNav);
                $(horizontalNavigation).first().trigger('contentUpdated');
            }
        },

        updateUrlPath: function (targetUrl) {
            targetUrl.replace('&amp;', '&');
            targetUrl.replace('%2C', ',');

            window.mNavigationAjaxscrollCompatibility = 'true';
            window.location = targetUrl;
        },

        updateInstantlyMode: function (data, isHorizontalByDefault) {
            if (data['ajaxscroll'] == 'true') {
                this.updateUrlPath(data.url);
            }

            this.leftnavUpdate(data['leftnav']);
            this.horizontalNavigationUpdate(data['horizontalBar'], isHorizontalByDefault);
            this.productsUpdate(data['products']);
            this.pageTitleUpdate(data['pageTitle']);
            this.breadcrumbsUpdate(data['breadcrumbs']);
            this.updateCategoryViewData(data['categoryViewData']);
            this.updateQuickNavigation(data['quickNavigation']);
        }
    };
});
