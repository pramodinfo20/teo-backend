<?php
$hidden = [
    'vehicle_variant_id' => true,
    'approval_code' => true,
];

$config = [
    'super_type' => false, // ['info'=>'Supertype',  'map'=>$db->newQuery ('super_types')->orderBy('letter')->get('super_type_id=>name')],
    'default_color' => ['info' => 'Vorgabe Farbe',
        'map' => $db->newQuery('colors')->orderBy('vin_color_code')->get('color_id=>name')],
    'is_dp' => ['info' => 'Post Variante'],
    'zielstaat' => ['info' => 'Zielstaat'],
    'default_production_location' => ['info' => 'Standard Produktionsstandort', 'map' => [0 => 'Aachen', 3348 => 'Düren', 3368 => 'Köln']],
    'prio' => false, //['info'=>'Listenposition'],
    'vin_batch' => false, // ['info'=>'VIN Konfigurationschlüssel "Batch" (A-Z)',
    // 'readonly'=>1,
    // 'map'=>['A'=>'A (01)','B'=>'B (02)','C'=>'C (03)','D'=>'D (04)','E'=>'E (05)','F'=>'F (06)','G'=>'G (07)','H'=>'H (08)','I'=>'I (09)','J'=>'J (10)','K'=>'K (11)','L'=>'L (12)','M'=>'M (13)','N'=>'N (14)','O'=>'O (15)','P'=>'P (16)','Q'=>'Q (17)','R'=>'R (18)','S'=>'S (19)','T'=>'T (20)','U'=>'U (21)','V'=>'V (22)','W'=>'W (23)','X'=>'X (24)','Y'=>'Y (25)','Z'=>'Z (26)']],
    'vin_method' => false, // ['info'=>'VIN Erzeugung',                                        'sel'=>['sop2017', 'sop']],
    'charger_controllable' => ['info' => 'Intelligentes Laden'],
    'battery' => false, //['info'=>'Batterietyp (z.B.: SDA, V6/1, V6/2, ...)', 'readonly'=>1],
    'battery_id' => false, // ['info'=>'Batterie Kennung',  'readonly'=>1],
    'luftdruck_vorne' => ['info' => 'Luftdruck vorne', 'unit' => 'kPa'],
    'luftdruck_hinten' => ['info' => 'Luftdruck hinten', 'unit' => 'kPa'],
    'windchill_variant_comment' => ['info' => 'Komentar'],
];

