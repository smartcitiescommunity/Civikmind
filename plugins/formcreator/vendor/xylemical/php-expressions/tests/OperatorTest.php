<?php
/**
 * @file
 */

namespace Xylemical\Expressions;

use PHPUnit\Framework\TestCase;

class OperatorTest extends TestCase
{
    public function testInvalidOperands()
    {
        $operator = new Operator('', 0, 0, 3, 'is_bool');

        $this->expectException('Xylemical\\Expressions\\ExpressionException');

        $operator->evaluate([], new Context, new Token(''));
    }

}
