<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkshopCompanies
 *
 * @ORM\Table(name="workshop_companies")
 * @ORM\Entity
 */
class WorkshopCompanies
{
    /**
     * @var int
     *
     * @ORM\Column(name="workshop_company_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="workshop_companies_workshop_company_id_seq", allocationSize=1,
     *                                                                                   initialValue=1)
     */
    private $workshopCompanyId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    public function getWorkshopCompanyId(): ?int
    {
        return $this->workshopCompanyId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }


}
