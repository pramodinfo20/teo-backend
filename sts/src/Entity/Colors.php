<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colors
 *
 * @ORM\Table(name="colors")
 * @ORM\Entity
 */
class Colors
{
    /**
     * @var int
     *
     * @ORM\Column(name="color_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="colors_color_id_seq", allocationSize=1, initialValue=1)
     */
    private $colorId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vin_color_code", type="text", nullable=true)
     */
    private $vinColorCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="rgb", type="string", length=6, nullable=true, options={"default"="FFCC00","fixed"=true})
     */
    private $rgb = 'FFCC00';

    /**
     * @var string
     *
     * @ORM\Column(name="color_key", type="string", length=2, nullable=false, options={"default"="GE","fixed"=true})
     */
    private $colorKey = 'GE';

    public function getColorId(): ?int
    {
        return $this->colorId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getVinColorCode(): ?string
    {
        return $this->vinColorCode;
    }

    public function setVinColorCode(?string $vinColorCode): self
    {
        $this->vinColorCode = $vinColorCode;

        return $this;
    }

    public function getRgb(): ?string
    {
        return $this->rgb;
    }

    public function setRgb(?string $rgb): self
    {
        $this->rgb = $rgb;

        return $this;
    }

    public function getColorKey(): ?string
    {
        return $this->colorKey;
    }

    public function setColorKey(string $colorKey): self
    {
        $this->colorKey = $colorKey;

        return $this;
    }


}
