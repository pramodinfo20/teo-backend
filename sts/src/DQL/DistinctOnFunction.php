<?php

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use function sprintf;

/**
 * "DISTINCT_ON" "(" StringPrimary "," StringPrimary ")"
 */
final class DistinctOnFunction extends FunctionNode
{
    /** @var Node */
    public $fieldExpression;

    /** @var Node */
    public $patternExpression;

    /**
     * @inheritdoc
     */
    public function getSql(SqlWalker $sqlWalker) : string
    {
        return sprintf(
            "DISTINCT ON (%s, %s) 'distinct' ",
            $this->fieldExpression->dispatch($sqlWalker),
            $this->patternExpression->dispatch($sqlWalker)
        );
    }

    /**
     * @inheritdoc
     */
    public function parse(Parser $parser) : void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->fieldExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->patternExpression = $parser->StringPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
