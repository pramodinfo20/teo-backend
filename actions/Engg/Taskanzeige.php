<?php

class ACtion_TaskAnzeige extends AClass_Base {

    private $search_array = array('vin', 'c2cbox', 'timestamp_created', 'timestamp_started', 'timestamp_completed', 'task_name', 'success', 'result', 'comment');
    private $headcols = array('c2cbox', 'vin', 'timestamp_created', 'timestamp_started', 'timestamp_completed', 'success', 'result', 'comment', 'task_name');
    private $datacols = array('c2cbox' => 'c2cbox',
        'vin' => 'vin',
        'timestamp_created' => 'timestamp_created',
        'timestamp_started' => 'timestamp_started',
        'timestamp_completed' => 'timestamp_completed',
        'success' => 'success',
        'result' => 'result',
        'comment' => 'comment',
        'task_name' => 'sota_task_types.task_name');

    function __construct() {
        parent::__construct();
        if ($this->controller) {
            $this->diagnosePtr = $this->controller->getObject('diagnose');
        }
    }

    function Execute() {
        $query = $this->diagnosePtr->newQuery('sota_tasks')->join('sota_task_types', 'sota_task_types.task_type_id=sota_tasks.task_type_id');
        $query = $this->searchInputs($query);
        $this->tablelist = $query->orderBy('timestamp_created', 'desc')->get(implode(',', $this->datacols));
    }

    function searchInputs($query) {
        foreach ($this->search_array as $s) {
            if (isset($_POST[$s]) && $_POST[$s] != "") {
                $temp = '%' . $_POST[$s] . '%';
                $query = $query->where($this->datacols[$s] . '::text', 'like', $temp);
            }
        }
        return $query;
    }

    function getHead_Html() {
        $result = "";
        foreach ($this->headcols as $colname) {
            $result .= '<th>' . $colname . '</th>';
        }
        return $result;
    }

    function getSearchRow_Html() {
        $result = "";
        foreach ($this->headcols as $colname) {
            $result .= '<td>';
            if (in_array($colname, $this->search_array)) {
                $result .= '<input name=' . $colname . ' onchange="form.submit()">';
            }
            $result .= '</td>';
        }
        return $result;
    }

    function getBody_Html() {
        $result = "";
        foreach ($this->tablelist as $row) {
            $result .= '<tr>';
            foreach ($row as $k => $v) {
                $result .= '<td>' . $v . '</td>';
            }
            $result .= '</tr>';
        }
        return $result;
    }

    function WriteHtmlContent() {
        parent::WriteHtmlContent();
        $tablehead = $this->getHead_Html();
        $tableSearchRow = $this->getSearchRow_Html();
        if ($this->tablelist) {
            $tablebody = $this->getBody_Html();
        }
        echo <<<HTML
        <h1>Taskanzeige</h1>
        <br>
        <form method="post" action="{$_SERVER['PHP_SELF']}">
            <table class="sales">
                <thead>
                    <tr>
                        {$tablehead}
                    </tr>
                    <tr>
                        {$tableSearchRow}
                    </tr>
                </thead>
                <tbody>
                    {$tablebody}
                    <tr>
                        <td>
                            <button type="submit">Filter zurücksetzen</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <input type="hidden" name="action" value="{$this->action}">
        </form>
HTML;
    }

}