<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AllowedSymbols
 *
 * @ORM\Table(name="allowed_symbols", uniqueConstraints={@ORM\UniqueConstraint(name="allowed_symbols_symbol_key", columns={"symbol"})})
 * @ORM\Entity
 */
class AllowedSymbols
{
    /**
     * @var int
     *
     * @ORM\Column(name="allowed_symbols_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="allowed_symbols_allowed_symbols_id_seq", allocationSize=1, initialValue=1)
     */
    private $allowedSymbolsId;

    /**
     * @var string
     *
     * @ORM\Column(name="symbol", type="text", nullable=false)
     */
    private $symbol;

    public function getAllowedSymbolsId(): ?int
    {
        return $this->allowedSymbolsId;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }


}
