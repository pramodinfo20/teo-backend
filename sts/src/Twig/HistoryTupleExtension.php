<?php

namespace App\Twig;

use App\Enum\Entity\HistoryEvents;
use App\Model\History\HistoryTuple;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HistoryTupleExtension extends AbstractExtension
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;


    /**
     * HistoryTuple Extension constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct( TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('historyValueTd', [$this, 'historyValueTd'],
                ['is_safe' => ['html']]),
            new TwigFilter('historyRawBeforeValue', [$this, 'historyRawBeforeValue'],
                ['is_safe' => ['html']]),
            new TwigFilter('historyRawAfterValue', [$this, 'historyRawAfterValue'],
                ['is_safe' => ['html']]),
            new TwigFilter('historyRawByEventValue', [$this, 'historyRawByEventValue'],
                ['is_safe' => ['html']])
        ];
    }

    /**
     * @param HistoryTuple $tuple
     * @param array        $classes
     * @param array        $spanClasses
     * @param array        $objectLevels
     * @param array        $prePostFix
     * @param int          $colspan
     *
     * @param array        $codingArray
     * @param array        $decodingKeys
     *
     * @return string
     */
    public function historyValueTd(
        HistoryTuple $tuple,
        array $classes = [],
        array $spanClasses = [],
        array $objectLevels = [],
        array $prePostFix = [],
        int $colspan = null,
        array $codingArray = [],
        array $decodingKeys = []
    ) : string
    {
        $value = "";
        $prefix = (isset($prePostFix['prefix'])) ? $prePostFix['prefix'] : '';
        $postfix = (isset($prePostFix['postfix'])) ? $prePostFix['postfix'] : '';
        $colspanTxt = (is_null($colspan)) ? "" : " colspan='$colspan' ";

        $class = ($tuple->isEqual()) ? ' ' : ' updateValue ';
        $beforeValue = $tuple->getBeforeValue();
        $afterValue = $tuple->getAfterValue();

        $pValTooltipPrefix = $this->translator->trans('twig.historyTupleExtension.previous', [], 'services');

        $wrapScalarValue = function ($value) use ($prefix, $postfix) : string {
            return "{$prefix}{$value}{$postfix}";
        };

        $getScalarValue = function ($beforeValue, $afterValue, $class, $classes)
            use ($wrapScalarValue, $colspanTxt, $pValTooltipPrefix, $spanClasses, $codingArray, $decodingKeys):
        string  {

            $spanClassesString = "";

            if (!empty($spanClasses)) {
                $spanClassesString = implode(" ", $spanClasses);
            }

            if (is_null($beforeValue) || is_null($afterValue)) {
                $class = "";
                if (is_null($beforeValue)) {
                    $currentValue = $afterValue;
                } else {
                    $currentValue = $beforeValue;
                }

                if (is_bool($afterValue)) {
                    $currentValue = ($currentValue) ?
                        $this->translator->trans('ecu.sw.partials.twig.header.yes', [], 'messages')
                        : $this->translator->trans('ecu.sw.partials.twig.header.no', [], 'messages');
                }

                if (!empty($codingArray)) {
                    $tmpValue = $codingArray;

                    foreach ($decodingKeys as $key) {
                        $tmpValue = $tmpValue[$key];
                    }

                    $currentValue = $tmpValue[$currentValue];
                }


                if (empty($classes)) {
                    $value = "<td $colspanTxt class='$class'>
                <span class='historyValue $spanClassesString'>{$wrapScalarValue($currentValue)}</span>
                        </td>";
                } else {
                    $classesString = implode(" ", $classes);
                    $value = "<td $colspanTxt class='$class $classesString'>
                <span class='historyValue $spanClassesString'>{$wrapScalarValue($currentValue)}</span>
                          </td>";
                }
            } else {
                if (is_bool($afterValue)) {
                    $beforeValue = ($beforeValue) ?
                        $this->translator->trans('ecu.sw.partials.twig.header.yes', [], 'messages')
                        : $this->translator->trans('ecu.sw.partials.twig.header.no', [], 'messages');
                    $afterValue = ($afterValue) ?
                        $this->translator->trans('ecu.sw.partials.twig.header.yes', [], 'messages')
                        : $this->translator->trans('ecu.sw.partials.twig.header.no', [], 'messages');
                }

                if (!empty($codingArray)) {
                    $tmpValue = $codingArray;

                    foreach ($decodingKeys as $key) {
                        $tmpValue = $tmpValue[$key];
                    }

                    $beforeValue= $tmpValue[$beforeValue];
                    $afterValue = $tmpValue[$afterValue];
                }

                if (empty($classes)) {
                    $value = "<td $colspanTxt class='$class'>
                <span class='historyValue $spanClassesString' title='$pValTooltipPrefix {$wrapScalarValue($beforeValue)}'>{$wrapScalarValue($afterValue)}</span>
                        </td>";
                } else {
                    $classesString = implode(" ", $classes);
                    $value = "<td $colspanTxt class='$class $classesString'>
                <span class='historyValue $spanClassesString' title='$pValTooltipPrefix {$wrapScalarValue($beforeValue)}'>{$wrapScalarValue($afterValue)}</span>
                          </td>";
                }

            }

            return $value;
        };

        if ((is_scalar($afterValue) || is_null($afterValue)) && (is_scalar($beforeValue) || is_null($beforeValue))) {
            $value = $getScalarValue($beforeValue, $afterValue, $class, $classes);
        } else {
            if (empty($objectLevels)) {
                if (empty($classes)) {
                    $value = "<td $colspanTxt class='historyError'><span class='historyValue'>History Error</span></td>";
                } else {
                    $classesString = implode(" ", $classes);
                    $value = "<td $colspanTxt class='historyError $classesString'><span class='historyValue'>History Error</span></td>";
                }
            } else {
                foreach ($objectLevels as $level) {
                    if (!is_null($beforeValue)) {
                        $beforeValue = $beforeValue->$level();
                    }

                    if (!is_null($afterValue)) {
                        $afterValue = $afterValue->$level();
                    }
                }

                $value = $getScalarValue($beforeValue, $afterValue, $class, $classes);
            }
        }

        return $value;
    }

    private function getScalarRawValue($value) {
        if (is_bool($value)) {
            $value = ($value) ? 1 : 0;
        }

        return $value;
    }

    /**
     * @param HistoryTuple $tuple
     * @param array $objectLevels
     *
     * @return string
     */
    public function historyRawBeforeValue(HistoryTuple $tuple, array $objectLevels = [])
    {
        $value = "";

        $beforeValue = $tuple->getBeforeValue();

        if (is_scalar($beforeValue) || is_null($beforeValue)) {
            $value = $this->getScalarRawValue($beforeValue);
        } else if (is_array($beforeValue)){
            $value = $beforeValue;
        } else {
            if (empty($objectLevels)) {
                $value = "History Error";
            } else {
                foreach ($objectLevels as $level) {
                    $beforeValue = $beforeValue->$level();
                }

                $value = $this->getScalarRawValue($beforeValue);
            }
        }

        return $value;
    }

    /**
     * @param HistoryTuple $tuple
     * @param array $objectLevels
     *
     * @return string
     */
    public function historyRawAfterValue(HistoryTuple $tuple, array $objectLevels = [])
    {
        $value = "";

        $afterValue = $tuple->getAfterValue();

        if (is_scalar($afterValue) || is_null($afterValue)) {
            $value = $this->getScalarRawValue($afterValue);
        } else if (is_array($afterValue)){
            $value = $afterValue;
        } else {
            if (empty($objectLevels)) {
                $value = "History Error";
            } else {
                foreach ($objectLevels as $level) {
                    $afterValue = $afterValue->$level();
                }

                $value = $this->getScalarRawValue($afterValue);
            }
        }

        return $value;
    }

    /**
     * @param HistoryTuple $tuple
     * @param int $event
     * @param array $objectLevels
     *
     * @return string
     */
    public function historyRawByEventValue(HistoryTuple $tuple, int $event, array $objectLevels = [])
    {
        switch ($event) {
            case HistoryEvents::CREATE:
            case HistoryEvents::UPDATE:
                return $this->historyRawAfterValue($tuple, $objectLevels);
                break;
            case HistoryEvents::DELETE:
                return $this->historyRawBeforeValue($tuple, $objectLevels);
                break;
        }

    }
}

