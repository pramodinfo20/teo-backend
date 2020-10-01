<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ErrorRecipients
 *
 * @ORM\Table(name="error_recipients")
 * @ORM\Entity
 */
class ErrorRecipients
{
    /**
     * @var string
     *
     * @ORM\Column(name="error", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="error_recipients_error_seq", allocationSize=1, initialValue=1)
     */
    private $error;

    /**
     * @var string|null
     *
     * @ORM\Column(name="recipients", type="text", nullable=true)
     */
    private $recipients;

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getRecipients(): ?string
    {
        return $this->recipients;
    }

    public function setRecipients(?string $recipients): self
    {
        $this->recipients = $recipients;

        return $this;
    }


}
