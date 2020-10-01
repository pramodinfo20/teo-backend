<?php

namespace App\Entity;

use App\Model\EqualI;
use Doctrine\ORM\Mapping as ORM;

/**
 * EcuCommunicationProtocols
 *
 * @ORM\Table(name="ecu_communication_protocols", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_communication_protocols_ecu_communication_protocol_name_key", columns={"ecu_communication_protocol_name"})})
 * @ORM\Entity
 */
class EcuCommunicationProtocols implements EqualI
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_communication_protocol_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_communication_protocols_ecu_communication_protocol_id_seq", allocationSize=1,
     *                                                                                                      initialValue=1)
     */
    private $ecuCommunicationProtocolId;

    /**
     * @var string
     *
     * @ORM\Column(name="ecu_communication_protocol_name", type="text", nullable=false)
     */
    private $ecuCommunicationProtocolName;

    public function getEcuCommunicationProtocolId(): ?int
    {
        return $this->ecuCommunicationProtocolId;
    }

    public function getEcuCommunicationProtocolName(): ?string
    {
        return $this->ecuCommunicationProtocolName;
    }

    public function setEcuCommunicationProtocolName(string $ecuCommunicationProtocolName): self
    {
        $this->ecuCommunicationProtocolName = $ecuCommunicationProtocolName;

        return $this;
    }

    public function __toString(): string
    {
        return $this->ecuCommunicationProtocolName;
    }

    public function equals(EqualI $interface) : bool
    {
        return $this->ecuCommunicationProtocolId == $interface->getEcuCommunicationProtocolId();
    }
}
