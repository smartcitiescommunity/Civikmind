<?php
/**
 * @file
 */

namespace Xylemical\Expressions;

use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{

    /**
     * Test basic crud for context variables.
     */
    public function testCrud()
    {
        $context = new Context();

        $this->assertEquals(1, $context->getVariable('a', 1));

        $context->setVariable('a', 10);
        $this->assertEquals(10, $context->getVariable('a'));
        $this->assertTrue($context->hasVariable('a'));

        // Check a variable gets deleted.
        $context->setVariable('a', null);
        $this->assertFalse($context->hasVariable('a'));
    }

    /**
     * Test the full get/set of the context.
     */
    public function testFullCrud()
    {
        $test = [
          'a' => 1,
          'b' => 2,
        ];

        $context = new Context((array)$test);

        $this->assertEquals($test, $context->getVariables());
    }
}
