<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SecureAccessProperties
 *
 * @ORM\Table(name="secure_access_properties", indexes={@ORM\Index(name="IDX_48B04D6849C211CA", columns={"cryptography_key_length_id"}), @ORM\Index(name="IDX_48B04D681F249842", columns={"key_calculation_algorithm_id"}), @ORM\Index(name="IDX_48B04D68BAA7FBD4", columns={"secure_access_level_id"})})
 * @ORM\Entity
 */
class SecureAccessProperties
{
    /**
     * @var int
     *
     * @ORM\Column(name="secure_access_properties_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="secure_access_properties_secure_access_properties_id_seq", allocationSize=1,
     *                                                                                                 initialValue=1)
     */
    private $secureAccessPropertiesId;

    /**
     * @var float|null
     *
     * @ORM\Column(name="min_time_between_request_and_first_data_frame_of_device_in_prog", type="float", precision=10,
     *                                                                                     scale=0, nullable=true)
     */
    private $minTimeBetweenRequestAndFirstDataFrameOfDeviceInProg;

    /**
     * @var CryptographyKeyLengths
     *
     * @ORM\ManyToOne(targetEntity="CryptographyKeyLengths")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cryptography_key_length_id", referencedColumnName="cryptography_key_length_id")
     * })
     */
    private $cryptographyKeyLength;

    /**
     * @var KeyCalculationAlgorithms
     *
     * @ORM\ManyToOne(targetEntity="KeyCalculationAlgorithms")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="key_calculation_algorithm_id", referencedColumnName="key_calculation_algorithm_id")
     * })
     */
    private $keyCalculationAlgorithm;

    /**
     * @var SecureAccessLevels
     *
     * @ORM\ManyToOne(targetEntity="SecureAccessLevels")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="secure_access_level_id", referencedColumnName="secure_access_level_id")
     * })
     */
    private $secureAccessLevel;

    public function getSecureAccessPropertiesId(): ?int
    {
        return $this->secureAccessPropertiesId;
    }

    public function getMinTimeBetweenRequestAndFirstDataFrameOfDeviceInProg(): ?float
    {
        return $this->minTimeBetweenRequestAndFirstDataFrameOfDeviceInProg;
    }

    public function setMinTimeBetweenRequestAndFirstDataFrameOfDeviceInProg(?float $minTimeBetweenRequestAndFirstDataFrameOfDeviceInProg): self
    {
        $this->minTimeBetweenRequestAndFirstDataFrameOfDeviceInProg = $minTimeBetweenRequestAndFirstDataFrameOfDeviceInProg;

        return $this;
    }

    public function getCryptographyKeyLength(): ?CryptographyKeyLengths
    {
        return $this->cryptographyKeyLength;
    }

    public function setCryptographyKeyLength(?CryptographyKeyLengths $cryptographyKeyLength): self
    {
        $this->cryptographyKeyLength = $cryptographyKeyLength;

        return $this;
    }

    public function getKeyCalculationAlgorithm(): ?KeyCalculationAlgorithms
    {
        return $this->keyCalculationAlgorithm;
    }

    public function setKeyCalculationAlgorithm(?KeyCalculationAlgorithms $keyCalculationAlgorithm): self
    {
        $this->keyCalculationAlgorithm = $keyCalculationAlgorithm;

        return $this;
    }

    public function getSecureAccessLevel(): ?SecureAccessLevels
    {
        return $this->secureAccessLevel;
    }

    public function setSecureAccessLevel(?SecureAccessLevels $secureAccessLevel): self
    {
        $this->secureAccessLevel = $secureAccessLevel;

        return $this;
    }


}
