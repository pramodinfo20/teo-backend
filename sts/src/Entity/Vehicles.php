<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Vehicles
 *
 * @ORM\Table(name="vehicles", uniqueConstraints={@ORM\UniqueConstraint(name="vehicles_vin_key", columns={"vin"}),
 *                             @ORM\UniqueConstraint(name="vehicles_code_key", columns={"code"}),
 *                                                                             @ORM\UniqueConstraint(name="vehicles_vin_idx", columns={"vin"})},
 *                                                                                                                            indexes={@ORM\Index(name="vehicles_station_id_idx", columns={"station_id"}), @ORM\Index(name="vehicles_c2cbox_idx", columns={"c2cbox"}), @ORM\Index(name="vehicles_depot_id_idx", columns={"depot_id"}), @ORM\Index(name="IDX_1FCE69FAFF3D07C3", columns={"penta_variant_id"}), @ORM\Index(name="IDX_1FCE69FA602D1907", columns={"sub_vehicle_configuration_id"}), @ORM\Index(name="IDX_1FCE69FAEE30E2C2", columns={"vehicle_variant"}), @ORM\Index(name="IDX_1FCE69FA24C64F32", columns={"special_qs_approval_set_user"}), @ORM\Index(name="IDX_1FCE69FA5450AE9", columns={"penta_number_id"}), @ORM\Index(name="IDX_1FCE69FA44990C25", columns={"park_id"}), @ORM\Index(name="IDX_1FCE69FA7ADA1FB5", columns={"color_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\VehiclesRepository")
 */
class Vehicles
{
    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vehicles_vehicle_id_seq", allocationSize=1, initialValue=1)
     */
    private $vehicleId;

    /**
     * @var float
     *
     * @ORM\Column(name="usable_battery_capacity", type="float", precision=10, scale=0, nullable=false)
     */
    private $usableBatteryCapacity;

    /**
     * @var string|null
     *
     * @ORM\Column(name="c2cbox", type="text", nullable=true)
     */
    private $c2cbox;

    /**
     * @var string
     *
     * @ORM\Column(name="vin", type="text", nullable=false)
     */
    private $vin;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="code", type="text", nullable=true)
     */
    private $code;

    /**
     * @var float|null
     *
     * @ORM\Column(name="lat", type="float", precision=10, scale=0, nullable=true)
     */
    private $lat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="lon", type="float", precision=10, scale=0, nullable=true)
     */
    private $lon;

    /**
     * @var int
     *
     * @ORM\Column(name="precondition_duration", type="integer", nullable=false, options={"default"="1800"})
     */
    private $preconditionDuration = '1800';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="emergency_charge_time", type="datetime", nullable=true)
     */
    private $emergencyChargeTime;

    /**
     * @var int|null
     *
     * @ORM\Column(name="update_timestamp", type="integer", nullable=true)
     */
    private $updateTimestamp;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ikz", type="text", nullable=true)
     */
    private $ikz;

    /**
     * @var bool
     *
     * @ORM\Column(name="charger_controllable", type="boolean", nullable=false, options={"default"="1"})
     */
    private $chargerControllable = true;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vin_bcm", type="text", nullable=true)
     */
    private $vinBcm;

    /**
     * @var float
     *
     * @ORM\Column(name="fallback_power_odd", type="float", precision=10, scale=0, nullable=false, options={"default"="1500"})
     */
    private $fallbackPowerOdd = '1500';

    /**
     * @var float
     *
     * @ORM\Column(name="fallback_power_even", type="float", precision=10, scale=0, nullable=false, options={"default"="1500"})
     */
    private $fallbackPowerEven = '1500';

    /**
     * @var bool
     *
     * @ORM\Column(name="refitted_c2c", type="boolean", nullable=false)
     */
    private $refittedC2c = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="three_phase_charger", type="boolean", nullable=false)
     */
    private $threePhaseCharger = false;

    /**
     * @var int
     *
     * @ORM\Column(name="charger_power", type="integer", nullable=false, options={"default"="3000"})
     */
    private $chargerPower = '3000';

    /**
     * @var bool
     *
     * @ORM\Column(name="late_charging", type="boolean", nullable=false)
     */
    private $lateCharging = false;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="late_charging_time", type="time", nullable=true)
     */
    private $lateChargingTime;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="finished_status", type="boolean", nullable=true)
     */
    private $finishedStatus = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="enable_preconditioning", type="boolean", nullable=false, options={"default"="1"})
     */
    private $enablePreconditioning = true;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="gps_timestamp", type="datetime", nullable=true)
     */
    private $gpsTimestamp;

    /**
     * @var bool
     *
     * @ORM\Column(name="qmlocked", type="boolean", nullable=false)
     */
    private $qmlocked = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="special_qs_approval", type="boolean", nullable=false)
     */
    private $specialQsApproval = false;

    /**
     * @var int|null
     *
     * @ORM\Column(name="precondition_power", type="integer", nullable=true, options={"default"="1500"})
     */
    private $preconditionPower = '1500';

    /**
     * @var int|null
     *
     * @ORM\Column(name="options_group_info", type="integer", nullable=true)
     */
    private $optionsGroupInfo;

    /**
     * @var int|null
     *
     * @ORM\Column(name="external_id", type="integer", nullable=true)
     */
    private $externalId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="penta_kennwort", type="text", nullable=true)
     */
    private $pentaKennwort;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_soc", type="integer", nullable=true)
     */
    private $maxSoc;

    /**
     * @var int|null
     *
     * @ORM\Column(name="park_position", type="integer", nullable=true)
     */
    private $parkPosition;

    /**
     * @var float|null
     *
     * @ORM\Column(name="mlat", type="float", precision=10, scale=0, nullable=true)
     */
    private $mlat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="mlon", type="float", precision=10, scale=0, nullable=true)
     */
    private $mlon;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mgps_user", type="text", nullable=true)
     */
    private $mgpsUser;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="mgps_timestamp", type="datetime", nullable=true)
     */
    private $mgpsTimestamp;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="depot_dispatch_date", type="datetime", nullable=true)
     */
    private $depotDispatchDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="special_qs_approval_set_time", type="datetime", nullable=true)
     */
    private $specialQsApprovalSetTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="special_qs_approval_comment", type="text", nullable=true)
     */
    private $specialQsApprovalComment;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mcomment", type="text", nullable=true, options={"comment"="Comment concerning  this vehicle. For example for entering a description of what the vehicle is currently used for (testing purposes etc.)"})
     */
    private $mcomment;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="replacement_vehicles", type="boolean", nullable=true)
     */
    private $replacementVehicles = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="penta_keyword", type="text", nullable=true)
     */
    private $pentaKeyword;

    /**
     * @var PentaVariants
     *
     * @ORM\ManyToOne(targetEntity="PentaVariants")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="penta_variant_id", referencedColumnName="penta_variant_id")
     * })
     */
    private $pentaVariant;

    /**
     * @var SubVehicleConfigurations
     *
     * @ORM\ManyToOne(targetEntity="SubVehicleConfigurations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sub_vehicle_configuration_id", referencedColumnName="sub_vehicle_configuration_id")
     * })
     */
    private $subVehicleConfiguration;

    /**
     * @var VehicleVariants
     *
     * @ORM\ManyToOne(targetEntity="VehicleVariants")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_variant", referencedColumnName="vehicle_variant_id")
     * })
     */
    private $vehicleVariant;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="special_qs_approval_set_user", referencedColumnName="id")
     * })
     */
    private $specialQsApprovalSetUser;

    /**
     * @var PentaNumbers
     *
     * @ORM\ManyToOne(targetEntity="PentaNumbers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="penta_number_id", referencedColumnName="penta_number_id")
     * })
     */
    private $pentaNumber;

    /**
     * @var ParkLines
     *
     * @ORM\ManyToOne(targetEntity="ParkLines")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="park_id", referencedColumnName="park_id")
     * })
     */
    private $park;

    /**
     * @var Colors
     *
     * @ORM\ManyToOne(targetEntity="Colors")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="color_id", referencedColumnName="color_id")
     * })
     */
    private $color;

    /**
     * @var Stations
     *
     * @ORM\ManyToOne(targetEntity="Stations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="station_id", referencedColumnName="station_id")
     * })
     */
    private $station;

    /**
     * @var Depots
     *
     * @ORM\ManyToOne(targetEntity="Depots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depot_id", referencedColumnName="depot_id")
     * })
     */
    private $depot;

    /**
     * @var Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AvailabilityParameters", inversedBy="vehicle")
     * @ORM\JoinTable(name="analysis.availability_parameter_sets",
     *   joinColumns={
     *     @ORM\JoinColumn(name="vehicle_id", referencedColumnName="vehicle_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="parameter_id", referencedColumnName="parameter_id")
     *   }
     * )
     */
    private $parameter;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="DhlExpressServiceProvider", inversedBy="vehicle")
     * @ORM\JoinTable(name="dhl_express_vehicles",
     *   joinColumns={
     *     @ORM\JoinColumn(name="vehicle_id", referencedColumnName="vehicle_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="service_provider_id", referencedColumnName="service_provider_id")
     *   }
     * )
     */
    private $serviceProvider;

    /**
     * @var Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Parts", inversedBy="vehicle")
     * @ORM\JoinTable(name="options_at_vehicles",
     *   joinColumns={
     *     @ORM\JoinColumn(name="vehicle_id", referencedColumnName="vehicle_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="part_id", referencedColumnName="part_id")
     *   }
     * )
     */
    private $part;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parameter = new \Doctrine\Common\Collections\ArrayCollection();
        $this->serviceProvider = new \Doctrine\Common\Collections\ArrayCollection();
        $this->part = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getVehicleId(): ?int
    {
        return $this->vehicleId;
    }

    public function getUsableBatteryCapacity(): ?float
    {
        return $this->usableBatteryCapacity;
    }

    public function setUsableBatteryCapacity(float $usableBatteryCapacity): self
    {
        $this->usableBatteryCapacity = $usableBatteryCapacity;

        return $this;
    }

    public function getC2cbox(): ?string
    {
        return $this->c2cbox;
    }

    public function setC2cbox(?string $c2cbox): self
    {
        $this->c2cbox = $c2cbox;

        return $this;
    }

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(string $vin): self
    {
        $this->vin = $vin;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(?float $lon): self
    {
        $this->lon = $lon;

        return $this;
    }

    public function getPreconditionDuration(): ?int
    {
        return $this->preconditionDuration;
    }

    public function setPreconditionDuration(int $preconditionDuration): self
    {
        $this->preconditionDuration = $preconditionDuration;

        return $this;
    }

    public function getEmergencyChargeTime(): ?\DateTimeInterface
    {
        return $this->emergencyChargeTime;
    }

    public function setEmergencyChargeTime(?\DateTimeInterface $emergencyChargeTime): self
    {
        $this->emergencyChargeTime = $emergencyChargeTime;

        return $this;
    }

    public function getUpdateTimestamp(): ?int
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(?int $updateTimestamp): self
    {
        $this->updateTimestamp = $updateTimestamp;

        return $this;
    }

    public function getIkz(): ?string
    {
        return $this->ikz;
    }

    public function setIkz(?string $ikz): self
    {
        $this->ikz = $ikz;

        return $this;
    }

    public function getChargerControllable(): ?bool
    {
        return $this->chargerControllable;
    }

    public function setChargerControllable(bool $chargerControllable): self
    {
        $this->chargerControllable = $chargerControllable;

        return $this;
    }

    public function getVinBcm(): ?string
    {
        return $this->vinBcm;
    }

    public function setVinBcm(?string $vinBcm): self
    {
        $this->vinBcm = $vinBcm;

        return $this;
    }

    public function getFallbackPowerOdd(): ?float
    {
        return $this->fallbackPowerOdd;
    }

    public function setFallbackPowerOdd(float $fallbackPowerOdd): self
    {
        $this->fallbackPowerOdd = $fallbackPowerOdd;

        return $this;
    }

    public function getFallbackPowerEven(): ?float
    {
        return $this->fallbackPowerEven;
    }

    public function setFallbackPowerEven(float $fallbackPowerEven): self
    {
        $this->fallbackPowerEven = $fallbackPowerEven;

        return $this;
    }

    public function getRefittedC2c(): ?bool
    {
        return $this->refittedC2c;
    }

    public function setRefittedC2c(bool $refittedC2c): self
    {
        $this->refittedC2c = $refittedC2c;

        return $this;
    }

    public function getThreePhaseCharger(): ?bool
    {
        return $this->threePhaseCharger;
    }

    public function setThreePhaseCharger(bool $threePhaseCharger): self
    {
        $this->threePhaseCharger = $threePhaseCharger;

        return $this;
    }

    public function getChargerPower(): ?int
    {
        return $this->chargerPower;
    }

    public function setChargerPower(int $chargerPower): self
    {
        $this->chargerPower = $chargerPower;

        return $this;
    }

    public function getLateCharging(): ?bool
    {
        return $this->lateCharging;
    }

    public function setLateCharging(bool $lateCharging): self
    {
        $this->lateCharging = $lateCharging;

        return $this;
    }

    public function getLateChargingTime(): ?\DateTimeInterface
    {
        return $this->lateChargingTime;
    }

    public function setLateChargingTime(?\DateTimeInterface $lateChargingTime): self
    {
        $this->lateChargingTime = $lateChargingTime;

        return $this;
    }

    public function getFinishedStatus(): ?bool
    {
        return $this->finishedStatus;
    }

    public function setFinishedStatus(?bool $finishedStatus): self
    {
        $this->finishedStatus = $finishedStatus;

        return $this;
    }

    public function getEnablePreconditioning(): ?bool
    {
        return $this->enablePreconditioning;
    }

    public function setEnablePreconditioning(bool $enablePreconditioning): self
    {
        $this->enablePreconditioning = $enablePreconditioning;

        return $this;
    }

    public function getGpsTimestamp(): ?\DateTimeInterface
    {
        return $this->gpsTimestamp;
    }

    public function setGpsTimestamp(?\DateTimeInterface $gpsTimestamp): self
    {
        $this->gpsTimestamp = $gpsTimestamp;

        return $this;
    }

    public function getQmlocked(): ?bool
    {
        return $this->qmlocked;
    }

    public function setQmlocked(bool $qmlocked): self
    {
        $this->qmlocked = $qmlocked;

        return $this;
    }

    public function getSpecialQsApproval(): ?bool
    {
        return $this->specialQsApproval;
    }

    public function setSpecialQsApproval(bool $specialQsApproval): self
    {
        $this->specialQsApproval = $specialQsApproval;

        return $this;
    }

    public function getPreconditionPower(): ?int
    {
        return $this->preconditionPower;
    }

    public function setPreconditionPower(?int $preconditionPower): self
    {
        $this->preconditionPower = $preconditionPower;

        return $this;
    }

    public function getOptionsGroupInfo(): ?int
    {
        return $this->optionsGroupInfo;
    }

    public function setOptionsGroupInfo(?int $optionsGroupInfo): self
    {
        $this->optionsGroupInfo = $optionsGroupInfo;

        return $this;
    }

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }

    public function setExternalId(?int $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getPentaKennwort(): ?string
    {
        return $this->pentaKennwort;
    }

    public function setPentaKennwort(?string $pentaKennwort): self
    {
        $this->pentaKennwort = $pentaKennwort;

        return $this;
    }

    public function getMaxSoc(): ?int
    {
        return $this->maxSoc;
    }

    public function setMaxSoc(?int $maxSoc): self
    {
        $this->maxSoc = $maxSoc;

        return $this;
    }

    public function getParkPosition(): ?int
    {
        return $this->parkPosition;
    }

    public function setParkPosition(?int $parkPosition): self
    {
        $this->parkPosition = $parkPosition;

        return $this;
    }

    public function getMlat(): ?float
    {
        return $this->mlat;
    }

    public function setMlat(?float $mlat): self
    {
        $this->mlat = $mlat;

        return $this;
    }

    public function getMlon(): ?float
    {
        return $this->mlon;
    }

    public function setMlon(?float $mlon): self
    {
        $this->mlon = $mlon;

        return $this;
    }

    public function getMgpsUser(): ?string
    {
        return $this->mgpsUser;
    }

    public function setMgpsUser(?string $mgpsUser): self
    {
        $this->mgpsUser = $mgpsUser;

        return $this;
    }

    public function getMgpsTimestamp(): ?\DateTimeInterface
    {
        return $this->mgpsTimestamp;
    }

    public function setMgpsTimestamp(?\DateTimeInterface $mgpsTimestamp): self
    {
        $this->mgpsTimestamp = $mgpsTimestamp;

        return $this;
    }

    public function getDepotDispatchDate(): ?\DateTimeInterface
    {
        return $this->depotDispatchDate;
    }

    public function setDepotDispatchDate(?\DateTimeInterface $depotDispatchDate): self
    {
        $this->depotDispatchDate = $depotDispatchDate;

        return $this;
    }

    public function getSpecialQsApprovalSetTime(): ?\DateTimeInterface
    {
        return $this->specialQsApprovalSetTime;
    }

    public function setSpecialQsApprovalSetTime(?\DateTimeInterface $specialQsApprovalSetTime): self
    {
        $this->specialQsApprovalSetTime = $specialQsApprovalSetTime;

        return $this;
    }

    public function getSpecialQsApprovalComment(): ?string
    {
        return $this->specialQsApprovalComment;
    }

    public function setSpecialQsApprovalComment(?string $specialQsApprovalComment): self
    {
        $this->specialQsApprovalComment = $specialQsApprovalComment;

        return $this;
    }

    public function getMcomment(): ?string
    {
        return $this->mcomment;
    }

    public function setMcomment(?string $mcomment): self
    {
        $this->mcomment = $mcomment;

        return $this;
    }

    public function getReplacementVehicles(): ?bool
    {
        return $this->replacementVehicles;
    }

    public function setReplacementVehicles(?bool $replacementVehicles): self
    {
        $this->replacementVehicles = $replacementVehicles;

        return $this;
    }

    public function getPentaKeyword(): ?string
    {
        return $this->pentaKeyword;
    }

    public function setPentaKeyword(?string $pentaKeyword): self
    {
        $this->pentaKeyword = $pentaKeyword;

        return $this;
    }

    public function getPentaVariant(): ?PentaVariants
    {
        return $this->pentaVariant;
    }

    public function setPentaVariant(?PentaVariants $pentaVariant): self
    {
        $this->pentaVariant = $pentaVariant;

        return $this;
    }

    public function getSubVehicleConfiguration(): ?SubVehicleConfigurations
    {
        return $this->subVehicleConfiguration;
    }

    public function setSubVehicleConfiguration(?SubVehicleConfigurations $subVehicleConfiguration): self
    {
        $this->subVehicleConfiguration = $subVehicleConfiguration;

        return $this;
    }

    public function getVehicleVariant(): ?VehicleVariants
    {
        return $this->vehicleVariant;
    }

    public function setVehicleVariant(?VehicleVariants $vehicleVariant): self
    {
        $this->vehicleVariant = $vehicleVariant;

        return $this;
    }

    public function getSpecialQsApprovalSetUser(): ?Users
    {
        return $this->specialQsApprovalSetUser;
    }

    public function setSpecialQsApprovalSetUser(?Users $specialQsApprovalSetUser): self
    {
        $this->specialQsApprovalSetUser = $specialQsApprovalSetUser;

        return $this;
    }

    public function getPentaNumber(): ?PentaNumbers
    {
        return $this->pentaNumber;
    }

    public function setPentaNumber(?PentaNumbers $pentaNumber): self
    {
        $this->pentaNumber = $pentaNumber;

        return $this;
    }

    public function getPark(): ?ParkLines
    {
        return $this->park;
    }

    public function setPark(?ParkLines $park): self
    {
        $this->park = $park;

        return $this;
    }

    public function getColor(): ?Colors
    {
        return $this->color;
    }

    public function setColor(?Colors $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getStation(): ?Stations
    {
        return $this->station;
    }

    public function setStation(?Stations $station): self
    {
        $this->station = $station;

        return $this;
    }

    public function getDepot(): ?Depots
    {
        return $this->depot;
    }

    public function setDepot(?Depots $depot): self
    {
        $this->depot = $depot;

        return $this;
    }

    /**
     * @return Collection|AvailabilityParameters[]
     */
    public function getParameter(): Collection
    {
        return $this->parameter;
    }

    public function addParameter(AvailabilityParameters $parameter): self
    {
        if (!$this->parameter->contains($parameter)) {
            $this->parameter[] = $parameter;
        }

        return $this;
    }

    public function removeParameter(AvailabilityParameters $parameter): self
    {
        if ($this->parameter->contains($parameter)) {
            $this->parameter->removeElement($parameter);
        }

        return $this;
    }

    /**
     * @return Collection|DhlExpressServiceProvider[]
     */
    public function getServiceProvider(): Collection
    {
        return $this->serviceProvider;
    }

    public function addServiceProvider(DhlExpressServiceProvider $serviceProvider): self
    {
        if (!$this->serviceProvider->contains($serviceProvider)) {
            $this->serviceProvider[] = $serviceProvider;
        }

        return $this;
    }

    public function removeServiceProvider(DhlExpressServiceProvider $serviceProvider): self
    {
        if ($this->serviceProvider->contains($serviceProvider)) {
            $this->serviceProvider->removeElement($serviceProvider);
        }

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

}
