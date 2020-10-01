<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * KeyCalculationAlgorithms
 *
 * @ORM\Table(name="key_calculation_algorithms", uniqueConstraints={@ORM\UniqueConstraint(name="key_calculation_algorithms_key_calculation_algorithm_name_key", columns={"key_calculation_algorithm_name"})})
 * @ORM\Entity
 */
class KeyCalculationAlgorithms
{
    /**
     * @var int
     *
     * @ORM\Column(name="key_calculation_algorithm_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="key_calculation_algorithms_key_calculation_algorithm_id_seq", allocationSize=1,
     *                                                                                                    initialValue=1)
     */
    private $keyCalculationAlgorithmId;

    /**
     * @var string
     *
     * @ORM\Column(name="key_calculation_algorithm_name", type="text", nullable=false)
     */
    private $keyCalculationAlgorithmName;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_symmetrical_key_calculation_algorithm", type="boolean", nullable=false)
     */
    private $isSymmetricalKeyCalculationAlgorithm;

    public function getKeyCalculationAlgorithmId(): ?int
    {
        return $this->keyCalculationAlgorithmId;
    }

    public function getKeyCalculationAlgorithmName(): ?string
    {
        return $this->keyCalculationAlgorithmName;
    }

    public function setKeyCalculationAlgorithmName(string $keyCalculationAlgorithmName): self
    {
        $this->keyCalculationAlgorithmName = $keyCalculationAlgorithmName;

        return $this;
    }

    public function getIsSymmetricalKeyCalculationAlgorithm(): ?bool
    {
        return $this->isSymmetricalKeyCalculationAlgorithm;
    }

    public function setIsSymmetricalKeyCalculationAlgorithm(bool $isSymmetricalKeyCalculationAlgorithm): self
    {
        $this->isSymmetricalKeyCalculationAlgorithm = $isSymmetricalKeyCalculationAlgorithm;

        return $this;
    }


}
