<?php

namespace App\Service\Ecu\Sw\Menu;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Interface Horizontal
 *
 * @package App\Service\Ecu\Sw\Menu
 */
abstract class Horizontal
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function setArguments(array $arguments = [])
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return array
     */
    public abstract function getMenu(): array;

    /**
     * @return mixed
     */
    public abstract function build();
}