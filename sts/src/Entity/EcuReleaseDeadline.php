<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuRevisions
 *
 * @ORM\Table(name="ecu_release_deadline")
 * @ORM\Entity
 */
class EcuReleaseDeadline
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="x_weeks", type="text", nullable=true)
     */
    private $xDeadline;

    /**
     * @var string|null
     *
     * @ORM\Column(name="y_weeks", type="text", nullable=true)
     */
    private $yDeadline;

    /**
     * @var string|null
     *
     * @ORM\Column(name="z_weeks", type="text", nullable=true)
     */
    private $zDeadline;

    public function getxDeadline(): ?string
    {
        return $this->xDeadline;
    }

    public function setxDeadline(?string $xDeadline): self
    {
        $this->xDeadline = $xDeadline;

        return $this;
    }

    public function getyDeadline(): ?string
    {
        return $this->yDeadline;
    }

    public function setyDeadline(?string $yDeadline): self
    {
        $this->yDeadline = $yDeadline;

        return $this;
    }

    public function getzDeadline(): ?string
    {
        return $this->zDeadline;
    }

    public function setzDeadline(?string $zDeadline): self
    {
        $this->zDeadline = $zDeadline;

        return $this;
    }


}
