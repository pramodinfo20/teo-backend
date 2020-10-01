<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QaHotline
 *
 * @ORM\Table(name="qa_hotline")
 * @ORM\Entity
 */
class QaHotline
{
    /**
     * @var int
     *
     * @ORM\Column(name="qa_hotline_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="qa_hotline_qa_hotline_id_seq", allocationSize=1, initialValue=1)
     */
    private $qaHotlineId;

    public function getQaHotlineId(): ?int
    {
        return $this->qaHotlineId;
    }


}
