<?php

namespace App\Repository;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuCommunicationProtocols;
use App\Entity\EcuSwVersions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EcuSwVersions|null find($id, $lockMode = null, $lockVersion = null)
 * @method EcuSwVersions|null findOneBy(array $criteria, array $orderBy = null)
 * @method EcuSwVersions[]    findAll()
 * @method EcuSwVersions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EcuSwVersionsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EcuSwVersions::class);
    }

    public function findAllSwsByEcuId(int $ecu)
    {
        return $this->createQueryBuilder('sw')
            ->select('sw.ecuSwVersionId', 'sw.stsPartNumber', 'sw.swVersion', 'sw.suffixIfIsSubEcuSwVersion as subversionSuffix')
            ->addSelect('CASE WHEN sw.suffixIfIsSubEcuSwVersion IS NULL THEN 1 ELSE 0 END as HIDDEN suffixIsNull')
            ->join('App:ConfigurationEcus', 'ecu', 'WITH', 'ecu.ceEcuId = sw.ceEcu')
            ->leftJoin('sw.parentSwVersion', 'subversion')
            ->where('sw.ceEcu = :ecu_id')
            ->setParameter('ecu_id', $ecu)
            ->addOrderBy('sw.swVersion', 'ASC')
            ->addOrderBy('suffixIsNull', 'DESC')
            ->addOrderBy('sw.suffixIfIsSubEcuSwVersion', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllReleasedSwsByEcuIdAndSubConfIdAndPrimary(int $ecu, int $subConfId, bool $primary)
    {
        return $this->createQueryBuilder('sw')
            ->select('sw.ecuSwVersionId', 'sw.stsPartNumber', 'sw.swVersion', 'sw.suffixIfIsSubEcuSwVersion as subversionSuffix')
            ->addSelect('CASE WHEN sw.suffixIfIsSubEcuSwVersion IS NULL THEN 1 ELSE 0 END as HIDDEN suffixIsNull')
            ->addSelect('CASE WHEN mapping.ecuSwVersion IS NULL THEN 0 ELSE 1 END as assignedSw')
            ->addSelect('CASE WHEN mapping.isPrimarySw != :primary THEN 1 ELSE 0 END as disabled')
            ->join('App:ConfigurationEcus', 'ecu', 'WITH', 'ecu.ceEcuId = sw.ceEcu')
            ->leftJoin('sw.parentSwVersion', 'subversion')
            ->leftJoin('App:EcuSwVersionSubVehicleConfigurationMapping', 'mapping', 'WITH',
                'sw.ecuSwVersionId = mapping.ecuSwVersion AND mapping.subVehicleConfiguration = :subConfId')
            ->where('sw.ceEcu = :ecu_id')
            ->andWhere('sw.releaseStatus = 2')
            ->setParameter('ecu_id', $ecu)
            ->setParameter('subConfId', $subConfId)
            ->setParameter('primary', $primary)
            ->addOrderBy('sw.swVersion', 'ASC')
            ->addOrderBy('suffixIsNull', 'DESC')
            ->addOrderBy('sw.suffixIfIsSubEcuSwVersion', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getHeaderInformationBySwId(EcuSwVersions $sw)
    {
        return $this->createQueryBuilder('sw')
            ->select('sw.ecuSwVersionId', 'sw.stsPartNumber', 'sw.swVersion', 'sw.suffixIfIsSubEcuSwVersion',
                'ecu.diagnosticSoftwareSupportsStsOdx2ForThisEcu as odxSts02', 'ecu.ceEcuId',
                'cp.ecuCommunicationProtocolId', 'rs.releaseStatusId',
                'gp.udsRequestId as request', 'gp.udsResponseId as response',
                'gp.windchillLink', 'gp.information', 'sw.odxVersion', 'gp.isBigEndian', 'gp.diagnosticIdentifier')
            ->join('App:ConfigurationEcus', 'ecu', 'WITH', 'ecu.ceEcuId = sw.ceEcu')
            ->join('App:EcuCommunicationProtocols', 'cp', 'WITH',
                'sw.ecuCommunicationProtocol = cp.ecuCommunicationProtocolId')
            ->join('App:ReleaseStatus', 'rs', 'WITH',
                'sw.releaseStatus = rs.releaseStatusId')
            ->join('App:EcuSwVersionGeneralProperties', 'gp', 'WITH',
                'sw.ecuSwVersionId = gp.esvgpEcuSwVersion')
            ->where('sw.ecuSwVersionId = :sw_id')
            ->setParameter('sw_id', $sw->getEcuSwVersionId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function checkIfExistsInDB(string $sw)
    {
        return $this->createQueryBuilder('sw')
            ->select('COUNT(sw.stsPartNumber)')
            ->where('sw.swVersion = :sw')
            ->setParameter('sw', $sw)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getParentIdIfExistsById(int $sw)
    {
        return $this->createQueryBuilder('sw')
            ->select('CASE WHEN sw.parentSwVersion IS NOT NULL THEN IDENTITY(sw.parentSwVersion) ELSE :null END as parent')
            ->where('sw.ecuSwVersionId = :sw_id')
            ->setParameters(['sw_id' => $sw, 'null' => null])
            ->getQuery()
            ->getResult();
    }

    public function getLockStatusById(int $sw)
    {
        return $this->createQueryBuilder('sw')
            ->select('ls.ecuSwVersionLockStatusName as status')
            ->join('App:EcuSwVersionLockStatus', 'ls', 'WITH',
                'sw.ecuSwVersionLockStatus = ls.ecuSwVersionLockStatusId')
            ->where('sw.ecuSwVersionId = :sw_id')
            ->setParameter('sw_id', $sw)
            ->getQuery()
            ->getResult();
    }

    public function getAllSubVersionsSuffixForSwByParentId(EcuSwVersions $sw)
    {
        return $this->createQueryBuilder('sw')
            ->select('sw.suffixIfIsSubEcuSwVersion as subversion_suffix')
            ->where('sw.parentSwVersion = :sw_id')
            ->setParameter('sw_id', $sw->getEcuSwVersionId())
            ->getQuery()
            ->getResult();
    }

    public function findOtherSwByEcuId(ConfigurationEcus $ecu, EcuSwVersions $sw, EcuCommunicationProtocols $protocol)
    {
        return $this->createQueryBuilder('sw')
            ->select('sw.ecuSwVersionId', 'sw.swVersion', 'sw.suffixIfIsSubEcuSwVersion as subversionSuffix')
            ->addSelect('CASE WHEN sw.suffixIfIsSubEcuSwVersion IS NULL THEN 1 ELSE 0 END as HIDDEN suffixIsNull')
            ->addSelect('CASE WHEN sw.ecuCommunicationProtocol = :protocol THEN 0 ELSE 1 END AS disable_protocol')
            ->addSelect('CASE WHEN sw.ecuSwVersionId = :current_sw THEN 1 ELSE 0 END AS disable_sw')
            ->join('App:ConfigurationEcus', 'ecu', 'WITH', 'ecu.ceEcuId = sw.ceEcu')
            ->leftJoin('sw.parentSwVersion', 'subversion')
            ->where('sw.ceEcu = :ecu_id')
            ->setParameter('ecu_id', $ecu->getCeEcuId())
            ->setParameter('protocol', $protocol->getEcuCommunicationProtocolId())
            ->setParameter('current_sw', $sw->getEcuSwVersionId())
            ->addOrderBy('sw.swVersion', 'ASC')
            ->addOrderBy('suffixIsNull', 'DESC')
            ->addOrderBy('sw.suffixIfIsSubEcuSwVersion', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
