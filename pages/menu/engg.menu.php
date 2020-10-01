<?php
$specialapp = '';
$selected = [];
$iSel = 0;

switch ($_GET['action']) {
    case '':
    case 'home':
        $iSel = 1;
        break;

    case 'search':
    case 'produzierte':
    case 'ausgelieferte':
    case 'vehiclebooking':
    case 'documentvehicles':
    case 'tagesstatistik':
    case 'verlaufsdaten':
    case 'dtcverwaltung':
        $iSel = 2;
        break;

    case 'vehicleConfigurations':
    case 'updateinterface':
    case 'taskanzeige':
    case 'ebomanzeige':
        $iSel = 3;
        break;

    case 'parameterManagement':
    case 'parameterSettings':
    case 'propertySettings':
    case 'diagSwValSet':
    case 'addEcu':
    case 'diagSwParamManagement':
        $iSel = 4;
        break;

    case 'globalParameters':
    case 'globalValuesSets':
        $iSel = 5;
        break;

    case 'cocParameters':
    case 'cocValuesSets':
//    case 'parameterlist':
        $iSel = 6;
        break;

    case 'teoexceptions':
    case 'specialapp':
        $iSel = 7;
        break;

    case 'userrole2Functionality':
    case 'userrole2Company':
    case 'responsiblePersons':
    case 'manageFunctions':
    case 'uploadHr':
    case 'uploadHrHistory':
        $iSel = 8;
        break;

//    Not removed to easy find in future
//    case 'adminHistory':
//    case 'serverKeys':
//    case 'teoexceptions':
//    case 'vehiclebooking':
//    case 'entwicklung':
//    case 'parameterlist':
//    case 'ebom':
//    case 'ecuSwConf':
//    case 'diagSwValSet':
//        break;

}

$selected[$iSel] = " selected";
if (isset ($GLOBALS['pageController'])) {
    $user = $GLOBALS['pageController']->GetObject('user');
}

