<?php
/**
 * Created by PhpStorm.
 * User: Jakub Kotlorz, FEV
 * Date: 2/1/19
 * Time: 10:37 AM
 */
?>

<style>
    <?php include "../css/parameterlist.css"; ?>
</style>

<div id="search_window" class="seitenteiler">
    <fieldset class="">
        <legend class="collapsible"><span class="genericon genericon-expand"></span>Search Vehicle Configuration
        </legend>
        <!--            <div id="search_window_collapsible" class="collapsible_content">-->
        <div id="search_window_collapsible" class="">

            <div class="search" style="display:inline-block">
                <label for="inp_search_veh_conf">Search configuration</label><br>
                <input id="inp_search_veh_conf" name="inp_search_veh_conf" type="text" value="" style="">
            </div>
            <br>
            <div class="selectors" style="display:inline-block">
                <label for="sel_type">Type</label><br>
                <select id="sel_type" size="10" style=""></select>
            </div>
            <div class="selectors" style="display:inline-block">
                <label for="sel_year">Year</label><br>
                <select id="sel_year" size="10" style=""></select>
            </div>
            <div class="selectors" style="display:inline-block">
                <label for="sel_series">Series</label><br>
                <select id="sel_series" size="10" style=""></select>
            </div>

            <div class="options-bar">
                <ul class="submenu_ul">
                    <li><span id="btn_search_veh_conf" class="sts_submenu W140 disabled">Search</span></li>
                </ul>
            </div>
            <br>
            <div class="" style="">
                <label for="sel_search_result_list">Search Result</label><br>
                <select id="sel_search_result_list" size="10" style="width: 100%"></select>
            </div>

            <div class="options-bar">
                <ul class="submenu_ul">
                    <li><span id="btn_select_vehicle_configuration" class="sts_submenu W140 disabled">Select Configuration</span>
                    </li>
                </ul>
            </div>

        </div>
    </fieldset>
</div>
<script>
    var selectedType = '';
    var selectedYear = '';
    var selectedSeries = '';


    $(document).ready(function () {
        console.log("i am ready");
        getTypeList();
    });

    $('#btn_search_veh_conf').on('click', function (e) {
        if ($(this).hasClass('disabled'))
            return false;
        let searchInputText = $('#inp_search_veh_conf').val();

        if (searchInputText.length) {
            getSearchListResultByTextSearch(searchInputText.toUpperCase());
        } else {
            getSearchListResult(selectedType, selectedYear, selectedSeries);
        }
    });

    // $('#btn_select_vehicle_configuration').on('click', function () {
    //     if ($(this).hasClass('disabled'))
    //         return false;
    //
    //     let selectedConfiguration = $('#sel_search_result_list').val();
    //     executeSelectedOperationOnVC(selectedConfiguration, '0');
    // });


    $('#sel_type').on('change', function (event) {
        selectedType = $(this).find("option:selected").val();
        getYearList(selectedType);
        $('#sel_series').empty();
    });

    $('#sel_year').on('change', function (event) {
        selectedType = $('#sel_type').find("option:selected").val();
        selectedYear = $(this).find("option:selected").val();

        getSeriesList(selectedType, selectedYear);
    });

    $('#sel_series').on('change', function (event) {
        selectedType = $('#sel_type').find("option:selected").val();
        selectedYear = $('#sel_year').find("option:selected").val();
        selectedSeries = $(this).find("option:selected").val();

        enableSearchButton();
    });

    $('#sel_search_result_list').on('change', function (event) {
        enableSelectButton();
    });

    $('#inp_search_veh_conf').on('input', function (event) {
        if ($('#inp_search_veh_conf').val()) enableSearchButton();
        else disableSearchButton();
    });


    function getTypeList() {
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetTypesList',
            },
            dataType: "json"
        })
            .done(function (ajaxResultData) {
                fillSelectList('sel_type', ajaxResultData);
            });
    }

    function getYearList(vehicleType) {
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetYearsList',
                vehicleType: vehicleType
            },
            dataType: "json"
        })
            .done(function (ajaxResultData) {
                fillSelectList('sel_year', ajaxResultData);
            });
    }

    function getSeriesList(vehicleType, vehicleYear) {
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetSeriesList',
                vehicleType: vehicleType,
                vehicleYear: vehicleYear
            },
            dataType: "json"
        })
            .done(function (ajaxResultData) {
                fillSelectList('sel_series', ajaxResultData);
            });
    }

    function getSearchListResult(vehicleType, vehicleYear, vehicleSeries) {
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetSearchResult',
                vehicleType: vehicleType,
                vehicleYear: vehicleYear,
                vehicleSeries: vehicleSeries
            },
            dataType: "json"
        })
            .done(function (ajaxResultData) {
                fillSelectConfigurationBoxList(ajaxResultData);
            });
    }

    function getSearchListResultByTextSearch(textSearch) {
        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetSearchResultByTextInput',
                textSearch: textSearch
            },
            dataType: "json"
        })
            .done(function (ajaxResultData) {
                fillSelectConfigurationBoxList(ajaxResultData);
            });
    }

    function enableSearchButton() {
        $('#btn_search_veh_conf').removeClass("disabled");
    }

    function disableSearchButton() {
        $('#btn_search_veh_conf').addClass("disabled");
    }

    function enableSelectButton() {
        $('#btn_select_vehicle_configuration').removeClass("disabled");
    }

    function disableSelectButton() {
        $('#btn_select_vehicle_configuration').addClass("disabled");
    }

    function fillSelectList(elementId, dataToList) {
        let select = document.getElementById(elementId);
        $('#' + elementId).empty();

        console.log(dataToList);
        if (dataToList.length) {
            dataToList.forEach(function (singleType) {
                if (singleType.length)
                    select.options[select.options.length] = new Option(singleType, singleType);
            })
        } else {
            select.options[select.options.length] = new Option('-', '-');
        }
    }

    function fillSelectConfigurationBoxList(dataToList) {
        $('#sel_search_result_list option').remove();
        let select = $('#sel_search_result_list');
        $.each(dataToList, function (index, value) {
            if (index != 0) {
                select.append('<option value="' + index + '" disabled>' + value['vehicle_configuration_key'] + '</option>');
                $.each(value['minors'], function (index1, value1) {
                    select.append('<option value="' + value1['sub_vehicle_configuration_id'] + '">---> ' + value1['name'] + '</option>');
                })
            }
        });
    }


</script>

<style>
    .group {
        display: block;
    }

    .historyTable {;
    }

    .historyTableRow {;
    }

    .assigned-structure {
        color: #8e4125;
    }

    .success-message {
        padding: 20px;
        background-color: #34a34a;
        color: white;
    }

    .options-bar {
        text-align: center;
    }

    .options-bar .submenu_ul li {
        padding: 20px;
    }

    .selectors {
        width: calc(100% / 3 - 2px);
    }

    .selectors > select {
        width: 100%;
    }

    .search {
        width: 100%;
    }

    .search > input {
        width: 100%;
        box-sizing: border-box;
    }
</style>