<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Analysisframework.djangoMigrations
 *
 * @ORM\Table(name="analysisframework.django_migrations", schema="analysisframework")
 * @ORM\Entity
 */
class DjangoMigrations
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="analysisframework.django_migrations_id_seq", allocationSize=1,
     *                                                                                   initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="app", type="string", length=255, nullable=false)
     */
    private $app;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="applied", type="datetimetz", nullable=false)
     */
    private $applied;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApp(): ?string
    {
        return $this->app;
    }

    public function setApp(string $app): self
    {
        $this->app = $app;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getApplied(): ?\DateTimeInterface
    {
        return $this->applied;
    }

    public function setApplied(\DateTimeInterface $applied): self
    {
        $this->applied = $applied;

        return $this;
    }


}
