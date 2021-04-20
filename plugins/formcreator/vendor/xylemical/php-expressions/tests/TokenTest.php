<?php
/**
 * @file
 */

namespace Xylemical\Expressions;

use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{

    /**
     * Tests the precedence.
     */
    public function testPrecedence()
    {
        $token = new Token('');

        $this->assertEquals(0, $token->getPrecedence());
    }

    /**
     * Tests the associativity.
     */
    public function testAssociativity() {
        $token = new Token('');
        $this->assertEquals(Operator::NONE_ASSOCIATIVE, $token->getAssociativity());
    }

    /**
     * Tests the priority level.
     */
    public function testHigherPriority()
    {
        $token = new Token('', new Operator('', 1, Operator::RIGHT_ASSOCIATIVE, 0, 'is_bool'));
        $lower = new Token('', new Operator('', 2, Operator::NONE_ASSOCIATIVE, 0, 'is_bool'));

        $this->assertTrue($token->hasHigherPriority($lower));
    }
}