/*

$coc = [
    'vmax'                                  => ['min'=>60,'max'=>220, ],
    'max_power_hour'                        => ['unit'=>'kW'],
    'max_power'                             => ['unit'=>'kW'],
    'max_power_30min'                       => ['unit'=>'kW'],
    'track_width_1'                         => ['unit'=>'mm'],
    'track_width_2'                         => ['unit'=>'mm'],
    'num_axles'                             => ['sel'=>[1,2,3],   ],
    'num_wheels'                            => ['sel'=>[2,4,6,8], ],
    'num_controlled_axles'                  => ['sel'=>[1,2,3],   ],
    'num_driven_axles'                      => ['sel'=>[1,2,3],   ],
];


$coc = [
    'year'                                  => ['info'=>'Name COC-Papier: "Jahr:"'],
    'number'                                => ['info'=>'Name in COC-Papier: "laufende Nummer:"'],
    'hsn'                                   => ['info'=>'Name in COC-Papier: "2.1. HSN:"'],
    'tsn'                                   => ['info'=>'Name in COC-Papier: "2.2. TSN:"'],
    'variant'                               => ['info'=>'Name in COC-Papier: "0.2. Variante:"'],
    'version'                               => ['info'=>'Name in COC-Papier: "0.2. Version:"'],
    'length'                                => ['info'=>'Name in COC-Papier: "5. Länge:"'],
    'width'                                 => ['info'=>'Name in COC-Papier: "6. Breite:"'],
    'height'                                => ['info'=>'Name in COC-Papier: "7. Höhe:"'],
    'length_cargo_area'                     => ['info'=>'Name in COC-Papier: "11. Länge der Ladefläche:"'],
    'mass_ready_to_start_min'               => ['info'=>'Name in COC-Papier: "13. Masse Fzg. Fahrbereit:" (zu berechnen)'],
    'mass_ready_to_start_max'               => ['info'=>'Name in COC-Papier: "13. Masse Fzg. Fahrbereit:" (zu berechnen)'],
    'compartment_kind'                      => ['info'=>'Name in COC-Papier: "38. Art des Aufbaus:"'],
    'number_of_seats'                       => ['info'=>'Name in COC-Papier: "42. Anz. Sitzpl."'],
    'official_compartment_kind'             => ['info'=>'Name in COC-Papier: "4 Amtl. Aufbau:"'],
    'name'                                  => ['info'=>'Name in COC-Papier'],
    'vehicle_combination'                   => ['info'=>'Aufkleber: zGG. Fahrzeugkombination'],
    'type'                                  => ['info'=>'COC: 0.2 Typ / Aufkleber: Typ'],
    'sub_type'                              => ['info'=>'Aufkleber: (Feld unter Typ)'],
    'vv_pz'                                 => ['info'=>'BName in COC-Papier: "2.2. VV/PZ:"'],
    'official_compartment_text'             => ['info'=>'Amtl. Aufbau Bezeichnung,'],
    'light_angle'                           => ['info'=>'Aufkleber:  (Scheinwerfersymbol)'],
    'description'                           => ['info'=>'Fahrzeugvariantenbeschreibung'],
    'vmax'                                  => ['info'=>'COC: 29. Höchstgeschwindigkeit',              'min'=>60,'max'=>220, ],
    'configuration'                         => ['info'=>'Konfiguration'],
    'fuel'                                  => ['info'=>'COC: 26. Kraftstoff'],
    'max_power_hour'                        => ['info'=>'COC: 27.2. höchste Stundenleistung',           'unit'=>'kW'],
    'max_power'                             => ['info'=>'COC: 27.3. höchste Nennleistung',              'unit'=>'kW'],
    'max_power_30min'                       => ['info'=>'COC: 27.3. höchste 30-Minuten-Leistung',       'unit'=>'kW'],
    'track_width_1'                         => ['info'=>'COC: 30. Spurweite 1',                         'unit'=>'mm'],
    'track_width_2'                         => ['info'=>'COC: 30. Spurweite 2',                         'unit'=>'mm'],
    'gearbox'                               => ['info'=>'COC: 28. Getriebetyp'],
    'tyre_dimensions_axle1'                 => ['info'=>'COC: 35. Reifen-/Radkombinationen Achse1'],
    'tyre_dimensions_axle2'                 => ['info'=>'COC: 35. Reifen-/Radkombinationen Achse2'],
    'colour'                                => ['info'=>'COC: 40. Farbe des Fahrzeugs'],
    'num_doors'                             => ['info'=>'COC: 41. Anz./Anord. Türen'],
    'tow_bar_mount'                         => ['info'=>'COC: 45. anbringbare Anhängevorrichtung'],
    'stationary_noise'                      => ['info'=>'COC: 46. Standgeräusch'],
    'pass_by_noise'                         => ['info'=>'COC: 46. Fahrgeräusch'],
    'emission_characteristics'              => ['info'=>'COC: 48. Abgasverhalten'],
    'combined_energy_consumption'           => ['info'=>'COC: 49. CO2 Emi/Kraftstoffverbr./elektr. Energieverbr.: elektr. Energieverbrauch kombiniert'],
    'range'                                 => ['info'=>'COC: 49. CO2 Emi/Kraftstoffverbr./elektr. Energieverbr.: elektr. Reichweite elektr.'],
    'additional_annotations'                => ['info'=>'COC: 52. Bemerkungen'],
    'vehicle_category'                      => ['info'=>'COC: 0.4 Fahrzeugklasse'],
    'trade_name'                            => ['info'=>'COC: 0.2.1 Handelsbezeichnung'],
    'trade_mark'                            => ['info'=>'COC: 0.1 Fabrikmarke'],
    'make'                                  => ['info'=>'Hersteller'],
    'manufacturer_adress'                   => ['info'=>'COC: 0.5 Herstelleranschrift'],
    'factory_nameplate_location'            => ['info'=>'COC: 0.6 Fabrikschildanbringung'],
    'vin_location'                          => ['info'=>'COC: 0.6 FIN-Anbringung Fahrgestell'],
    'num_axles'                             => ['info'=>'COC: 1. Anzahl Achsen',                        'sel'=>[1,2,3],   ],
    'num_wheels'                            => ['info'=>'COC: 1. Anzahl Räder',                         'sel'=>[2,4,6,8], ],
    'num_controlled_axles'                  => ['info'=>'COC: 2. gelenkte Achsen',                      'sel'=>[1,2,3],   ],
    'controlled_axles_location'             => ['info'=>'COC: 2. Lage: (gelenkte Achsen)'],
    'num_driven_axles'                      => ['info'=>'COC: 3. Anzahl Antriebsachsen',                'sel'=>[1,2,3],   ],
    'driven_axles_location'                 => ['info'=>'COC: 3. Lage: (Antriebsachsen)'],
    'driven_axles_connection'               => ['info'=>'COC: 3. Anzahl Antriebsachsen: gegenseit. Verb.'],
    'wheelbase'                             => ['info'=>'COC: 4. Radstand'],
    'axle_distance_1_2'                     => ['info'=>'COC: 4.1. Achsabstand: 1-2'],
    'axle_distance_2_3'                     => ['info'=>'COC: 4.1. Achsabstand: 2-3'],
    'axle_distance_3_4'                     => ['info'=>'COC: 4.1. Achsabstand: 3-4'],

    'fifth_wheel_position'                  => ['info'=>'COC: 8. Sattelvormaß'],
    'fifth_wheel_position_distance'         => ['info'=>'COC: 9. Abst. Zwisch. Fzg. Front u. Mittelpl. Anhvorricht'],
    'kerb_weight_axle_1'                    => ['info'=>'COC: 13.1. Verteilung dieser Masse: Achse1'],
    'kerb_weight_axle_2'                    => ['info'=>'COC: 13.1. Verteilung dieser Masse: Achse2'],
    'actual_weight'                         => ['info'=>'COC: 13.2. Tatsächliche Masse Fzg.'],
    'max_laden_mass'                        => ['info'=>'COC: 16.1. tech zl. Gesamtmasse'],
    'max_laden_mass_axle_1'                 => ['info'=>'COC: 16.2. tech zl. Achslast: Achse1'],
    'max_laden_mass_axle_2'                 => ['info'=>'COC: 16.2. tech zl. Achslast: Achse2'],
    'max_laden_mass_combined'               => ['info'=>'COC: 16.4. tech zl. Gesamtmasse kombiniert'],
    'cert_for_international_traffic'        => ['info'=>'COC: 17. Zulassung für grenzüberschreitenden Verkehr'],
    'max_laden_mass_17_0'                   => ['info'=>'COC: 17. vorgesehende höchstzul. Massen:'],
    'max_laden_mass_intended'               => ['info'=>'COC: 17. vorgesehende höchstzul. Massen:'],
    'max_laden_mass_intended'               => ['info'=>'COC: 17. vorgesehende höchstzul. Massen:'],

    'powertrain_manufacturer'               => ['info'=>'COC: 20. Herst. Antrieb'],
    'powertrain_type_examination'           => ['info'=>'COC: 21. Baumuster'],
    'powertrain_type'                       => ['info'=>'COC: 22. Arbeitsverfahren'],
    'pure_electric_drive'                   => ['info'=>'COC: 23. reiner Elektroantrieb'],
    'hybrid_electric_drive'                 => ['info'=>'COC: 23.1. Hybrid (elektro) Antrieb'],

    'overhang'                              => ['info'=>'COC: 12 Überhang hinten:'],
    'max_overhang'                          => ['info'=>'COC: 12.1 Höchstzul. Überhang hinten:'],
    'mass_incomplete_vehicle_min'           => ['info'=>'COC: 14. Masse unvollst. Fzg. fahrbereit Min'],
    'mass_incomplete_vehicle_max'           => ['info'=>'COC: 14. Masse unvollst. Fzg. fahrbereit Max'],
    'mass_incomplete_vehicle_axle_1'        => ['info'=>'COC: 14.1 Masse unvollst. Fzg. fahrbereit: Achse1'],
    'mass_incomplete_vehicle_axle_2'        => ['info'=>'COC: 14.1 Masse unvollst. Fzg. fahrbereit: Achse2'],
    'actual_weight_incomplete_vehicle'      => ['info'=>'COC: 14.2 Tatsächliche Masse unvollst. Fzg.'],
    'min_weight_completed_vehicle'          => ['info'=>'COC: 15 Mindestmasse vervollst. Fzg.'],
    'min_weight_completed_vehicle_axle_1'   => ['info'=>'COC: 15.1 Mindestmasse vervollst. Fzg.: Verteilung dieser Masse: Achse1'],
    'min_weight_completed_vehicle_axle_2'   => ['info'=>'COC: 15.1 Mindestmasse vervollst. Fzg.: Verteilung dieser Masse: Achse2'],
    'max_length'                            => ['info'=>'COC: 5.1 Höchstzul. Länge:',       'unit'=>'mm'],
    'max_width'                             => ['info'=>'COC: 6.1 Höchstzul. Breite',       'unit'=>'mm'],
    'max_height'                            => ['info'=>'COC: 7.1 Höchstzul. Höhe',         'unit'=>'mm'],
    'manufacturer'                          => ['info'=>'Zus. Zulassungsdaten: 2. Hersteller'],
    'vehicle_class'                         => ['info'=>'Zus. Zulassungsdaten: 5. Fahrzeugklasse'],
    'fuel_text_short'                       => ['info'=>'Zus. Zulassungsdaten: P.3. Text Kraftstoff kurz'],
    'national_emission_class'               => ['info'=>'Zus. Zulassungsdaten: 14. Nat. Emiklasse'],
    'national_emission_class_code'          => ['info'=>'Zus. Zulassungsdaten: 14.1 Nat. Emiklasse: Code zu V9 od. 14'],
   ];
*/

?>
