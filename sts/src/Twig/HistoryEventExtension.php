<?php

namespace App\Twig;

use App\Enum\Entity\HistoryEvents;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HistoryEventExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('historyEventClass', [$this, 'historyEventClass'],
                ['is_safe' => ['html']]),
            new TwigFilter('historyEventValueClass', [$this, 'historyEventValueClass'],
                ['is_safe' => ['html']])
        ];
    }

    /**
     * @param int $historyEvent
     *
     * @return string
     */
    public function historyEventClass(int $historyEvent)
    {
        $class = "";
        switch ($historyEvent) {
            case HistoryEvents::CREATE:
                $class = " createEvent ";
                break;
            case HistoryEvents::UPDATE:
                $class = " updateEvent ";
                break;
            case HistoryEvents::DELETE:
                $class = " deleteEvent ";
                break;
            default:
                $class = " errorEvent ";
                break;
        }

        return $class;
    }

    /**
     * @param int $historyEvent
     *
     * @return string
     */
    public function historyEventValueClass(int $historyEvent)
    {
        $class = "";
        switch ($historyEvent) {
            case HistoryEvents::CREATE:
                $class = " createValue ";
                break;
            case HistoryEvents::UPDATE:
                break;
            case HistoryEvents::DELETE:
                $class = " deleteValue ";
                break;
            default:
                $class = " historyError ";
                break;
        }

        return $class;
    }
}

