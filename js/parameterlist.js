'use strict';

function UncheckSibling(checkbox) {
    var sibling = checkbox.parentElement.nextElementSibling.firstElementChild;

    if (sibling) {
        sibling.disabled = !checkbox.checked;
    }
}

function OnVarChanged(value, oldValue) {
    var elm, enable = (value != oldValue);

    elm = document.getElementById('id_save_variable_inactive');
    if (elm)
        elm.style.display = enable ? 'none' : 'block';
    elm = document.getElementById('id_save_variable_active');
    if (elm)
        elm.style.display = enable ? 'block' : 'none';
}

var prev_type = 1;

function OnVarTypeChanged(type_id) {
    var elem;
    type_id++;
    if (elem = document.getElementById('id_variable' + prev_type))
        elem.style.display = 'none';
    if (elem = document.getElementById('id_variable' + type_id))
        elem.style.display = 'inline';
    prev_type = type_id;
}

function onVariantPartChanged(enable) {
    var elm = document.getElementById('id_save_variant_disabled');
    if (elm)
        elm.style.display = enable ? 'none' : 'block';
    elm = document.getElementById('id_save_variant');
    if (elm)
        elm.style.display = enable ? 'block' : 'none';

    elm = document.getElementById('id_copy_variant');
    if (elm)
        elm.style.display = enable ? 'none' : 'block';
    elm = document.getElementById('id_copy_variant_disabled');
    if (elm)
        elm.style.display = enable ? 'block' : 'none';

}

function UpdateVariantConfigString(sender) {

    var id_sender = sender.id;
    var id_div = id_sender + '_key';
    var div_key = document.getElementById(id_div);
    var index = sender.selectedIndex;
    var val_sender = sender.options[index].value;
    if (div_key)
        div_key.innerHTML = val_sender;

    var new_type, new_key;
    if (div_key = document.getElementById('id_config_type'))
        new_key = new_type = div_key.value;
    if (div_key = document.getElementById('id_series_key'))
        new_type += div_key.innerHTML;
    if (div_key = document.getElementById('id_layout_key'))
        new_type += div_key.innerHTML;
    if (div_key = document.getElementById('id_feature_key'))
        new_type += div_key.innerHTML;
    if (div_key = document.getElementById('id_battery_key'))
        new_type += div_key.innerHTML;

    var selbox, i, name, pos, new_exist = false;
    selbox = document.getElementById('select_' + new_key);
    if (selbox) {
        for (i = 0; i < selbox.options.length; i++) {
            name = selbox.options[i].text;
            pos = name.indexOf(new_type);
            if (pos > 0) {
                new_exist = true;
                break;
            }
        }
    }

    var div_exists;
    if (div_exists = document.getElementById('id_variant_exists')) {
        div_exists.style.visibility = new_exist ? 'visible' : 'hidden';
    }
    onVariantPartChanged(!new_exist);
}

function ShowInfoBlock(show) {
    var elm, block, icon;
    var display_style = show ? 'flex' : 'none';

    block = document.getElementById('id_infoBlock');
    if (block)
        block.style.display = display_style;

    icon = document.getElementById('id_infoState0');
    if (icon)
        icon.style.display = show ? 'block' : 'none';

    icon = document.getElementById('id_infoState1');
    if (icon)
        icon.style.display = show ? 'none' : 'block';

}

function HandleInfoBlock(show) {
    ShowInfoBlock(show);
    Ajaxecute('showInfoBlock', 'visibility=' + show);
}

function OnSelectVariable(selectbox) {
    if (!CheckModifiedAndConfirm())
        return;

    document.suchform.submit();
}

function OnValuetypeChanged(typeSelector, col_id) {
    var index = typeSelector.selectedIndex;
    var elmEdit = document.getElementById('id_value_edit_' + col_id);
    var elmMacros = document.getElementById('id_macro_select_' + col_id);
    var elmTokens = document.getElementById('id_token_select_' + col_id);

    if (elmEdit && elmMacros && elmTokens) {
        elmEdit.style.display = ((index == 1) || (index == 2)) ? 'inline' : 'none';
        elmMacros.style.display = (index == 3) ? 'inline' : 'none';
        elmTokens.style.display = (index == 4) ? 'inline' : 'none';
    } else if (elmEdit && elmMacros) {
        elmEdit.style.display = ((index == 1) || (index == 2)) ? 'inline' : 'none';
        elmMacros.style.display = (index == 3) ? 'inline' : 'none';
    }
}

