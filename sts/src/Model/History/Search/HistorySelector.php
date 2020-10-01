<?php

namespace App\Model\History\Search;

use DateTime;

class HistorySelector
{
    /**
     * @var DateTime
     */
    private $createdFrom;

    /**
     * @var DateTime
     */
    private $createdTo;

    /**
     * @var int
     */
    private $createdBy;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var int
     */
    private $event;

    /**
     * @var int
     */
    private $fk;

    /**
     * @var array
     */
    private $filters;

    /**
     * @return DateTime
     */
    public function getCreatedFrom(): ?DateTime
    {
        return $this->createdFrom;
    }

    /**
     * @param DateTime $createdFrom
     *
     * @return HistorySelector
     */
    public function setCreatedFrom(DateTime $createdFrom = null): HistorySelector
    {
        $this->createdFrom = $createdFrom;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedTo(): ?DateTime
    {
        return $this->createdTo;
    }

    /**
     * @param DateTime $createdTo
     *
     * @return HistorySelector
     */
    public function setCreatedTo(DateTime $createdTo = null): HistorySelector
    {
        $this->createdTo = $createdTo;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    /**
     * @param int $createdBy
     *
     * @return HistorySelector
     */
    public function setCreatedBy(int $createdBy = null): HistorySelector
    {
        $this->createdBy = $createdBy;
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
     * @return HistorySelector
     */
    public function setComment(string $comment = null): HistorySelector
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return int
     */
    public function getEvent(): ?int
    {
        return $this->event;
    }

    /**
     * @param int $event
     *
     * @return HistorySelector
     */
    public function setEvent(int $event = null): HistorySelector
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilters(): ?array
    {
        return $this->filters;
    }

    /**
     * @param array $filters
     *
     * @return HistorySelector
     */
    public function setFilters(array $filters = null): HistorySelector
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @return int
     */
    public function getFk() : ?int
    {
        return $this->fk;
    }

    /**
     * @param int $fk
     *
     * @return HistorySelector
     */
    public function setFk(int $fk = null)
    {
        $this->fk = $fk;
        return $this;
    }
}