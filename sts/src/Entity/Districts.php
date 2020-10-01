<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Districts
 *
 * @ORM\Table(name="districts", indexes={@ORM\Index(name="districts_depot_id_idx", columns={"depot_id"}),
 *                              @ORM\Index(name="districts_vehicle_fri_idx", columns={"vehicle_fri"}),
 *                                                                           @ORM\Index(name="districts_vehicle_tue_idx", columns={"vehicle_tue"}),
 *                                                                                                                        @ORM\Index(name="districts_vehicle_wed_idx", columns={"vehicle_wed"}),
 *                                                                                                                                                                     @ORM\Index(name="districts_vehicle_sat_idx", columns={"vehicle_sat"}),
 *                                                                                                                                                                                                                  @ORM\Index(name="districts_vehicle_sun_idx", columns={"vehicle_sun"}),
 *                                                                                                                                                                                                                                                               @ORM\Index(name="districts_vehicle_thu_idx", columns={"vehicle_thu"}),
 *                                                                                                                                                                                                                                                                                                            @ORM\Index(name="districts_vehicle_mon_idx", columns={"vehicle_mon"})})
 * @ORM\Entity
 */
class Districts
{
    /**
     * @var int
     *
     * @ORM\Column(name="district_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="districts_district_id_seq", allocationSize=1, initialValue=1)
     */
    private $districtId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var float|null
     *
     * @ORM\Column(name="required_soc_mon", type="float", precision=10, scale=0, nullable=true)
     */
    private $requiredSocMon;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="departure_mon", type="time", nullable=true)
     */
    private $departureMon;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="departure_tue", type="time", nullable=true)
     */
    private $departureTue;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="departure_wed", type="time", nullable=true)
     */
    private $departureWed;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="departure_thu", type="time", nullable=true)
     */
    private $departureThu;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="departure_fri", type="time", nullable=true)
     */
    private $departureFri;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="departure_sat", type="time", nullable=true)
     */
    private $departureSat;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="departure_sun", type="time", nullable=true)
     */
    private $departureSun;

    /**
     * @var float|null
     *
     * @ORM\Column(name="required_soc_tue", type="float", precision=10, scale=0, nullable=true)
     */
    private $requiredSocTue;

    /**
     * @var float|null
     *
     * @ORM\Column(name="required_soc_wed", type="float", precision=10, scale=0, nullable=true)
     */
    private $requiredSocWed;

    /**
     * @var float|null
     *
     * @ORM\Column(name="required_soc_thu", type="float", precision=10, scale=0, nullable=true)
     */
    private $requiredSocThu;

    /**
     * @var float|null
     *
     * @ORM\Column(name="required_soc_fri", type="float", precision=10, scale=0, nullable=true)
     */
    private $requiredSocFri;

    /**
     * @var float|null
     *
     * @ORM\Column(name="required_soc_sat", type="float", precision=10, scale=0, nullable=true)
     */
    private $requiredSocSat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="required_soc_sun", type="float", precision=10, scale=0, nullable=true)
     */
    private $requiredSocSun;

    /**
     * @var Vehicles
     *
     * @ORM\ManyToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_wed", referencedColumnName="vehicle_id")
     * })
     */
    private $vehicleWed;

    /**
     * @var Vehicles
     *
     * @ORM\ManyToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_tue", referencedColumnName="vehicle_id")
     * })
     */
    private $vehicleTue;

    /**
     * @var Vehicles
     *
     * @ORM\ManyToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_thu", referencedColumnName="vehicle_id")
     * })
     */
    private $vehicleThu;

    /**
     * @var Vehicles
     *
     * @ORM\ManyToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_sun", referencedColumnName="vehicle_id")
     * })
     */
    private $vehicleSun;

    /**
     * @var Vehicles
     *
     * @ORM\ManyToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_sat", referencedColumnName="vehicle_id")
     * })
     */
    private $vehicleSat;

    /**
     * @var Vehicles
     *
     * @ORM\ManyToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_mon", referencedColumnName="vehicle_id")
     * })
     */
    private $vehicleMon;

    /**
     * @var Vehicles
     *
     * @ORM\ManyToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_fri", referencedColumnName="vehicle_id")
     * })
     */
    private $vehicleFri;

    /**
     * @var Depots
     *
     * @ORM\ManyToOne(targetEntity="Depots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depot_id", referencedColumnName="depot_id")
     * })
     */
    private $depot;

    public function getDistrictId(): ?int
    {
        return $this->districtId;
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

    public function getRequiredSocMon(): ?float
    {
        return $this->requiredSocMon;
    }

    public function setRequiredSocMon(?float $requiredSocMon): self
    {
        $this->requiredSocMon = $requiredSocMon;

        return $this;
    }

    public function getDepartureMon(): ?\DateTimeInterface
    {
        return $this->departureMon;
    }

    public function setDepartureMon(?\DateTimeInterface $departureMon): self
    {
        $this->departureMon = $departureMon;

        return $this;
    }

    public function getDepartureTue(): ?\DateTimeInterface
    {
        return $this->departureTue;
    }

    public function setDepartureTue(?\DateTimeInterface $departureTue): self
    {
        $this->departureTue = $departureTue;

        return $this;
    }

    public function getDepartureWed(): ?\DateTimeInterface
    {
        return $this->departureWed;
    }

    public function setDepartureWed(?\DateTimeInterface $departureWed): self
    {
        $this->departureWed = $departureWed;

        return $this;
    }

    public function getDepartureThu(): ?\DateTimeInterface
    {
        return $this->departureThu;
    }

    public function setDepartureThu(?\DateTimeInterface $departureThu): self
    {
        $this->departureThu = $departureThu;

        return $this;
    }

    public function getDepartureFri(): ?\DateTimeInterface
    {
        return $this->departureFri;
    }

    public function setDepartureFri(?\DateTimeInterface $departureFri): self
    {
        $this->departureFri = $departureFri;

        return $this;
    }

    public function getDepartureSat(): ?\DateTimeInterface
    {
        return $this->departureSat;
    }

    public function setDepartureSat(?\DateTimeInterface $departureSat): self
    {
        $this->departureSat = $departureSat;

        return $this;
    }

    public function getDepartureSun(): ?\DateTimeInterface
    {
        return $this->departureSun;
    }

    public function setDepartureSun(?\DateTimeInterface $departureSun): self
    {
        $this->departureSun = $departureSun;

        return $this;
    }

    public function getRequiredSocTue(): ?float
    {
        return $this->requiredSocTue;
    }

    public function setRequiredSocTue(?float $requiredSocTue): self
    {
        $this->requiredSocTue = $requiredSocTue;

        return $this;
    }

    public function getRequiredSocWed(): ?float
    {
        return $this->requiredSocWed;
    }

    public function setRequiredSocWed(?float $requiredSocWed): self
    {
        $this->requiredSocWed = $requiredSocWed;

        return $this;
    }

    public function getRequiredSocThu(): ?float
    {
        return $this->requiredSocThu;
    }

    public function setRequiredSocThu(?float $requiredSocThu): self
    {
        $this->requiredSocThu = $requiredSocThu;

        return $this;
    }

    public function getRequiredSocFri(): ?float
    {
        return $this->requiredSocFri;
    }

    public function setRequiredSocFri(?float $requiredSocFri): self
    {
        $this->requiredSocFri = $requiredSocFri;

        return $this;
    }

    public function getRequiredSocSat(): ?float
    {
        return $this->requiredSocSat;
    }

    public function setRequiredSocSat(?float $requiredSocSat): self
    {
        $this->requiredSocSat = $requiredSocSat;

        return $this;
    }

    public function getRequiredSocSun(): ?float
    {
        return $this->requiredSocSun;
    }

    public function setRequiredSocSun(?float $requiredSocSun): self
    {
        $this->requiredSocSun = $requiredSocSun;

        return $this;
    }

    public function getVehicleWed(): ?Vehicles
    {
        return $this->vehicleWed;
    }

    public function setVehicleWed(?Vehicles $vehicleWed): self
    {
        $this->vehicleWed = $vehicleWed;

        return $this;
    }

    public function getVehicleTue(): ?Vehicles
    {
        return $this->vehicleTue;
    }

    public function setVehicleTue(?Vehicles $vehicleTue): self
    {
        $this->vehicleTue = $vehicleTue;

        return $this;
    }

    public function getVehicleThu(): ?Vehicles
    {
        return $this->vehicleThu;
    }

    public function setVehicleThu(?Vehicles $vehicleThu): self
    {
        $this->vehicleThu = $vehicleThu;

        return $this;
    }

    public function getVehicleSun(): ?Vehicles
    {
        return $this->vehicleSun;
    }

    public function setVehicleSun(?Vehicles $vehicleSun): self
    {
        $this->vehicleSun = $vehicleSun;

        return $this;
    }

    public function getVehicleSat(): ?Vehicles
    {
        return $this->vehicleSat;
    }

    public function setVehicleSat(?Vehicles $vehicleSat): self
    {
        $this->vehicleSat = $vehicleSat;

        return $this;
    }

    public function getVehicleMon(): ?Vehicles
    {
        return $this->vehicleMon;
    }

    public function setVehicleMon(?Vehicles $vehicleMon): self
    {
        $this->vehicleMon = $vehicleMon;

        return $this;
    }

    public function getVehicleFri(): ?Vehicles
    {
        return $this->vehicleFri;
    }

    public function setVehicleFri(?Vehicles $vehicleFri): self
    {
        $this->vehicleFri = $vehicleFri;

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


}
