<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feldbetreuung
 *
 * @ORM\Table(name="feldbetreuung")
 * @ORM\Entity
 */
class Feldbetreuung
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="feldbetreuung_id_seq", allocationSize=1, initialValue=1)
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
     * @var integer[]|null
     *
     * @ORM\Column(name="nl_id", type="integer[]", nullable=true)
     */
    private $nlId;

    /**
     * @var integer[]|null
     *
     * @ORM\Column(name="nl_dp_id", type="integer[]", nullable=true)
     */
    private $nlDpId;

    /**
     * @var text[]|null
     *
     * @ORM\Column(name="nl_name", type="text[]", nullable=true)
     */
    private $nlName;

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

    public function getNlId()
    {
        return $this->nlId;
    }

    public function setNlId($nlId): self
    {
        $this->nlId = $nlId;

        return $this;
    }

    public function getNlDpId()
    {
        return $this->nlDpId;
    }

    public function setNlDpId($nlDpId): self
    {
        $this->nlDpId = $nlDpId;

        return $this;
    }

    public function getNlName()
    {
        return $this->nlName;
    }

    public function setNlName($nlName): self
    {
        $this->nlName = $nlName;

        return $this;
    }


}
