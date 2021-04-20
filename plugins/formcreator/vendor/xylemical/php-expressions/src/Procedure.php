<?php

/**
 * @file
 */

namespace Xylemical\Expressions;

/**
 * Class Procedure
 *
 * @package Xylemical\Expressions
 */
class Procedure extends Operator
{

    /**
     * {@inheritdoc}
     */
    public function __construct($regex, $operands, callable $evaluator, $priority = 0, $associativity = Operator::LEFT_ASSOCIATIVE) {
        parent::__construct($regex, $priority, $associativity, $operands, $evaluator);
    }
}
