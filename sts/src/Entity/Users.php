<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Users
 *
 * @ORM\Table(name="users", uniqueConstraints={@ORM\UniqueConstraint(name="username_unique", columns={"username"})},
 *                          indexes={@ORM\Index(name="IDX_1483A5E91FDCE57C", columns={"workshop_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 */
class Users
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="users_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="text", nullable=false)
     */
    private $username;

    /**
     * @var int|null
     *
     * @ORM\Column(name="division_id", type="integer", nullable=true)
     */
    private $divisionId;

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
     * @var int|null
     *
     * @ORM\Column(name="addedby", type="integer", nullable=true)
     */
    private $addedby;

    /**
     * @var string|null
     *
     * @ORM\Column(name="role", type="text", nullable=true)
     */
    private $role;

    /**
     * @var int|null
     *
     * @ORM\Column(name="zspl_id", type="integer", nullable=true)
     */
    private $zsplId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="notifications", type="integer", nullable=true)
     */
    private $notifications;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="cookies_accepted", type="datetime", nullable=true)
     */
    private $cookiesAccepted;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="privacy_accepted", type="datetime", nullable=true)
     */
    private $privacyAccepted;

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
     * @ORM\Column(name="expires", type="datetimetz", nullable=true)
     */
    private $expires;

    /**
     * @var Workshops
     *
     * @ORM\ManyToOne(targetEntity="Workshops")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="workshop_id", referencedColumnName="workshop_id")
     * })
     */
    private $workshop;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDivisionId(): ?int
    {
        return $this->divisionId;
    }

    public function setDivisionId(?int $divisionId): self
    {
        $this->divisionId = $divisionId;

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

    public function getAddedby(): ?int
    {
        return $this->addedby;
    }

    public function setAddedby(?int $addedby): self
    {
        $this->addedby = $addedby;

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

    public function getZsplId(): ?int
    {
        return $this->zsplId;
    }

    public function setZsplId(?int $zsplId): self
    {
        $this->zsplId = $zsplId;

        return $this;
    }

    public function getNotifications(): ?int
    {
        return $this->notifications;
    }

    public function setNotifications(?int $notifications): self
    {
        $this->notifications = $notifications;

        return $this;
    }

    public function getCookiesAccepted(): ?\DateTimeInterface
    {
        return $this->cookiesAccepted;
    }

    public function setCookiesAccepted(?\DateTimeInterface $cookiesAccepted): self
    {
        $this->cookiesAccepted = $cookiesAccepted;

        return $this;
    }

    public function getPrivacyAccepted(): ?\DateTimeInterface
    {
        return $this->privacyAccepted;
    }

    public function setPrivacyAccepted(?\DateTimeInterface $privacyAccepted): self
    {
        $this->privacyAccepted = $privacyAccepted;

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

    public function getExpires(): ?\DateTimeInterface
    {
        return $this->expires;
    }

    public function setExpires(?\DateTimeInterface $expires): self
    {
        $this->expires = $expires;

        return $this;
    }

    public function getWorkshop(): ?Workshops
    {
        return $this->workshop;
    }

    public function setWorkshop(?Workshops $workshop): self
    {
        $this->workshop = $workshop;

        return $this;
    }

    public function __toString(): string
    {
        return $this->username;
    }
}
