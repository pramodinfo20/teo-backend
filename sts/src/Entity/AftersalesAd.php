<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AftersalesAd
 *
 * @ORM\Table(name="aftersales_ad")
 * @ORM\Entity
 */
class AftersalesAd
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="aftersales_ad_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tel", type="text", nullable=true)
     */
    private $tel;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mail", type="text", nullable=true)
     */
    private $mail;

    /**
     * @var string|null
     *
     * @ORM\Column(name="gebiet", type="text", nullable=true)
     */
    private $gebiet;

    /**
     * @var integer[]|null
     *
     * @ORM\Column(name="plz", type="integer[]", nullable=true)
     */
    private $plz;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getGebiet(): ?string
    {
        return $this->gebiet;
    }

    public function setGebiet(?string $gebiet): self
    {
        $this->gebiet = $gebiet;

        return $this;
    }

    public function getPlz()
    {
        return $this->plz;
    }

    public function setPlz($plz): self
    {
        $this->plz = $plz;

        return $this;
    }


}
