<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransporterDates
 *
 * @ORM\Table(name="transporter_dates")
 * @ORM\Entity
 */
class TransporterDates
{
    /**
     * @var int
     *
     * @ORM\Column(name="transporter_date_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="transporter_dates_transporter_date_id_seq", allocationSize=1,
     *                                                                                  initialValue=1)
     */
    private $transporterDateId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="transporter_id", type="integer", nullable=true)
     */
    private $transporterId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="transporter_date", type="datetimetz", nullable=true)
     */
    private $transporterDate;

    /**
     * @var int|null
     *
     * @ORM\Column(name="station_id", type="integer", nullable=true)
     */
    private $stationId;

    public function getTransporterDateId(): ?int
    {
        return $this->transporterDateId;
    }

    public function getTransporterId(): ?int
    {
        return $this->transporterId;
    }

    public function setTransporterId(?int $transporterId): self
    {
        $this->transporterId = $transporterId;

        return $this;
    }

    public function getTransporterDate(): ?\DateTimeInterface
    {
        return $this->transporterDate;
    }

    public function setTransporterDate(?\DateTimeInterface $transporterDate): self
    {
        $this->transporterDate = $transporterDate;

        return $this;
    }

    public function getStationId(): ?int
    {
        return $this->stationId;
    }

    public function setStationId(?int $stationId): self
    {
        $this->stationId = $stationId;

        return $this;
    }


}
