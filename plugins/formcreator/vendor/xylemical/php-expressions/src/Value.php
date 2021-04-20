<?php

/**
 * @file
 */

namespace Xylemical\Expressions;

/**
 * Class Value
 *
 * @package Xylemical\Expressions
 */
class Value extends Operator
{

    /**
     * {@inheritdoc}
     */
    public function __construct($regex, callable $evaluator, $priority = 10, $associativity = Operator::NONE_ASSOCIATIVE) {
        parent::__construct($regex, $priority, $associativity, 0, $evaluator);
    }
}
