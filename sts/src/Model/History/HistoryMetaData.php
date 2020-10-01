<?php

namespace App\Model\History;

use App\Entity\HistoryEvents;
use App\Entity\Users;

class HistoryMetaData
{
    /**
     * @var Users
     */
    private $user;

    /**
     * @var \DateTime
     */
    private $dateTime;

    /**
     * @var HistoryEvents
     */
    private $historyEvent;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $comment;

    /**
     * @return Users
     */
    public function getUser(): ?Users
    {
        return $this->user;
    }

    /**
     * @param Users $user
     *
     * @return HistoryMetaData
     */
    public function setUser(Users $user = null): HistoryMetaData
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime(): ?\DateTime
    {
        return $this->dateTime;
    }

    /**
     * @param \DateTime $dateTime
     *
     * @return HistoryMetaData
     */
    public function setDateTime(\DateTime $dateTime = null): HistoryMetaData
    {
        $this->dateTime = $dateTime;
        return $this;
    }

    /**
     * @return HistoryEvents
     */
    public function getHistoryEvent(): ?HistoryEvents
    {
        return $this->historyEvent;
    }

    /**
     * @param HistoryEvents $historyEvent
     *
     * @return HistoryMetaData
     */
    public function setHistoryEvent(HistoryEvents $historyEvent = null): HistoryMetaData
    {
        $this->historyEvent = $historyEvent;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return HistoryMetaData
     */
    public function setName(string $name = null): HistoryMetaData
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return HistoryMetaData
     */
    public function setComment(string $comment = null): HistoryMetaData
    {
        $this->comment = $comment;
        return $this;
    }
}