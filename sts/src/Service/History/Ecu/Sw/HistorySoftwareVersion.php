<?php

namespace App\Service\History\Ecu\Sw;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuSwVersions;
use App\Enum\Entity\EcuCommunicationProtocols as EcuCommunicationProtocolsEnum;
use App\Enum\Entity\HistoryEvents;
use App\Enum\HistoryTypes;
use App\History\Strategies\HistoryStrategy;
use App\History\Strategies\HistoryStrategyFactory;
use App\Model\Header as HeaderModel;
use App\Model\Odx2Collection;
use App\Service\Ecu\Sw\Parameter;
use App\Service\Ecu\Sw\SoftwareVersion;
use App\Service\Ecu\Sw\Header;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class HistorySoftwareVersion implements HistorySoftwareVersionI
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var HistoryStrategy
     */
    private $history;

    /**
     * @var SoftwareVersion
     */
    private $softwareVersionService;

    /**
     * @var Header
     */
    private $headerService;

    /**
     * @var Parameter
     */
    private $parameterService;

    /**
     * Parameter constructor.
     *
     * @param ObjectManager          $manager
     * @param EntityManagerInterface $entityManager
     * @param HistoryStrategyFactory $history
     * @param SoftwareVersion        $softwareVersionService
     * @param Header                 $headerService
     * @param Parameter              $parameterService
     *
     * @throws \Exception
     */
    public function __construct(
        ObjectManager $manager,
        EntityManagerInterface $entityManager,
        HistoryStrategyFactory $history,
        SoftwareVersion $softwareVersionService,
        Header $headerService,
        Parameter $parameterService
    )
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->history = $history->getHistoryStrategy(HistoryTypes::ECU_PARAMETER_MANAGEMENT);
        $this->softwareVersionService = $softwareVersionService;
        $this->headerService = $headerService;
        $this->parameterService = $parameterService;
    }

    /**
     * Create new SubVersion with history
     *
     * @param EcuSwVersions $sw
     * @param string        $suffix
     * @param string        $flag
     *
     * @return EcuSwVersions|null
     * @throws \Exception
     */
    public function createNewSubversion(
        EcuSwVersions $sw, string $suffix, string $flag
    ): ?EcuSwVersions
    {
        $beforeHeader =  new HeaderModel();

        $beforeOdxCollection = new Odx2Collection();

        try {
            $newSubversion = $this->softwareVersionService->createNewSubversion($sw, $suffix, $flag);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterHeader =  $this->headerService->getHeader($newSubversion);

        $afterOdxCollection = $this->parameterService->getParameters(
            2, EcuCommunicationProtocolsEnum::getProtocolNameById(SoftwareVersion::DEFAULT_PROTOCOL), $newSubversion
        );

        try {
            $this->history->init($newSubversion->getEcuSwVersionId());
            $this->history->save($beforeHeader, $afterHeader, HistoryEvents::CREATE);
            $this->history->save($beforeOdxCollection, $afterOdxCollection, HistoryEvents::CREATE);
        } catch(\Exception $exception) {
            throw $exception;
        }

        return $newSubversion;
    }

    /**
     * Delete Software Version | Subversion by Id
     *
     * @param EcuSwVersions $sw
     *
     * @return void
     * @throws \Exception
     */
    public function deleteSwById(EcuSwVersions $sw): void
    {
        $beforeHeader =  $this->headerService->getHeader($sw);

        $beforeOdxCollection = $this->parameterService->getParameters(
            2, EcuCommunicationProtocolsEnum::getProtocolNameById(SoftwareVersion::DEFAULT_PROTOCOL), $sw
        );

        $deletedName = (is_null($sw->getSuffixIfIsSubEcuSwVersion())) ?
            $sw->getSwVersion() : $sw->getSwVersion(). " ---> " . $sw->getSuffixIfIsSubEcuSwVersion();
        try {
            $this->softwareVersionService->deleteSwById($sw);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterHeader =  new HeaderModel();

        $afterOdxCollection = new Odx2Collection();

        try {
            $this->history->init(null, $deletedName);
            $this->history->save($beforeHeader, $afterHeader, HistoryEvents::DELETE);
            $this->history->save($beforeOdxCollection, $afterOdxCollection, HistoryEvents::DELETE);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Create new Software Version
     *
     * @param ConfigurationEcus $ecu
     * @param string            $sw_version
     *
     * @return EcuSwVersions
     * @throws \Exception
     */
    public function createNewSw(ConfigurationEcus $ecu, string $sw_version): EcuSwVersions
    {
        $beforeHeader =  new HeaderModel();

        $beforeOdxCollection = new Odx2Collection();

        try {
            $newSw = $this->softwareVersionService->createNewSw($ecu, $sw_version);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterHeader =  $this->headerService->getHeader($newSw);

        $afterOdxCollection = $this->parameterService->getParameters(
            2, EcuCommunicationProtocolsEnum::getProtocolNameById(SoftwareVersion::DEFAULT_PROTOCOL), $newSw
        );

        try {
            $this->history->init($newSw->getEcuSwVersionId());
            $this->history->save($beforeHeader, $afterHeader, HistoryEvents::CREATE);
            $this->history->save($beforeOdxCollection, $afterOdxCollection, HistoryEvents::CREATE);
        } catch(\Exception $exception) {
            throw $exception;
        }

        return $newSw;
    }


    /**
     * Copy Software Version | Subversion by Id
     *
     * @param EcuSwVersions $sw
     * @param string        $StsOrSuffix
     * @param int           $flag
     *
     * @return EcuSwVersions
     * @throws \Exception
     */
    public function copySwById(
        EcuSwVersions $sw,
        string $StsOrSuffix,
        int $flag = SoftwareVersion::COPY_SOFTWARE_VERSION): EcuSwVersions
    {
        $beforeHeader =  new HeaderModel();

        $beforeOdxCollection = new Odx2Collection();

        try {
            $copiedSw = $this->softwareVersionService->copySwById($sw, $StsOrSuffix, $flag);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterHeader =  $this->headerService->getHeader($copiedSw);

        $afterOdxCollection = $this->parameterService->getParameters(
            2, EcuCommunicationProtocolsEnum::getProtocolNameById(SoftwareVersion::DEFAULT_PROTOCOL), $copiedSw
        );

        try {
            $this->history->init($copiedSw->getEcuSwVersionId());
            $this->history->save($beforeHeader, $afterHeader, HistoryEvents::CREATE);
            $this->history->save($beforeOdxCollection, $afterOdxCollection, HistoryEvents::CREATE);
        } catch(\Exception $exception) {
            throw $exception;
        }

        return $copiedSw;
    }
}