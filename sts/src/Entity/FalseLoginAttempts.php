<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FalseLoginAttempts
 *
 * @ORM\Table(name="false_login_attempts")
 * @ORM\Entity
 */
class FalseLoginAttempts
{
    /**
     * @var int
     *
     * @ORM\Column(name="false_login_attempts_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="false_login_attempts_false_login_attempts_id_seq", allocationSize=1,
     *                                                                                         initialValue=1)
     */
    private $falseLoginAttemptsId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ip_address", type="text", nullable=true)
     */
    private $ipAddress;

    /**
     * @var string|null
     *
     * @ORM\Column(name="username", type="text", nullable=true)
     */
    private $username;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="timestamp_attempt", type="datetimetz", nullable=true)
     */
    private $timestampAttempt;

    public function getFalseLoginAttemptsId(): ?int
    {
        return $this->falseLoginAttemptsId;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getTimestampAttempt(): ?\DateTimeInterface
    {
        return $this->timestampAttempt;
    }

    public function setTimestampAttempt(?\DateTimeInterface $timestampAttempt): self
    {
        $this->timestampAttempt = $timestampAttempt;

        return $this;
    }


}
