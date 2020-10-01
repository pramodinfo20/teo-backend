<?php

namespace App\ParamConverters\History;

use App\History\Strategies\HistoryStrategyFactory;
use App\History\Strategies\HistoryStrategyI;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class HistoryStrategyParamConverter implements ParamConverterInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var HistoryStrategyFactory
     */
    protected $factory;

    /**
     * Parameter constructor.
     *
     * @param ObjectManager $manager
     * @param EntityManagerInterface $entityManager
     * @param HistoryStrategyFactory $factory
     */
    public function __construct(
        ObjectManager $manager,
        EntityManagerInterface $entityManager,
        HistoryStrategyFactory $factory
    )
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->factory = $factory;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request   $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     * @throws \Exception
     */
    public function apply(Request $request, ParamConverter $configuration) : bool
    {
        $class = $configuration->getClass();

        try {
            $object = $this->factory->getHistoryStrategy((int) $request->get('strategy'));
        } catch (\Exception $exception) {
            throw $exception;
        }

        $request->attributes->set($configuration->getName(), $object);

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration) : bool
    {
        if ($configuration->getClass() === HistoryStrategyI::class) {
            return true;
        }

        return false;
    }
}