<div class="inner_container">
    <?php
    if ($this->fullView) {
        include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";
    }
    ?>

    <div class="inner_container">
        <h1><?php echo $this->translate['generalSearch']['header']; ?></h1>
        <div class="row ">
            <div class="columns six">
                <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
            </div>
        </div>

        <div class="row ">
            <div id="qs_fehler_wrap" class="columns twelve">
                <div>
                    <?php echo $this->form->genSearchForm() ?>
                </div>
            </div>
            <div class="columns twelve">
                <?php echo $this->depotFilterContent; ?>
            </div>

        </div>

        <?php if ($this->fullView): ?>

            <div class="row ">
                <div class="columns twelve">
                    <?php
                    //                                         echo $this->qform_vehicles->getContent();
                    $firstPage = $this->translate['generalSearch']['firstPage'];
                    $previousPage = $this->translate['generalSearch']['previousPage'];
                    $nextPage = $this->translate['generalSearch']['nextPage'];
                    $lastPage = $this->translate['generalSearch']['lastPage'];
                    $page = $this->translate['generalSearch']['page'];
                    $linePerPage = $this->translate['generalSearch']['linePerPage'];
                    $pageoptions = '
                                             <div class="pager">
                                             <a href="#" class="first"  title="Erste Seite" >' . $firstPage . '</a>
                                             <a href="#" class="prev"  title="Vorherige Seite" ><span class="genericon genericon-previous"></span>' . $previousPage . '</a>
                                             <span class="pagedisplay"></span> <!-- this can be any element, including an input -->
                                             <a href="#" class="next" title="Nächste Seite" >' . $nextPage . '<span class="genericon genericon-next"></span></a>
                                             <a href="#" class="last"  title="Letzte Seite" >' . $lastPage . '</a>
                              
                                             ' . $page . ' <select class="gotoPage"></select>
                                             ' . $linePerPage . '<select class="pagesize">
                                             <option value="50">50</option>
                                             <option value="100">100</option>
                                             <option value="300">300</option>
                                             </select>
                                             </div>';
                    echo $pageoptions;
                    ?>
                    <div class="quickform">
                        <form method="post" id="vehicle_fertig_status" action="index.php?action=search"
                              novalidate="novalidate">
                            <input type="hidden" name="todays_vehicles" id="todays_vehicles-0" value="">
                            <input type="hidden" name="to_set_vehicles" id="to_set_vehicles" value="">
                            <input type="hidden" id="qs_qm_action" name="action" value="saveQS">
                            <div style="overflow-y:auto; height: 450px; position:relative;" class="wrapper">
                                <?php echo $this->qs_vehicles->getContent(); ?>
                            </div>
                            <fieldset class="row">
                                <fieldset class="columns four inline_elements" id="qfauto-3966">

                                </fieldset>
                                <fieldset class="columns four inline_elements" id="qfauto-3967">
                                </fieldset>
                                <fieldset class="columns four inline_elements" id="qfauto-3968">
                                    <div class="row">
                                        <div class="element">
                                            <input type="submit" value="Speichern" style="float: right; margin: 4px"
                                                   name="">
                                        </div>
                                    </div>
                                </fieldset>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>

        <?php endif ?>

    </div>
    <div id="body-sn-input" style="display:none">

        <h2 id="h2-body-vin"></h2>
        <div style="margin: 10px 10px;">Body Seriennummer<br>
            <input type="hidden" name="vehicle_id" id="body-vehicle_id" value="">
            <input type="hidden" name="previous" id="body-previous" value="">
            <input type="text" id="body-sn" size="18" maxlength="32" value="">
        </div>
        <div style="margin: 10px 10px;">Datum des Einbaus<br>
            <input type="text" id="body-date" size="10" maxlength="10" value="<?php echo date('d.m.Y'); ?>">
        </div>
        <div style="margin: 20px 10px;">
            <button id="body-submit">Seriennummer speichern</button>
        </div>
    </div>
    <div id="dialog-form"></div>

