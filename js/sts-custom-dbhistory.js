/**
 * StreetScooter cloud System Custom Javascript Functions
 * Pradeep Mohan <Pradeep.Mohan@streetscooter.eu>
 */

jQuery(document).ready(function ($) {

    var $table = $('#db_history_list'),
        // define pager options
        pagerOptions = {
            // target the pager markup - see the HTML block below
            container: $(".pager"),
            // output string - default is '{page}/{totalPages}';
            // possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
            // also {page:input} & {startRow:input} will add a modifiable input in place of the value
            output: '{startRow} - {endRow} / {filteredRows} ({totalRows})',
            // if true, the table will remain the same height no matter how many records are displayed. The space is made up by an empty
            // table row set to a height to compensate; default is false
            fixedHeight: true,
            // remove rows from the table to speed up the sort of large tables.
            // setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with the pager enabled.
            removeRows: false,
            size: 50,
            page: 0,

//		    customAjaxUrl: function(table, url) {
//		    	alert(url);
//		          return url;
//		      },
//		      
            ajaxUrl: 'index.php?action=ajaxRows&page={page}&size={size}&{filterList:filter}&{sortList:column}',
            ajaxProcessing: function (data) {
                if (data && data.hasOwnProperty('rows')) {
                    var indx, r, row, c, d = data.rows,
                        // total number of rows (required)
                        total = data.total_rows,
                        // array of header names (optional)
                        headers = data.headers,
                        // cross-reference to match JSON key within data (no spaces)
                        headerXref = headers.join(',').replace(/\s+/g, '').split(','),
                        // all rows: array of arrays; each internal array has the table cell data for that row
                        rows = [],
                        // len should match pager set size (c.size)
                        len = d.length;
                    // this will depend on how the json is set up - see City0.json
                    // rows
                    for (r = 0; r < len; r++) {
                        row = []; // new row array
                        // cells
                        for (c in d[r]) {
                            if (typeof (c) === "string") {
                                // match the key with the header to get the proper column index
                                indx = $.inArray(c, headerXref);
                                // add each table cell data to row array
                                if (indx >= 0) {
                                    row[indx] = d[r][c];
                                }
                            }
                        }
                        rows.push(row); // add new row array to rows array
                    }
                    // in version 2.10, you can optionally return $(rows) a set of table rows within a jQuery object
                    return [total, rows, headers];
                }
            },
            processAjaxOnInit: true,
            // go to page selector - select dropdown that sets the current page
            cssGoto: '.gotoPage'
        };

    // Initialize tablesorter
    // ***********************
    $table
        .tablesorter({
            theme: 'default',
            headerTemplate: '{content} {icon}', // new in v2.7. Needed to add the bootstrap icon!
//		      widthFixed: true,
            widgets: ['zebra', 'filter', 'resizable'],
            widgetOptions: {
                resizable_addLastColumn: true
            }
        })

        // initialize the pager plugin
        // ****************************
        .tablesorterPager(pagerOptions);


});