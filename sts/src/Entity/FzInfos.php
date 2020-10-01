<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FzInfos
 *
 * @ORM\Table(name="fz_infos")
 * @ORM\Entity
 */
class FzInfos
{
    /**
     * @var int
     *
     * @ORM\Column(name="fz_info_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="fz_infos_fz_info_id_seq", allocationSize=1, initialValue=1)
     */
    private $fzInfoId;

    public function getFzInfoId(): ?int
    {
        return $this->fzInfoId;
    }


}
