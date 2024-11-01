/*global jQuery */
/*jslint devel: true, node: true */

"use strict";
var hreflang = {
    
    loadposts: function (blogid, postid, container, cat, page) {
        
        jQuery('#hreflang-loading').css('display', 'inline');
        
        if (page === undefined) {
            page = 1;
        }
        
        var checkcat = jQuery('#wphpmb-check').attr('checked');
        var samedate = jQuery('#wphpmb-date').attr('checked');
        
        jQuery('#wphpmb-search').attr('disabled', false);
        jQuery('#hreflang-reload').attr({'value': blogid, 'disabled': false});
        
        var title_filter = jQuery('#wphpmb-search').val();
        
        jQuery.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'post',
            data: {
                action: 'get_posts',
                data: blogid,
                post: postid,
                cat: (checkcat === 'checked' ? cat : null),
                sd: (samedate === 'checked' ? "true" : "false"),
                s: (title_filter !== ''? title_filter : null),
                page: page
            },
            success: function (result) {
                jQuery('#' + container).html(result);
                jQuery('#hreflang-loading').css('display', 'none');
            }
        });
    },
    
    removeMap: function (data) {
                
        jQuery.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'post',
            data: {
                action: 'remove_mapping',
                payload: data
            },
            success: function (result) {
                result = result.split(";");
                hreflang.loadposts(result[0], result[1], 'wphpmb', result[2]);
            }
        });
        
    },
    
    mappost: function (data) {
        
        jQuery.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'post',
            data: {
                action: 'store_mapping',
                payload: data
            },
            success: function (result) {
                
                //reset background colors on rows
                jQuery('.hreflang tr').removeClass('rowselected');
                
                //assign class to newly selected row, plus the next tr
                jQuery('.hreflang td input').filter(function () {
                    return jQuery(this).val() === result;
                }).parent().parent().addClass('rowselected').next().addClass('rowselected');
                
                //and enable the remove button, if not already.
                jQuery('#hrlbutrmpar').attr('disabled', false).addClass('button-primary');
            }
        });
    }
    
};

//assign the hreflang object to EburyLabs namespace
if (typeof window.EburyLabs !== 'object') {
    var EburyLabs = {
        hreflang: hreflang
    };
} else {
    EburyLabs.hreflang = hreflang;
}