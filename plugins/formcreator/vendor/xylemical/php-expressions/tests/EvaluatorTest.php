<?php
/**
 * @file
 */

namespace Xylemical\Expressions;

use PHPUnit\Framework\TestCase;
use Xylemical\Expressions\Math\BcMath;

class EvaluatorTest extends TestCase
{
    /**
     * @var \Xylemical\Expressions\Parser
     */
    protected $parser;

    /**
     * @var \Xylemical\Expressions\Evaluator
     */
    protected $evaluator;

    /**
     * @var
     */
    protected $context;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
        parent::setUp();

        $math = new BcMath();
        $factory = new ExpressionFactory($math);

        // Add in variable processor for lexing.
        $factory->addOperator(new Value('\$[a-zA-Z_][a-zA-Z0-9_]*', function(array $operands, Context $context, Token $token) {
            return $context->getVariable(substr($token->getValue(), 1));
        }));

        $lexer = new Lexer($factory);;
        $this->parser = new Parser($lexer);

        $this->evaluator = new Evaluator();

        $this->context = new Context();
        $this->context->setVariable('a', 10);
    }

    /**
     * Test a basic evaluation.
     */
    public function testBasicEvaluation()
    {
        $tokens = $this->parser->parse('1 + 1');

        $result = $this->evaluator->evaluate($tokens, $this->context);

        $this->assertEquals('2', $result);
    }

    /**
     * Test a basic function.
     */
    public function testBasicFunction()
    {
        $tokens = $this->parser->parse('min(0, -1)');

        $result = $this->evaluator->evaluate($tokens, $this->context);

        $this->assertEquals('-1', $result);

        $tokens = $this->parser->parse('max(00, -1)');

        $result = $this->evaluator->evaluate($tokens, $this->context);

        $this->assertEquals('0', $result);
    }

    /**
     * Tests a basic variable substitution behaviour.
     */
    public function testVariableSubstitution()
    {
        $tokens = $this->parser->parse('$a * 2');

        $result = $this->evaluator->evaluate($tokens, $this->context);

        $this->assertEquals('20', $result);
    }

    /**
     * Test an improper setup of the expression.
     */
    public function testImproperTokens1()
    {
        $this->expectException('Xylemical\\Expressions\\ExpressionException');

        $tokens = $this->parser->parse('1 + 1');
        $tokens[] = new Token(',');

        $result = $this->evaluator->evaluate($tokens, $this->context);
    }

    /**
     * Test an improper setup of the expression.
     */
    public function testImproperTokens2()
    {
        $this->expectException('Xylemical\\Expressions\\ExpressionException');

        $tokens = $this->parser->parse('1 + 1 2');

        $result = $this->evaluator->evaluate($tokens, $this->context);
    }

    /**
     * Test an improper setup of the expression.
     */
    public function testImproperTokens3()
    {
        $this->expectException('Xylemical\\Expressions\\ExpressionException');

        $tokens = $this->parser->parse('min(1)');

        $result = $this->evaluator->evaluate($tokens, $this->context);
    }

    /**
     * Test that AND operator takes precedence over OR
     *
     * @return void
     */
    public function testBooleanOperatorsPrecedence()
    {
        $tokens = $this->parser->parse('0 AND 0 OR 1 AND 1 OR 1 AND 0');

        $result = $this->evaluator->evaluate($tokens, $this->context);

        $this->assertEquals('1', $result);
    }

}
