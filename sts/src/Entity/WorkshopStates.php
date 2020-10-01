<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkshopStates
 *
 * @ORM\Table(name="workshop_states", uniqueConstraints={@ORM\UniqueConstraint(name="workshop_entry_constrain", columns={"vehicle_id", "entry_date"})})
 * @ORM\Entity
 */
class WorkshopStates
{
    /**
     * @var int
     *
     * @ORM\Column(name="workshop_states_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="workshop_states_workshop_states_id_seq", allocationSize=1, initialValue=1)
     */
    private $workshopStatesId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=true)
     */
    private $vehicleId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="entry_date", type="datetimetz", nullable=true)
     */
    private $entryDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="exit_date", type="datetimetz", nullable=true)
     */
    private $exitDate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="work_type", type="text", nullable=true)
     */
    private $workType;

    public function getWorkshopStatesId(): ?int
    {
        return $this->workshopStatesId;
    }

    public function getVehicleId(): ?int
    {
        return $this->vehicleId;
    }

    public function setVehicleId(?int $vehicleId): self
    {
        $this->vehicleId = $vehicleId;

        return $this;
    }

    public function getEntryDate(): ?\DateTimeInterface
    {
        return $this->entryDate;
    }

    public function setEntryDate(?\DateTimeInterface $entryDate): self
    {
        $this->entryDate = $entryDate;

        return $this;
    }

    public function getExitDate(): ?\DateTimeInterface
    {
        return $this->exitDate;
    }

    public function setExitDate(?\DateTimeInterface $exitDate): self
    {
        $this->exitDate = $exitDate;

        return $this;
    }

    public function getWorkType(): ?string
    {
        return $this->workType;
    }

    public function setWorkType(?string $workType): self
    {
        $this->workType = $workType;

        return $this;
    }


}