function SubmitCommand(cmd) {
    var elm = document.getElementById('idr_command');
    if (elm)
        elm.value = cmd;
    document.mainform.submit();
}

function move_up(col_id) {
    var this_ord = document.getElementById('id_order_' + col_id);
    var prev_ord = document.getElementById('id_order_' + (col_id - 1));

    if (this_ord && prev_ord) {
        var tmp = this_ord.value;
        this_ord.value = prev_ord.value;
        prev_ord.value = tmp;

        SubmitCommand('refresh');
    }
}

function move_down(col_id) {
    var this_ord = document.getElementById('id_order_' + col_id);
    var next_ord = document.getElementById('id_order_' + (col_id + 1));

    if (this_ord && next_ord) {
        var tmp = this_ord.value;
        this_ord.value = next_ord.value;
        next_ord.value = tmp;

        SubmitCommand('refresh');
    }
}

function addParamDef() {
    SubmitCommand('add');
}

function delParamDef(col_id, del) {
    var id = 'id_del' + col_id;
    var elm = document.getElementById(id);

    elm.value = del;

    elm = document.getElementById('id_a0_del' + col_id);
    if (elm)
        elm.style.display = del ? 'none' : 'inline';
    elm = document.getElementById('id_a1_del' + col_id);
    if (elm)
        elm.style.display = del ? 'inline' : 'none';
}

function CheckModifiedAndConfirm() {
    if (modified) {
        return confirm("Daten wurden geändert jedoch nicht gespeichert!\nWollen Sie wirklich die Änderung verwerfen?");
    }

    if (editMode) {
        return confirm("Aktuelle Bearbeitung verlassen?");
    }
    return true;
}


function SaveParametersX(cmd, prefix) {
    var cmdElement = document.getElementById(prefix + '_command');
    var form = document.getElementById(prefix + '_form');
    if (form && cmdElement) {
        cmdElement.value = cmd;
        form.submit();
    }
}


function SaveParameters(cmd) {
    SaveParametersX(cmd, 'idr');
}


function AddNewRows() {
    var scrollpos = document.getElementById('id_scrollpos');
    var scrollbox = document.getElementById('id_scrollbox');
    if (scrollbox && scrollpos)
        scrollpos.value = scrollbox.scrollTop;

    SaveParametersX('add', 'idr');
}


function undefault(param_id) {
    $('#id_default-' + param_id).val(0);
    $('#id_undefault-' + param_id).hide();
    $('#id_reset-' + param_id).show();
    $('#id_is_default-' + param_id).hide();
    $('#id_is_not_default-' + param_id).show();
}


function resetValue(param_id) {
    $('#id_default-' + param_id).val(1);
    $('#id_undefault-' + param_id).show();
    $('#id_reset-' + param_id).hide();
    $('#id_is_default-' + param_id).show();
    $('#id_is_not_default-' + param_id).hide();
}

function SetVersionEditMode(bEdit) {
    var aElem;
    var editElemsMode = bEdit ? 'visible' : 'hidden';
    var otherElemsMode = bEdit ? 'hidden' : 'visible';

    // alert ("Noch nicht fertig!");

    aElem = document.getElementById('selectSwVersion');
    if (aElem) aElem.style.visibility = editElemsMode;

    aElem = document.getElementById('idEnableChangeSW');
    if (aElem) aElem.style.visibility = otherElemsMode;

    aElem = document.getElementById('idVersionButtons');
    if (aElem) aElem.style.visibility = editElemsMode;
}


function OnBtSaveSwClick() {
    SetVersionEditMode(false);
    document.getElementById('idl_command').value = 'set_revision';
    // alert(document.suchform);
    document.suchform.submit();
}

function OnBtCopyParamsClick() {
    SaveParametersX('copy_params', 'idr');
}

