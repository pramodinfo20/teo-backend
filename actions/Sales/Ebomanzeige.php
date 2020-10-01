<?php

class ACtion_EbomAnzeige extends AClass_Base
{

    private $configlist;

    private $table;

    private $graph;

    private $configbom;


    function __construct()
    {

        parent::__construct();
        $this->useCommandMapping = true;
        if ($this->controller) {
            $this->leitwartePtr = $this->controller->getObject('ladeleitwarte');
        }

    }


    function Execute()
    {

        parent::Execute();

    }


    function sucheKonfiguration()
    {

        if($_POST) {
            $this->$verify_input_post = $this->controller->ErrorMsgInputPost();
        }

        $config = str_replace('*', '%', $_POST['config']);
        $config = str_replace('?', '_', $config);
        $this->configlist = $this->leitwartePtr->newQuery('vehicle_variants')
            ->where('windchill_variant_name', 'like', $config)
            ->get('windchill_variant_name');
        foreach ($this->configlist as $c) {
            $this->configbom[] = [
                'config' => $c,
                'date' => $this->leitwartePtr->newQuery('components')
                    ->where('part_number', '=', $c['windchill_variant_name'])
                    ->getOne('ebom_datum')
            ];
        }

    }


    function ebomTabelle($id)
    {

        // $this->table[$parameter]['Name'] = $parameter;
        $key = $id[0];
        /*
         * $this->ebomdate = $this->leitwartePtr->newQuery('components')
         * ->where('structure_level','=',0)->where('part_number','=',$key)
         * ->join('ebom_variant_components','components.part_number=ebom_variant_components.part_number', 'right outer join')->getOne('');
         */
        $this->table[$key]['data'] = $this->leitwartePtr->newQuery('components')
            ->where('ebom_variant_components.structure_level', '=', 0)
            ->where('part_number', '=', $key)
            ->where('ebom_variant_components.verbaut_bis', 'IS', 'NULL')
            ->join('ebom_variant_components', 'components.part_number=ebom_variant_components.part_number', 'right outer join')
            ->getOne('structure_level,components.part_number, part_name, element_name, version, context, lifecyclestatus, amount, unit, phantomfertigungsteil');
        $this->table[$id[0]]['liste'] = $this->rekursiveTabelle([
            1,
            $key
        ]);

    }


    function printGraphs()
    {

        $this->graph = true;

    }


    function rekursiveTabelle($parameters)
    {

        $ebomlvl = $this->leitwartePtr->newQuery('components')
            ->where('ebom_variant_components.structure_level', '=', $parameters[0])
            ->where('ebom_variant_components.verbaut_bis', 'is null')
            ->where('ebom_variant_components.parent_id', '=', $parameters[1])
            ->join('ebom_variant_components', 'components.part_number=ebom_variant_components.part_number', 'right outer join')
            ->get('ebom_variant_components.structure_level,components.part_number, part_name, element_name, version, context, lifecyclestatus, ebom_variant_components.amount, unit, phantomfertigungsteil');

        $result = array();
        foreach ($ebomlvl as &$lvl) {

            $result[$lvl['part_number']]['data'] = $lvl;
            if (! empty($this->leitwartePtr->newQuery('components')
                ->where('ebom_variant_components.structure_level', '=', $lvl['structure_level'] + 1)
                ->where('ebom_variant_components.verbaut_bis', 'is null')
                ->where('ebom_variant_components.parent_id', '=', $lvl['part_number'])
                ->join('ebom_variant_components', 'components.part_number=ebom_variant_components.part_number', 'right outer join')
                ->get('structure_level,components.part_number, part_name, element_name, version, context, lifecyclestatus, amount, unit, phantomfertigungsteil'))) {

                $result[$lvl['part_number']]['liste'] = $this->rekursiveTabelle([
                    $lvl['structure_level'] + 1,
                    $lvl['part_number']
                ]);
            }
            $count ++;
        }
        return $result;

    }


