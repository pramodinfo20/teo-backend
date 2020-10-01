<?php

namespace App\Service\History\Ecu\Sw;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuSwVersions;
use App\Service\Ecu\Sw\SoftwareVersion;

interface HistorySoftwareVersionI
{
    /**
     * Delete Software Version | Subversion by Id
     *
     * @param EcuSwVersions $sw
     *
     * @return void
     * @throws \Exception
     */
    public function deleteSwById(EcuSwVersions $sw): void;

    /**
     * Create new Software Version
     *
     * @param ConfigurationEcus $ecu
     * @param string            $sw_version
     *
     * @return EcuSwVersions
     * @throws \Exception
     */
    public function createNewSw(ConfigurationEcus $ecu, string $sw_version): EcuSwVersions;

    /**
     * Create new SubVersion
     *
     * @param EcuSwVersions $sw
     * @param string        $suffix
     * @param string        $flag
     *
     * @return EcuSwVersions|null
     * @thows \Exception
     */
    public function createNewSubversion(EcuSwVersions $sw, string $suffix, string $flag): ?EcuSwVersions;

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
        int $flag = SoftwareVersion::COPY_SOFTWARE_VERSION
    ): EcuSwVersions;
}