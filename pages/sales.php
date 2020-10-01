<?php
/**
* sales.php
* Template für die Benutzer Rolle Sales
* @author Pradeep Mohan
*/

$pageoptions='
<div class="pager">
<a href="#" class="first"  title="Erste Seite" >Erste</a>
<a href="#" class="prev"  title="Vorherige Seite" ><span class="genericon genericon-previous"></span>Vorherige Seite</a>
<span class="pagedisplay"></span> <!-- this can be any element, including an input -->
<a href="#" class="next" title="Nächste Seite" >Nächste Seite<span class="genericon genericon-next"></span></a>
<a href="#" class="last"  title="Letzte Seite" >Letzte</a>

Seite: <select class="gotoPage"></select>
Zeile pro Seite: <select class="pagesize">
<option value="20">20</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="300">300</option>
</select>
</div>';

?>
<div class="inner_container" style="min-height: 600px">
<?php include $_SERVER['STS_ROOT']."/pages/menu/sales.menu.php"; ?>
	<div class="row ">
		<div class="columns six">
			<span class="error_msg">
			<?php if(is_array($this->msgs)) echo implode('<br>',$this->msgs); ?>
			</span>
		</div>
	</div>
	<?php if(isset($this->commonVehicleMgmtPtr)) : ?>
	<div id="fahrzeugverwaltung" class="submenu_target_child current">
		<div class="columns twelve ">
		<h1>Fahrzeug Verwaltung</h1>
			<?php
				echo $this->commonVehicleMgmtPtr->printContent();
			?>
		</div>
	</div>
	<?php endif;
	if ($this->action=="home")
	{
	    include "./actions/html/Sales.Home.Welcome.php";
	}
	if ($this->action=="dritt"): ?>
	<div id="pvssop" class="submenu_target_child current">
		<div class="columns twelve ">
		<h1>Drittkunden Fahrzeuge</h1>
			<?php
				if (isset($this->newVehiclesList)) echo $this->newVehiclesList;
				else if (isset($this->qform_vehicle)) echo $this->qform_vehicle->getContent();
			?>
		</div>
	</div>
	<?php elseif ($this->action=="showDivisionsDeliveryPlan"): ?>

	<div id="showDivisionsDeliveryPlan" class="submenu_target_child current">
	<div class="row">
		<div class="columns twelve" >
	<h1>Auslieferungsplan <?php echo date('Y');?></h1>
	<?php
		if(isset($this->qform_monthyear))
		echo $this->qform_monthyear->getContent();
	?></div>
	</div>
	<?php
	if(isset($this->deliveryToDivisionResults))
	{

		$displaytable = new DisplayTable (array_merge(array(),$this->deliveryToDivisionResults),array('id'=>''));
		echo $displaytable->getContent();
		//echo '<a href="?action=resetProductionPlan&yearmonth='.$_POST['yearmonth'].'&variant_value='.$_POST['variant_value'].'">Produktionsplan löschen</a>';
	}
	else if(isset($this->delivery_plan_week))
	{
	    echo $this->delivery_plan_week->getContent();
	}
	?>
	<?php if(isset($this->qform_delplan)) :?>
		<div class="row">
			<div class="columns ten" >
			<?php echo $this->qform_delplan->getContent();?><br><br>
			</div>
		</div>
		<?php
		elseif (isset($this->qform_delplan_week)):?>
		<div class="row">
			<div class="columns ten" >
			<?php echo $this->qform_delplan_week->getContent();?><br><br>
			</div>
		</div>
		<?php
		endif;
		?>
	</div>
	<?php elseif ($this->action=="showProPlan"): ?>

	<div id="showProPlan" class="submenu_target_child current">
	<div class="row">
		<div class="columns twelve" >
	<h1>Produktionsplan <?php echo date('Y');?></h1>
	<?php
		if(isset($this->qform_monthyear))
		echo $this->qform_monthyear->getContent();
	?></div>
	</div>

	<?php if(isset($this->qform_proplan)) :?>
		<div class="row">
			<div class="columns ten" >
			<?php echo $this->qform_proplan->getContent();?><br><br>
			</div>
		</div>
		<?php
		endif;
	?>
	</div>
	<?php elseif ($this->action=="manuell_auslieferung"): ?>

	<div id="manuell_auslieferung" class="submenu_target_child current">
		<div class="row">
				<div class="columns twelve" >
					<h1>Manuell Auslieferung</h1>
					<?php
					if(isset($this->content)) echo '<span class="error_msg"><strong>'.$this->content.'</strong></span>';?>
				</div>
		</div>
		<div class="row">
				<div class="columns four" style="background: #F1F1F1;padding: 1.2em; border-radius: 4px">
				<?php
				if(isset($this->available_vehicle_count))
				{	foreach($this->available_vehicle_count as $vehicle_variant)
					{
						echo $this->vehicle_variants[$vehicle_variant['vehicle_variant']].' : '.$vehicle_variant['vcnt'].' Fahrzeuge verfügbar <br>';
					}
				}
				echo 'B16 Aftersales : '.$this->aftersalescount.'<br>';
				echo 'B16 Produktion: '.$this->produktioncount.'<br>';
				?>
				</div>
		</div>
		<div class="row">
				<div class="columns twelve" >
				<?php
