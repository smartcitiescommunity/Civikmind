<?php
/**
 * @file
 */

namespace Xylemical\Expressions;

use PHPUnit\Framework\TestCase;

class ExpressionExceptionTest extends TestCase
{

    /**
     * Tests out the CRUD for the exception.
     */
    public function testCrud()
    {
        $values = [1, 2];
        $operator = new Operator('', 1, Operator::RIGHT_ASSOCIATIVE, 0, 'is_bool');

        $exception = new ExpressionException('message', $operator, $values);

        $this->assertEquals($exception->getMessage(), 'message');
        $this->assertEquals($exception->getOperator(), $operator);
        $this->assertEquals($exception->getValues(), $values);
    }

}
