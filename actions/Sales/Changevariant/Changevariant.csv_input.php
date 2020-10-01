<form method="post" enctype="multipart/form-data" name="mainForm" action="<?php echo $_SERVER['PHP_SELF']; ?>"
      id="id_Form">
    <input type="hidden" id="id_command" name="command" value=""/>
    <input type="hidden" name="MAX_FILE_SIZE" value="30000"/>
    <input type="hidden" name="action" value="<?php echo $this->action; ?>"/>

    <div style="position:relative;top:0px;left:50px">
        <h2><?php $this->EchoText('CSVHDL_1'); ?></h2>

        <div class="horiznt">
            <?php
            $this->csvTool->WriteHtml_CsvUploadTable(700, 400);
            //echo $this->csvTool->GetHtml_ErrorBox ();
            ?>
        </div>

        <div class="horiznt" style="margin-left:30px;width:200px;">
            <?php echo $this->csvTool->GetHtml_ErrorBox(); ?>
        </div>

    </div>
</form>
<p>&nbsp;</p>
