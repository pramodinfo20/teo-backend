<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TicketsGas
 *
 * @ORM\Table(name="tickets_gas")
 * @ORM\Entity
 */
class TicketsGas
{
    /**
     * @var int
     *
     * @ORM\Column(name="ticket_id_gas", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="tickets_gas_ticket_id_gas_seq", allocationSize=1, initialValue=1)
     */
    private $ticketIdGas;

    /**
     * @var string|null
     *
     * @ORM\Column(name="akz", type="text", nullable=true)
     */
    private $akz;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="datum", type="date", nullable=true)
     */
    private $datum;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="werkstatttermin", type="date", nullable=true)
     */
    private $werkstatttermin;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="fertigstellung", type="date", nullable=true)
     */
    private $fertigstellung;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ticket_id_hotline", type="text", nullable=true)
     */
    private $ticketIdHotline;

    /**
     * @var string|null
     *
     * @ORM\Column(name="stornogrund", type="text", nullable=true)
     */
    private $stornogrund;

    /**
     * @var string|null
     *
     * @ORM\Column(name="bemerkung", type="text", nullable=true)
     */
    private $bemerkung;

    /**
     * @var string|null
     *
     * @ORM\Column(name="werkstatt", type="text", nullable=true)
     */
    private $werkstatt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tel_1", type="text", nullable=true)
     */
    private $tel1;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tel_2", type="text", nullable=true)
     */
    private $tel2;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="datum_upload", type="date", nullable=true)
     */
    private $datumUpload;

    public function getTicketIdGas(): ?int
    {
        return $this->ticketIdGas;
    }

    public function getAkz(): ?string
    {
        return $this->akz;
    }

    public function setAkz(?string $akz): self
    {
        $this->akz = $akz;

        return $this;
    }

    public function getDatum(): ?\DateTimeInterface
    {
        return $this->datum;
    }

    public function setDatum(?\DateTimeInterface $datum): self
    {
        $this->datum = $datum;

        return $this;
    }

    public function getWerkstatttermin(): ?\DateTimeInterface
    {
        return $this->werkstatttermin;
    }

    public function setWerkstatttermin(?\DateTimeInterface $werkstatttermin): self
    {
        $this->werkstatttermin = $werkstatttermin;

        return $this;
    }

    public function getFertigstellung(): ?\DateTimeInterface
    {
        return $this->fertigstellung;
    }

    public function setFertigstellung(?\DateTimeInterface $fertigstellung): self
    {
        $this->fertigstellung = $fertigstellung;

        return $this;
    }

    public function getTicketIdHotline(): ?string
    {
        return $this->ticketIdHotline;
    }

    public function setTicketIdHotline(?string $ticketIdHotline): self
    {
        $this->ticketIdHotline = $ticketIdHotline;

        return $this;
    }

    public function getStornogrund(): ?string
    {
        return $this->stornogrund;
    }

    public function setStornogrund(?string $stornogrund): self
    {
        $this->stornogrund = $stornogrund;

        return $this;
    }

    public function getBemerkung(): ?string
    {
        return $this->bemerkung;
    }

    public function setBemerkung(?string $bemerkung): self
    {
        $this->bemerkung = $bemerkung;

        return $this;
    }

    public function getWerkstatt(): ?string
    {
        return $this->werkstatt;
    }

    public function setWerkstatt(?string $werkstatt): self
    {
        $this->werkstatt = $werkstatt;

        return $this;
    }

    public function getTel1(): ?string
    {
        return $this->tel1;
    }

    public function setTel1(?string $tel1): self
    {
        $this->tel1 = $tel1;

        return $this;
    }

    public function getTel2(): ?string
    {
        return $this->tel2;
    }

    public function setTel2(?string $tel2): self
    {
        $this->tel2 = $tel2;

        return $this;
    }

    public function getDatumUpload(): ?\DateTimeInterface
    {
        return $this->datumUpload;
    }

    public function setDatumUpload(?\DateTimeInterface $datumUpload): self
    {
        $this->datumUpload = $datumUpload;

        return $this;
    }


}
