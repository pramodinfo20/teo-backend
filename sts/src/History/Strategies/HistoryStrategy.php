<?php

namespace App\History\Strategies;

use App\Model\ConvertibleToHistoryI;
use App\Model\History\HistoryI;
use App\Model\History\HistoryMetaData;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use App\History\Traits\DiffModels;

abstract class HistoryStrategy implements HistoryStrategyI
{
    use DiffModels;
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Parameter constructor.
     *
     * @param ObjectManager $manager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ObjectManager $manager, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    public function initSession(int $id = null, string $comment = null): void
    {
        if (isset($_SESSION['tmp_history'])) {
            unset($_SESSION['tmp_history']);
        }

        $_SESSION['tmp_history']['created_at'] = date('Y-m-d H:i:s');
        $_SESSION['tmp_history']['comment'] = $comment;
        $_SESSION['tmp_history']['fk'] = $id;
    }

    public function closeSession(): void
    {
        unset($_SESSION['tmp_history']);
    }

    public abstract function init(int $fk = null, string $name = null) : void;

    public abstract function save(
        ConvertibleToHistoryI $beforeInterface,
        ConvertibleToHistoryI $afterInterface,
        int $event
    ) : void;

    public abstract function load(int $id) : HistoryI;

    public abstract function getMetaData(int $id) : HistoryMetaData;

    public abstract function getTableName() : string;

    public abstract function getLegacyAction() : string;

    public abstract function isOnlyLog(): bool;
}