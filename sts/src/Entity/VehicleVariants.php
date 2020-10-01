<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * VehicleVariants
 *
 * @ORM\Table(name="vehicle_variants", uniqueConstraints={@ORM\UniqueConstraint(name="vehicle_variants_windchill_variant_name_key", columns={"windchill_variant_name"})}, indexes={@ORM\Index(name="IDX_6E8A5C75602D5E40", columns={"super_type"}), @ORM\Index(name="IDX_6E8A5C758F701CE7", columns={"default_color"}), @ORM\Index(name="IDX_6E8A5C75F35D972", columns={"default_production_location"}), @ORM\Index(name="IDX_6E8A5C754CC09541", columns={"coc_released_by"})})
 * @ORM\Entity
 */
class VehicleVariants
{
    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_variant_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vehicle_variants_vehicle_variant_id_seq", allocationSize=1, initialValue=1)
     */
    private $vehicleVariantId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="year", type="date", nullable=true)
     */
    private $year;

    /**
     * @var int|null
     *
     * @ORM\Column(name="number", type="bigint", nullable=true)
     */
    private $number;

    /**
     * @var int|null
     *
     * @ORM\Column(name="hsn", type="integer", nullable=true)
     */
    private $hsn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tsn", type="text", nullable=true)
     */
    private $tsn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="variant", type="text", nullable=true)
     */
    private $variant;

    /**
     * @var string|null
     *
     * @ORM\Column(name="version", type="text", nullable=true)
     */
    private $version;

    /**
     * @var int|null
     *
     * @ORM\Column(name="length", type="integer", nullable=true)
     */
    private $length;

    /**
     * @var int|null
     *
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    private $width;

    /**
     * @var int|null
     *
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    private $height;

    /**
     * @var int|null
     *
     * @ORM\Column(name="length_cargo_area", type="integer", nullable=true)
     */
    private $lengthCargoArea;

    /**
     * @var int|null
     *
     * @ORM\Column(name="mass_ready_to_start", type="integer", nullable=true)
     */
    private $massReadyToStart;

    /**
     * @var string|null
     *
     * @ORM\Column(name="compartment_kind", type="text", nullable=true)
     */
    private $compartmentKind;

    /**
     * @var int|null
     *
     * @ORM\Column(name="number_of_seats", type="integer", nullable=true)
     */
    private $numberOfSeats;

    /**
     * @var string|null
     *
     * @ORM\Column(name="official_compartment_kind", type="text", nullable=true)
     */
    private $officialCompartmentKind;

    /**
     * @var string|null
     *
     * @ORM\Column(name="windchill_variant_name", type="text", nullable=true)
     */
    private $windchillVariantName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vehicle_combination", type="text", nullable=true)
     */
    private $vehicleCombination;

    /**
     * @var string|null
     *
     * @ORM\Column(name="type", type="text", nullable=true)
     */
    private $type;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sub_type", type="text", nullable=true)
     */
    private $subType;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vv_pz", type="text", nullable=true)
     */
    private $vvPz;

    /**
     * @var string|null
     *
     * @ORM\Column(name="official_compartment_text", type="text", nullable=true)
     */
    private $officialCompartmentText;

    /**
     * @var string|null
     *
     * @ORM\Column(name="approval_code", type="text", nullable=true)
     */
    private $approvalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="light_angle", type="text", nullable=true)
     */
    private $lightAngle;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var int|null
     *
     * @ORM\Column(name="vmax", type="integer", nullable=true)
     */
    private $vmax;

    /**
     * @var string|null
     *
     * @ORM\Column(name="configuration", type="text", nullable=true)
     */
    private $configuration;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fuel", type="text", nullable=true)
     */
    private $fuel;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_power_hour", type="integer", nullable=true)
     */
    private $maxPowerHour;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_power", type="integer", nullable=true)
     */
    private $maxPower;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_power_30min", type="integer", nullable=true)
     */
    private $maxPower30min;

    /**
     * @var int|null
     *
     * @ORM\Column(name="track_width_1", type="integer", nullable=true)
     */
    private $trackWidth1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="track_width_2", type="integer", nullable=true)
     */
    private $trackWidth2;

    /**
     * @var string|null
     *
     * @ORM\Column(name="gearbox", type="text", nullable=true)
     */
    private $gearbox;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tyre_dimensions_axle1", type="text", nullable=true)
     */
    private $tyreDimensionsAxle1;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tyre_dimensions_axle2", type="text", nullable=true)
     */
    private $tyreDimensionsAxle2;

    /**
     * @var string|null
     *
     * @ORM\Column(name="colour", type="text", nullable=true)
     */
    private $colour;

    /**
     * @var int|null
     *
     * @ORM\Column(name="num_doors", type="integer", nullable=true)
     */
    private $numDoors;

    /**
     * @var int|null
     *
     * @ORM\Column(name="stationary_noise", type="integer", nullable=true)
     */
    private $stationaryNoise;

    /**
     * @var int|null
     *
     * @ORM\Column(name="pass_by_noise", type="integer", nullable=true)
     */
    private $passByNoise;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emission_characteristics", type="text", nullable=true)
     */
    private $emissionCharacteristics;

    /**
     * @var int|null
     *
     * @ORM\Column(name="combined_energy_consumption", type="integer", nullable=true)
     */
    private $combinedEnergyConsumption;

    /**
     * @var int|null
     *
     * @ORM\Column(name="range", type="integer", nullable=true)
     */
    private $range;

    /**
     * @var string|null
     *
     * @ORM\Column(name="additional_annotations", type="text", nullable=true)
     */
    private $additionalAnnotations;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vehicle_category", type="text", nullable=true)
     */
    private $vehicleCategory;

    /**
     * @var string|null
     *
     * @ORM\Column(name="trade_name", type="text", nullable=true)
     */
    private $tradeName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="make", type="text", nullable=true)
     */
    private $make;

    /**
     * @var string|null
     *
     * @ORM\Column(name="manufacturer_adress", type="text", nullable=true)
     */
    private $manufacturerAdress;

    /**
     * @var string|null
     *
     * @ORM\Column(name="factory_nameplate_location", type="text", nullable=true)
     */
    private $factoryNameplateLocation;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vin_location", type="text", nullable=true)
     */
    private $vinLocation;

    /**
     * @var int|null
     *
     * @ORM\Column(name="num_axles", type="integer", nullable=true)
     */
    private $numAxles;

    /**
     * @var int|null
     *
     * @ORM\Column(name="num_wheels", type="integer", nullable=true)
     */
    private $numWheels;

    /**
     * @var int|null
     *
     * @ORM\Column(name="num_driven_axles", type="integer", nullable=true)
     */
    private $numDrivenAxles;

    /**
     * @var string|null
     *
     * @ORM\Column(name="driven_axles_location", type="text", nullable=true)
     */
    private $drivenAxlesLocation;

    /**
     * @var string|null
     *
     * @ORM\Column(name="driven_axles_connection", type="text", nullable=true)
     */
    private $drivenAxlesConnection;

    /**
     * @var int|null
     *
     * @ORM\Column(name="wheelbase", type="integer", nullable=true)
     */
    private $wheelbase;

    /**
     * @var int|null
     *
     * @ORM\Column(name="axle_distance_1_2", type="integer", nullable=true)
     */
    private $axleDistance12;

    /**
     * @var int|null
     *
     * @ORM\Column(name="axle_distance_2_3", type="integer", nullable=true)
     */
    private $axleDistance23;

    /**
     * @var int|null
     *
     * @ORM\Column(name="axle_distance_3_4", type="integer", nullable=true)
     */
    private $axleDistance34;

    /**
     * @var int|null
     *
     * @ORM\Column(name="fifth_wheel_position", type="integer", nullable=true)
     */
    private $fifthWheelPosition;

    /**
     * @var int|null
     *
     * @ORM\Column(name="kerb_weight_axle_1", type="integer", nullable=true)
     */
    private $kerbWeightAxle1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="kerb_weight_axle_2", type="integer", nullable=true)
     */
    private $kerbWeightAxle2;

    /**
     * @var int|null
     *
     * @ORM\Column(name="actual_weight", type="integer", nullable=true)
     */
    private $actualWeight;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_laden_mass", type="integer", nullable=true)
     */
    private $maxLadenMass;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_laden_mass_combined", type="integer", nullable=true)
     */
    private $maxLadenMassCombined;

    /**
     * @var string|null
     *
     * @ORM\Column(name="powertrain_manufacturer", type="text", nullable=true)
     */
    private $powertrainManufacturer;

    /**
     * @var string|null
     *
     * @ORM\Column(name="powertrain_type_examination", type="text", nullable=true)
     */
    private $powertrainTypeExamination;

    /**
     * @var string|null
     *
     * @ORM\Column(name="powertrain_type", type="text", nullable=true)
     */
    private $powertrainType;

    /**
     * @var string|null
     *
     * @ORM\Column(name="pure_electric_drive", type="text", nullable=true)
     */
    private $pureElectricDrive;

    /**
     * @var string|null
     *
     * @ORM\Column(name="hybrid_electric_drive", type="text", nullable=true)
     */
    private $hybridElectricDrive;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_laden_mass_axle_1", type="integer", nullable=true)
     */
    private $maxLadenMassAxle1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_laden_mass_axle_2", type="integer", nullable=true)
     */
    private $maxLadenMassAxle2;

    /**
     * @var int|null
     *
     * @ORM\Column(name="mass_incomplete_vehicle", type="integer", nullable=true)
     */
    private $massIncompleteVehicle;

    /**
     * @var int|null
     *
     * @ORM\Column(name="mass_incomplete_vehicle_axle_1", type="integer", nullable=true)
     */
    private $massIncompleteVehicleAxle1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="mass_incomplete_vehicle_axle_2", type="integer", nullable=true)
     */
    private $massIncompleteVehicleAxle2;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_overhang", type="integer", nullable=true)
     */
    private $maxOverhang;

    /**
     * @var int|null
     *
     * @ORM\Column(name="actual_weight_incomplete_vehicle", type="integer", nullable=true)
     */
    private $actualWeightIncompleteVehicle;

    /**
     * @var int|null
     *
     * @ORM\Column(name="min_weight_completed_vehicle", type="integer", nullable=true)
     */
    private $minWeightCompletedVehicle;

    /**
     * @var int|null
     *
     * @ORM\Column(name="min_weight_completed_vehicle_axle_1", type="integer", nullable=true)
     */
    private $minWeightCompletedVehicleAxle1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="min_weight_completed_vehicle_axle_2", type="integer", nullable=true)
     */
    private $minWeightCompletedVehicleAxle2;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_length", type="integer", nullable=true)
     */
    private $maxLength;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_width", type="integer", nullable=true)
     */
    private $maxWidth;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_height", type="integer", nullable=true)
     */
    private $maxHeight;

    /**
     * @var string|null
     *
     * @ORM\Column(name="manufacturer", type="text", nullable=true)
     */
    private $manufacturer;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vehicle_class", type="text", nullable=true)
     */
    private $vehicleClass;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fuel_text_short", type="text", nullable=true)
     */
    private $fuelTextShort;

    /**
     * @var string|null
     *
     * @ORM\Column(name="national_emission_class", type="text", nullable=true)
     */
    private $nationalEmissionClass;

    /**
     * @var string|null
     *
     * @ORM\Column(name="national_emission_class_code", type="text", nullable=true)
     */
    private $nationalEmissionClassCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="windchill_variant_comment", type="text", nullable=true)
     */
    private $windchillVariantComment;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fahrzeugvariante", type="text", nullable=true)
     */
    private $fahrzeugvariante;

    /**
     * @var string|null
     *
     * @ORM\Column(name="zielstaat", type="text", nullable=true, options={"default"="DE"})
     */
    private $zielstaat = 'DE';

    /**
     * @var int|null
     *
     * @ORM\Column(name="luftdruck_vorne", type="integer", nullable=true)
     */
    private $luftdruckVorne;

    /**
     * @var int|null
     *
     * @ORM\Column(name="luftdruck_hinten", type="integer", nullable=true)
     */
    private $luftdruckHinten;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_dp", type="boolean", nullable=false, options={"default"="1"})
     */
    private $isDp = true;

    /**
     * @var int|null
     *
     * @ORM\Column(name="prio", type="integer", nullable=true)
     */
    private $prio;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vin_batch", type="string", nullable=true, options={"fixed"=true})
     */
    private $vinBatch;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vin_method", type="text", nullable=true)
     */
    private $vinMethod;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="charger_controllable", type="boolean", nullable=true)
     */
    private $chargerControllable;

    /**
     * @var string|null
     *
     * @ORM\Column(name="battery", type="text", nullable=true)
     */
    private $battery;

    /**
     * @var string|null
     *
     * @ORM\Column(name="trade_mark", type="text", nullable=true, options={"default"="StreetScooter"})
     */
    private $tradeMark = 'StreetScooter';

    /**
     * @var int|null
     *
     * @ORM\Column(name="num_controlled_axles", type="integer", nullable=true)
     */
    private $numControlledAxles;

    /**
     * @var int|null
     *
     * @ORM\Column(name="controlled_axles_location", type="integer", nullable=true)
     */
    private $controlledAxlesLocation;

    /**
     * @var bool
     *
     * @ORM\Column(name="left_hand", type="boolean", nullable=false)
     */
    private $leftHand = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="metric_or_imperial_system", type="boolean", nullable=false)
     */
    private $metricOrImperialSystem = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="cert_for_international_traffic", type="boolean", nullable=false, options={"default"="1"})
     */
    private $certForInternationalTraffic = true;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_laden_mass_17_0", type="integer", nullable=true)
     */
    private $maxLadenMass170;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_laden_mass_17_1", type="integer", nullable=true)
     */
    private $maxLadenMass171;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_laden_mass_17_2_axle_1", type="integer", nullable=true)
     */
    private $maxLadenMass172Axle1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_laden_mass_17_2_axle_2", type="integer", nullable=true)
     */
    private $maxLadenMass172Axle2;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_laden_mass_17_4", type="integer", nullable=true)
     */
    private $maxLadenMass174;

    /**
     * @var string|null
     *
     * @ORM\Column(name="rim_type_axle_1", type="text", nullable=true)
     */
    private $rimTypeAxle1;

    /**
     * @var string|null
     *
     * @ORM\Column(name="rim_type_axle_2", type="text", nullable=true)
     */
    private $rimTypeAxle2;

    /**
     * @var string|null
     *
     * @ORM\Column(name="base_type", type="text", nullable=true)
     */
    private $baseType;

    /**
     * @var string|null
     *
     * @ORM\Column(name="base_variant", type="text", nullable=true)
     */
    private $baseVariant;

    /**
     * @var string|null
     *
     * @ORM\Column(name="base_version", type="text", nullable=true)
     */
    private $baseVersion;

    /**
     * @var string|null
     *
     * @ORM\Column(name="base_approval_code", type="text", nullable=true)
     */
    private $baseApprovalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="base_manufacturer_adress", type="text", nullable=true)
     */
    private $baseManufacturerAdress;

    /**
     * @var int|null
     *
     * @ORM\Column(name="distance_to_coupling", type="integer", nullable=true)
     */
    private $distanceToCoupling;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_towable_mass", type="integer", nullable=true)
     */
    private $maxTowableMass;

    /**
     * @var string|null
     *
     * @ORM\Column(name="hitch_approval_code", type="text", nullable=true)
     */
    private $hitchApprovalCode;

    /**
     * @var int|null
     *
     * @ORM\Column(name="hitch_property_d", type="integer", nullable=true)
     */
    private $hitchPropertyD;

    /**
     * @var int|null
     *
     * @ORM\Column(name="hitch_property_v", type="integer", nullable=true)
     */
    private $hitchPropertyV;

    /**
     * @var int|null
     *
     * @ORM\Column(name="hitch_property_s", type="integer", nullable=true)
     */
    private $hitchPropertyS;

    /**
     * @var int|null
     *
     * @ORM\Column(name="hitch_property_u", type="integer", nullable=true)
     */
    private $hitchPropertyU;

    /**
     * @var int|null
     *
     * @ORM\Column(name="co2_combi", type="integer", nullable=true)
     */
    private $co2Combi;

    /**
     * @var string|null
     *
     * @ORM\Column(name="kinds_of_hitches", type="text", nullable=true)
     */
    private $kindsOfHitches;

    /**
     * @var int|null
     *
     * @ORM\Column(name="battery_id", type="integer", nullable=true)
     */
    private $batteryId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="esp_func", type="boolean", nullable=true)
     */
    private $espFunc;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="coc_released_date", type="datetimetz", nullable=true)
     */
    private $cocReleasedDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="approval_date", type="datetimetz", nullable=true)
     */
    private $approvalDate;

    /**
     * @var SuperTypes
     *
     * @ORM\ManyToOne(targetEntity="SuperTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="super_type", referencedColumnName="super_type_id")
     * })
     */
    private $superType;

    /**
     * @var Colors
     *
     * @ORM\ManyToOne(targetEntity="Colors")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="default_color", referencedColumnName="color_id")
     * })
     */
    private $defaultColor;

    /**
     * @var Depots
     *
     * @ORM\ManyToOne(targetEntity="Depots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="default_production_location", referencedColumnName="depot_id")
     * })
     */
    private $defaultProductionLocation;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="coc_released_by", referencedColumnName="id")
     * })
     */
    private $cocReleasedBy;

    /**
     * @var Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Parts", inversedBy="variant")
     * @ORM\JoinTable(name="variant_parts_mapping",
     *   joinColumns={
     *     @ORM\JoinColumn(name="variant_id", referencedColumnName="vehicle_variant_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="part_id", referencedColumnName="part_id")
     *   }
     * )
     */
    private $part;

    /**
     * @var Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="VehicleExternalNames", inversedBy="variant")
     * @ORM\JoinTable(name="vehicle_external_mapping",
     *   joinColumns={
     *     @ORM\JoinColumn(name="variant_id", referencedColumnName="vehicle_variant_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="external_id", referencedColumnName="external_id")
     *   }
     * )
     */
    private $external;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->part = new \Doctrine\Common\Collections\ArrayCollection();
        $this->external = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getVehicleVariantId(): ?int
    {
        return $this->vehicleVariantId;
    }

    public function getYear(): ?\DateTimeInterface
    {
        return $this->year;
    }

    public function setYear(?\DateTimeInterface $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getHsn(): ?int
    {
        return $this->hsn;
    }

    public function setHsn(?int $hsn): self
    {
        $this->hsn = $hsn;

        return $this;
    }

    public function getTsn(): ?string
    {
        return $this->tsn;
    }

    public function setTsn(?string $tsn): self
    {
        $this->tsn = $tsn;

        return $this;
    }

    public function getVariant(): ?string
    {
        return $this->variant;
    }

    public function setVariant(?string $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getLengthCargoArea(): ?int
    {
        return $this->lengthCargoArea;
    }

    public function setLengthCargoArea(?int $lengthCargoArea): self
    {
        $this->lengthCargoArea = $lengthCargoArea;

        return $this;
    }

    public function getMassReadyToStart(): ?int
    {
        return $this->massReadyToStart;
    }

    public function setMassReadyToStart(?int $massReadyToStart): self
    {
        $this->massReadyToStart = $massReadyToStart;

        return $this;
    }

    public function getCompartmentKind(): ?string
    {
        return $this->compartmentKind;
    }

    public function setCompartmentKind(?string $compartmentKind): self
    {
        $this->compartmentKind = $compartmentKind;

        return $this;
    }

    public function getNumberOfSeats(): ?int
    {
        return $this->numberOfSeats;
    }

    public function setNumberOfSeats(?int $numberOfSeats): self
    {
        $this->numberOfSeats = $numberOfSeats;

        return $this;
    }

    public function getOfficialCompartmentKind(): ?string
    {
        return $this->officialCompartmentKind;
    }

    public function setOfficialCompartmentKind(?string $officialCompartmentKind): self
    {
        $this->officialCompartmentKind = $officialCompartmentKind;

        return $this;
    }

    public function getWindchillVariantName(): ?string
    {
        return $this->windchillVariantName;
    }

    public function setWindchillVariantName(?string $windchillVariantName): self
    {
        $this->windchillVariantName = $windchillVariantName;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getVehicleCombination(): ?string
    {
        return $this->vehicleCombination;
    }

    public function setVehicleCombination(?string $vehicleCombination): self
    {
        $this->vehicleCombination = $vehicleCombination;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSubType(): ?string
    {
        return $this->subType;
    }

    public function setSubType(?string $subType): self
    {
        $this->subType = $subType;

        return $this;
    }

    public function getVvPz(): ?string
    {
        return $this->vvPz;
    }

    public function setVvPz(?string $vvPz): self
    {
        $this->vvPz = $vvPz;

        return $this;
    }

    public function getOfficialCompartmentText(): ?string
    {
        return $this->officialCompartmentText;
    }

    public function setOfficialCompartmentText(?string $officialCompartmentText): self
    {
        $this->officialCompartmentText = $officialCompartmentText;

        return $this;
    }

    public function getApprovalCode(): ?string
    {
        return $this->approvalCode;
    }

    public function setApprovalCode(?string $approvalCode): self
    {
        $this->approvalCode = $approvalCode;

        return $this;
    }

    public function getLightAngle(): ?string
    {
        return $this->lightAngle;
    }

    public function setLightAngle(?string $lightAngle): self
    {
        $this->lightAngle = $lightAngle;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getVmax(): ?int
    {
        return $this->vmax;
    }

    public function setVmax(?int $vmax): self
    {
        $this->vmax = $vmax;

        return $this;
    }

    public function getConfiguration(): ?string
    {
        return $this->configuration;
    }

    public function setConfiguration(?string $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    public function getFuel(): ?string
    {
        return $this->fuel;
    }

    public function setFuel(?string $fuel): self
    {
        $this->fuel = $fuel;

        return $this;
    }

    public function getMaxPowerHour(): ?int
    {
        return $this->maxPowerHour;
    }

    public function setMaxPowerHour(?int $maxPowerHour): self
    {
        $this->maxPowerHour = $maxPowerHour;

        return $this;
    }

    public function getMaxPower(): ?int
    {
        return $this->maxPower;
    }

    public function setMaxPower(?int $maxPower): self
    {
        $this->maxPower = $maxPower;

        return $this;
    }

    public function getMaxPower30min(): ?int
    {
        return $this->maxPower30min;
    }

    public function setMaxPower30min(?int $maxPower30min): self
    {
        $this->maxPower30min = $maxPower30min;

        return $this;
    }

    public function getTrackWidth1(): ?int
    {
        return $this->trackWidth1;
    }

    public function setTrackWidth1(?int $trackWidth1): self
    {
        $this->trackWidth1 = $trackWidth1;

        return $this;
    }

    public function getTrackWidth2(): ?int
    {
        return $this->trackWidth2;
    }

    public function setTrackWidth2(?int $trackWidth2): self
    {
        $this->trackWidth2 = $trackWidth2;

        return $this;
    }

    public function getGearbox(): ?string
    {
        return $this->gearbox;
    }

    public function setGearbox(?string $gearbox): self
    {
        $this->gearbox = $gearbox;

        return $this;
    }

    public function getTyreDimensionsAxle1(): ?string
    {
        return $this->tyreDimensionsAxle1;
    }

    public function setTyreDimensionsAxle1(?string $tyreDimensionsAxle1): self
    {
        $this->tyreDimensionsAxle1 = $tyreDimensionsAxle1;

        return $this;
    }

    public function getTyreDimensionsAxle2(): ?string
    {
        return $this->tyreDimensionsAxle2;
    }

    public function setTyreDimensionsAxle2(?string $tyreDimensionsAxle2): self
    {
        $this->tyreDimensionsAxle2 = $tyreDimensionsAxle2;

        return $this;
    }

    public function getColour(): ?string
    {
        return $this->colour;
    }

    public function setColour(?string $colour): self
    {
        $this->colour = $colour;

        return $this;
    }

    public function getNumDoors(): ?int
    {
        return $this->numDoors;
    }

    public function setNumDoors(?int $numDoors): self
    {
        $this->numDoors = $numDoors;

        return $this;
    }

    public function getStationaryNoise(): ?int
    {
        return $this->stationaryNoise;
    }

    public function setStationaryNoise(?int $stationaryNoise): self
    {
        $this->stationaryNoise = $stationaryNoise;

        return $this;
    }

    public function getPassByNoise(): ?int
    {
        return $this->passByNoise;
    }

    public function setPassByNoise(?int $passByNoise): self
    {
        $this->passByNoise = $passByNoise;

        return $this;
    }

    public function getEmissionCharacteristics(): ?string
    {
        return $this->emissionCharacteristics;
    }

    public function setEmissionCharacteristics(?string $emissionCharacteristics): self
    {
        $this->emissionCharacteristics = $emissionCharacteristics;

        return $this;
    }

    public function getCombinedEnergyConsumption(): ?int
    {
        return $this->combinedEnergyConsumption;
    }

    public function setCombinedEnergyConsumption(?int $combinedEnergyConsumption): self
    {
        $this->combinedEnergyConsumption = $combinedEnergyConsumption;

        return $this;
    }

    public function getRange(): ?int
    {
        return $this->range;
    }

    public function setRange(?int $range): self
    {
        $this->range = $range;

        return $this;
    }

    public function getAdditionalAnnotations(): ?string
    {
        return $this->additionalAnnotations;
    }

    public function setAdditionalAnnotations(?string $additionalAnnotations): self
    {
        $this->additionalAnnotations = $additionalAnnotations;

        return $this;
    }

    public function getVehicleCategory(): ?string
    {
        return $this->vehicleCategory;
    }

    public function setVehicleCategory(?string $vehicleCategory): self
    {
        $this->vehicleCategory = $vehicleCategory;

        return $this;
    }

    public function getTradeName(): ?string
    {
        return $this->tradeName;
    }

    public function setTradeName(?string $tradeName): self
    {
        $this->tradeName = $tradeName;

        return $this;
    }

    public function getMake(): ?string
    {
        return $this->make;
    }

    public function setMake(?string $make): self
    {
        $this->make = $make;

        return $this;
    }

    public function getManufacturerAdress(): ?string
    {
        return $this->manufacturerAdress;
    }

    public function setManufacturerAdress(?string $manufacturerAdress): self
    {
        $this->manufacturerAdress = $manufacturerAdress;

        return $this;
    }

    public function getFactoryNameplateLocation(): ?string
    {
        return $this->factoryNameplateLocation;
    }

    public function setFactoryNameplateLocation(?string $factoryNameplateLocation): self
    {
        $this->factoryNameplateLocation = $factoryNameplateLocation;

        return $this;
    }

    public function getVinLocation(): ?string
    {
        return $this->vinLocation;
    }

    public function setVinLocation(?string $vinLocation): self
    {
        $this->vinLocation = $vinLocation;

        return $this;
    }

    public function getNumAxles(): ?int
    {
        return $this->numAxles;
    }

    public function setNumAxles(?int $numAxles): self
    {
        $this->numAxles = $numAxles;

        return $this;
    }

    public function getNumWheels(): ?int
    {
        return $this->numWheels;
    }

    public function setNumWheels(?int $numWheels): self
    {
        $this->numWheels = $numWheels;

        return $this;
    }

    public function getNumDrivenAxles(): ?int
    {
        return $this->numDrivenAxles;
    }

    public function setNumDrivenAxles(?int $numDrivenAxles): self
    {
        $this->numDrivenAxles = $numDrivenAxles;

        return $this;
    }

    public function getDrivenAxlesLocation(): ?string
    {
        return $this->drivenAxlesLocation;
    }

    public function setDrivenAxlesLocation(?string $drivenAxlesLocation): self
    {
        $this->drivenAxlesLocation = $drivenAxlesLocation;

        return $this;
    }

    public function getDrivenAxlesConnection(): ?string
    {
        return $this->drivenAxlesConnection;
    }

    public function setDrivenAxlesConnection(?string $drivenAxlesConnection): self
    {
        $this->drivenAxlesConnection = $drivenAxlesConnection;

        return $this;
    }

    public function getWheelbase(): ?int
    {
        return $this->wheelbase;
    }

    public function setWheelbase(?int $wheelbase): self
    {
        $this->wheelbase = $wheelbase;

        return $this;
    }

    public function getAxleDistance12(): ?int
    {
        return $this->axleDistance12;
    }

    public function setAxleDistance12(?int $axleDistance12): self
    {
        $this->axleDistance12 = $axleDistance12;

        return $this;
    }

    public function getAxleDistance23(): ?int
    {
        return $this->axleDistance23;
    }

    public function setAxleDistance23(?int $axleDistance23): self
    {
        $this->axleDistance23 = $axleDistance23;

        return $this;
    }

    public function getAxleDistance34(): ?int
    {
        return $this->axleDistance34;
    }

    public function setAxleDistance34(?int $axleDistance34): self
    {
        $this->axleDistance34 = $axleDistance34;

        return $this;
    }

    public function getFifthWheelPosition(): ?int
    {
        return $this->fifthWheelPosition;
    }

    public function setFifthWheelPosition(?int $fifthWheelPosition): self
    {
        $this->fifthWheelPosition = $fifthWheelPosition;

        return $this;
    }

    public function getKerbWeightAxle1(): ?int
    {
        return $this->kerbWeightAxle1;
    }

    public function setKerbWeightAxle1(?int $kerbWeightAxle1): self
    {
        $this->kerbWeightAxle1 = $kerbWeightAxle1;

        return $this;
    }

    public function getKerbWeightAxle2(): ?int
    {
        return $this->kerbWeightAxle2;
    }

    public function setKerbWeightAxle2(?int $kerbWeightAxle2): self
    {
        $this->kerbWeightAxle2 = $kerbWeightAxle2;

        return $this;
    }

    public function getActualWeight(): ?int
    {
        return $this->actualWeight;
    }

    public function setActualWeight(?int $actualWeight): self
    {
        $this->actualWeight = $actualWeight;

        return $this;
    }

    public function getMaxLadenMass(): ?int
    {
        return $this->maxLadenMass;
    }

    public function setMaxLadenMass(?int $maxLadenMass): self
    {
        $this->maxLadenMass = $maxLadenMass;

        return $this;
    }

    public function getMaxLadenMassCombined(): ?int
    {
        return $this->maxLadenMassCombined;
    }

    public function setMaxLadenMassCombined(?int $maxLadenMassCombined): self
    {
        $this->maxLadenMassCombined = $maxLadenMassCombined;

        return $this;
    }

    public function getPowertrainManufacturer(): ?string
    {
        return $this->powertrainManufacturer;
    }

    public function setPowertrainManufacturer(?string $powertrainManufacturer): self
    {
        $this->powertrainManufacturer = $powertrainManufacturer;

        return $this;
    }

    public function getPowertrainTypeExamination(): ?string
    {
        return $this->powertrainTypeExamination;
    }

    public function setPowertrainTypeExamination(?string $powertrainTypeExamination): self
    {
        $this->powertrainTypeExamination = $powertrainTypeExamination;

        return $this;
    }

    public function getPowertrainType(): ?string
    {
        return $this->powertrainType;
    }

    public function setPowertrainType(?string $powertrainType): self
    {
        $this->powertrainType = $powertrainType;

        return $this;
    }

    public function getPureElectricDrive(): ?string
    {
        return $this->pureElectricDrive;
    }

    public function setPureElectricDrive(?string $pureElectricDrive): self
    {
        $this->pureElectricDrive = $pureElectricDrive;

        return $this;
    }

    public function getHybridElectricDrive(): ?string
    {
        return $this->hybridElectricDrive;
    }

    public function setHybridElectricDrive(?string $hybridElectricDrive): self
    {
        $this->hybridElectricDrive = $hybridElectricDrive;

        return $this;
    }

    public function getMaxLadenMassAxle1(): ?int
    {
        return $this->maxLadenMassAxle1;
    }

    public function setMaxLadenMassAxle1(?int $maxLadenMassAxle1): self
    {
        $this->maxLadenMassAxle1 = $maxLadenMassAxle1;

        return $this;
    }

    public function getMaxLadenMassAxle2(): ?int
    {
        return $this->maxLadenMassAxle2;
    }

    public function setMaxLadenMassAxle2(?int $maxLadenMassAxle2): self
    {
        $this->maxLadenMassAxle2 = $maxLadenMassAxle2;

        return $this;
    }

    public function getMassIncompleteVehicle(): ?int
    {
        return $this->massIncompleteVehicle;
    }

    public function setMassIncompleteVehicle(?int $massIncompleteVehicle): self
    {
        $this->massIncompleteVehicle = $massIncompleteVehicle;

        return $this;
    }

    public function getMassIncompleteVehicleAxle1(): ?int
    {
        return $this->massIncompleteVehicleAxle1;
    }

    public function setMassIncompleteVehicleAxle1(?int $massIncompleteVehicleAxle1): self
    {
        $this->massIncompleteVehicleAxle1 = $massIncompleteVehicleAxle1;

        return $this;
    }

    public function getMassIncompleteVehicleAxle2(): ?int
    {
        return $this->massIncompleteVehicleAxle2;
    }

    public function setMassIncompleteVehicleAxle2(?int $massIncompleteVehicleAxle2): self
    {
        $this->massIncompleteVehicleAxle2 = $massIncompleteVehicleAxle2;

        return $this;
    }

    public function getMaxOverhang(): ?int
    {
        return $this->maxOverhang;
    }

    public function setMaxOverhang(?int $maxOverhang): self
    {
        $this->maxOverhang = $maxOverhang;

        return $this;
    }

    public function getActualWeightIncompleteVehicle(): ?int
    {
        return $this->actualWeightIncompleteVehicle;
    }

    public function setActualWeightIncompleteVehicle(?int $actualWeightIncompleteVehicle): self
    {
        $this->actualWeightIncompleteVehicle = $actualWeightIncompleteVehicle;

        return $this;
    }

    public function getMinWeightCompletedVehicle(): ?int
    {
        return $this->minWeightCompletedVehicle;
    }

    public function setMinWeightCompletedVehicle(?int $minWeightCompletedVehicle): self
    {
        $this->minWeightCompletedVehicle = $minWeightCompletedVehicle;

        return $this;
    }

    public function getMinWeightCompletedVehicleAxle1(): ?int
    {
        return $this->minWeightCompletedVehicleAxle1;
    }

    public function setMinWeightCompletedVehicleAxle1(?int $minWeightCompletedVehicleAxle1): self
    {
        $this->minWeightCompletedVehicleAxle1 = $minWeightCompletedVehicleAxle1;

        return $this;
    }

    public function getMinWeightCompletedVehicleAxle2(): ?int
    {
        return $this->minWeightCompletedVehicleAxle2;
    }

    public function setMinWeightCompletedVehicleAxle2(?int $minWeightCompletedVehicleAxle2): self
    {
        $this->minWeightCompletedVehicleAxle2 = $minWeightCompletedVehicleAxle2;

        return $this;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function setMaxLength(?int $maxLength): self
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    public function getMaxWidth(): ?int
    {
        return $this->maxWidth;
    }

    public function setMaxWidth(?int $maxWidth): self
    {
        $this->maxWidth = $maxWidth;

        return $this;
    }

    public function getMaxHeight(): ?int
    {
        return $this->maxHeight;
    }

    public function setMaxHeight(?int $maxHeight): self
    {
        $this->maxHeight = $maxHeight;

        return $this;
    }

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?string $manufacturer): self
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getVehicleClass(): ?string
    {
        return $this->vehicleClass;
    }

    public function setVehicleClass(?string $vehicleClass): self
    {
        $this->vehicleClass = $vehicleClass;

        return $this;
    }

    public function getFuelTextShort(): ?string
    {
        return $this->fuelTextShort;
    }

    public function setFuelTextShort(?string $fuelTextShort): self
    {
        $this->fuelTextShort = $fuelTextShort;

        return $this;
    }

    public function getNationalEmissionClass(): ?string
    {
        return $this->nationalEmissionClass;
    }

    public function setNationalEmissionClass(?string $nationalEmissionClass): self
    {
        $this->nationalEmissionClass = $nationalEmissionClass;

        return $this;
    }

    public function getNationalEmissionClassCode(): ?string
    {
        return $this->nationalEmissionClassCode;
    }

    public function setNationalEmissionClassCode(?string $nationalEmissionClassCode): self
    {
        $this->nationalEmissionClassCode = $nationalEmissionClassCode;

        return $this;
    }

    public function getWindchillVariantComment(): ?string
    {
        return $this->windchillVariantComment;
    }

    public function setWindchillVariantComment(?string $windchillVariantComment): self
    {
        $this->windchillVariantComment = $windchillVariantComment;

        return $this;
    }

    public function getFahrzeugvariante(): ?string
    {
        return $this->fahrzeugvariante;
    }

    public function setFahrzeugvariante(?string $fahrzeugvariante): self
    {
        $this->fahrzeugvariante = $fahrzeugvariante;

        return $this;
    }

    public function getZielstaat(): ?string
    {
        return $this->zielstaat;
    }

    public function setZielstaat(?string $zielstaat): self
    {
        $this->zielstaat = $zielstaat;

        return $this;
    }

    public function getLuftdruckVorne(): ?int
    {
        return $this->luftdruckVorne;
    }

    public function setLuftdruckVorne(?int $luftdruckVorne): self
    {
        $this->luftdruckVorne = $luftdruckVorne;

        return $this;
    }

    public function getLuftdruckHinten(): ?int
    {
        return $this->luftdruckHinten;
    }

    public function setLuftdruckHinten(?int $luftdruckHinten): self
    {
        $this->luftdruckHinten = $luftdruckHinten;

        return $this;
    }

    public function getIsDp(): ?bool
    {
        return $this->isDp;
    }

    public function setIsDp(bool $isDp): self
    {
        $this->isDp = $isDp;

        return $this;
    }

    public function getPrio(): ?int
    {
        return $this->prio;
    }

    public function setPrio(?int $prio): self
    {
        $this->prio = $prio;

        return $this;
    }

    public function getVinBatch(): ?string
    {
        return $this->vinBatch;
    }

    public function setVinBatch(?string $vinBatch): self
    {
        $this->vinBatch = $vinBatch;

        return $this;
    }

    public function getVinMethod(): ?string
    {
        return $this->vinMethod;
    }

    public function setVinMethod(?string $vinMethod): self
    {
        $this->vinMethod = $vinMethod;

        return $this;
    }

    public function getChargerControllable(): ?bool
    {
        return $this->chargerControllable;
    }

    public function setChargerControllable(?bool $chargerControllable): self
    {
        $this->chargerControllable = $chargerControllable;

        return $this;
    }

    public function getBattery(): ?string
    {
        return $this->battery;
    }

    public function setBattery(?string $battery): self
    {
        $this->battery = $battery;

        return $this;
    }

    public function getTradeMark(): ?string
    {
        return $this->tradeMark;
    }

    public function setTradeMark(?string $tradeMark): self
    {
        $this->tradeMark = $tradeMark;

        return $this;
    }

    public function getNumControlledAxles(): ?int
    {
        return $this->numControlledAxles;
    }

    public function setNumControlledAxles(?int $numControlledAxles): self
    {
        $this->numControlledAxles = $numControlledAxles;

        return $this;
    }

    public function getControlledAxlesLocation(): ?int
    {
        return $this->controlledAxlesLocation;
    }

    public function setControlledAxlesLocation(?int $controlledAxlesLocation): self
    {
        $this->controlledAxlesLocation = $controlledAxlesLocation;

        return $this;
    }

    public function getLeftHand(): ?bool
    {
        return $this->leftHand;
    }

    public function setLeftHand(bool $leftHand): self
    {
        $this->leftHand = $leftHand;

        return $this;
    }

    public function getMetricOrImperialSystem(): ?bool
    {
        return $this->metricOrImperialSystem;
    }

    public function setMetricOrImperialSystem(bool $metricOrImperialSystem): self
    {
        $this->metricOrImperialSystem = $metricOrImperialSystem;

        return $this;
    }

    public function getCertForInternationalTraffic(): ?bool
    {
        return $this->certForInternationalTraffic;
    }

    public function setCertForInternationalTraffic(bool $certForInternationalTraffic): self
    {
        $this->certForInternationalTraffic = $certForInternationalTraffic;

        return $this;
    }

    public function getMaxLadenMass170(): ?int
    {
        return $this->maxLadenMass170;
    }

    public function setMaxLadenMass170(?int $maxLadenMass170): self
    {
        $this->maxLadenMass170 = $maxLadenMass170;

        return $this;
    }

    public function getMaxLadenMass171(): ?int
    {
        return $this->maxLadenMass171;
    }

    public function setMaxLadenMass171(?int $maxLadenMass171): self
    {
        $this->maxLadenMass171 = $maxLadenMass171;

        return $this;
    }

    public function getMaxLadenMass172Axle1(): ?int
    {
        return $this->maxLadenMass172Axle1;
    }

    public function setMaxLadenMass172Axle1(?int $maxLadenMass172Axle1): self
    {
        $this->maxLadenMass172Axle1 = $maxLadenMass172Axle1;

        return $this;
    }

    public function getMaxLadenMass172Axle2(): ?int
    {
        return $this->maxLadenMass172Axle2;
    }

    public function setMaxLadenMass172Axle2(?int $maxLadenMass172Axle2): self
    {
        $this->maxLadenMass172Axle2 = $maxLadenMass172Axle2;

        return $this;
    }

    public function getMaxLadenMass174(): ?int
    {
        return $this->maxLadenMass174;
    }

    public function setMaxLadenMass174(?int $maxLadenMass174): self
    {
        $this->maxLadenMass174 = $maxLadenMass174;

        return $this;
    }

    public function getRimTypeAxle1(): ?string
    {
        return $this->rimTypeAxle1;
    }

    public function setRimTypeAxle1(?string $rimTypeAxle1): self
    {
        $this->rimTypeAxle1 = $rimTypeAxle1;

        return $this;
    }

    public function getRimTypeAxle2(): ?string
    {
        return $this->rimTypeAxle2;
    }

    public function setRimTypeAxle2(?string $rimTypeAxle2): self
    {
        $this->rimTypeAxle2 = $rimTypeAxle2;

        return $this;
    }

    public function getBaseType(): ?string
    {
        return $this->baseType;
    }

    public function setBaseType(?string $baseType): self
    {
        $this->baseType = $baseType;

        return $this;
    }

    public function getBaseVariant(): ?string
    {
        return $this->baseVariant;
    }

    public function setBaseVariant(?string $baseVariant): self
    {
        $this->baseVariant = $baseVariant;

        return $this;
    }

    public function getBaseVersion(): ?string
    {
        return $this->baseVersion;
    }

    public function setBaseVersion(?string $baseVersion): self
    {
        $this->baseVersion = $baseVersion;

        return $this;
    }

    public function getBaseApprovalCode(): ?string
    {
        return $this->baseApprovalCode;
    }

    public function setBaseApprovalCode(?string $baseApprovalCode): self
    {
        $this->baseApprovalCode = $baseApprovalCode;

        return $this;
    }

    public function getBaseManufacturerAdress(): ?string
    {
        return $this->baseManufacturerAdress;
    }

    public function setBaseManufacturerAdress(?string $baseManufacturerAdress): self
    {
        $this->baseManufacturerAdress = $baseManufacturerAdress;

        return $this;
    }

    public function getDistanceToCoupling(): ?int
    {
        return $this->distanceToCoupling;
    }

    public function setDistanceToCoupling(?int $distanceToCoupling): self
    {
        $this->distanceToCoupling = $distanceToCoupling;

        return $this;
    }

    public function getMaxTowableMass(): ?int
    {
        return $this->maxTowableMass;
    }

    public function setMaxTowableMass(?int $maxTowableMass): self
    {
        $this->maxTowableMass = $maxTowableMass;

        return $this;
    }

    public function getHitchApprovalCode(): ?string
    {
        return $this->hitchApprovalCode;
    }

    public function setHitchApprovalCode(?string $hitchApprovalCode): self
    {
        $this->hitchApprovalCode = $hitchApprovalCode;

        return $this;
    }

    public function getHitchPropertyD(): ?int
    {
        return $this->hitchPropertyD;
    }

    public function setHitchPropertyD(?int $hitchPropertyD): self
    {
        $this->hitchPropertyD = $hitchPropertyD;

        return $this;
    }

    public function getHitchPropertyV(): ?int
    {
        return $this->hitchPropertyV;
    }

    public function setHitchPropertyV(?int $hitchPropertyV): self
    {
        $this->hitchPropertyV = $hitchPropertyV;

        return $this;
    }

    public function getHitchPropertyS(): ?int
    {
        return $this->hitchPropertyS;
    }

    public function setHitchPropertyS(?int $hitchPropertyS): self
    {
        $this->hitchPropertyS = $hitchPropertyS;

        return $this;
    }

    public function getHitchPropertyU(): ?int
    {
        return $this->hitchPropertyU;
    }

    public function setHitchPropertyU(?int $hitchPropertyU): self
    {
        $this->hitchPropertyU = $hitchPropertyU;

        return $this;
    }

    public function getCo2Combi(): ?int
    {
        return $this->co2Combi;
    }

    public function setCo2Combi(?int $co2Combi): self
    {
        $this->co2Combi = $co2Combi;

        return $this;
    }

    public function getKindsOfHitches(): ?string
    {
        return $this->kindsOfHitches;
    }

    public function setKindsOfHitches(?string $kindsOfHitches): self
    {
        $this->kindsOfHitches = $kindsOfHitches;

        return $this;
    }

    public function getBatteryId(): ?int
    {
        return $this->batteryId;
    }

    public function setBatteryId(?int $batteryId): self
    {
        $this->batteryId = $batteryId;

        return $this;
    }

    public function getEspFunc(): ?bool
    {
        return $this->espFunc;
    }

    public function setEspFunc(?bool $espFunc): self
    {
        $this->espFunc = $espFunc;

        return $this;
    }

    public function getCocReleasedDate(): ?\DateTimeInterface
    {
        return $this->cocReleasedDate;
    }

    public function setCocReleasedDate(?\DateTimeInterface $cocReleasedDate): self
    {
        $this->cocReleasedDate = $cocReleasedDate;

        return $this;
    }

    public function getApprovalDate(): ?\DateTimeInterface
    {
        return $this->approvalDate;
    }

    public function setApprovalDate(?\DateTimeInterface $approvalDate): self
    {
        $this->approvalDate = $approvalDate;

        return $this;
    }

    public function getSuperType(): ?SuperTypes
    {
        return $this->superType;
    }

    public function setSuperType(?SuperTypes $superType): self
    {
        $this->superType = $superType;

        return $this;
    }

    public function getDefaultColor(): ?Colors
    {
        return $this->defaultColor;
    }

    public function setDefaultColor(?Colors $defaultColor): self
    {
        $this->defaultColor = $defaultColor;

        return $this;
    }

    public function getDefaultProductionLocation(): ?Depots
    {
        return $this->defaultProductionLocation;
    }

    public function setDefaultProductionLocation(?Depots $defaultProductionLocation): self
    {
        $this->defaultProductionLocation = $defaultProductionLocation;

        return $this;
    }

    public function getCocReleasedBy(): ?Users
    {
        return $this->cocReleasedBy;
    }

    public function setCocReleasedBy(?Users $cocReleasedBy): self
    {
        $this->cocReleasedBy = $cocReleasedBy;

        return $this;
    }

    /**
     * @return Collection|Parts[]
     */
    public function getPart(): Collection
    {
        return $this->part;
    }

    public function addPart(Parts $part): self
    {
        if (!$this->part->contains($part)) {
            $this->part[] = $part;
        }

        return $this;
    }

    public function removePart(Parts $part): self
    {
        if ($this->part->contains($part)) {
            $this->part->removeElement($part);
        }

        return $this;
    }

    /**
     * @return Collection|VehicleExternalNames[]
     */
    public function getExternal(): Collection
    {
        return $this->external;
    }

    public function addExternal(VehicleExternalNames $external): self
    {
        if (!$this->external->contains($external)) {
            $this->external[] = $external;
        }

        return $this;
    }

    public function removeExternal(VehicleExternalNames $external): self
    {
        if ($this->external->contains($external)) {
            $this->external->removeElement($external);
        }

        return $this;
    }

}
