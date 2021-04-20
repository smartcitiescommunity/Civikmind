<?php
/**
 * @file
 */

namespace Xylemical\Expressions;

use PHPUnit\Framework\TestCase;
use Xylemical\Expressions\Math\BcMath;

class LexerTest extends TestCase
{

    /**
     * @var \Xylemical\Expressions\Lexer
     */
    protected $lexer;

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

        $this->lexer = new Lexer($factory);
    }

    /**
     * Tests lexing without whitespace at all.
     */
    public function testWithoutWhitespaceLex()
    {
        // Tokenize a string.
        $tokens = $this->lexer->tokenize('1-2.3*4/5%$a==(1--2.3)*(4/5)');

        // Check there are the right number of tokens.
        $this->assertEquals(21, count($tokens));

        // Check each of the token values to ensure they match.
        $expected = [
          '1', '-', '2.3', '*', '4', '/', '5', '%', '$a', '==',
          '(', '1', '-', '-2.3', ')', '*', '(', '4', '/', '5', ')',
        ];
        foreach ($expected as $index => $value) {
            $this->assertEquals($value, $tokens[$index]->getValue());
        }
    }

    /**
     * Tests lexing with whitespace at all.
     */
    public function testWithWhitespaceLex()
    {
        // Tokenize a string.
        $tokens = $this->lexer->tokenize('1 - 2.3 * 4 / 5 % $a == (1 - -2.3) * (4 / 5)');

        // Check there are the right number of tokens.
        $this->assertEquals(21, count($tokens));

        // Check each of the token values to ensure they match.
        $expected = [
          '1', '-', '2.3', '*', '4', '/', '5', '%', '$a', '==',
          '(', '1', '-', '-2.3', ')', '*', '(', '4', '/', '5', ')',
        ];
        foreach ($expected as $index => $value) {
            $this->assertEquals($value, $tokens[$index]->getValue());
        }
    }

    /**
     * Test a blank expression throws a lexer exception.
     *
     * @throws \Xylemical\Expressions\LexerException
     */
    public function testBlankExpression()
    {
        $this->expectException('Xylemical\\Expressions\\LexerException');

        // Tokenize a string.
        $this->lexer->tokenize('');
    }
}
