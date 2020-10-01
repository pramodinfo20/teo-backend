<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentVehicles
 *
 * @ORM\Table(name="document_vehicles", indexes={@ORM\Index(name="IDX_900F98A37587657C", columns={"vehicleid"}),
 *                                      @ORM\Index(name="IDX_900F98A3A76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class DocumentVehicles
{
    /**
     * @var int
     *
     * @ORM\Column(name="doc_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="document_vehicles_doc_id_seq", allocationSize=1, initialValue=1)
     */
    private $docId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="doc_name", type="string", length=100, nullable=true)
     */
    private $docName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="privileges", type="text", nullable=true)
     */
    private $privileges;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_on", type="datetime", nullable=true)
     */
    private $createdOn;

    /**
     * @var int|null
     *
     * @ORM\Column(name="filesize", type="integer", nullable=true)
     */
    private $filesize;

    /**
     * @var string|null
     *
     * @ORM\Column(name="doc_type", type="blob", nullable=true)
     */
    private $docType;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mime_type", type="string", nullable=true)
     */
    private $mimeType;

    /**
     * @var string|null
     *
     * @ORM\Column(name="file_name", type="string", length=100, nullable=true)
     */
    private $fileName;

    /**
     * @var Vehicles
     *
     * @ORM\ManyToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicleid", referencedColumnName="vehicle_id")
     * })
     */
    private $vehicleid;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    public function getDocId(): ?int
    {
        return $this->docId;
    }

    public function getDocName(): ?string
    {
        return $this->docName;
    }

    public function setDocName(?string $docName): self
    {
        $this->docName = $docName;

        return $this;
    }

    public function getPrivileges(): ?string
    {
        return $this->privileges;
    }

    public function setPrivileges(?string $privileges): self
    {
        $this->privileges = $privileges;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeInterface
    {
        return $this->createdOn;
    }

    public function setCreatedOn(?\DateTimeInterface $createdOn): self
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    public function getFilesize(): ?int
    {
        return $this->filesize;
    }

    public function setFilesize(?int $filesize): self
    {
        $this->filesize = $filesize;

        return $this;
    }

    public function getDocType()
    {
        return $this->docType;
    }

    public function setDocType($docType): self
    {
        $this->docType = $docType;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getVehicleid(): ?Vehicles
    {
        return $this->vehicleid;
    }

    public function setVehicleid(?Vehicles $vehicleid): self
    {
        $this->vehicleid = $vehicleid;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }


}
