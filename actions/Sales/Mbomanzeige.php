<?php

class ACtion_MbomAnzeige extends AClass_Base
{

    private $configlist;

    private $table;

    private $graph;


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

        $config = str_replace('*', '%', $_POST['config']);
        $config = str_replace('?', '_', $config);
        $this->configlist = $this->leitwartePtr->newQuery('vehicle_variants')
            ->where('windchill_variant_name', 'like', $config)
            ->get('windchill_variant_name');

    }


    function mbomTabelle($id)
    {

        // $this->table[$parameter]['Name'] = $parameter;
        $key = $id[0];
        /*
         * $this->mbomdate = $this->leitwartePtr->newQuery('components')
         * ->where('structure_level','=',0)->where('part_number','=',$key)
         * ->join('mbom_variant_components','components.part_number=mbom_variant_components.part_number', 'right outer join')->getOne('');
         */
        $this->table[$key]['data'] = $this->leitwartePtr->newQuery('components')
            ->where('structure_level', '=', 0)
            ->where('part_number', '=', $key)
            ->where('mbom_variant_components.verbaut_bis', 'IS', 'NULL')
            ->join('mbom_variant_components', 'components.part_number=mbom_variant_components.part_number', 'right outer join')
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

        $mbomlvl = $this->leitwartePtr->newQuery('components')
            ->where('structure_level', '=', $parameters[0])
            ->where('parent_id', '=', $parameters[1])
            ->join('mbom_variant_components', 'components.part_number=mbom_variant_components.part_number', 'right outer join')
            ->get('structure_level,components.part_number, part_name, element_name, version, context, lifecyclestatus, amount, unit, phantomfertigungsteil');

        $result = array();
        foreach ($mbomlvl as &$lvl) {

            $result[$lvl['part_number']]['data'] = $lvl;
            if (! empty($this->leitwartePtr->newQuery('components')
                ->where('structure_level', '=', $lvl['structure_level'] + 1)
                ->where('parent_id', '=', $lvl['part_number'])
                ->where('mbom_variant_components.verbaut_bis', 'IS', 'NULL')
                ->join('mbom_variant_components', 'components.part_number=mbom_variant_components.part_number', 'right outer join')
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
        <form action="{$_SERVER['PHP_SELF']}" method="post">
    	Fahrzeug Konfiguration eingeben:	<span class="ttip"><input type="text" name="config" placeholder="{$_POST['config']}"><span class="ttiptext" style="top:40px">Wenn Sie nur einen Teil der Windchill-Konfiguration kennen oder mehrere Autos mit ähnlicher Konfiguration suchen, fügen sie ein ? für ein fehlendes und ein * für mehrere fehlende Zeichen ein</span></span>
    		<input type="submit" name="command[sucheKonfiguration]" value="suchen">
            <input type="hidden" name="action" value="{$this->action}">
    	</form>
        <br>
Heredoc;
        if (! empty($this->configlist)) {
            echo '<ul>';
            $count = 0;

            foreach ($this->configlist as $config) {
                if ($count > 49) {
                    echo '<p>Es können nicht mehr als 50 Ergebnisse für diese Suche angezeigt werden</p>';
                    break;
                }
                echo '<li><a href="?action=' . $this->action . '&config=' . $config['windchill_variant_name'] . '&command[mbomTabelle]&param[0]=' . $config['windchill_variant_name'] . '">' . $config['windchill_variant_name'] . '</a></li>';
                $count ++;
            }
            echo '</ul>';
        }
        /*
         * if(!empty($this->mbomlvl)){
         * if($_REQUEST['param'][1]!='NULL') echo '<h3>Bestandteile von: '.$_REQUEST['param'][1].'</h3>';
         * echo '<table>';
         * echo '<thead><tr>';
         * foreach($this->mbomlvl[0] as $k=>$v){
         * echo '<th>'.$k.'</th>';
         * }
         * echo '<tr></thead><tbody>';
         * foreach($this->mbomlvl as $lvl){
         * echo '<tr>';
         * foreach($lvl as $k => $v){
         * if($k == 'part_number' && !empty($this->mbomlvl=$this->leitwartePtr->newQuery('components')->where('structure_level','=',$lvl['structure_level']+1)->where('parent_id','=',$lvl['part_number'])->join('mbom_variant_components','components.part_number=mbom_variant_components.part_number', 'right outer join')->get('structure_level,components.part_number, part_name, element_name, version, context, lifecyclestatus, amount, unit, phantomfertigungsteil'))){
         * $structure = $lvl['structure_level']+1;
         * echo '<td><a href="?action='.$this->action.'&command[mbomTabelle]&param[]='.$structure.'&param[]='.$lvl['part_number'].'">'.$v.'</a></td>';
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
            echo '<p>Zu diesem Fahrzeug wurde noch keine Mbom hochgeladen. Dies können sie unter Mbom-Verwaltung tun</p>';
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
            echo '<tr class="' . $tables['data']['part_number'] . '" style="display: none;" ><td>';
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