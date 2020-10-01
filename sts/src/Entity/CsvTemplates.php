<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CsvTemplates
 *
 * @ORM\Table(name="csv_templates")
 * @ORM\Entity
 */
class CsvTemplates
{
    /**
     * @var int
     *
     * @ORM\Column(name="template_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="csv_templates_template_id_seq", allocationSize=1, initialValue=1)
     */
    private $templateId;

    /**
     * @var int
     *
     * @ORM\Column(name="userid", type="integer", nullable=false)
     */
    private $userid;

    /**
     * @var string|null
     *
     * @ORM\Column(name="userrole", type="text", nullable=true)
     */
    private $userrole;

    /**
     * @var string|null
     *
     * @ORM\Column(name="csvfields", type="text", nullable=true)
     */
    private $csvfields;

    /**
     * @var string|null
     *
     * @ORM\Column(name="template_name", type="text", nullable=true)
     */
    private $templateName;

    public function getTemplateId(): ?int
    {
        return $this->templateId;
    }

    public function getUserid(): ?int
    {
        return $this->userid;
    }

    public function setUserid(int $userid): self
    {
        $this->userid = $userid;

        return $this;
    }

    public function getUserrole(): ?string
    {
        return $this->userrole;
    }

    public function setUserrole(?string $userrole): self
    {
        $this->userrole = $userrole;

        return $this;
    }

    public function getCsvfields(): ?string
    {
        return $this->csvfields;
    }

    public function setCsvfields(?string $csvfields): self
    {
        $this->csvfields = $csvfields;

        return $this;
    }

    public function getTemplateName(): ?string
    {
        return $this->templateName;
    }

    public function setTemplateName(?string $templateName): self
    {
        $this->templateName = $templateName;

        return $this;
    }


}