function UpdateTabVariantData() {
    var edSuche = document.getElementById('suchtext_auto');
    var suchtext = edSuche ? encodeURI(edSuche.value) : '';

    alert(_urlparams);
    // var url = '?action=' + action + '&filter[variantType]=' + variantType + '&filter[variant]=' + variantId + '&filter[paramType]=' + paramType + '&filter[ecu]=' + variantEcu + '&filter[suchtext]=' + suchtext;
    // alert (url);
    var url = '?' + _urlparams + '&filter[suchtext]=' + suchtext;
    document.location.href = url;
}

function OnSelectVariant(selectBox) {
    if (!CheckModifiedAndConfirm())
        return;

    document.suchform.submit();
}

function OnSelectSwVersion(sel) {
    if (!CheckModifiedAndConfirm())
        return;
    document.suchform.submit();
}

function OnSelectSubVariant(sel) {
    if (!CheckModifiedAndConfirm())
        return;
    document.suchform.submit();
}

function OnSelectParameterType(sel) {
    if (!CheckModifiedAndConfirm())
        return;
    document.suchform.submit();
}

function OnSelectEcu(sel) {
    if (!CheckModifiedAndConfirm())
        return;
    document.suchform.submit();
}


function OnEcuVersionChanged(selBox) {
    if (!CheckModifiedAndConfirm())
        return;

    if ((selBox.options.length > 0) && (selBox.selectedIndex >= 0))
        document.mainform.submit();
}

function DeleteRev() {
    if (!confirm('Hiermit wird die ausgewählte SW Version\nmitsamt aller Parametereinstellungen unwiederruflich gelöscht!\n\nWollen Sie wirklich forfahren?'))
        return;

    window.location.href = _this_page + '?action=' + action + '&command=delete_rev';
}


function SafeDeleteVariant(has_sub) {
    var sub_extra = has_sub ? '\nmitsamt aller Unterkonfigurationen' : '';
    if (confirm('Sind Sie Sicher, dass Sie diese Konfiguration' + sub_extra + ' unwiederruflich löschen wollen?\n'))
        window.location.href = _this_page + '?action=' + action + '&command=delete_variant';
}

function TabSelect(url) {
    if (!CheckModifiedAndConfirm())
        return;
    window.location.href = url;
}


function OnPartsChanged() {
    var elem;
    elem = document.getElementById('id_save_parts_disabled');
    if (elem)
        elem.style.display = 'none';
    elem = document.getElementById('id_save_parts');
    if (elem)
        elem.style.display = 'inline-block';
}


function UpdateTableColumnWidth() {
    var headertable = document.getElementById('id_tableHeader');
    var headercols = document.getElementById('id_headercols');
    var no = headercols.childNodes.length - 1;
    var bodytable = document.getElementById('id_tableBody');

    var tdlist = bodytable.rows[0];
    var nu = tdlist.childNodes.length;
    var wo, wu, tdu, tdo, i;

    wu = bodytable.offsetWidth + 10;
    wo = headertable.offsetWidth;

    if (wu > wo)
        headertable.style.width = wu + 'px';
    else
        bodytable.style.width = (wo - 10) + 'px';

    for (i = 0; i < no; i++) {
        tdu = tdlist.cells[i];
        tdo = headercols.childNodes[i];

        wu = tdu.offsetWidth;
        tdo.style.width = wu + 'px';

        wo = tdo.offsetWidth;
        if (wu != wo) {
            tdu.style.width = wo + 'px';
        }
    }

}

