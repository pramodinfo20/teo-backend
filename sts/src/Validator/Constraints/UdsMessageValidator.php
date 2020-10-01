<?php

namespace App\Validator\Constraints;

use App\Model\Odx2Parameter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UdsMessageValidator extends ConstraintValidator
{
    public function validate($parameter, Constraint $constraint): void
    {
        if (!$constraint instanceof UdsMessage) {
            throw new UnexpectedTypeException($constraint, UdsMessage::class);
        }

        if (!($parameter instanceof Odx2Parameter)) {
            throw new UnexpectedValueException($parameter, Odx2Parameter::class);
        }

        $bits = $parameter->getBytes() * 1024;
        $startBit = $parameter->getStartBit();
        $stopBit = $parameter->getStopBit();

        if (!($startBit <= $stopBit)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('bytes')
                ->setTranslationDomain('validators')
                ->addViolation();

            $this->context->buildViolation($constraint->startBitMessage)
                ->atPath('startBit')
                ->setTranslationDomain('validators')
                ->addViolation();
        }

        if (!($stopBit <= $bits)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('bytes')
                ->setTranslationDomain('validators')
                ->addViolation();

            $this->context->buildViolation($constraint->stopBitMessage)
                ->setParameter('%bits%', $bits)
                ->atPath('stopBit')
                ->setTranslationDomain('validators')
                ->addViolation();
        }
    }
}