<?php

namespace App\ParamConverters\History;

use App\Enum\HistoryTypes;
use App\History\Creators\HistoryTypeStaticCreator;
use App\Model\History\HistoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class HistoryParamConverter implements ParamConverterInterface
{

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
            $object = HistoryTypeStaticCreator::getHistoryType((int) $request->get('type'));
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
        if ($configuration->getClass() === HistoryType::class) {
            return true;
        }

        return false;
    }
}