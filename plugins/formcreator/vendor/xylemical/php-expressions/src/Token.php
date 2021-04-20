<?php

/**
 * @file
 */

namespace Xylemical\Expressions;

/**
 * Class Token
 *
 * @package Xylemical\Expressions
 */
class Token
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var \Xylemical\Expressions\Operator
     */
    protected $operator;

    /**
     * Token constructor.
     */
    public function __construct($value, Operator $operator = NULL)
    {
        $this->value = $value;
        $this->operator = $operator;
    }

    /**
     * Get the value for the token.
     *
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Get the operator for the token.
     *
     * @return \Xylemical\Expressions\Operator
     */
    public function getOperator() {
        return $this->operator;
    }

    /**
     * Indicate the token is an operator.
     *
     * @return bool
     */
    public function isOperator() {
        return ($this->operator instanceof Operator) &&
               !$this->isValue() &&
               !$this->isFunction();
    }

    /**
     * Indicate the token is a value.
     *
     * @return bool
     */
    public function isValue() {
        return ($this->operator instanceof Value);
    }

    /**
     * Indicate the token is a function.
     */
    public function isFunction() {
        return ($this->operator instanceof Procedure);
    }

    /**
     * Get the priority of the token.
     *
     * @return int
     */
    public function getPrecedence()
    {
        if (!$this->operator) {
            return 0;
        }
        return $this->operator->getPriority();
    }

    /**
     * Get the associativity of the token.
     *
     * @return int
     */
    public function getAssociativity() {
        if (!$this->operator) {
            return Operator::NONE_ASSOCIATIVE;
        }
        return $this->operator->getAssociativity();
    }

    /**
     * Indicate this token has a lower priority than $token.
     *
     * @param \Xylemical\Expressions\Token $token
     *
     * @return bool
     */
    public function hasHigherPriority(Token $token) {
        // Left associativity precedence.
        if ($this->getAssociativity() === Operator::LEFT_ASSOCIATIVE && $this->getPrecedence() <= $token->getPrecedence()) {
            return true;
        }
        // Right associativity precedence.
        if ($this->getAssociativity() === Operator:: RIGHT_ASSOCIATIVE && $this->getPrecedence() < $token->getPrecedence()) {
            return true;
        }
        return false;
    }
}