// 				if(isset($this->content))
// 					echo $this->content;
// 				if(isset($this->debugcontent))
// 					echo $this->debugcontent;
				if(isset($this->manual_delivery_content)):
					echo $this->manual_delivery_content;
				endif;
				?>
			</div>
		</div>
	</div>
	<?php elseif ($this->action=="depotShow"): ?>

	<div id="depotShow" class="submenu_target_child current">
		<div class="row">
			<div class="columns twelve" >
			<h1>ZSP Zuordnung Ansicht</h1>
				<?php
				if(isset($this->depotShowContent)):
					$processed_listObjects=array(array('headingone'=>array('Niederlassung','ZSP','Fahrzeug Variante','Freie Ladesäulen')));
				foreach($this->depotShowContent as &$single):
					$single['vehicle_variant_value_allowed']=$this->vehicle_variants[$single['vehicle_variant_value_allowed']];
				endforeach;

					$displaytable = new DisplayTable (array_merge($processed_listObjects,$this->depotShowContent),array('id'=>''));
					echo $displaytable->getContent();
				endif;
				?>
			</div>
		</div>
	</div>
	<?php elseif($this->action=="newvehiclespost"):
		if (isset($this->newVehiclesList)):
			echo $this->newVehiclesList;
		else:
					?>
		<div id="newvehiclespost" class="submenu_target_child current">
		<div class="row">
			<div class="columns eight ">
			<?php
				if(isset($this->vehiclesPost))
				{
					$processed_listObjects=array('headingone'=>array('Batch','Start AKZ','Start IKZ','Anzahl der Fahrzeuge','Beaufgetragt am','Added by','Action'));

					if(!empty($this->vehiclesPost))
					{
					foreach ($this->vehiclesPost as $batch)
					{
						?>
						<form action="index.php" method="post">
							<?php
							$thisbatch=array();

							foreach($batch as $key=>$value)
							{
								if($key=='addedby_userid')
								{
									$username=$this->ladeLeitWartePtr->allUsersPtr->getUserNameFromId($value);
									$thisbatch[]=$username;
								}
								else
									$thisbatch[]=$value;
							}


							$thisbatch[]='<a href="?action=newvehiclespost&batch='.$batch['tempid'].'">Bearbeiten</a>';
							$processed_listObjects[]=$thisbatch;
							?>
						</form>
						<?php

					}
					$displaytable = new DisplayTable ($processed_listObjects,array('id'=>'zentralelist'));
					echo $displaytable->getContent();
					}
					else
						echo 'Keine Neue Fahrzeuge!';
				}
				?>
			</div>
		</div>
	</div>
	<?php endif;
    elseif ($this->action=="transporter_manage"):
    ?>
		<div id="transporter_manage" class="row submenu_target_child <?php if ($this->action=="transporter_manage") echo ' current '; ?>"   >
		<div class="columns eight">
		<h1>Spediteure verwalten</h1>
		<form action="index.php" method="POST">
		<?php
            if(isset($this->transporter_manage)):
            {
                echo '<div class="quickform zebra" >';
                echo '<fieldset class="row pad_large">';
                echo '<fieldset class="columns three"><strong>Spediteuer</strong></fieldset>';
                echo '<fieldset class="columns eight">
                          <span class="columns one">Bündle</span>
                          <span class="columns three">Work</span>
                          <span class="columns three">Work L</span>
                          <span class="columns three">Work XL</span>
                     </fieldset>';
                echo '</fieldset>';
                foreach($this->transporter_manage as $transporter)
                {
                    echo '<fieldset class="row pad_large">';
                    echo '<fieldset class="columns three">'.$transporter['name'].'</fieldset>';
                    echo '<fieldset class="columns eight">';
                    $transporter_id=$transporter['transporter_id'];
                    $bundle_id=0;
                    if(!empty($transporter['bundles']))
                    {
                        foreach($transporter['bundles'] as $bundle_id=>$bundle)
                        {
                            echo '<div class="row pad_large"><span class="columns one no_pad"><strong>'.$bundle_id.'</strong></span>';
                            ksort($bundle);
                            foreach($bundle as $stype=>$stypecnt)
                            {
                                echo '<input type="number" class="columns three"
	                              name="tid_'.$transporter_id.'_bid_'.$bundle_id.'_stype_'.$stype.'" value="'.$stypecnt.'">';
                            }
                            echo '</div><br>';
                        }
                    }
                    echo '<input type="hidden" name="max_bundle_transporter_'.$transporter_id.'"
                                               id="max_bundle_transporter_'.$transporter_id.'"
                                        value="'.$bundle_id.'">
                                        <a href="#" class="new_bundle_add"
                                        data-super_types_list="'.implode(',',$this->super_type_ids).'"
                                        data-transporter="'.$transporter_id.'"
                                        data-bundle_id="'.++$bundle_id.'">Neue Bündle hinzufügen
                        </a><br></fieldset>';
                    echo '</fieldset>';
                }
                echo '</div>';

            }
            endif;
     	?>
     	<input type="hidden" name="action" value="transporter_manage">
     	<input type="submit" name="save_transporter" value="Speichern">
     	</form>
		</div>
	</div>
	<?php
	elseif ($this->action=="pool_redeliver"):?>
	<div id="pool_redeliver" class="row submenu_target_child <?php if ($this->action=="pool_redeliver") echo ' current '; ?>"   >
		<div class="columns eight quickform">
		<h1>Sts_Pool Fahzeuge an ZSP zuweisen</h1>
		Hier werden nur die Fahrzeuge die folgende Bedingungen erfüllen angezeigt.
		<ol>
		<li>Fahrzeug ist an Sts_Pool zugewiesen </li>
		<li>Fahrzeug ist schon einmal ausgeliefert </li>
		<li>Fahrzeug hat gültige Positionsdaten</li>
		<li>Fahrzeug Positionsdaten lautet dass, das Fahrzeug weiter als 10 km entfernt von Sts_Pool (Würselen) ist.</li>
		</ol>
			<form action="index.php" method="POST">
				<fieldset class="row pad_large">
					<fieldset class="columns three">
					<h3>Fahrzeug auswählen</h3>
						<select name="pool_vehicle" id="pool_vehicle">
            			<?php foreach($this->vehicles as $vehicle)
            			{
            			    echo '<option value="'.$vehicle['vehicle_id'].'">'.$vehicle['vin'].'</option>';
            			}
            			?>
            			</select>
            		</fieldset>
                    <fieldset class="columns three">
                    <h3>ZSP auswählen</h3>
                    <?php $depots=$this->ladeLeitWartePtr->depotsPtr->getAllValidDepots();?>
                    <select name="pool_vehicle_depot" id="pool_vehicle_depot">
                    <option></option>
                    		<?php
                    		foreach($depots as $depot)
                    		{
                    		    echo '<option value="'.$depot['depot_id'].'">'.$depot['name'].'('.$depot['dp_depot_id'].')</option>';
                    		}?>
                    </select>
                    </fieldset>
				</fieldset>
                <fieldset class="row pad_large">
                    <input type="hidden" name="action" value="pool_redeliver">
                    <input type="submit" name="pool_redeliver_save" value="Zuweisen">
                </fieldset>
			</form>
		</div>
	</div>
	<?php endif;?>
	<div id="newvehicles" class="row submenu_target_child <?php if ($this->action=="newvehicles") echo ' current '; ?>"   >
		<div class="columns twelve">
			<?php
			    if (isset($this->csvTool) && ($this->csvTool->step < 2))
			    {
			         $this->csvTool->WriteContent ();
			    }
				else if (isset($this->newVehiclesList))
				{
				    echo $this->newVehiclesList;
				}
				else if (isset($this->qform_vehicle))
				{
				    echo $this->qform_vehicle->getContent();
				}
			?>

		</div>
	</div>

	<div id="vehicleoverview" class="submenu_target_child <?php if ($this->action=="overview") echo ' current '; ?>">
		<div class="columns twelve ">
		<h1>Fahrzeug Übersicht</h1>
		<a href="#" class="allsave">Alle änderungen speichern</a><br>
		<span class="save_status error_msg"></span><br>
			<?php echo $pageoptions; ?>
			<div style="overflow-x: scroll">
			<?php
				$processedVehicles[]=array('headingone'=>$this->vehiclesHeadings);

				if(!empty($this->vehicles))
				{
// 					foreach($this->vehicles as $vehicle)
// 					{
// 						$vehicleid=$vehicle['vehicle_id'];

// 						foreach($vehicle as $key=>&$eachcol)
// 						{
// 							if(!$eachcol)
// 								$eachcol='--';

// 							if ($key=='vehicle_id' || $key=='dname')
// 								$eachcol='<span id="'.$key.'-'.$vehicleid.'" >'.$eachcol.'</span>';
// 							else if ($key=='production_date' || $key=='delivery_date' )
// 							{
// 								if($eachcol!='--')
// 									$eachcol='<span id="'.$key.'-'.$vehicleid.'" class="editable">'.date('Y-m-d', strtotime($eachcol)).'</span>';
// 								else
// 									$eachcol='<span id="'.$key.'-'.$vehicleid.'" class="editable">--</span>';
// 							}
// 							else if($key=='comments')
// 							{
// 								if(!empty($eachcol) &&  $eachcol!='--')
// 								{
// 									//@todo check how to edit these comments.. or rather add these as attribute values
// 									if(unserialize($eachcol)!==false)
// 									{
// 										$eachcol=unserialize($eachcol);
// 									}

// 									$comment_string='';

// 									if(is_array($eachcol))
// 									{
// 										foreach($eachcol as $singlecomment)
// 											$comment_string.=$singlecomment['addedon'].':'.$singlecomment['content'];
// 									}
// 									else
// 										$comment_string.=$eachcol;

// 									$eachcol='<span id="'.$key.'-'.$vehicleid.'" class="editable" >'.$comment_string.'</span>';
// 								}
// 								else
// 									$eachcol='<span id="'.$key.'-'.$vehicleid.'" class="editable">'.$eachcol.'</span>';

// 							}
// 							else
// 								$eachcol='<span id="'.$key.'-'.$vehicleid.'" class="editable">'.$eachcol.'</span>';



// 						}
// 					 	$processedVehicles[]=array($vehicle["vehicle_id"],$vehicle["tsnumber"],$vehicle["vin"],$vehicle["code"	],$vehicle["ikz"],$vehicle["production_date"],
// 					 									$vehicle["delivery_date"],$vehicle["delivery_week"],$vehicle["coc"],$vehicle["vorhaben"],$vehicle["dname"],'',$vehicle['comments']);

// 					}

					$displaytable = new DisplayTable ($processedVehicles,array('id'=>'vehicles_list_table'));
					echo '<form method="post">'.$displaytable->getContent().'</form>';
				}
				else
					echo "Keine Fahrzeuge gefunden!";
				?>
			</div>
			<?php echo $pageoptions; ?>
		</div>
		<div style="display:none"><table id="edited_data"><thead><th>VIN</th><th>Alte Wert</th><th>Neue Wert</th></thead><tbody></tbody></table></div>
	</div>



	<div id="exportcsv" class="row submenu_target_child <?php if ($this->action=="exportcsv" || $this->action=="saveexportcsv") echo ' current '; ?>"   >
		<div class="columns twelve">
			<h1>CSV Export</h1>
			<?php  if(isset($this->qform_csv)) echo $this->qform_csv->getContent();
					else echo $this->listofoptions;
			?>
		</div>
	</div>


	<div id="exportpdf" class="row submenu_target_child <?php if ($this->action=="exportpdf") echo ' current '; ?>"   >
		<div class="columns twelve">
			<h1>PDF Export</h1>
			<?php  if(isset($this->pdfLink)) echo $this->pdfLink;
					else if(isset($this->qform_pdf)) echo $this->qform_pdf->getContent();
			?>
		</div>
	</div>

	<div id="begleitschein" class="row submenu_target_child <?php if ($this->action=="begleitschein") echo ' current '; ?>"   >
		<div class="columns twelve">
			<h1>Fahrzeugbegleitschein</h1>
			<?php  if(isset($this->pdfLink)) echo $this->pdfLink;
					else if(isset($this->qform_pdf)) echo $this->qform_pdf->getContent();
			?>
		</div>
	</div>

	<div id="exportxml" class="row submenu_target_child <?php if ($this->action=="exportxml") echo ' current '; ?>"   >
		<div class="columns twelve">
			<h1>XML Export</h1>
			<?php  if(isset($this->pdfLink)) echo $this->pdfLink;
					else if(isset($this->qform_xml)) echo $this->qform_xml->getContent();
			?>
		</div>
	</div>

	<div id="genpdf" class="row submenu_target_child <?php if ($this->action=="genpdf") echo ' current '; ?>"   >
		<div class="columns twelve">

			<?php

			if(isset($this->qform_pdf)) echo $this->qform_pdf->getContent();
			?>
		</div>
	</div>


	<div id="gencoc" class="row submenu_target_child <?php if ($this->action=="gencoc") echo ' current '; ?>"   >
		<div class="columns twelve">

			<?php
			if(isset($this->page)) echo $this->page;
			?>
		</div>
	</div>

	<div id="delivery" class="row submenu_target_child <?php if ($this->action=="delivery") echo ' current '; ?>"   >
		<div class="columns twelve">
			<h1>Auslieferung</h1>
			<div class="row">
			<?php //if(isset($this->qform_delivery_sales)) echo $this->qform_delivery_sales->getContent();
			?>
			</div>
			<div class="row">
			<?php

			//Hier werden nur Fahrzeuge, die keine Auslieferdatum haben und diesen Monat ausgeliefert werden sollen angezeigt.<br>
				if(isset($this->content)) echo  '<span class="error_msg">'.$this->content.'</span>';
				if(isset($this->lieferscheinFname)) echo  $this->lieferscheinFname;
				if(isset($this->pentaCSVLink)) echo  $this->pentaCSVLink;
				?>
				<form action="index.php" method="post" class="quickform">
					<fieldset>
						<fieldset class="columns four inline_elements">
							<h3>1. Auszulieferende Fahrzeuge auswählen</h3>
						</fieldset>
						<fieldset class="columns four inline_elements">
							<h3>2. Auslieferungsdatum setzen</h3>
							<div class="row ">
								<div class="element group">
									<input type="text" class="date_selector_sales_new"
										name="date_selector"> <a class="date_selector_sales_new_set"
										style="margin-left: 20px"> Auslieferungsdatum setzen </a>
								</div>
							</div>
						</fieldset>
						<fieldset class="columns four inline_elements">
							<h3 id="qfauto-8">3. Möchten Sie die Emails an FPS/FPV schicken?</h3>
							<div class="row">
								<p class="label"></p>
								<div class="element">
									<input type="checkbox" checked="checked"
										name="send_notification_emails" id="send_notification_emails"
										value="1"><label for="send_notification_emails">Ja, Emails an
										FPS,FPV schicken!</label>
								</div>
							</div>
							<div class="row">
								<p class="label"></p>
								<div class="element">
									<input type="hidden" name="action" value="saveDeliveryDateNew">
									<input type="hidden" name="deliver_vehicles"
										id="deliver_vehicles_list" value=""> <input type="hidden"
										name="deliver_vehicles_date" value=""> <input
										type="submit" id="saveDeliverySubmit" value="Speichern"
										name="saveDeliverySubmit">
								</div>
							</div>
						</fieldset>
					</fieldset>
				<?php
				    echo $pageoptions;
                    if (isset($this->ajax_delivery_table))  echo $this->ajax_delivery_table->getContent();
                    echo $pageoptions;
                    ?>
				</form>
				<br><br>
			</div>
		</div>
	</div>

	<div id="delivery_old" class="row submenu_target_child <?php if ($this->action=="delivery_old") echo ' current '; ?>"   >
		<div class="columns twelve">
			<?php
				if(isset($this->lieferscheinFname)) echo  $this->lieferscheinFname;
				if(isset($this->qform_pdf)) echo $this->qform_pdf->getContent();

			?>
		</div>
	</div>

	<div id="show_finished_vehicles" class="row submenu_target_child <?php if ($this->action=="show_finished_vehicles") echo ' current '; ?>"   >
		<div class="columns twelve">
			<?php
				if(isset($this->finished_vehicles))
				{
					echo '<h1>Aftersales Fahrzeuge</h1>';
					echo $this->finished_vehicles['pool'];

					echo '<h1>Produktion Fahrzeuge</h1>';
					echo $this->finished_vehicles['production'];
				}
			?>
		</div>
	</div>


	<div id="depotassign" class="row submenu_target_child <?php if ($this->action=="depotassign") echo ' current '; ?>"   >
		<div class="columns eight">
			<?php echo $pageoptions; ?>
			<?php

				$processedAssign[]=array('headingone'=>array('VIN','Code','ZSP','Lieferdatum <br> dd.mm.YYYY'));


				if(!empty($this->depotassignresult))
				{
					foreach($this->depotassignresult as $vehicle)
					{
// 						if($vehicle["production_date"])
// 						$pdate=date('Y-m-d', strtotime($vehicle["production_date"]));
// 						else $pdate='';
						//$vehicle["vehicle_id"]
						if($vehicle["delivery_date"]) $vdd=date('d-m-Y',strtotime($vehicle["delivery_date"]));
						else $vdd='--';

					 	$processedAssign[]=array($vehicle["vin"],$vehicle["code"],
					 							'<input type="hidden" name="vehicle_id" class="vehicle_id" value="'.$vehicle['vehicle_id'].'" ><span class="depot_search">'.$vehicle['dname'].'('.$vehicle['dp_depot_id'].')</span>',
					 							'<input type="hidden" name="vehicle_id" class="vehicle_id" value="'.$vehicle['vehicle_id'].'" ><span class="delivery_date">'.$vdd.'</span>');
					}
					$displaytable = new DisplayTable ($processedAssign,array('id'=>'zsp_assign'));
					echo '<form method="post" >'.$displaytable->getContent().'</form>';
					$listofoptions='<div class="init_hidden zsp_selector_sales_wrap" ><select class="zsp_selector_sales"  name="zsp"><option></option>';
					$depots=$this->ladeLeitWartePtr->depotsPtr->getAll();
					foreach ($depots as $depot)
					{
						$listofoptions.= '<option value="'.$depot['depot_id'].'" >'.$depot['name'].'('.$depot['dp_depot_id'].')</option>';
					}

					$listofoptions.='</select></div>';
					echo $listofoptions;


				}
				else
					echo "Keine Fahrzeuge gefunden!";

			?>
			<?php echo $pageoptions;
			echo '<div class="init_hidden date_selector_sales_wrap" ><input type="text" class="date_selector_sales" name="delivery_date" ></div>'; ?>
		</div>
	</div>
	<?php

	if(time()>=$this->start_time && time()<=$this->end_time):?>
	<div id="fahrzeuge_zuweisen" class="row submenu_target_child <?php if ($this->action=="fahrzeuge_zuweisen") echo ' current '; ?>"   >
		<div class="columns eight">
			<?php
			if(isset($this->qform_vehicles_deliver_request)) :
				echo $this->qform_vehicles_deliver_request->getContent();
			else :
			?>
			Innerhalb der nächsten 4 Stunden bekommen Sie die Auslieferungsunterlagen.
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<div id="auto_fahrzeuge_zuweisen" class="row submenu_target_child <?php if ($this->action=="auto_fahrzeuge_zuweisen") echo ' current '; ?>"   >
		<div  class="row">
			<div class="columns four" style="background: #F1F1F1;padding: 1.2em; border-radius: 4px">
				<?php
				if(isset($this->qform_vehicles_deliver_variant_select)):
					echo $this->qform_vehicles_deliver_variant_select->getContent();
				elseif(isset($this->qform_vehicles_deliver_request)) :

					foreach($this->aftersalescount as $key=>$aftersalescount)
						echo $this->vehicle_variants[$key].' Aftersales : '.$aftersalescount.'<br>';
					foreach($this->produktioncount as $key=>$produktioncount)
						echo $this->vehicle_variants[$key].' Produktion : '.$produktioncount.'<br>';
				?>
				<a href='?action=auto_fahrzeuge_zuweisen'>Zurück zum Variante Auswahl</a>
			</div>
		</div>
		<div  class="row">
			<div class="columns six">
				<div class="row">
					<div class="columns five">
				        <?php echo $this->qform_vehicles_deliver_request->getContent();?>
				    </div>
				</div>
				<div class="row">
    			<div class="column two-thirds" style="max-height:600px; overflow-y: auto; ">
    				<?php echo '<h1>Auslieferungsreihenfolge</h1>'.$this->showorder_content;
    				?>

    			</div>
    			<div class="column one-third" style="height: 90%; overflow-y: auto; ">

        				<h2>Transporter Anfrage Datum </h2>
        				<?php
    				    if(isset($this->qform_save_transporter_date)):
    				        echo $this->qform_save_transporter_date->getContent();
    				    endif;
       				     ?>

    			</div>
    		</div>

		     </div>
		     <div class="columns six">
        			<?php
        				if(isset($this->vehicles_for_delivery)):
        				echo '<div class="wrap_vehicles_to_deliver">';
        				echo $this->vehicles_for_delivery;
        				echo '</div>';
        				endif;
            			else :
            				echo $this->content;
            			endif; ?>
    		</div>
		</div>
	</div>

	<div id="fahrzeug_tauschen" class="row submenu_target_child <?php if ($this->action=="fahrzeug_tauschen") echo ' current '; ?>"   >
		<div  class="row">
			<div class="columns eight">
			<h1>Fahrzeug Tauschen</h1>
				<?php if(isset($this->qform_vehicle_exchange)) echo $this->qform_vehicle_exchange->getContent();?>
			</div>
		</div>
	</div>

	<div id="thirdparty_delivery" class="row submenu_target_child <?php if ($this->action=="thirdparty_delivery") echo ' current '; ?>"   >
		<div  class="row">
			<div class="columns eight">
			<h1>Drittkunden Auslieferung</h1>
			<?php
				if(isset($this->content)) echo  '<span class="error_msg">'.$this->content.'</span>';
				if(isset($this->lieferscheinFname)) echo  $this->lieferscheinFname;
				if(isset($this->pentaCSVLink)) echo  $this->pentaCSVLink;
				?>
			<table id="">
				<thead><tr>
				<?php $headings=explode(',','Auftragsnummer,Wunschtermin,Fahrzeugvariante,Fahrzeug Farbe,accountname,Rechnungsanschrift,Primärer Kontakt,Kontakt Telefon,Fahrzeug wählen,Fahrzeug Ausgeliefert?,');
					foreach($headings as $heading)
					{
						if($heading=='Fahrzeug wählen')
							$styleparams='style="min-width: 300px;"';
						else $styleparams='';
						echo '<th '.$styleparams.'>'.$heading.'</th>';
					}
				?>
				</tr></thead>
				<tbody>
				<?php if ($this->quickform_thirdparties) echo implode('',$this->quickform_thirdparties); ?>
			 	</tbody>
			 </table>
			</div>
		</div>
	</div>

	<?php if($this->action=="workshop_delivery"): ?>
		<div id="workshop_delivery" class="row submenu_target_child <?php if ($this->action=="workshop_delivery") echo ' current '; ?>"   >
    		<div class="row">
    			<div class="columns ten">
    				<?php
    				if(isset($this->lieferscheinFname))
    				    echo $this->lieferscheinFname;
    				echo $this->qform_workshop_delivery->getContent();
    				?>
    			</div>
    		</div>
		</div>
	<?php
	endif;
	?>
</div>
<script type="text/javascript">
	// autocomplete
	$("#vehicle_variant_wc-0").chosen();
</script>
