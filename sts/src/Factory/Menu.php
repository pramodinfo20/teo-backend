<?php

namespace App\Factory;

use App\Service\Ecu\Sw\Menu\Horizontal;
use Doctrine\Common\Persistence\ObjectManager;

class Menu
{
    /**
     * @var ObjectManager
     */
    private static $manager;

    /**
     * Menu constructor.
     *
     * @param ObjectManager $manager
     */
    public static function setObjectManager(ObjectManager $manager)
    {
        self::$manager = $manager;
    }

    /**
     * Build menus
     *
     * @param $menu
     *
     * @return Horizontal
     */
    public static function create($menu): Horizontal
    {
        return new $menu(self::$manager);
    }
}