echo <<<HEREDOC
	<div class="row ">
        <div class="columns twelve">
            <ul class="submenu_ul engg_menu">

            <li class="dropdown">
	            <div class="sts_submenu {$selected[2]}">{$this->translate['enggMenu']['btnVehicles']}</div>
	            <div class="dropdown-content">
	                <a href="?action=search">{$this->translate['enggMenu']['itemVehicleSearch']}</a>
	                <a href="?action=produzierte">{$this->translate['enggMenu']['itemListOfProducedVehicles']}</a>
					<a href="?action=ausgelieferte&initPage">{$this->translate['enggMenu']['itemListOfDeliveredVehicles']}</a>
	                <!-- <a href="?action=tagesstatistik&initPage">{$this->translate['enggMenu']['itemDailyStatistics']}</a> -->
					<!-- <a href="?action=verlaufsdaten&initPage">{$this->translate['enggMenu']['itemHistory']}</a> -->
	                <a href="?action=dtcverwaltung&initPage">{$this->translate['enggMenu']['itemDTCMagenament']}</a>
	                <a href="?action=fehlerliste">{$this->translate['enggMenu']['itemErrorList']}</a>
					<a href="?action=vehiclebooking&initPage">{$this->translate['enggMenu']['itemTestVehicleBooking']}</a>
					<a href="?action=documentvehicles&initPage">{$this->translate['enggMenu']['itemTestVehicleDocuments']}</a>
	            </div>
            </li>

            <li class="dropdown">
                <div class="sts_submenu {$selected[3]}">{$this->translate['enggMenu']['btnVehiclesConf']}</div>
                <div class="dropdown-content">
					<a href="?action=vehicleConfigurations">{$this->translate['enggMenu']['itemVehiclesConfAdministration']}</a>
					<a href="#" class="ui-state-disabled">{$this->translate['enggMenu']['itemVehiclesConfDisplay']}</a>
					<a href="#" class="ui-state-disabled">{$this->translate['enggMenu']['itemVehiclesPropAdministration']}</a>
                    <a href="?action=updateinterface&initPage">{$this->translate['enggMenu']['itemUpdateIterface']}</a>
                    <a href="?action=taskanzeige">{$this->translate['enggMenu']['itemTask']}</a>
                    <a href="?action=ebomanzeige&initPage">{$this->translate['enggMenu']['itemDisplayEBOM']}</a>
                </div>
            </li>
            
            <li class="dropdown">
                <div class="sts_submenu {$selected[4]}">{$this->translate['enggMenu']['btnECUs']}</div>
                <div class="dropdown-content">
                    <a href="?action=parameterManagement">{$this->translate['enggMenu']['itemECUsParamManagement']}</a>
					<a href="?action=parameterSettings">{$this->translate['enggMenu']['itemECUsSoftwareManagement']}</a>
					<a href="?action=addEcu">{$this->translate['enggMenu']['itemAddEcu']}</a>
					<a href="?action=propertiesManagement">{$this->translate['enggMenu']['itemECUsPropManagement']}</a>
					<a href="#" class="ui-state-disabled">{$this->translate['enggMenu']['itemECUsPropAssignment']}</a>
                    <a href="#" class="ui-state-disabled">{$this->translate['enggMenu']['itemECUsSoftwareUpload']}</a>
                	<a href="?action=diagSwValSet&initPage">{$this->translate['enggMenu']['itemECUsODXConf']}</a>
					<a href="#" class="ui-state-disabled">{$this->translate['enggMenu']['itemECUsEoLTester']}</a>
					<a href="?action=diagSwParamManagement">{$this->translate['enggMenu']['diagSwParamManagement']}</a>		
                </div>
            </li>
            
            <li class="dropdown">
                <div class="sts_submenu {$selected[5]}">{$this->translate['enggMenu']['btnGlobalParam']}</div>
                <div class="dropdown-content">
					<a href="?action=globalParameters">{$this->translate['enggMenu']['itemGlobalParamManagement']}</a>
	                <a href="?action=globalValuesSets">{$this->translate['enggMenu']['itemGlobalParamAssigment']}</a>
                </div>
            </li>
            
            <li class="dropdown">
                <div class="sts_submenu {$selected[6]}">{$this->translate['enggMenu']['btnCoc']}</div>
                <div class="dropdown-content">
	                <a href="?action=cocParameters">{$this->translate['enggMenu']['itemCocParameterManagement']}</a>
	                <a href="?action=cocValuesSets">{$this->translate['enggMenu']['itemCocParameterAssignment']}</a>
	                <a href="?action=cocGenerationEngg">{$this->translate['enggMenu']['itemCocGeneration']}</a>
                </div>
            </li>
            
            <li class="dropdown">
            <div>
                <div class="sts_submenu {$selected[7]}">{$this->translate['enggMenu']['btnQuality']}</div>
                <div class="dropdown-content">
                	<a href="?action=teoexceptions&initPage">{$this->translate['enggMenu']['itemAdministrationDeferal']}</a>
                    <a href="?action=specialapp&initPage">{$this->translate['enggMenu']['itemPermitsVehicle']}</a>
                    <a href="#" class="ui-state-disabled">{$this->translate['enggMenu']['itemApprovalDisplayEoL']}</a>
                </div>      
            </li>
HEREDOC;

if ($user->IsAdmin()) {
echo <<<HEREDOC
            <li class="dropdown" {$display}>
                <div class="sts_submenu {$selected[8]}">{$this->translate['enggMenu']['btnAdministration']}</div>
                <div class="dropdown-content">
                	<a href="?action=userrole2Functionality">{$this->translate['enggMenu']['itemUserRoleAdministration']}</a>
                	<a href="?action=userrole2Company">{$this->translate['enggMenu']['itemUserRoleAssignment']}</a>
                    <a href="?action=responsiblePersons">{$this->translate['enggMenu']['itemAdministrationResponsibilities']}</a>
                    <a href="?action=useradministration">TEO Manager</a>
<!--                	<a href="?action=responsiblePersons2"><b>Administration Responsibilities old</b></a>-->
                	<a href="?action=manageFunctions">{$this->translate['enggMenu']['itemAdministrationWritePermission']}</a>
                	<a href="?action=uploadHr">{$this->translate['enggMenu']['itemUserListUpload']}</a>
                	<a href="?action=uploadHrHistory">{$this->translate['enggMenu']['itemUserListUploadHistory']}</a>
                	<a href="#" class="ui-state-disabled">{$this->translate['enggMenu']['itemApprovalSpecial']}</a>
	                <a href="#" class="ui-state-disabled">{$this->translate['enggMenu']['itemVehicleHistory']}</a>
		            <a href="#" class="ui-state-disabled">{$this->translate['enggMenu']['itemUploadServerKeys']}</a>
<!--		        <a href="?action=translations" >{$this->translate['enggMenu']['itemTranslations']}</a> -->
                </div>
            </li>
HEREDOC;
}

echo <<<HEREDOC
	    </ul>		                
	</div>
</div>
HEREDOC;
?>


