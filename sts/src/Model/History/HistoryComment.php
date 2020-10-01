<?php

namespace App\Model\History;

class HistoryComment
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $comment;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return HistoryComment
     */
    public function setId(int $id = null): HistoryComment
    {
        $this->id = $id;
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
     * @return HistoryComment
     */
    public function setComment(string $comment = null): HistoryComment
    {
        $this->comment = $comment;
        return $this;
    }


}