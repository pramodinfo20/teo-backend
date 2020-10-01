<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * C2cConfiguration
 *
 * @ORM\Table(name="c2c_configuration")
 * @ORM\Entity
 */
class C2cConfiguration
{
    /**
     * @var string
     *
     * @ORM\Column(name="c2cbox", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="c2c_configuration_c2cbox_seq", allocationSize=1, initialValue=1)
     */
    private $c2cbox;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sd_card", type="text", nullable=true)
     */
    private $sdCard;

    /**
     * @var string|null
     *
     * @ORM\Column(name="reverse_tunnel", type="text", nullable=true)
     */
    private $reverseTunnel;

    /**
     * @var string|null
     *
     * @ORM\Column(name="password", type="text", nullable=true)
     */
    private $password;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vin_ok", type="text", nullable=true)
     */
    private $vinOk;

    /**
     * @var string|null
     *
     * @ORM\Column(name="can_ok", type="text", nullable=true)
     */
    private $canOk;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fingerprint", type="text", nullable=true)
     */
    private $fingerprint;

    /**
     * @var string|null
     *
     * @ORM\Column(name="c2c_software_version", type="text", nullable=true)
     */
    private $c2cSoftwareVersion;

    /**
     * @var string|null
     *
     * @ORM\Column(name="wifi_password", type="text", nullable=true)
     */
    private $wifiPassword;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sim_number", type="text", nullable=true)
     */
    private $simNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="module_number", type="text", nullable=true)
     */
    private $moduleNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="modem_software_version", type="text", nullable=true)
     */
    private $modemSoftwareVersion;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ssh_key", type="text", nullable=true)
     */
    private $sshKey;

    /**
     * @var string|null
     *
     * @ORM\Column(name="manufacturer", type="text", nullable=true)
     */
    private $manufacturer;

    /**
     * @var int|null
     *
     * @ORM\Column(name="sd_card_timestamp", type="integer", nullable=true)
     */
    private $sdCardTimestamp;

    /**
     * @var string|null
     *
     * @ORM\Column(name="timezone_set", type="text", nullable=true)
     */
    private $timezoneSet;

    /**
     * @var string|null
     *
     * @ORM\Column(name="timezone", type="text", nullable=true, options={"default"="Europe/Berlin"})
     */
    private $timezone = 'Europe/Berlin';

    /**
     * @var bool|null
     *
     * @ORM\Column(name="updateable", type="boolean", nullable=true)
     */
    private $updateable;

    public function getC2cbox(): ?string
    {
        return $this->c2cbox;
    }

    public function getSdCard(): ?string
    {
        return $this->sdCard;
    }

    public function setSdCard(?string $sdCard): self
    {
        $this->sdCard = $sdCard;

        return $this;
    }

    public function getReverseTunnel(): ?string
    {
        return $this->reverseTunnel;
    }

    public function setReverseTunnel(?string $reverseTunnel): self
    {
        $this->reverseTunnel = $reverseTunnel;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getVinOk(): ?string
    {
        return $this->vinOk;
    }

    public function setVinOk(?string $vinOk): self
    {
        $this->vinOk = $vinOk;

        return $this;
    }

    public function getCanOk(): ?string
    {
        return $this->canOk;
    }

    public function setCanOk(?string $canOk): self
    {
        $this->canOk = $canOk;

        return $this;
    }

    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }

    public function setFingerprint(?string $fingerprint): self
    {
        $this->fingerprint = $fingerprint;

        return $this;
    }

    public function getC2cSoftwareVersion(): ?string
    {
        return $this->c2cSoftwareVersion;
    }

    public function setC2cSoftwareVersion(?string $c2cSoftwareVersion): self
    {
        $this->c2cSoftwareVersion = $c2cSoftwareVersion;

        return $this;
    }

    public function getWifiPassword(): ?string
    {
        return $this->wifiPassword;
    }

    public function setWifiPassword(?string $wifiPassword): self
    {
        $this->wifiPassword = $wifiPassword;

        return $this;
    }

    public function getSimNumber(): ?string
    {
        return $this->simNumber;
    }

    public function setSimNumber(?string $simNumber): self
    {
        $this->simNumber = $simNumber;

        return $this;
    }

    public function getModuleNumber(): ?string
    {
        return $this->moduleNumber;
    }

    public function setModuleNumber(?string $moduleNumber): self
    {
        $this->moduleNumber = $moduleNumber;

        return $this;
    }

    public function getModemSoftwareVersion(): ?string
    {
        return $this->modemSoftwareVersion;
    }

    public function setModemSoftwareVersion(?string $modemSoftwareVersion): self
    {
        $this->modemSoftwareVersion = $modemSoftwareVersion;

        return $this;
    }

    public function getSshKey(): ?string
    {
        return $this->sshKey;
    }

    public function setSshKey(?string $sshKey): self
    {
        $this->sshKey = $sshKey;

        return $this;
    }

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?string $manufacturer): self
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getSdCardTimestamp(): ?int
    {
        return $this->sdCardTimestamp;
    }

    public function setSdCardTimestamp(?int $sdCardTimestamp): self
    {
        $this->sdCardTimestamp = $sdCardTimestamp;

        return $this;
    }

    public function getTimezoneSet(): ?string
    {
        return $this->timezoneSet;
    }

    public function setTimezoneSet(?string $timezoneSet): self
    {
        $this->timezoneSet = $timezoneSet;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getUpdateable(): ?bool
    {
        return $this->updateable;
    }

    public function setUpdateable(?bool $updateable): self
    {
        $this->updateable = $updateable;

        return $this;
    }


}
