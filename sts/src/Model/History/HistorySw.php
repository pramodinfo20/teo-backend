<?php


namespace App\Model\History;


use App\Model\History\HistoryI;
use App\Model\History\Traits\HistoryEvent;

class HistorySw implements HistoryI
{
    use HistoryEvent;

    /**
     * @var int
     */
    private $odx;

    /**
     * @var HistoryHeader
     */
    private $historyHeader;

    /**
     * @var HistoryOdxCollection
     */
    private $historyOdxCollection;

    /**
     * @return mixed
     */
    public function getOdx() : ?int
    {
        return $this->odx;
    }

    /**
     * @param int $odx
     *
     * @return HistorySw
     */
    public function setOdx($odx = null) : HistorySw
    {
        $this->odx = $odx;
        return $this;
    }


    /**
     * @return HistoryHeader
     */
    public function getHistoryHeader(): HistoryHeader
    {
        return $this->historyHeader;
    }

    /**
     * @param HistoryHeader $historyHeader
     *
     * @return HistorySw
     */
    public function setHistoryHeader(HistoryHeader $historyHeader): HistorySw
    {
        $this->historyHeader = $historyHeader;
        return $this;
    }

    /**
     * @return HistoryOdxCollection
     */
    public function getHistoryOdxCollection(): HistoryOdxCollection
    {
        return $this->historyOdxCollection;
    }

    /**
     * @param HistoryOdxCollection $historyOdxCollection
     *
     * @return HistorySw
     */
    public function setHistoryOdxCollection(HistoryOdxCollection $historyOdxCollection): HistorySw
    {
        $this->historyOdxCollection = $historyOdxCollection;
        return $this;
    }
}