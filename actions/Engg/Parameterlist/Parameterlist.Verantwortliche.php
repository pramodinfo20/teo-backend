<div class="mainframe verantwortliche" id="id_mainframe">
    <div class="scrollboxY" style="width: 100%">
        <h2>Verantwortlichkeiten</h2>
        <h3>Fahrzeug allgemein</h3>
        <hr>
        <div class="gruppe">
            <div class="element">
                <div class="privLabel color_main">Fahrzeugkonfiguration<br>Fahrzeugeigenschaften<br>Komponenten</div>
                <?php $this->WriteHtml_OwnerAndPrivileged_New(self::PRIV_VARIANT_EDIT, 0); ?>
            </div>

            <div class="element">
                <div class="privLabel color_main">COC-Werte</div>
                <?php $this->WriteHtml_OwnerAndPrivileged_New(self::PRIV_VARIANT_COC, 0); ?>
            </div>

            <div class="element">
                <div class="privLabel color_main">Fahrzeuge anlegen / löschen</div>
                <?php $this->WriteHtml_OwnerAndPrivileged_New(self::PRIV_VARIANT_CREATE, 0); ?>
            </div>

            <div class="element">
                <div class="privLabel color_main">ODX-Eigenschaften von ECU Geräten setzen</div>
                <?php $this->WriteHtml_OwnerAndPrivileged_New(self::PRIV_ECU_PROPERTIES, 0); ?>
            </div>

            <div class="element">
                <div class="privLabel color_main">Administration globaler Parameter</div>
                <?php $this->WriteHtml_OwnerAndPrivileged_New(self::PRIV_VARIABLE_ADMIN, 0); ?>
            </div>
        </div>

        <h3>ECU Software + Parameter</h3>
        <hr>
        <div class="gruppe">

            <?php
            foreach ($this->allEcuNames as $ecu_id => $ecu_name) {
                echo <<<HEREDOC
          <div class="element">
            <div class="privLabel color_ecu">ECU Parameter für <br><span>$ecu_name</span></div>
HEREDOC;
                $this->m_Permission[self::PRIV_ECU_DATA] = [];
                $this->WriteHtml_OwnerAndPrivileged_New(self::PRIV_ECU_DATA, $ecu_id);
                echo <<<HEREDOC
          </div>
HEREDOC;
            }
            ?>
        </div>

        <h3>Globale Parameter</h3>
        <hr>
        <div class="gruppe">

            <?php
            foreach ($this->DB_Variables as $pset_id => $set) {
                echo <<<HEREDOC
          <div class="element">
            <div class="privLabel color_variable">{$set['odx_name']}</div>
HEREDOC;

                $this->m_Permission[self::PRIV_SET_VARIABLE] = [];
                $this->WriteHtml_OwnerAndPrivileged_New(self::PRIV_SET_VARIABLE, $pset_id);
                echo <<<HEREDOC
          </div>
HEREDOC;
            }
            ?>
        </div>

    </div>
</div>
