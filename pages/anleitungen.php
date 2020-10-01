<?php
/**
 * anleitungen.php
 * Template für anleitungen
 * @author Pradeep Mohan
 */

$div = $this->requestPtr->getProperty('div');
$zspl = $this->requestPtr->getProperty('zspl');
$depot = $this->requestPtr->getProperty('depot');
$msgs = '';


$qform_fr = new QuickformHelper ($displayheader, "zspl_upload_form");
$qform_fr->add_fahrzeug_suchen();
$formprint = $qform_fr->getContent();


$vin = $this->requestPtr->getProperty('vin');
$kennzeichen = $this->requestPtr->getProperty('kennzeichen');

?>
<div class="inner_container">
    <div class="row ">
        <div class="columns six">
            <h1>Anleitungen</h1>
        </div>
    </div>
    <div class="row ">
        <div class="columns six">
            <?php if (is_array($msgs)) echo implode('<br>', $msgs); ?>
        </div>
    </div>
    <div class="row ">
        <div class="columns twelve">
            <h2>Anleitungen als PDF Datei herunterladen</h2>
            <?php switch ($user->getUserRole()) {

                case 'zentrale':
                    ?>
                    <a href="anleitungen/Bedienungsanleitung_StreetScooterCloudSystem_Webinterface_DeutschePost_Zentrale.pdf">Bedinungsanleitung_StreetScooterCloudSystem_Webinterface_DeutschePost.pdf</a>
                    <br><?php
                    break;
                case 'fpv':
                    ?><a
                        href="anleitungen/Bedienungsanleitung_StreetScooterCloudSystem_Webinterface_DeutschePost_Fpv.pdf">Bedinungsanleitung_StreetScooterCloudSystem_Webinterface_DeutschePost.pdf</a>
                    <br><?php
                    break;
                case 'fuhrparksteuer':
                    ?><a href="anleitungen/Bedienungsanleitung_StreetScooterCloudSystem_Webinterface_Fps.pdf">Bedinungsanleitung_StreetScooterCloudSystem_Webinterface_DeutschePost.pdf</a>
                    <br><?php
                    break;
                case 'chrginfra_ebg':
                case 'chrginfra_aix':
                    ?><a
                        href="anleitungen/Bedienungsanleitung_StreetScooterCloudSystem_Webinterface_LadeInfrastruktur.pdf">Bedinungsanleitung_StreetScooterCloudSystem_Webinterface_LadeInfrastruktur.pdf"</a>
                    <br><?php
                    break;
                case 'aftersales':
                case 'engg':
                case 'sales':
                    ?><a href="anleitungen/StS_Variantenmanagement_V1.5.pdf">StS_Variantenmanagement_V1.5.pdf</a>
                    <br><br>
                    <a href="anleitungen/Bedienungsanleitung_StreetScooterCloudSystem_Webinterface_StreetScooter.pdf">Bedinungsanleitung_StreetScooterCloudSystem_Webinterface_StreetScooter.pdf</a>
                    <br><?php
                    break;
                default:
                    echo 'Keine Anleitungen gefunden!';
                    break;
            }
            ?>
        </div>
    </div>
    <div class="row ">
        <div class="columns twelve">
            <h2>Videos</h2>
            <?php for ($i = 1; $i <= 10; $i++) {

                if (file_exists('videos/' . $user->getUserRole() . $i . '.mp4')) {
                    if (file_exists('videos/' . $user->getUserRole() . $i . '.txt')) {

                        $filename = 'videos/' . $user->getUserRole() . $i . '.txt';
                        $fresource = fopen($filename, "r");
                        $videotitle = fread($fresource, filesize($filename));
                        fclose($fresource);
                    }
                    echo '<h3>' . $videotitle . '</h3>';
                    echo '<video controls="" width="960px" name="media" ><source src="videos/' . $user->getUserRole() . $i . '.mp4" type="video/mp4"></video>';
                }


            }

            ?>

        </div>
    </div>
    <div class="row ">
        <div class="columns twelve">
            <a href="index.php" onClick='window.close()'>Zurück</a>
        </div>
    </div>
</div>