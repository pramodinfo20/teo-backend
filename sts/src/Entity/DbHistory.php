<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DbHistory
 *
 * @ORM\Table(name="db_history")
 * @ORM\Entity
 */
class DbHistory
{
    /**
     * @var int
     *
     * @ORM\Column(name="queryid", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="db_history_queryid_seq", allocationSize=1, initialValue=1)
     */
    private $queryid;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tablename", type="text", nullable=true)
     */
    private $tablename;

    /**
     * @var string|null
     *
     * @ORM\Column(name="updatecols", type="text", nullable=true)
     */
    private $updatecols;

    /**
     * @var string|null
     *
     * @ORM\Column(name="oldvals", type="text", nullable=true)
     */
    private $oldvals;

    /**
     * @var string|null
     *
     * @ORM\Column(name="newvals", type="text", nullable=true)
     */
    private $newvals;

    /**
     * @var int|null
     *
     * @ORM\Column(name="userid", type="integer", nullable=true)
     */
    private $userid;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="update_timestamp", type="datetimetz", nullable=true)
     */
    private $updateTimestamp;

    /**
     * @var string|null
     *
     * @ORM\Column(name="username", type="text", nullable=true)
     */
    private $username;

    /**
     * @var string|null
     *
     * @ORM\Column(name="where_stmt", type="text", nullable=true)
     */
    private $whereStmt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="affected_ids", type="text", nullable=true)
     */
    private $affectedIds;

    public function getQueryid(): ?int
    {
        return $this->queryid;
    }

    public function getTablename(): ?string
    {
        return $this->tablename;
    }

    public function setTablename(?string $tablename): self
    {
        $this->tablename = $tablename;

        return $this;
    }

    public function getUpdatecols(): ?string
    {
        return $this->updatecols;
    }

    public function setUpdatecols(?string $updatecols): self
    {
        $this->updatecols = $updatecols;

        return $this;
    }

    public function getOldvals(): ?string
    {
        return $this->oldvals;
    }

    public function setOldvals(?string $oldvals): self
    {
        $this->oldvals = $oldvals;

        return $this;
    }

    public function getNewvals(): ?string
    {
        return $this->newvals;
    }

    public function setNewvals(?string $newvals): self
    {
        $this->newvals = $newvals;

        return $this;
    }

    public function getUserid(): ?int
    {
        return $this->userid;
    }

    public function setUserid(?int $userid): self
    {
        $this->userid = $userid;

        return $this;
    }

    public function getUpdateTimestamp(): ?\DateTimeInterface
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(?\DateTimeInterface $updateTimestamp): self
    {
        $this->updateTimestamp = $updateTimestamp;

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

    public function getWhereStmt(): ?string
    {
        return $this->whereStmt;
    }

    public function setWhereStmt(?string $whereStmt): self
    {
        $this->whereStmt = $whereStmt;

        return $this;
    }

    public function getAffectedIds(): ?string
    {
        return $this->affectedIds;
    }

    public function setAffectedIds(?string $affectedIds): self
    {
        $this->affectedIds = $affectedIds;

        return $this;
    }


}
