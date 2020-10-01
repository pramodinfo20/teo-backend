<?php

namespace App\History\Traits;

use App\Enum\Entity\HistoryEvents;
use App\Model\EqualI;
use App\Model\ConvertibleToHistoryI;
use App\Model\History\HistoryI;
use App\Model\History\HistoryOdx2Collection;
use App\Model\History\HistoryTuple;

trait DiffModels
{
    public function getHistoryModel(
        ConvertibleToHistoryI $beforeInterface,
        ConvertibleToHistoryI $afterInterface,
        \ReflectionClass $reflection,
        int $event
    ): HistoryI
    {
        switch ($event) {
            case HistoryEvents::CREATE:
                return $this->getHistoryCreateOrDeleteModel($afterInterface, $reflection, $event);
                break;
            case HistoryEvents::UPDATE:
                return $this->getHistoryDiffModel($beforeInterface, $afterInterface, $reflection);
                break;
            case HistoryEvents::DELETE:
                return $this->getHistoryCreateOrDeleteModel($beforeInterface, $reflection, $event);
                break;
            default:
                throw new \Exception("History - get history model method. Wrong event!");
                break;
        }
    }

    private function getHistoryDiffModel(
        ConvertibleToHistoryI $beforeInterface,
        ConvertibleToHistoryI $afterInterface,
        \ReflectionClass $reflection
    ): HistoryI
    {
        $className = "App\Model\History\History{$reflection->getShortName()}";

        $historyModel = new $className();

        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();

            $getterPrefix = $reflection->hasMethod("get" . ucfirst($name)) ? "get" :
                ($reflection->hasMethod("is" . ucfirst($name)) ? "is" : "");
            $getter = $getterPrefix . ucfirst($name);
            $setter = "set" . ucfirst($name);
            $historyTuple = new HistoryTuple();

            if ($afterInterface->$getter() != $beforeInterface->$getter()) {
                $historyTuple->setIsEqual(false);
            } else {
                $historyTuple->setIsEqual(true);
            }

            $historyTuple->setBeforeValue($beforeInterface->$getter());
            $historyTuple->setAfterValue($afterInterface->$getter());

            $historyModel->$setter($historyTuple);
        }

        $historyModel->setHistoryEvent(HistoryEvents::UPDATE);

