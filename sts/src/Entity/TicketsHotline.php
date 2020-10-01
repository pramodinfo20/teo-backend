<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TicketsHotline
 *
 * @ORM\Table(name="tickets_hotline")
 * @ORM\Entity
 */
class TicketsHotline
{
    /**
     * @var int
     *
     * @ORM\Column(name="tickets_hotline_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="tickets_hotline_tickets_hotline_id_seq", allocationSize=1, initialValue=1)
     */
    private $ticketsHotlineId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ticket_id", type="text", nullable=true)
     */
    private $ticketId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="status", type="text", nullable=true)
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(name="priorität", type="text", nullable=true)
     */
    private $priorität;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="datum", type="date", nullable=true)
     */
    private $datum;

    /**
     * @var string|null
     *
     * @ORM\Column(name="akz", type="text", nullable=true)
     */
    private $akz;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vin", type="text", nullable=true)
     */
    private $vin;

    /**
     * @var string|null
     *
     * @ORM\Column(name="typ", type="text", nullable=true)
     */
    private $typ;

    /**
     * @var string|null
     *
     * @ORM\Column(name="org_e", type="text", nullable=true)
     */
    private $orgE;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ort", type="text", nullable=true)
     */
    private $ort;

    /**
     * @var string|null
     *
     * @ORM\Column(name="str_nr", type="text", nullable=true)
     */
    private $strNr;

    /**
     * @var int|null
     *
     * @ORM\Column(name="plz", type="integer", nullable=true)
     */
    private $plz;

    /**
     * @var string|null
     *
     * @ORM\Column(name="bereich", type="text", nullable=true)
     */
    private $bereich;

    /**
     * @var string|null
     *
     * @ORM\Column(name="kat_1", type="text", nullable=true)
     */
    private $kat1;

    /**
     * @var string|null
     *
     * @ORM\Column(name="kat_2", type="text", nullable=true)
     */
    private $kat2;

    /**
     * @var string|null
     *
     * @ORM\Column(name="kat_3", type="text", nullable=true)
     */
    private $kat3;

    /**
     * @var string|null
     *
     * @ORM\Column(name="kurzbesch", type="text", nullable=true)
     */
    private $kurzbesch;

    /**
     * @var string|null
     *
     * @ORM\Column(name="l_bereich", type="text", nullable=true)
     */
    private $lBereich;

    /**
     * @var string|null
     *
     * @ORM\Column(name="l_kat_1", type="text", nullable=true)
     */
    private $lKat1;

    /**
     * @var string|null
     *
     * @ORM\Column(name="l_kat_2", type="text", nullable=true)
     */
    private $lKat2;

    /**
     * @var string|null
     *
     * @ORM\Column(name="l_kurzbesch", type="text", nullable=true)
     */
    private $lKurzbesch;

    /**
     * @var string|null
     *
     * @ORM\Column(name="l_detailbesch", type="text", nullable=true)
     */
    private $lDetailbesch;

    public function getTicketsHotlineId(): ?int
    {
        return $this->ticketsHotlineId;
    }

    public function getTicketId(): ?string
    {
        return $this->ticketId;
    }

    public function setTicketId(?string $ticketId): self
    {
        $this->ticketId = $ticketId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPriorität(): ?string
    {
        return $this->priorität;
    }

    public function setPriorität(?string $priorität): self
    {
        $this->priorität = $priorität;

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

    public function getAkz(): ?string
    {
        return $this->akz;
    }

    public function setAkz(?string $akz): self
    {
        $this->akz = $akz;

        return $this;
    }

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(?string $vin): self
    {
        $this->vin = $vin;

        return $this;
    }

    public function getTyp(): ?string
    {
        return $this->typ;
    }

    public function setTyp(?string $typ): self
    {
        $this->typ = $typ;

        return $this;
    }

    public function getOrgE(): ?string
    {
        return $this->orgE;
    }

    public function setOrgE(?string $orgE): self
    {
        $this->orgE = $orgE;

        return $this;
    }

    public function getOrt(): ?string
    {
        return $this->ort;
    }

    public function setOrt(?string $ort): self
    {
        $this->ort = $ort;

        return $this;
    }

    public function getStrNr(): ?string
    {
        return $this->strNr;
    }

    public function setStrNr(?string $strNr): self
    {
        $this->strNr = $strNr;

        return $this;
    }

    public function getPlz(): ?int
    {
        return $this->plz;
    }

    public function setPlz(?int $plz): self
    {
        $this->plz = $plz;

        return $this;
    }

    public function getBereich(): ?string
    {
        return $this->bereich;
    }

    public function setBereich(?string $bereich): self
    {
        $this->bereich = $bereich;

        return $this;
    }

    public function getKat1(): ?string
    {
        return $this->kat1;
    }

    public function setKat1(?string $kat1): self
    {
        $this->kat1 = $kat1;

        return $this;
    }

    public function getKat2(): ?string
    {
        return $this->kat2;
    }

    public function setKat2(?string $kat2): self
    {
        $this->kat2 = $kat2;

        return $this;
    }

    public function getKat3(): ?string
    {
        return $this->kat3;
    }

    public function setKat3(?string $kat3): self
    {
        $this->kat3 = $kat3;

        return $this;
    }

    public function getKurzbesch(): ?string
    {
        return $this->kurzbesch;
    }

    public function setKurzbesch(?string $kurzbesch): self
    {
        $this->kurzbesch = $kurzbesch;

        return $this;
    }

    public function getLBereich(): ?string
    {
        return $this->lBereich;
    }

    public function setLBereich(?string $lBereich): self
    {
        $this->lBereich = $lBereich;

        return $this;
    }

    public function getLKat1(): ?string
    {
        return $this->lKat1;
    }

    public function setLKat1(?string $lKat1): self
    {
        $this->lKat1 = $lKat1;

        return $this;
    }

    public function getLKat2(): ?string
    {
        return $this->lKat2;
    }

    public function setLKat2(?string $lKat2): self
    {
        $this->lKat2 = $lKat2;

        return $this;
    }

    public function getLKurzbesch(): ?string
    {
        return $this->lKurzbesch;
    }

    public function setLKurzbesch(?string $lKurzbesch): self
    {
        $this->lKurzbesch = $lKurzbesch;

        return $this;
    }

    public function getLDetailbesch(): ?string
    {
        return $this->lDetailbesch;
    }

    public function setLDetailbesch(?string $lDetailbesch): self
    {
        $this->lDetailbesch = $lDetailbesch;

        return $this;
    }


}
