<?php

namespace App\Enum\Entity;

class EcuCommunicationProtocols
{
    const ECU_COMMUNICATION_PROTOCOL_NO_COMMUNICATION = 1;
    const ECU_COMMUNICATION_PROTOCOL_UDS = 2;
    const ECU_COMMUNICATION_PROTOCOL_XCP = 3;
    const ECU_COMMUNICATION_PROTOCOL_UDS_XCP = 4;

    const ECU_COMMUNICATION_PROTOCOL_NO_COMMUNICATION_NAME = 'no communication';
    const ECU_COMMUNICATION_PROTOCOL_UDS_NAME = 'UDS';
    const ECU_COMMUNICATION_PROTOCOL_XCP_NAME = 'XCP';
    const ECU_COMMUNICATION_PROTOCOL_UDS_XCP_NAME = 'UDS+XCP';

    private static $availableProtocols = [
        self::ECU_COMMUNICATION_PROTOCOL_NO_COMMUNICATION => self::ECU_COMMUNICATION_PROTOCOL_NO_COMMUNICATION_NAME,
        self::ECU_COMMUNICATION_PROTOCOL_UDS => self::ECU_COMMUNICATION_PROTOCOL_UDS_NAME,
        self::ECU_COMMUNICATION_PROTOCOL_XCP => self::ECU_COMMUNICATION_PROTOCOL_XCP_NAME,
        self::ECU_COMMUNICATION_PROTOCOL_UDS_XCP => self::ECU_COMMUNICATION_PROTOCOL_UDS_XCP_NAME
    ];

    public static function getAvailableProtocols(): array
    {
        return self::$availableProtocols;
    }

    public static function getProtocolNameById(int $protocol): string
    {
        return (array_key_exists($protocol, self::$availableProtocols)) ? self::$availableProtocols[$protocol] : null;
    }

    public static function getProtocolIdByName(string $protocol): int
    {
        $flippedProtocols = array_flip(self::$availableProtocols);

        return (array_key_exists($protocol, $flippedProtocols)) ? $flippedProtocols[$protocol] : null;
    }
}