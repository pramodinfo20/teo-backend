<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * C2cProblems
 *
 * @ORM\Table(name="c2c_problems", uniqueConstraints={@ORM\UniqueConstraint(name="c2c_problems_c2cbox_problem_key", columns={"c2cbox", "problem"})})
 * @ORM\Entity
 */
class C2cProblems
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="problem", type="text", nullable=true)
     */
    private $problem;

    /**
     * @var string|null
     *
     * @ORM\Column(name="action", type="text", nullable=true)
     */
    private $action;

    /**
     * @var bool
     *
     * @ORM\Column(name="problem_solved", type="boolean", nullable=false)
     */
    private $problemSolved = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="solution", type="text", nullable=true)
     */
    private $solution;

    /**
     * @var string|null
     *
     * @ORM\Column(name="remarks", type="text", nullable=true)
     */
    private $remarks;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="problem_date", type="date", nullable=false, options={"default"="now"})
     */
    private $problemDate = 'now';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="solution_date", type="date", nullable=true)
     */
    private $solutionDate;

    /**
     * @var C2cConfiguration
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="C2cConfiguration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="c2cbox", referencedColumnName="c2cbox")
     * })
     */
    private $c2cbox;

    public function getProblem(): ?string
    {
        return $this->problem;
    }

    public function setProblem(?string $problem): self
    {
        $this->problem = $problem;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getProblemSolved(): ?bool
    {
        return $this->problemSolved;
    }

    public function setProblemSolved(bool $problemSolved): self
    {
        $this->problemSolved = $problemSolved;

        return $this;
    }

    public function getSolution(): ?string
    {
        return $this->solution;
    }

    public function setSolution(?string $solution): self
    {
        $this->solution = $solution;

        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): self
    {
        $this->remarks = $remarks;

        return $this;
    }

    public function getProblemDate(): ?\DateTimeInterface
    {
        return $this->problemDate;
    }

    public function setProblemDate(\DateTimeInterface $problemDate): self
    {
        $this->problemDate = $problemDate;

        return $this;
    }

    public function getSolutionDate(): ?\DateTimeInterface
    {
        return $this->solutionDate;
    }

    public function setSolutionDate(?\DateTimeInterface $solutionDate): self
    {
        $this->solutionDate = $solutionDate;

        return $this;
    }

    public function getC2cbox(): ?C2cConfiguration
    {
        return $this->c2cbox;
    }

    public function setC2cbox(?C2cConfiguration $c2cbox): self
    {
        $this->c2cbox = $c2cbox;

        return $this;
    }


}
