<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BookingUsers
 *
 * @ORM\Table(name="booking_users", indexes={@ORM\Index(name="IDX_85FF585CA76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class BookingUsers
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="booking_users_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="cost_centre", type="integer", nullable=true)
     */
    private $costCentre;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCostCentre(): ?int
    {
        return $this->costCentre;
    }

    public function setCostCentre(?int $costCentre): self
    {
        $this->costCentre = $costCentre;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }


}
