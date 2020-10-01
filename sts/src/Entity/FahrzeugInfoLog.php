<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FahrzeugInfoLog
 *
 * @ORM\Table(name="fahrzeug_info_log")
 * @ORM\Entity
 */
class FahrzeugInfoLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="fahrzeug_info_log_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

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
     * @var string|null
     *
     * @ORM\Column(name="status", type="text", nullable=true)
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(name="info", type="text", nullable=true)
     */
    private $info;

    /**
     * @var string|null
     *
     * @ORM\Column(name="quelle", type="text", nullable=true)
     */
    private $quelle;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(?string $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getQuelle(): ?string
    {
        return $this->quelle;
    }

    public function setQuelle(?string $quelle): self
    {
        $this->quelle = $quelle;

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


}
