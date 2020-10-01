<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * KeyCalculationAlgorithmKeyLengthConstraints
 *
 * @ORM\Table(name="key_calculation_algorithm_key_length_constraints", uniqueConstraints={@ORM\UniqueConstraint(name="key_calculation_algorithm_key_key_calculation_algorithm_id__key", columns={"key_calculation_algorithm_id", "cryptography_key_length_id"})}, indexes={@ORM\Index(name="IDX_1A57A72C49C211CA", columns={"cryptography_key_length_id"}), @ORM\Index(name="IDX_1A57A72C1F249842", columns={"key_calculation_algorithm_id"})})
 * @ORM\Entity
 */
class KeyCalculationAlgorithmKeyLengthConstraints
{
    /**
     * @var int
     *
     * @ORM\Column(name="kcaklc_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="key_calculation_algorithm_key_length_constraints_kcaklc_id_seq", allocationSize=1,
     *                                                                                                       initialValue=1)
     */
    private $kcaklcId;

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

    public function getKcaklcId(): ?int
    {
        return $this->kcaklcId;
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


}
