<?php
/**
 * upload2.php
 * @author FEV
 */
?>

<?php include $_SERVER['STS_ROOT'] . "/pages/menu/qs.menu.php"; ?>

<div class="inner_container"></div>
<div class="row ">
    <div class="columns six">
        <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
    </div>
</div>

<div class="row">
    <div class="columns five">
        <h1><?php echo $this->translate['uploadHR']['header']; ?></h1>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST') : ?>
            <h4><?php echo $this->translate['uploadHR']['uploadResults']; ?></h4>
            <?php echo $this->uploadResultString ?>
            <hr/>
        <?php endif ?>
        <p><?php echo $this->translate['uploadHR']['headerNote']; ?></p>
        <!--        <p class="error-message">Use this form to upload a new CSV file and import into database</p>-->
        <form enctype="multipart/form-data" action="/?action=upload2" method="post">
            <h4><?php echo $this->translate['uploadHR']['title']; ?></h4>
            <p><input type="file" name="csvfile"/></p>
            <p><input type="submit" value=<?php echo $this->translate['uploadHR']['btnUpload']; ?>></p>
        </form>
        <h4><?php echo $this->translate['uploadHR']['exampleFile']; ?></h4>
        <code>
          <?php echo $this->translate['uploadHR']['code']; ?>
        </code>
        <p><?php echo $this->translate['uploadHR']['note']; ?></p>

        <!--        <a href="#" onClick="window.history.back();">Back</a>-->
    </div>

    <div class="columns seven">
        <h1><?php echo $this->translate['uploadHR']['listHeader']; ?></h1>
        <?php if (is_array($this->storedHRList)): ?>
            <h4><?php echo count($this->storedHRList) ?></strong><?php echo $this->translate['uploadHR']['listTitle']; ?></h4>
            <p><a href="#" data-target="hr_list_content" class="parent_hidden_text">
                    <span class="genericon genericon-plus"> </span><span><?php echo $this->translate['uploadHR']['showList']; ?></span>
                </a></p>
            <div class="child_hidden_text hr_list_content">
                <table>
                    <tr>
                        <th><?php echo $this->translate['uploadHR']['listName']; ?></th>
                        <th><?php echo $this->translate['uploadHR']['listOrganizationID']; ?></th>
                        <th><?php echo $this->translate['uploadHR']['listBusinessUnit']; ?></th>
                        <th><?php echo $this->translate['uploadHR']['listStatus']; ?></th>
                        <th><?php echo $this->translate['uploadHR']['listLeader']; ?></th>
                        <th><?php echo $this->translate['uploadHR']['listDeputyID']; ?></th></th>
                    </tr><?php foreach ($this->storedHRList as $record): ?>
                        <tr>
                        <td><?php echo $record['person']; ?></td>
                        <td><?php echo $record['organization_id']; ?></td>
                        <td><?php echo $record['business_unit']; ?></td>
                        <td><?php echo $record['kind']; ?></td>
                        <td><?php echo $record['is_leader']; ?></td>
                        <td><?php echo $record['deputy_organization_id']; ?></td>
                        </tr><?php endforeach ?>
                </table>
            </div>
        <?php else: ?>
            <p><?php echo $this->translate['uploadHR']['noDataNote']; ?></p><p><?php echo $this->translate['uploadHR']['noDataTip']; ?></p>
        <?php endif ?>
    </div>
</div>


<style>
    .error-message {
        padding: 20px;
        background-color: #f45f42;
        color: white;
    }

    .success-message {
        padding: 20px;
        background-color: #34a34a;
        color: white;
    }

    .info-message {
        padding: 20px;
        background-color: #34a3f0;
        color: white;
    }
</style>