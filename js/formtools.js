var on_selected_btn = [];


function EnableElements (element_ids, enabled)
{
    var id, i;

    if (! Array.isArray(element_ids))
        element_ids = [element_ids];

    for (i=0; i<element_ids.length; i++)
    {
        id = element_ids[i];
        element = document.getElementById (id);
        if (element)
            element.disabled = !enabled;
    }
}


function UpdateNumsel ()
{
    var span_numsel = document.getElementById ('id_numsel');
    
    if (span_numsel)
    {   
        span_numsel.innerHTML = numsel;
    }
    // EnableElements (['id_btn_delete1', 'id_btn_edit1', 'id_btn_undo'], (numsel>0));
    EnableElements (on_selected_btn, (numsel>0));
}


function SelectAllRows (id_table, checked)
{
    var table = document.getElementById (id_table);
    var disabled;
    var count = 0;
    
    if (table)
    {
        var allInputs = table.getElementsByTagName('input');
        for (var i = 0, input; input = allInputs[i]; i++) 
        {
            if (input.id == 'id_serach_selected') {
                input.checked = ! checked;
            }
            else if (input.id.substr(0,11)=='id_selected') {
                input.checked = checked;
                count++;
            }
        }
        
        numsel = prevsel + (checked ? count : 0);
        UpdateNumsel ();
    }
}

function OnSelectRow (chckbox)
{
    if (chckbox.checked)
        numsel++;
    else
        numsel--;
    
    UpdateNumsel();
}

function SubmitFormId (command, id)
{
    if (command != '')
    {
        element = document.getElementById(id);
        element.value = command;
    }
    document.getElementById("id_Form").submit(); 
}

function SubmitForm (command)
{
    SubmitFormId (command, 'id_command');
}

function SubmitAndGoto (page)
{
    var element;
    element = document.getElementById('id_goto_page');
    element.value = page;
    element = document.getElementById('id_command');
    element.value = 'goto';
    document.getElementById("id_Form").submit();
}


function OnComboBox (element)
{
    var index   = element.selectedIndex;
    var value   = element.options[index].value; 
    var textbox = document.getElementById(element.id + '_text');
    
    if (value=='edit')
    {
        textbox.style.visibility = 'visible';
    }
    else
    {
        SubmitAndGoto (1);
    }
}

function doScroll(selectID)
{
    var selectCtrl  = document.getElementById(selectID);
    if (selectCtrl)
    {
        var oh          = selectCtrl.options[0].offsetHeight;   if (oh==0) oh=17;
        var selpos      = selectCtrl.selectedIndex * oh;
        var dest        = (selectCtrl.size-1)* oh/2;
        var nScroll     = selpos-dest;

        if (nScroll>0)
            selectCtrl.scrollTop = nScroll;
    }        
}

function getPosition (element, maxstep) 
{
    var top = 0, left = 0;
    
    do {
        top += element.offsetTop  || 0;
        left += element.offsetLeft || 0;
        element = element.offsetParent;
        maxstep--;
    } while(element && maxstep);

    return [left, top];
};


function ZeigAndereStandorte (element)
{
    var index   = element.selectedIndex;
    var value   = element.options[index].value; 
    var longlist= document.getElementById('id_all_depots');
    
    var pos = getPosition (element,2);
    
    if (longlist)
    {
        if (value=='edit')
        {
            var left = pos[0];
            var top  = pos[1] - longlist.offsetHeight;
            longlist.style.left = pos[0] + 'px';
            longlist.style.top = (pos[1] - longlist.offsetHeight) + 'px';
            
            longlist.style.visibility = 'visible';
            longlist.style.visibility = 'visible';
            longlist.style.visibility = 'visible';
            longlist.style.zIndex = 99;
        }
        else
        {    
            longlist.style.visibility = 'hidden';
        }
    }
}

function HabAnderenStandort (element)
{
    var i, option;
    var index       = element.selectedIndex;
    var value       = element.options[index].value; 
    var depotname   = element.options[index].text;
    var stdlist     = document.getElementById('id_to_location');
    var othername   = document.getElementById('id_othername');
    
    if (stdlist && othername)
    {
        if (stdlist.options.length==5)
        {
            stdlist.options.add (document.createElement("option"));
        }
        stdlist.options[5].value    = value;
        stdlist.options[5].text     = depotname;
        stdlist.selectedIndex       = 5;
        othername.value             = depotname;
        /*        
        for (i=0; i<stdlist.options.length; i++)
        {
            if (stdlist.options[i].value == value)
            {    
                stdlist.selectedIndex = i;
                return;
            }
        }
        option = document.createElement("option");
        option.value = value;
        option.text  = caption;
        stdlist.options.add (option);
        stdlist.selectedIndex = i;
        */
    }
    element = document.getElementById('id_all_depots');
    element.style.visibility = 'hidden';      
}



function ChangePageSize(element)
{
    var index, value;
    
    index =  element.selectedIndex;
    value =  element.options[index].value; 
    element = document.getElementById('id_size');
    element.value = value;
    
    SubmitAndGoto (1);
}

function EnableButtons (bEnable)
{
    disabled = !bEnable;
    document.mainForm.bt_change.disabled = disabled;
    document.mainForm.bt_add.disabled = disabled;
}


function SwapListType(type)
{
    id       = 'id_data_' + type;
    div      = document.getElementById (id);
    txfield = document.getElementById ('id_fahrzeugliste');
    
    if (div && txfield)
    {
        txfield.value = div.innerHTML;
    }
}


function SetInvisible (iCol, bInvis, sizeBackup)
{
    var span = document.getElementById ('id_span_' + iCol);

    iCol--;
    if (bInvis)
    {
        sizeBackup[iCol] = span.style.width;
         span.style.width = 0;
        span.style.visibility = 'hidden';
    }
    else
    {
         span.style.width = sizeBackup[iCol];
        span.style.visibility = 'visible';
    }
}

function CheckInputVisibility (nCols, map, sizeBackup)
{
    var usedCols = new Array (nCols+1);
    var selBox, value, iInput;

    for (i=0; i<=nCols; i++)
        usedCols[i] = false;
    
    for (i=1; i<=nCols; i++)
    {
        selBox = document.getElementById ('id_sel_' + i);
        if (selBox)
            value = selBox.selectedIndex;
        
        iInput = map[value];
        if (iInput)
            usedCols[iInput] = true;
    }    
    for (i=1; i<=nCols; i++)
        SetInvisible (i, usedCols[i]);
}

