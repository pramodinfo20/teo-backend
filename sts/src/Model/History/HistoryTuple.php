<?php


namespace App\Model\History;


class HistoryTuple
{
    /**
     * @var mixed
     */
    private $beforeValue;

    /**
     * @var mixed
     */
    private $afterValue;

    /**
     * @var bool
     */
    private $isEqual;

    /**
     * @return mixed
     */
    public function getBeforeValue()
    {
        return $this->beforeValue;
    }

    /**
     * @param mixed $beforeValue
     *
     * @return HistoryTuple
     */
    public function setBeforeValue($beforeValue)
    {
        $this->beforeValue = $beforeValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAfterValue()
    {
        return $this->afterValue;
    }

    /**
     * @param mixed $afterValue
     *
     * @return HistoryTuple
     */
    public function setAfterValue($afterValue)
    {
        $this->afterValue = $afterValue;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEqual(): bool
    {
        return $this->isEqual;
    }

    /**
     * @param bool $isEqual
     *
     * @return HistoryTuple
     */
    public function setIsEqual(bool $isEqual): HistoryTuple
    {
        $this->isEqual = $isEqual;
        return $this;
    }
}