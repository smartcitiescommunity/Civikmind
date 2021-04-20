<?php

/**
 * @file
 */

namespace Xylemical\Expressions;

/**
 * Class Operator
 *
 * @package Xylemical\Expressions
 */
class Operator
{
    const LEFT_ASSOCIATIVE = -1;
    const NONE_ASSOCIATIVE = 0;
    const RIGHT_ASSOCIATIVE = 1;

    /**
     * The regular expression used to match the token.
     *
     * @var string
     */
    protected $regex;

    /**
     * Used to perform the calculation.
     *
     * @var callable
     */
    protected $evaluator;

    /**
     * The number of values needed to be passed to the evaluator.
     *
     * @var int
     */
    protected $operands = 0;

    /**
     * The associativity of the token.
     *
     * @var int
     */
    protected $associativity = self::NONE_ASSOCIATIVE;

    /**
     * @var int
     */
    protected $priority;

    /**
     * Operator constructor.
     *
     * @param $regex
     * @param int $priority
     * @param int $associativity
     * @param int $operands
     * @param callable $evaluator
     */
    public function __construct($regex, $priority, $associativity, $operands, callable $evaluator) {
        $this->regex = $regex;
        $this->evaluator = $evaluator;
        $this->priority = $priority;
        $this->associativity = $associativity;
        $this->operands = $operands;
    }

    /**
     * Get the regular expression used to discover the operator.
     *
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Get the associativity.
     *
     * @return int
     */
    public function getAssociativity()
    {
        return $this->associativity;
    }

    /**
     * Get the priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Get the number of operands this operator requires.
     *
     * @return int
     */
    public function getOperands()
    {
        return $this->operands;
    }

    /**
     * Evaluates using the values passed through.
     *
     * @param string[] $values
     * @param Context $context
     *
     * @param Token $token
     *
     * @return string
     *
     * @throws \Xylemical\Expressions\ExpressionException
     */
    public function evaluate(array $values, Context $context, Token $token)
    {
        if (count($values) !== $this->getOperands()) {
            throw new ExpressionException('Invalid number of operands for operator.',
              $this, $values);
        }
        $evaluator = $this->evaluator;
        return (string)$evaluator($values, $context, $token);
    }
}
