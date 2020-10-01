<?php

namespace App\Entity;

use App\Model\EqualI;
use Doctrine\ORM\Mapping as ORM;

/**
 * ReleaseStatus
 *
 * @ORM\Table(name="release_status", uniqueConstraints={@ORM\UniqueConstraint(name="release_status_release_status_name_key", columns={"release_status_name"})})
 * @ORM\Entity
 */
class ReleaseStatus implements EqualI
{
    /**
     * @var int
     *
     * @ORM\Column(name="release_status_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="release_status_release_status_id_seq", allocationSize=1, initialValue=1)
     */
    private $releaseStatusId;

    /**
     * @var string
     *
     * @ORM\Column(name="release_status_name", type="text", nullable=false)
     */
    private $releaseStatusName;

    public function getReleaseStatusId(): ?int
    {
        return $this->releaseStatusId;
    }

    public function getReleaseStatusName(): ?string
    {
        return $this->releaseStatusName;
    }

    public function setReleaseStatusName(string $releaseStatusName): self
    {
        $this->releaseStatusName = $releaseStatusName;

        return $this;
    }

    public function __toString(): string
    {
        return $this->releaseStatusName;
    }


    public function equals(EqualI $interface): bool
    {
        return $this->releaseStatusId == $interface->getReleaseStatusId();
    }
}
