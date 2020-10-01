<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SecureAccessLevels
 *
 * @ORM\Table(name="secure_access_levels", uniqueConstraints={@ORM\UniqueConstraint(name="secure_access_levels_secure_access_level_key", columns={"secure_access_level"})})
 * @ORM\Entity
 */
class SecureAccessLevels
{
    /**
     * @var int
     *
     * @ORM\Column(name="secure_access_level_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="secure_access_levels_secure_access_level_id_seq", allocationSize=1,
     *                                                                                        initialValue=1)
     */
    private $secureAccessLevelId;

    /**
     * @var int
     *
     * @ORM\Column(name="secure_access_level", type="integer", nullable=false)
     */
    private $secureAccessLevel;

    public function getSecureAccessLevelId(): ?int
    {
        return $this->secureAccessLevelId;
    }

    public function getSecureAccessLevel(): ?int
    {
        return $this->secureAccessLevel;
    }

    public function setSecureAccessLevel(int $secureAccessLevel): self
    {
        $this->secureAccessLevel = $secureAccessLevel;

        return $this;
    }


}
