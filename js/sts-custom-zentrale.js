jQuery(document).ready(function ($) {
    $('#deliveryplanshow').tablesorter({
        widgets: ['zebra', 'scroller'],
        widgetOptions: {
            // scroll tbody to top after sorting
            scroller_upAfterSort: true,
            // pop table header into view while scrolling up the page
            scroller_jumpToHeader: true,

            scroller_height: 500,
            // set number of columns to fix
            scroller_fixedColumns: 2,
            // add a fixed column overlay for styling
            scroller_addFixedOverlay: false,
            // add hover highlighting to the fixed column (disable if it causes slowing)
            scroller_rowHighlight: 'hover',

            // bar width is now calculated; set a value to override
            scroller_barWidth: null
        }
    });

    $('#overview_assignment').tablesorter({
        widgets: ['zebra', 'scroller'],
        widgetOptions: {
            // scroll tbody to top after sorting
            scroller_upAfterSort: true,
            // pop table header into view while scrolling up the page
            scroller_jumpToHeader: true,

            scroller_height: 500,
            // set number of columns to fix
            scroller_fixedColumns: 3,
            // add a fixed column overlay for styling
            scroller_addFixedOverlay: false,
            // add hover highlighting to the fixed column (disable if it causes slowing)
            scroller_rowHighlight: 'hover',

            // bar width is now calculated; set a value to override
            scroller_barWidth: null
        }
    });


});