    function WriteHtmlContent()
    {

        parent::WriteHtmlContent();
        echo <<<Heredoc
        <h1>Ebom und Qualitygates anzeigen</h1>
        <div class="twelve columns padding_all">
            <form action="{$_SERVER['PHP_SELF']}" method="post">
        	Fahrzeug Konfiguration eingeben:	<span class="ttip"><input id="ebomconfig_search" type="text" name="config" placeholder="{$_POST['config']}" required><span class="ttiptext" style="top:40px">Wenn Sie nur einen Teil der Windchill-Konfiguration kennen oder mehrere Autos mit ähnlicher Konfiguration suchen, fügen sie ein ? für ein fehlendes und ein * für mehrere fehlende Zeichen ein</span></span>
        		<input type="submit" name="command[sucheKonfiguration]" value="suchen">
                <input type="hidden" name="action" value="{$this->action}">
        	</form>
        </div>
        <br>
Heredoc;
        if (! empty($this->configlist)) {
            echo '<ul>';
            $count = 0;

            foreach ($this->configbom as $config) {
                if ($count > 49) {
                    echo '<p>Es können nicht mehr als 50 Ergebnisse für diese Suche angezeigt werden</p>';
                    break;
                }
                if (empty($config['date'])) {
                    echo '<li><p>' . $config['config']['windchill_variant_name'] . '(Keine Ebom für diese Konfiguration vorhanden)<p></li>';
                } else {
                    echo '<li><a href="?action=' . $this->action . '&config=' . $config['config']['windchill_variant_name'] . '&command[ebomTabelle]&param[0]=' . $config['config']['windchill_variant_name'] . '">' . $config['config']['windchill_variant_name'] . '(' . $config['date']['ebom_datum'] . ')</a></li>';
                }
                $count ++;
            }
            echo '</ul>';
        }
        /*
         * if(!empty($this->ebomlvl)){
         * if($_REQUEST['param'][1]!='NULL') echo '<h3>Bestandteile von: '.$_REQUEST['param'][1].'</h3>';
         * echo '<table>';
         * echo '<thead><tr>';
         * foreach($this->ebomlvl[0] as $k=>$v){
         * echo '<th>'.$k.'</th>';
         * }
         * echo '<tr></thead><tbody>';
         * foreach($this->ebomlvl as $lvl){
         * echo '<tr>';
         * foreach($lvl as $k => $v){
         * if($k == 'part_number' && !empty($this->ebomlvl=$this->leitwartePtr->newQuery('components')->where('structure_level','=',$lvl['structure_level']+1)->where('parent_id','=',$lvl['part_number'])->join('ebom_variant_components','components.part_number=ebom_variant_components.part_number', 'right outer join')->get('structure_level,components.part_number, part_name, element_name, version, context, lifecyclestatus, amount, unit, phantomfertigungsteil'))){
         * $structure = $lvl['structure_level']+1;
         * echo '<td><a href="?action='.$this->action.'&command[ebomTabelle]&param[]='.$structure.'&param[]='.$lvl['part_number'].'">'.$v.'</a></td>';
         * }
         * else{
         * echo '<td>'.$v.'</td>';
         * }
         * }
         * echo '</tr>';
         * }
         * echo '<tbody></table>';
         * }
         */
        if (! empty($this->table[$_REQUEST['config']]['data'])) {
            echo '<h3></h3>';
            echo '<table><thead><tr>';
            echo '<th>Aufklappen</th>';
            foreach ($this->table[$_GET['config']]['data'] as $k => $v) {
                echo '<th>' . $k . '</th>';
            }
            echo '</tr></thead>';
            echo '<tbody>';
            echo '<tr>';
            echo '<td><a id=' . $this->table[$_GET['config']]['data']['part_number'] . ' href="#" onclick="show(\'' . $this->table[$_GET['config']]['data']['part_number'] . '\')">[+]</a></td>';
            foreach ($this->table[$_GET['config']]['data'] as $k => $v) {
                echo '<td>' . $v . '</td>';
            }
            echo '</tr>';
            $this->printtablerecursive($this->table[$_GET['config']]);
            echo '</tbody></table>';
            echo '<a href="?action=' . $this->action . '&command[printGraphs]" target="_blank" rel="noopener">Diagramme zu Quality-Gates anzeigen lassen(comming soon)</a>';
        } else if (! empty($this->table)) {
            echo '<p>Zu diesem Fahrzeug wurde noch keine Ebom hochgeladen. Dies können sie unter Ebom-Verwaltung tun</p>';
        }
        echo <<<javascript
        <script>
        function show(part){
             var elements = document.getElementsByClassName(part);
             var button = document.getElementById(part);
             if(button.innerHTML == "[+]"){
                button.innerHTML="[-]";
             }
             else{
                button.innerHTML="[+]";
             }
             for(var i=0; i<elements.length; i++){
                if(elements[i].style.display == "none"){
                    elements[i].style.display ="table-row";
                }
                else{
                    elements[i].style.display = "none";
                    var childelement = elements[i].getElementsByTagName("a");
                    if(typeof childelement[0] !== 'undefined'){
                        if(childelement[0].innerHTML == "[-]")
                        {
                            childelement[0].innerHTML ="[+]";
                            hiderecursive(childelement[0].getAttribute("id"));
                        }
                    }
                }
             }   
        }

        function hiderecursive(id){
            var elements = document.getElementsByClassName(id);
            for(var i=0; i<elements.length; i++)
            {
                elements[i].style.display = "none";
                var childelement = elements[i].getElementsByTagName("a");
                if(typeof childelement[0] !== 'undefined'){    
                    if(childelement[0].innerHTML == "[-]")
                    {
                        childelement[0].innerHTML ="[+]";
                        hiderecursive(childelement[0].getAttribute("id"));
                    }
                }
            }
        }

        </script>
javascript;
        if ($this->graph) {
            echo '<img src="/images/QG_Plot.png">';
        }

    }


    function printtablerecursive(&$tables)
    {

        foreach ($tables['liste'] as $t) {
            echo '<tr class="' . $tables['data']['part_number'] . '" style="display: none;"><td>';
            if (! empty($t['liste'])) {
                echo '<a id=' . $t['data']['part_number'] . ' href="#" onclick="show(\'' . $t['data']['part_number'] . '\')">[+]</a>';
            }
            echo '</td>';
            foreach ($t['data'] as $k => $v) {
                echo '<td>' . $v . '</td>';
            }
            echo '</tr>';
            if (! empty($t['liste'])) {
                $this->printtablerecursive($t);
            }
        }

    }

}