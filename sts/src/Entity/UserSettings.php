<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserSettings
 *
 * @ORM\Table(name="user_settings", uniqueConstraints={@ORM\UniqueConstraint(name="c_sts_userid",
 *                                  columns={"sts_userid"})})
 * @ORM\Entity
 */
class UserSettings
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="user_settings_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="settings", type="text", nullable=false)
     */
    private $settings;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sts_userid", referencedColumnName="id")
     * })
     */
    private $stsUserid;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSettings(): ?string
    {
        return $this->settings;
    }

    public function setSettings(string $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function getStsUserid(): ?Users
    {
        return $this->stsUserid;
    }

    public function setStsUserid(?Users $stsUserid): self
    {
        $this->stsUserid = $stsUserid;

        return $this;
    }


}
