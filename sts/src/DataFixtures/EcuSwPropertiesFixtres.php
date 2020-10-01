<?php


namespace App\DataFixtures;


use App\Entity\EcuSwProperties;
use App\Entity\EcuSwPropertiesMapping;
use App\Entity\EcuSwVersions;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class EcuSwPropertiesFixtres extends Fixture
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
//        for ($i = 0; $i < 10; $i++) {
//            $ecuSwProperties = new EcuSwProperties();
//            $ecuSwProperties->setName('Property '. $i . ' name');
//            $ecuSwProperties->setValue('Property '. $i . ' value');
//
//            $manager->persist($ecuSwProperties);
//
//            $ecuSwVersion = $manager
//                ->getRepository(EcuSwVersions::class)
//                ->find(102);
//
//            $ecuSwPropertiesMapping = new EcuSwPropertiesMapping();
//            $ecuSwPropertiesMapping->setEcuSwProperty($ecuSwProperties);
//            $ecuSwPropertiesMapping->setEcuSwVersion($ecuSwVersion);
//
//            $manager->persist($ecuSwPropertiesMapping);
//        }

        $manager->flush();
    }
}