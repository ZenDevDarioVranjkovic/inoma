/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'slick',
    'domReady!'
], function ($) {
    'use strict';

    // Handle click event on alphabet letters
    $('#alphabet .letter').click(function(e) {
        e.preventDefault();
        
        // Get the clicked letter
        var letter = $(this).text().toUpperCase();

        // Remove active class from all letters and add it to the clicked letter
        $('#alphabet .letter').parent().removeClass('active-letter');
        $(this).parent().addClass('active-letter');

        // Show only the brands that start with the clicked letter
        $('#names li').each(function() {
            var brandLetter = $(this).text().toUpperCase().charAt(0);
            if (brandLetter === letter) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Optional: Show all brands when clicking "All"
    $('#alphabet .letter-all').click(function(e) {
        e.preventDefault();
        $('#alphabet .letter').parent().removeClass('active-letter');
        $('#names li').show();
    });

    // Automatically trigger the click event on the first letter
    $('#alphabet .letter').first().click();
    $('#alphabet .letter').first().parent().addClass('active-letter');


    $(document).ready(function(){
        if ($(window).width() <= 768) {
            $(".custom-top-brands .pagebuilder-column-line").slick({
                dots: false,
                infinite: false,
                slidesToShow: 3,
                slidesToScroll: 1,
                arrows: false,
                autoplay: true,
                autoplaySpeed: 3000,
                responsive: [
                    {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1
                    }
                    },
                    {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1
                    }
                    }
                ]
            });
        }
    });


    // FAQ TABS

    $('.tab-content').hide();
    $('.tab-content:first').show();

    // Add active class to the first tab
    $('.custom-faq .pagebuilder-column-line:first').addClass('active');

    // On tab title click
    $('.custom-faq .pagebuilder-column-line h3').click(function() {
        $('.custom-faq .pagebuilder-column-line').removeClass('active');
        $(this).parent().parent().addClass('active');
        
    });

    // FAQ TABS

    // Product detail page collapsible tabs

    $('.product.data.items .item.title').on('click', function() {
        // Toggle the 'active' class for clicked title
        $(this).toggleClass('active');

        // Slide toggle the corresponding tab content
        $(this).next('.item.content').slideToggle();
    });

    // Product detail page

});
