<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 2/15/19
 * Time: 8:35 AM
 */
?>
<?php
include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";
?>

<?php
$this->uploadKeys();
?>
<div class="inner_container"></div>
<div class="row ">
    <div class="columns six">
        <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
    </div>
</div>
<style>
    .ui-tabs-vertical {
        width: 100%;
    }

    .ui-tabs-vertical .ui-tabs-nav {
        padding: .2em .1em .2em .2em;
        float: left;
        width: 13.3333333333%;
    }

    .ui-tabs-vertical .ui-tabs-nav li {
        clear: left;
        width: 100%;
        border-bottom-width: 1px !important;
        border-right-width: 0 !important;
        margin: 0 -1px .2em 0;
    }

    .ui-tabs-vertical .ui-tabs-nav li a {
        display: block;
    }

    .ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active {
        padding-bottom: 0;
        padding-right: .1em;
        border-right-width: 1px;
    }

    .ui-tabs-vertical .ui-tabs-panel {
        padding: 1em;
        margin-left: 15%;
        width: 65.3333333333%;
    }

    label {
        font-weight: bold;
    }
</style>

<div class="row">
    <div id="tabs">
        <ul>
            <li><a href="#tabs-1">Upload server keys</a></li>
            <li><a href="#tabs-2">Download server keys</a></li>
        </ul>
        <div id="tabs-1">
            <h1>Upload server keys</h1>
            <p>Upload public and private keys</p>
            <form action="<?php echo $_SERVER['REQUEST_URI'] ?>#tabs-1" id="upload_server_keys" method="POST"
                  enctype="multipart/form-data">
                <label for="privateKey">Private key</label>
                <input type="file" name="privateKey" id="privateKey" required>
                <label for="publicKey">Public key</label>
                <input type="file" name="publicKey" id="publicKey" required> <br/><br/>
                <input type="hidden" name="hiddenKey">
                <input type="submit" value="Upload keys" name="uploadKeys">
            </form>
        </div>
        <div id="tabs-2">
            <h1>Download server keys</h1>
            <div class="row">
                <div class="four columns">
                    <h3>Private key</h3>
                    <form action="./index.php?action=downloadFromDB" id="download_private_key" method="POST"
                          enctype="multipart/form-data" target="_new">
                        <input type="hidden" name="type" value="downloadPrivateKey">
                        <input type="submit" value="Download"
                               name="downloadPrivateKey" <?php echo !($this->checkIfKeysExist()) ? "disabled" : "" ?>>
                    </form>
                </div>
                <div class="four columns">
                    <h3>Public key</h3>
                    <form action="./index.php?action=downloadFromDB" id="download_public_key" method="POST"
                          enctype="multipart/form-data" target="_new">
                        <input type="hidden" name="type" value="downloadPublicKey">
                        <input type="submit" value="Download"
                               name="downloadPublicKey" <?php echo !($this->checkIfKeysExist()) ? "disabled" : "" ?>>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        $("#tabs").tabs().addClass("ui-tabs-vertical ui-helper-clearfix");
        $("#tabs li").removeClass("ui-corner-top").addClass("ui-corner-left");
    });
</script>
