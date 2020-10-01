<?php
/**
 * Created by PhpStorm.
 * User: Jakub Kotlorz, FEV
 * Date: 2/1/19
 * Time: 10:37 AM
 */
?>

<?php
include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";
?>

<div class="inner_container"></div>
<div class="row ">
    <div class="columns six">
        <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
    </div>
</div>

<div class="row">
    <div class="columns two">
        <h1>Categories</h1>
        <p>Choose from list below:</p>
        <form action="" id="adminhistory_cat">
            <select id="adminCategories" name="category" style="width: 100%;">
                <option value="0">---</option>
                <option value="userrole2Company">Company structures</option>
                <option value="responsiblePersons">Responsible persons</option>
                <option value="manageFunctions">Management functions</option>
            </select>
        </form>
    </div>
    <div class="columns eight">
        <div class="row">
            <h1 id="history_header">Admin history</h1>
        </div>
        <div id="history_content" class="row">
            <p>Choose category</p>
        </div>
    </div>
</div>

<script>

    let selectedCategoryId;
    let selectedCategoryName;

    //
    // refresh main table values
    //
    function refreshHistory(context) {

        $.ajax({
            method: "GET",
            url: "index.php",
            data: {
                action: '<?php echo $this->action; ?>',
                method: 'ajaxGetHistory',
                context: context
            },
            dataType: "json"
        })
            .done(function (historyValues) {

                let historyTable = $('<table>').addClass('historyTable');
                let headerRow = $('<tr>').addClass('historyTableRow');
                headerRow.append($('<th>').text("Person"));
                headerRow.append($('<th>').text("Date"));
                headerRow.append($('<th>').text("Description"));
                historyTable.append(headerRow);

                for (let i = 0; i < historyValues.length; i++) {
                    let row = $('<tr>').addClass('historyTableRow');
                    row.append($('<td>').text(historyValues[i]['username']));
                    row.append($('<td>').text(historyValues[i]['posting_date']));
                    row.append($('<td>').text(historyValues[i]['description']));
                    historyTable.append(row);
                }

                $('#history_content').children().remove();
                $('#history_content').html(historyTable);
            });
    }

    //
    // User changes category
    //
    $('#adminCategories').on('change', function () {

        selectedCategoryId = $(this).find("option:selected").val();
        selectedCategoryName = $(this).find("option:selected").text();

        if (selectedCategoryId == 0) {
            $('#history_header').text("Admin history");
            $('#history_content').children().remove();
            $('#history_content').html("<p>Choose category</p>");
        } else {
            $('#history_header').text(selectedCategoryName);

            refreshHistory(selectedCategoryId);
        }
    });

    //
    // after document loads
    //
    $(document).ready(function () {
        ;
    });

</script>

<style>
    .historyTable {;
    }

    .historyTableRow {;
    }
</style>