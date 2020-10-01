$(function () {
    /**
     * add new row to search form
     */
    $('body').on('click', '#new_row_butt', function (event) {
        $('#main_form_row').clone().appendTo('#new_row_inputs');
        $('.search_value').last().val("").change();
        $('.search_value_bool').last().val("-").change();
        $('.search_not_bool').last().val("-").change();
        $('.search_value').last().hide("fast");
        $('.search_value_bool').last().hide("fast");
        $('.search_not_bool').last().hide("fast");
        $('.more_parameters').last().empty();
        $('.more_parameters').last().attr('index', $('.more_parameters').length - 1);
        $('.general_search_cat_diag').last().hide('fast');
        $('.general_search_cat_dtc').last().hide('fast');
        $('.general_search_cat_qs_errors').last().hide('slow');
        $('.select_operator:not(:first)').show('fast');
        $('.select_operator:first').hide('fast');
        $('a.general_search_hidden:not(:first)').show('fast');
        //regex clear
        $('.regex').last().prop('disabled', false);
        $('.regex').last().attr('checked', false);
    });


    /**
     * delete added rows to search criteria
     */
    $('body').on('click', '#delete_all_fields', function (event) {
        $('#new_row_inputs').children().remove();
        $('.select_operator').hide('fast');
    });


    /**
     * delete current/clicked row
     */
    $('body').on('click', '.click_delete_row', function (event) {
        $(this).parent().remove();
        //$('.select_operator:first').hide('fast');
    });


    /**
     * on-change first search select field
     */
    $('body').on('change', '.general_search_cat', function (event) {
        // $('.more_parameters').children().not('.search_index').remove();
        //$(this).parent().find(".more_parameters").children().not('.search_index').remove();
        $(this).parents('div').children('.more_parameters').children().hide('fast', function () {
            $(this).remove();
        });
        var selected = $(this).find("option:selected").val();

        $(this).parent().children('.search_value').hide('fast');
        $(this).parent().children('.search_value_bool').hide('fast');
        $(this).parent().children('.search_not_bool').hide('fast');
        $(this).parent().children('.general_search_cat_qs_errors').hide('fast');
        $(this).parent().children('.general_search_cat_diag').hide('fast');
        $(this).parent().children('.general_search_cat_dtc').hide('fast');

        $(this).parent().children('.search_value').val('').change();
        $(this).parent().children('.search_value_bool').val('-').change();
        $(this).parent().children('.search_not_bool').val('-').change();

        switch (selected) {
            case 'vin':
            case 'ikz':
            case 'penta_kennwort':
            case 'c2cbox' :
            case 'vehicle_variants.variant' :
            case 'penta_numbers.penta_number' :
            case 'processed_teo_status.processed_diagnose_status' :
                $(this).parent().children('.search_value').show('slow');
                $(this).parent().children('.search_not_bool').show('slow');
                $(this).parent().children('.regex').prop('disabled', false);
                break;
            case 'special_qs_approval' :
            case 'finished_status' :
            case 'qmlocked' :
                $(this).parent().children('.search_value_bool').show('slow');
                $(this).parent().children('.search_not_bool').show('slow');
                $(this).parent().children('.regex').prop('disabled', true);
                $(this).parent().children('.regex').prop('checked', false);
                break;
            default:

        }


        if (selected == 'mechanical_errors') {
            $(this).parent().children('.general_search_cat_qs_errors').val("0").change();
            $(this).parent().children('.general_search_cat_qs_errors').show('slow');
            //$('.more_parameters').append("<div class='search_index' index='" + $(".search_index").length + "'></div>");
            $(this).parent().children('.general_search_cat_diag').hide('fast');
            $(this).parent().children('.general_search_cat_dtc').hide('fast');
            //regex
            $(this).parent().children('.regex').prop('disabled', false);
            //$(this).parent().children('.search_value').hide('fast');
            //$(this).parent().children('.search_value_bool').hide('fast');
            //$(this).parent().children('.search_not_bool').hide('fast');
            /** var div = $(this).parents('div').children('.more_parameters');
             $.ajax({
                    type: "GET",
                    url: "index.php?action=ajax&method=runFailuresSubCats",
                    data: {
                        qs_cat_id: 11,
                        qs_subcat_id: 0
                    }
                })
             .done(function( data ) {
                        var o = JSON.parse(data);

                        var str = '<fieldset>';

                        if(o.subcat_fields) {
                            str = str + '<select name="sub_cat_id" class="general_search_subcatfields_qs_errors">';
                            str += '<option value="0">---</option>';
                            $.each(o.subcat_fields, function (key, value) {
                                str = str + '<option value="' + key + '">' + value + '</option>';
                            });
                            str = str + '</select>';
                        }

                        str = str + '</fieldset>';
                        div.append(str);
                    }); **/

        } else if (selected == 'diagnostic') {
            //$(this).parent().children('.search_value').show('slow');
            //$(this).parent().children('.search_value_bool').show('slow');
            //$(this).parent().children('.search_not_bool').show('slow');
            //if( $(this).find( "option:selected" ).val() == 'diagnostic' ) {
            $(this).parent().children('.general_search_cat_diag').val("0").change();
            $(this).parent().children('.general_search_cat_qs_errors').hide('fast');
            $(this).parent().children('.general_search_cat_diag').show('slow');
            //regex
            $(this).parent().children('.regex').prop('disabled', false);
            /*} else {
                $(this).parent().children('.general_search_cat_qs_errors').hide('fast');
                $(this).parent().children('.general_search_cat_diag').hide('fast');
                $(this).parent().children('.general_search_cat_dtc').hide('fast');
            }*/
        }

    });


    /**
     * on-change DTC diagnose search select field
     */
    $('body').on('change', '.general_search_cat_diag', function (event) {
        $(this).parent().children('.search_not_bool').val('-').change();
        $(this).parent().children('.search_value').val('').change();
        if ($(this).find("option:selected").val() == 'ecu_dtc_pairs') {
            $(this).parent().children('.general_search_cat_dtc').show('slow');
            $(this).parent().children('.search_not_bool').show('slow');
            $(this).parent().children('.search_value').show('slow');

        } else {
            if ($(this).find("option:selected").val() == 'ecu_log_pairs') {
                $(this).parent().children('.general_search_cat_dtc').show('slow');
                $(this).parent().children('.search_not_bool').show('slow');
                $(this).parent().children('.search_value').show('slow');

            } else {
                $(this).parent().children('.general_search_cat_dtc').hide("fast");
                $(this).parent().children('.search_not_bool').hide('fast');
                $(this).parent().children('.search_value').hide('fast');
            }
        }
    });

    $('body').on('change', '.general_search_subcat2_qs_errors', function (event) {
        var div = $(this).parents('div').children('.more_parameters');
        //$('.more_parameters').children().not(':first').hide('fast', function(){ $(this).remove(); });
        div.children().not(':first').hide('fast', function () {
            $(this).remove();
        });
        var subcat = $(this).val();
        var index = div.attr('index');
        $.ajax({
            type: "GET",
            url: "index.php?action=ajax&method=runFailuresSubCats",
            data: {
                qs_cat_id: 2,
                qs_subcat_id: 0
            }
        })
            .done(function (data) {
                var o = JSON.parse(data);

                var str = '<fieldset>';

                if (o.fields) {
                    if (subcat != "") {
                        $.each(o.subcat_fields, function (key, value) {
                            str = str + '<span><label>' + value + ' </label><input type="text" name="qs_faults_search_input[' + index + '][' + subcat + '][' + key + ']" />&nbsp;&nbsp;&nbsp;</span>';
                        })
                    }
                }

                str = str + '</fieldset>';

                div.append(str).show('slow');
            });
    });

    $('body').on('change', '.general_search_subcat16_qs_errors', function (event) {
        var div = $(this).parents('div').children('.more_parameters');
        // $('.more_parameters').children().not(':nth-child(1)').not(':nth-child(2)').hide('fast', function(){ $(this).remove(); });
        div.children().not(':nth-child(1)').not(':nth-child(2)').hide('fast', function () {
            $(this).remove();
        });
        var index = div.attr('index');

        if ($(this).val() == "sonstiges") {

            var str = '<fieldset>';

            str = str + '<span><label>Text </label><input type="text" name="qs_faults_search_input[' + index + '][12][sonstiges_text]" />&nbsp;&nbsp;&nbsp;</span>';

            str = str + '</fieldset>';

            div.append(str).show('slow');
        }
    });

    /**
     * on-change first search select field
     */
    $('body').on('change', '.general_search_cat_qs_errors, .general_search_subcat_qs_errors', function (event) {
        var selectedValue = $(this).val();
        var subcategory = 0;
        var select = $(this).val();
        var div = $(this).parents('div').children('.more_parameters');
        var index = div.attr('index');
        var subcat11 = false;

        //div.children().remove();
        if (this.className == "general_search_subcat_qs_errors") {
            subcategory = selectedValue;
            selectedValue = 11;
            subcat11 = true;
            //$('.more_parameters').children().not(':first').not(':last').remove();
            div.children().not(':first').hide('fast', function () {
                $(this).remove();
            });
        } else {
            //$('.more_parameters').children().not(':last').remove();
            div.children().hide('fast', function () {
                $(this).remove();
            });
            if (selectedValue == 2) {
                subcategory = 0;
                selectedValue = 2;
            }
        }

        if (!(this.className != "general_search_subcat_qs_errors" && $(this).val() == 0)) {
            $.ajax({
                type: "GET",
                url: "index.php?action=ajax&method=runFailuresSubCats",
                data: {
                    qs_cat_id: selectedValue,
                    qs_subcat_id: subcategory
                }
            })
                .done(function (data) {
                    var o = JSON.parse(data);

                    var str = '<fieldset>';

                    if (o.fields) {

                        $.each(o.fields, function (key, value) {
                            //if (selectedValue == 11) {
                            str = str + '<span><label>' + value + ' </label><input type="text" name="qs_faults_search_input[' + index + '][' + select + '][' + key + ']" />&nbsp;&nbsp;&nbsp;</span>';
                            /*} else {
                                str = str + '<span><label>' + value + '</label><input type="text" name="qs_faults_search_input[' + key + ']" />&nbsp;&nbsp;&nbsp;</span>';
                            }*/
                        })
                    }

                    if (o.subcat) {
                        myArray = Object.entries(o.subcat);
                        if (subcategory != 0 || select == '17') {
                            str = str + '<select name="qs_faults_search_input[' + index + '][' + select + '][' + o.subcat + ']" ' +
                                'class="general_search_subcat16_qs_errors">';
                            str += '<option value="">---</option>';

                            if (select == '17') {
                                $.ajax({
                                    type: "GET",
                                    url: "index.php?action=ajax&method=getAllEcus",
                                    dataType: 'json',
                                    success: function (data) {
                                        $.each(data, function (key, value) {
                                            str += '<option value="' + value.name + '">' + value.name + '</option>';
                                        })

                                        str = str + '</select>';
                                        str = str + '</fieldset>';
                                        div.append(str).show('slow');
                                    }
                                });
                            } else {
                                $.each(o.subcat, function (key, value) {
                                    str = str + '<option value="' + key + '">' + value + '</option>';
                                });

                                str = str + '</select>';
                                str = str + '</fieldset>';
                                div.append(str).show('slow');
                            }
                        } else {
                            if ($.isArray(myArray)) {
                                if (selectedValue == 2 && subcategory == 0) {
                                    str = str + '<select name="child_cat[' + index + '][' + selectedValue + ']" class="general_search_subcat2_qs_errors">';
                                    str += '<option value="">---</option>';
                                    $.each(o.subcat, function (key, value) {
                                        str = str + '<option value="' + key + '">' + value + '</option>';
                                    });
                                } else if (selectedValue == 16 && subcategory == 0) {
                                    str = str + '<select name="qs_faults_search_input[' + index + '][' + selectedValue + '][' + o.subcat_name + ']" class="general_search_subsubcat_qs_errors">';
                                    str += '<option value="">---</option>';
                                    $.each(o.subcat, function (key, value) {
                                        str = str + '<option value="' + key + '">' + value + '</option>';
                                    });
                                } else {
                                    if (!(subcategory == "" && selectedValue == 11 && subcat11 == true)) {
                                        str = str + '<select name="child_cat[' + index + '][' + select + ']" class="general_search_subcat_qs_errors">';
                                        str += '<option value="">---</option>';
                                        $.each(o.subcat, function (key, value) {
                                            str = str + '<option value="' + key + '">' + value.label + '</option>';
                                        });
                                    }
                                }

                            } else {

                                str = str + '<select name="sub_cat_id["+index+"]">';
                                str += '<option value="">---</option>';
                                $.each(o.subcat, function (key, value) {
                                    str = str + '<option value="' + key + '">' + value + '</option>';
                                })
                            }

                            str = str + '</select>';
                            str = str + '</fieldset>';
                            div.append(str).show('slow');
                        }
                    }
                });

        }
    });


});
