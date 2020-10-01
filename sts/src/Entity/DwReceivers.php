<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Analysis.dwReceivers
 *
 * @ORM\Table(name="dw_receivers", schema="analysis")
 * @ORM\Entity
 */
class DwReceivers
{
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dw_receivers_email_seq", allocationSize=1, initialValue=1)
     */
    private $email;

    public function getEmail(): ?string
    {
        return $this->email;
    }


}