        return $historyModel;
    }

    private function getHistoryCreateOrDeleteModel(
        ConvertibleToHistoryI $interface,
        \ReflectionClass $reflection,
        int $event
    ): HistoryI
    {
        $className = "App\Model\History\History{$reflection->getShortName()}";

        $historyModel = new $className();

        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();

            $getterPrefix = $reflection->hasMethod("get" . ucfirst($name)) ? "get" :
                ($reflection->hasMethod("is" . ucfirst($name)) ? "is" : "");
            $getter = $getterPrefix . ucfirst($name);
            $setter = "set" . ucfirst($name);
            $historyTuple = new HistoryTuple();
            $historyTuple->setIsEqual(false);

            if ($event = HistoryEvents::CREATE) {
                $historyTuple->setBeforeValue(null);
                $historyTuple->setAfterValue($interface->$getter());
            } else {
                $historyTuple->setBeforeValue($interface->$getter());
                $historyTuple->setAfterValue(null);
            }
            $historyModel->$setter($historyTuple);
        }

        $historyModel->setHistoryEvent($event);

        return $historyModel;
    }

    private function getHistoryModelForCreateOrDeleteEvent(
        ConvertibleToHistoryI $interface,
        \ReflectionClass $reflection,
        int $event
    ): HistoryI
    {
        $className = "App\Model\History\History{$reflection->getShortName()}";

        $historyModel = new $className();

        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();

            $getterPrefix = $reflection->hasMethod("get" . ucfirst($name)) ? "get" :
                ($reflection->hasMethod("is" . ucfirst($name)) ? "is" : "");
            $getter = $getterPrefix . ucfirst($name);
            $setter = "set" . ucfirst($name);
            $historyTuple = new HistoryTuple();

            $historyTuple->setIsEqual(false);

            switch ($event) {
                case HistoryEvents::CREATE:
                    $historyTuple->setBeforeValue(null);
                    $historyTuple->setAfterValue($interface->$getter());
                    break;
                case HistoryEvents::DELETE:
                    $historyTuple->setBeforeValue($interface->$getter());
                    $historyTuple->setAfterValue(null);
                    break;
                default:
                    throw new \Exception("History - get history model method. Wrong event!");
                    break;
            }


            $historyModel->$setter($historyTuple);
        }

        $historyModel->setHistoryEvent($event);

        return $historyModel;
    }

    private function getModelIfExistInCollection(ConvertibleToHistoryI $collection, EqualI $model): ?EqualI
    {
        $parameters = $collection->getParameters()->toArray();

        $found = array_filter($parameters, function ($value) use ($model)
        {
            return $value->equals($model);
        });

        return (empty($found)) ? null : current($found);
    }

    public function getHistoryCollectionModel(
        ConvertibleToHistoryI $beforeCollectionInterface,
        ConvertibleToHistoryI $afterCollectionInterface,
        \ReflectionClass $reflection,
        int $event
    ): HistoryI
    {
        switch ($event) {
            case HistoryEvents::CREATE:
                return $this->getHistoryCollectionCreateOrDeleteModel($afterCollectionInterface, $reflection, $event);
                break;
            case HistoryEvents::UPDATE:
                return $this->getHistoryCollectionDiffModel($beforeCollectionInterface, $afterCollectionInterface,
                    $reflection);
                break;
            case HistoryEvents::DELETE:
                return $this->getHistoryCollectionCreateOrDeleteModel($beforeCollectionInterface, $reflection, $event);
                break;
            default:
                throw new \Exception("History - get history model method. Wrong event!");
                break;
        }
    }


    private function getHistoryCollectionDiffModel(
        ConvertibleToHistoryI $beforeCollectionInterface,
        ConvertibleToHistoryI $afterCollectionInterface,
        \ReflectionClass $reflection
    ): HistoryI
    {
        $className = "App\Model\History\History{$reflection->getShortName()}";

        $historyCollection = new $className();

        $beforeCollection = $beforeCollectionInterface->getParameters();
        $afterCollection = $afterCollectionInterface->getParameters();

        $parameterClass = get_class($beforeCollection->current());

        try {
            do {
                do {
                    if (!$afterCollection->current()) {
                        break;
                    }
                    if (($beforeCollection->current())->equals($afterCollection->current())) {
                        $historyCollection->addParameters(
                            $this->getHistoryDiffModel(
                                $beforeCollection->current(),
                                $afterCollection->current(),
                                new\ReflectionClass($parameterClass)
                            )
                        );
                        $afterCollection->next();
                        continue 2;
                    } else {
                        $modelBefore = $this->getModelIfExistInCollection(
                            $beforeCollectionInterface,
                            $afterCollection->current()
                        );

                        $modelAfter = $this->getModelIfExistInCollection(
                            $afterCollectionInterface,
                            $beforeCollection->current()
                        );

                        if ((is_null($modelBefore) && !is_null($modelAfter))
                            || (!is_null($modelBefore) && is_null($modelAfter))) {

                            switch (($beforeCollection->current())->compare($afterCollection->current())) {
                                case -1:
                                    $historyCollection->addParameters(
                                        $this->getHistoryModelForCreateOrDeleteEvent(
                                            $beforeCollection->current(),
                                            new\ReflectionClass($parameterClass),
                                            HistoryEvents::DELETE
                                        )
                                    );
                                    continue 3;
                                case 0:
                                    if (is_null($modelAfter)) {
                                        $historyCollection->addParameters(
                                            $this->getHistoryModelForCreateOrDeleteEvent(
                                                $beforeCollection->current(),
                                                new\ReflectionClass($parameterClass),
                                                HistoryEvents::DELETE
                                            )
                                        );
                                    } else {
                                        $historyCollection->addParameters(
                                            $this->getHistoryModelForCreateOrDeleteEvent(
                                                $afterCollection->current(),
                                                new\ReflectionClass($parameterClass),
                                                HistoryEvents::CREATE
                                            )
                                        );

                                        $afterCollection->next();
                                    }
                                    continue 3;
                                case 1:
                                    $historyCollection->addParameters(
                                        $this->getHistoryModelForCreateOrDeleteEvent(
                                            $afterCollection->current(),
                                            new\ReflectionClass($parameterClass),
                                            HistoryEvents::CREATE
                                        )
                                    );

                                    $afterCollection->next();
                                    continue 3;
                            }
                        } else if (!is_null($modelBefore) && !is_null($modelAfter)) {
                            $historyCollection->addParameters(
                                $this->getHistoryDiffModel(
                                    $modelBefore,
                                    $afterCollection->current(),
                                    new\ReflectionClass($parameterClass)
                                )
                            );
                            $afterCollection->next();
                            continue 2;
                        } else {
                            $historyCollection->addParameters(
                                $this->getHistoryDiffModel(
                                    $beforeCollection->current(),
                                    $afterCollection->current(),
                                    new\ReflectionClass($parameterClass)
                                )
                            );
                            $afterCollection->next();
                            continue 2;
                        }
                    }
                } while ($afterCollection->next());
            } while ($beforeCollection->next());

            do {
                if ($afterCollection->current()) {
                    $historyCollection->addParameters(
                        $this->getHistoryModelForCreateOrDeleteEvent(
                            $afterCollection->current(),
                            new\ReflectionClass($parameterClass),
                            HistoryEvents::CREATE
                        )
                    );
                }
            } while ($afterCollection->next());

        } catch (\ReflectionException $exception) {
            throw $exception;
        }

        $historyCollection->setHistoryEvent(HistoryEvents::UPDATE);

        return $historyCollection;
    }

    private function getHistoryCollectionCreateOrDeleteModel(
        ConvertibleToHistoryI $collectionInterface,
        \ReflectionClass $reflection,
        int $event
    ): HistoryI
    {
        $className = "App\Model\History\History{$reflection->getShortName()}";

        $historyCollection = new $className();

        $collection = $collectionInterface->getParameters();

        $parameterClass = get_class($collection->current());


        try {
            do {
                $historyCollection->addParameters(
                    $this->getHistoryModelForCreateOrDeleteEvent(
                        $collection->current(),
                        new\ReflectionClass($parameterClass),
                        $event
                    )
                );
            } while ($collection->next());

        } catch (\ReflectionException $exception) {
            throw $exception;
        }

        $historyCollection->setHistoryEvent($event);

        return $historyCollection;
    }
}