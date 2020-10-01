<?php
/**
 * Created by PhpStorm.
 * User: Jakub Kotlorz, FEV
 * Date: 2/26/19
 * Time: 3:30 PM
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
        <h1>Sets</h1>
        <p>Choose from list below:</p>
        <form action="" id="">
            <select id="sets" name="category" style="width: 100%;">
                <option value="0">---</option>
                <?php foreach ($this->getConversionTableSets() as $set) { ?>
                    <option value="<?php echo $set['id']; ?>"><?php echo $set['id'];
                        if ($set['frozen'] == "t") echo " - frozen"; ?></option>
                <?php } ?></select>
            </select>
        </form>
    </div>
    <div class="columns ten">
        <div class="row">
            <h1 id="history_header">Conversion Table</h1>
        </div>
        <div id="conversionTable" class="row">
            <p>Choose conversion table set</p>
        </div>
    </div>
</div>

<script>

    //
    // after document loads
    //
    $(function () {

        var selectedSet = 0;

        //
        // User selects SET
        //
        $('#sets').on('change', function () {

            selectedSet = $(this).find("option:selected").val();

            $('#conversionTable').children().remove();

            if (selectedSet == 0) {
                $('#conversionTable').html("<p>Choose conversion table set</p>");
            } else {
                $.ajax({
                    method: "GET",
                    url: "index.php",
                    data: {
                        action: '<?php echo $this->action; ?>',
                        method: 'ajaxGetConversionTableSet',
                        set_id: selectedSet
                    },
                    dataType: "json"
                })
                    .done(function (res) {
                        if (res == null) {
                            var list = $('<p>').text('This set is empty');
                        } else {
                            var list = $('<table>');
                            let row = $('<tr>')
                                .append($('<th>').text("Code"))
                                .append($('<th>').text("Character"));
                            list.append(row);

                            for (let i = 0; i < res.length; i++) {
                                let row = $('<tr>')
                                    .append($('<td>').text(res[i]['conversion_key']))
                                    .append($('<td>').text(res[i]['conversion_value']));
                                list.append(row);
                            }
                        }
                        $('#conversionTable').append(list);
                    });
            }
        });
    });

</script>

<style>

    table {
        border: 1px solid black;
        table-layout: fixed;
        width: 200px;
    }

    th, td {
        border: 1px solid black;
        width: 100px;
    }

</style>