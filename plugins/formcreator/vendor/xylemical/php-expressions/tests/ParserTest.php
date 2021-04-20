<?php
/**
 * @file
 */

namespace Xylemical\Expressions;

use PHPUnit\Framework\TestCase;
use Xylemical\Expressions\Math\BcMath;

class ParserTest extends TestCase
{

    /**
     * @var \Xylemical\Expressions\Parser
     */
    protected $parser;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
        parent::setUp();

        $math = new BcMath();
        $factory = new ExpressionFactory($math);

        // Add in variable processor for lexing.
        $factory->addOperator(new Value('\$[a-zA-Z_][a-zA-Z0-9_]*', function(array $operands, Context $context, Token $token) {
            return $context->getVariable(substr($token, 1));
        }));

        $lexer = new Lexer($factory);;
        $this->parser = new Parser($lexer);
    }

    /**
     * Tests parsing.
     */
    public function testParse()
    {
        // Parse a string.
        $tokens = $this->parser->parse('1-2.3 == 4/$a');

        // Check there are the right number of tokens.
        $this->assertEquals(count($tokens), 7);

        // Check each of the token values to ensure they match.
        $expected = [
          '1', '2.3', '-', '4', '$a', '/', '==',
        ];
        foreach ($expected as $index => $value) {
            $this->assertEquals($tokens[$index]->getValue(), $value);
        }
    }

    /**
     * Tests function parsing.
     */
    public function testFunction()
    {
        // Parse a string.
        $tokens = $this->parser->parse('min ( max ( 2, 3 ) / 3 * 3.1415, 0 )');

        // Check there are the right number of tokens.
        $this->assertEquals(count($tokens), 9);

        // Check each of the token values to ensure they match.
        $expected = [
          '2', '3', 'max', '3', '/', '3.1415', '*', '0', 'min',
        ];
        foreach ($expected as $index => $value) {
            $this->assertEquals($tokens[$index]->getValue(), $value);
        }
    }

    /**
     * Test exception from imbalanced parenthesis.
     */
    public function testUnbalanced1()
    {
        $this->expectException('\\Xylemical\\Expressions\\ParserException');

        $this->parser->parse('(');
    }

    /**
     * Test exception from imbalanced parenthesis.
     */
    public function testUnbalanced2()
    {
        $this->expectException('\\Xylemical\\Expressions\\ParserException');

        $this->parser->parse('(a,');
    }

    /**
     * Test exception from imbalanced parenthesis.
     */
    public function testUnbalanced3()
    {
        $this->expectException('\\Xylemical\\Expressions\\ParserException');

        $this->parser->parse('(a,a) + 2 )');
    }

    /**
     * Test exception from imbalanced parenthesis.
     */
    public function testUnbalanced4()
    {
        $this->expectException('\\Xylemical\\Expressions\\ParserException');

        $this->parser->parse('3,');
    }

}
