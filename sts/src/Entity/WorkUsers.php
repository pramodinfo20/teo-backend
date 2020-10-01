<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkUsers
 *
 * @ORM\Table(name="work_users")
 * @ORM\Entity
 */
class WorkUsers
{
    /**
     * @var int
     *
     * @ORM\Column(name="usersid", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="work_users_usersid_seq", allocationSize=1, initialValue=1)
     */
    private $usersid;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="text", nullable=false)
     */
    private $username;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="text", nullable=true)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passwd", type="text", nullable=true)
     */
    private $passwd;

    /**
     * @var string|null
     *
     * @ORM\Column(name="privileges", type="text", nullable=true)
     */
    private $privileges;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fname", type="text", nullable=true)
     */
    private $fname;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lname", type="text", nullable=true)
     */
    private $lname;

    /**
     * @var string|null
     *
     * @ORM\Column(name="telnummber", type="text", nullable=true)
     */
    private $telnummber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="role", type="text", nullable=true)
     */
    private $role;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="timestamp_last_logged_in", type="datetime", nullable=true)
     */
    private $timestampLastLoggedIn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ip_last_logged_in", type="string", length=16, nullable=true)
     */
    private $ipLastLoggedIn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="last_session_id", type="string", length=32, nullable=true)
     */
    private $lastSessionId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updatedate", type="datetime", nullable=true)
     */
    private $updatedate;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $status = false;

    public function getUsersid(): ?int
    {
        return $this->usersid;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPasswd(): ?string
    {
        return $this->passwd;
    }

    public function setPasswd(?string $passwd): self
    {
        $this->passwd = $passwd;

        return $this;
    }

    public function getPrivileges(): ?string
    {
        return $this->privileges;
    }

    public function setPrivileges(?string $privileges): self
    {
        $this->privileges = $privileges;

        return $this;
    }

    public function getFname(): ?string
    {
        return $this->fname;
    }

    public function setFname(?string $fname): self
    {
        $this->fname = $fname;

        return $this;
    }

    public function getLname(): ?string
    {
        return $this->lname;
    }

    public function setLname(?string $lname): self
    {
        $this->lname = $lname;

        return $this;
    }

    public function getTelnummber(): ?string
    {
        return $this->telnummber;
    }

    public function setTelnummber(?string $telnummber): self
    {
        $this->telnummber = $telnummber;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getTimestampLastLoggedIn(): ?\DateTimeInterface
    {
        return $this->timestampLastLoggedIn;
    }

    public function setTimestampLastLoggedIn(?\DateTimeInterface $timestampLastLoggedIn): self
    {
        $this->timestampLastLoggedIn = $timestampLastLoggedIn;

        return $this;
    }

    public function getIpLastLoggedIn(): ?string
    {
        return $this->ipLastLoggedIn;
    }

    public function setIpLastLoggedIn(?string $ipLastLoggedIn): self
    {
        $this->ipLastLoggedIn = $ipLastLoggedIn;

        return $this;
    }

    public function getLastSessionId(): ?string
    {
        return $this->lastSessionId;
    }

    public function setLastSessionId(?string $lastSessionId): self
    {
        $this->lastSessionId = $lastSessionId;

        return $this;
    }

    public function getUpdatedate(): ?\DateTimeInterface
    {
        return $this->updatedate;
    }

    public function setUpdatedate(?\DateTimeInterface $updatedate): self
    {
        $this->updatedate = $updatedate;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;

        return $this;
    }


}