(function () {

    function OnSelectVariantType() {
        var index = this.selectedIndex;
        var value = this.options[index].value;
        var idSel = 'select_' + variantType;
        var sel, vis, activeSel;

        if (!CheckModifiedAndConfirm())
            return;

        sel = document.getElementById(idSel);
        if (sel)
            sel.style.display = 'none';

        activeSel = document.getElementById('select_' + value);
        if (activeSel)
            activeSel.style.display = 'inline-block';

        vis = (value == initType) ? 'visible' : 'hidden';
        sel = document.getElementById('idRechte');
        if (sel)
            sel.style.visibility = vis;

        var caption = document.getElementById('id_captionTr51');
        if (caption)
            caption.innerHTML = (value == 'suchliste') ? 'Suchergebnis' : 'Fahrzeugvariante';

        variantType = value;

        if (activeSel) {
            if (activeSel.selectedIndex >= 0)
                OnSelectVariant(activeSel);
        }
    }

    function OnEcuChanged() {
        var index = this.selectedIndex;
        var value = this.options[index].value;
        var idSel = 'idEcuVersion' + selectedEcu;
        var sel, page;

        sel = document.getElementById(idSel);
        if (sel)
            sel.style.display = 'none';

        sel = document.getElementById('idEcuVersion' + value);
        if (sel)
            sel.style.display = 'inline';

        page = document.getElementById('idEcuRechts');
        if (page)
            page.style.display = (sel.selectedIndex >= 0) ? 'flex' : 'none';

        page = document.getElementById('idEcuBack');
        if (page)
            page.style.display = (sel.selectedIndex >= 0) ? 'none' : 'flex';

        selectedEcu = value;
    }


    function OnTabClick() {
        // var id = this.id;
        // alert (this.id);
    }


    var headroom = null;
    var scrlHeader = null;
    var scrlTable = null;

    function OnScroll() {
        headroom.style.left = -scrlTable.scrollLeft + 'px';
    }


    var mfWidth = 0;
    var mfHeight = 0;
    var offsWidth = 0;
    var offsHeight = 0;

    function InitMainFrame(me) {
        mfWidth = me.offsetWidth;
        mfHeight = me.offsetHeight;
        scrlHeader = document.getElementById('id_scrollHeader');
        scrlTable = document.getElementById('id_scrollTable');
        headroom = document.getElementById('id_headroom');

        if (scrlTable && headroom) {
            headroom.style.left = '0px';
            scrlTable.scrollTo(0, 0);
            scrlTable.addEventListener('scroll', OnScroll);
            /*
            var scWidth = scrollbox.offsetWidth;
            offsWidth   = (mfWidth  - scrollbox.offsetWidth) + 3;
            offsHeight  = (mfHeight - scrollbox.offsetHeight) + 3;
            */
            // me.style.width = (mfWidth+3) + 'px';
            /*
            scrollbox.style.width   = (mfWidth - offsWidth) + 'px';
            scrollbox.style.height  = (mfHeight - offsHeight) + 'px';
            alert ('mfWidth: ' + mfWidth  + "\nscWidth: " + scWidth);#
            */
        }

    }

    function OnFrameResize() {

    }

    function init() {
        var elem = document.getElementById('selectVariantType');
        if (elem)
            elem.addEventListener('change', OnSelectVariantType);

        var elem = document.getElementById('idSelectedEcu');
        if (elem)
            elem.addEventListener('change', OnEcuChanged);

        elem = document.getElementById('tab0');
        if (elem)
            elem.addEventListener('click', OnTabClick);

        elem = document.getElementById('tab1');
        if (elem)
            elem.addEventListener('click', OnTabClick);

        elem = document.getElementById('id_mainframe');
        if (elem) {
            elem.addEventListener('resize', OnFrameResize);
            InitMainFrame(elem);
        }
    }

    function OnLoad() {
        if (variantId)
            doScroll('select_' + initType);
    }

    document.addEventListener('DOMContentLoaded', init);
    window.addEventListener('load', OnLoad);


    //#######################################################################################

    function getPrivUser(fullid, xu, param) {
        var xuid, result = '';

        $(fullid + ' option').each(function () {
            xuid = $(this).val();
            if (xuid[0] == xu)
                result += ',' + xuid.substring(2);
        });
        if (result.length)
            return param + result.substring(1);

        return '';
    }

    function getAddedPriv(id) {
        return getPrivUser('#id-writer-' + id, 'E', '&addUser=');
    }

    function getRemovedPriv(id) {
        return getPrivUser('#id-engineers-' + id, 'W', '&dropUser=');
    }

    //#######################################################################################

    function BuildPrivilegsChangeParams(id) {
        var result = '&privId=' + id;
        var addUser = getAddedPriv(id);
        var dropUser = getRemovedPriv(id);
        return result;
    }

    //#######################################################################################
    function getXuidFromCmd(id, cmd) {
        var xuid = 0;
        var error_name = '';
        var stop_remove_fixed = false;

        switch (cmd) {
            case 'add':
            case 'add_owner':
                $('#id-engineers-' + id).each(function () {
                    xuid = $(this).val();
                });
                break;

            case 'rem':
                $('#id-writer-' + id).each(function () {
                    xuid = $(this).val();
                });
                if ((xuid[0] == 'B') || (xuid[0] == 'C'))
                    error_name = $('#id-writer-' + id + " option[value='" + xuid + "']").text();
                break;


            case 'rem_owner':
                $('#id-owner-' + id).each(function () {
                    xuid = $(this).val();
                });
                if ((xuid[0] == 'B') || (xuid[0] == 'C'))
                    error_name = $('#id-owner-' + id + " option[value='" + xuid + "']").text();
                break;

            case 'to_owner':
                $('#id-writer-' + id).each(function () {
                    xuid = $(this).val();
                });
                break;

            case 'to_writer':
                $('#id-owner-' + id).each(function () {
                    xuid = $(this).val();
                });
                break;

        }

        if (error_name != '') {
            alert('Benutzer ' + error_name + ' kann nicht entfernt werden.');
            return 0;
        }
        return xuid;
    }

    //#######################################################################################
    function HandlePrivilegsCommand(linkObject, useAjax) {
        if (linkObject.hasClass('inactiveLink'))
            return;

        var cmd = linkObject.data('cmd');
        var id = linkObject.closest('.priv_root').data('id');
        var xuid, params, ajaxresult;

        xuid = getXuidFromCmd(id, cmd);
        if (xuid == 0)
            return;

        if (useAjax) {
            ajaxresult = parseInt(Ajaxecute('priv-' + cmd, 'xuid=' + xuid + '&privId=' + id));
            console.log(ajaxresult);
            if (ajaxresult > 0)
                return false;
        }


        switch (cmd) {
            case 'add':
                $('#id-engineers-' + id + ' option:selected').remove().appendTo('#id-writer-' + id);
                $('#id-writer-' + id).focus();
                break;

            case 'add_owner':
                $('#id-engineers-' + id + ' option:selected').remove().appendTo('#id-owner-' + id);
                $('#id-owner-' + id).focus();
                break;

            case 'rem':
                $('#id-writer-' + id + ' option:selected').remove().appendTo('#id-engineers-' + id);
                $('#id-engineers-' + id).focus();
                break;

            case 'rem_owner':
                $('#id-owner-' + id + ' option:selected').remove().appendTo('#id-engineers-' + id);
                $('#id-engineers-' + id).focus();
                break;

            case 'to_owner':
                $('#id-writer-' + id + ' option:selected').remove().appendTo('#id-owner-' + id);
                $('#id-owner-' + id).focus();
                break;

            case 'to_writer':
                $('#id-owner-' + id + ' option:selected').remove().appendTo('#id-writer-' + id);
                $('#id-writer-' + id).focus();
                break;

            case 'save':
                if ($(this).children("div").hasClass('disabled'))
                    return false;

                params = BuildPrivilegsChangeParams(id);
                document.location.href = '?' + _urlparams + '&command=save_privileges' + params;
                return false;

            case 'abort':
                var url = '?' + _urlparams + '&command=cancel_privileges';
                document.location.href = url;
                return false;
        }

        var removeable = (xuid[0] != 'C');
        switch (cmd) {
            case 'add':
            case 'to_writer':
                EnablePrivLink(removeable, '#id-rem-' + id);
                EnablePrivLink(true, '#id-to_owner-' + id);
                EnablePrivLink(false, '#id-add-' + id);
                EnablePrivLink(false, '#id-add_owner-' + id);
                EnablePrivLink(false, '#id-rem_owner-' + id);
                EnablePrivLink(false, '#id-to_writer-' + id);
                break;

            case 'add_owner':
            case 'to_owner':

                EnablePrivLink(removeable, '#id-rem_owner-' + id);
                EnablePrivLink(true, '#id-to_writer-' + id);
                EnablePrivLink(false, '#id-add-' + id);
                EnablePrivLink(false, '#id-add_owner-' + id);
                EnablePrivLink(false, '#id-rem-' + id);
                EnablePrivLink(false, '#id-to_owner-' + id);
                break;

            case 'rem':
            case 'rem_owner':
                EnablePrivLink(true, '#id-add-' + id);
                EnablePrivLink(true, '#id-add_owner-' + id);
                EnablePrivLink(false, '#id-rem-' + id);
                EnablePrivLink(false, '#id-rem_owner-' + id);
                EnablePrivLink(false, '#id-to_owner-' + id);
                EnablePrivLink(false, '#id-to_writer-' + id);
                break;
        }


        var changes = getAddedPriv(id) + getRemovedPriv(id);
        if (changes.length)
            $('#id-save-' + id).removeClass('disabled');
        else
            $('#id-save-' + id).addClass('disabled');

        return false;
    }

    function HandlePrivilegsCommand_Standard() {
        HandlePrivilegsCommand($(this), false);
    }

    function HandlePrivilegsCommand_Enhanced() {
        HandlePrivilegsCommand($(this), true);
    }

    //#######################################################################################
    function EnablePrivLink(enable, linkId) {
        var linkObject = $(linkId);
        if (!linkObject)
            return;

        if (enable) {
            linkObject.removeClass('inactiveLink');
        } else {
            linkObject.addClass('inactiveLink');
        }
    }

    //#######################################################################################
    function HighlightSelect(type, id) {
        if (type != 'owner')
            $('#id-owner-' + id).val(0);

        if (type != 'writer')
            $('#id-writer-' + id).val(0);

        if (type != 'engineers')
            $('#id-engineers-' + id).val(0);
    }


    //#######################################################################################
    function HandlePrivilegsEnable() {
        var n = $("option", this).length;
        var typ = $(this).data('type');
        var id = $(this).closest('.priv_root').data('id');
        var xuid = $(this).val();
        var enable;

        enable = (typ == 'engineers') && (n > 0);
        EnablePrivLink(enable, '#id-add-' + id);
        EnablePrivLink(enable, '#id-add_owner-' + id);

        enable = (typ == 'owner') && (n > 0);
        EnablePrivLink(enable, '#id-to_writer-' + id);
        enable = enable && (xuid[0] != 'C')
        EnablePrivLink(enable, '#id-rem_owner-' + id);

        enable = (typ == 'writer') && (n > 0);
        EnablePrivLink(enable, '#id-to_owner-' + id);
        enable = enable && (xuid[0] != 'C')
        EnablePrivLink(enable, '#id-rem-' + id);

        HighlightSelect(typ, id);
    }

    //#######################################################################################

    var prevPriv = null;

    function ClosePrevPriv() {
        if (prevPriv) {
            prevPriv.children('.enhanced_priv_edit').css('visibility', 'hidden');
            prevPriv.css('width', prevPriv.data('widthbak'));
            //prevPriv.css('z-index', 1);
            prevPriv = null;
        }
    }

    function HandlePrivilegsClick(panel) {
        var id = panel.data('id');
        var edit = panel.children('.enhanced_priv_edit');

        ClosePrevPriv();

        panel.data('widthbak', panel.css('width'));
        panel.css('width', '420px');
        //panel.css ('z-index', 10);
        prevPriv = panel;
        edit.css('visibility', 'visible');
    }

    function CopyParams() {
        var csv_params = "";
        $('.param_selector:checked').each(function () {
            csv_params += String($(this).data('param')) + ',';
        });
        if (csv_params.length)
            $('#copy_param_list').val(csv_params);
        SaveParametersX('copyparams', 'idl');
    }

    //#######################################################################################
    jQuery(document).ready(function ($) {
        $('.enhanced_privilegs').click(function () {
            HandlePrivilegsClick($(this));
            return false;
        });

        $('.verantwortliche .privLabel').click(function () {
            HandlePrivilegsClick($(this).next('.enhanced_privilegs'));
            return false;
        });

        $('.verantwortliche').click(ClosePrevPriv);

        $('.privileges-select').click(HandlePrivilegsEnable);

        $('.single_priv_div .privCommand').click(HandlePrivilegsCommand_Standard);
        $('.enhanced_privilegs .privCommand').click(HandlePrivilegsCommand_Enhanced);

        $('#cb_select_all').click(function () {
            $('.param_selector').prop('checked', $(this).is(':checked'));
        });

        $('#id_copyparams').click(CopyParams);

        $('#id_scrollbox').scrollTop(scrollpos_main);

        $('.datepicker').datepicker({});
    });

}());


















