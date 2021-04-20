<?php

/**
 * @file
 */

namespace Xylemical\Expressions;

/**
 * Class ExpressionException
 *
 * @package Xylemical\Expressions
 */
class ExpressionException extends \Exception
{

    /**
     * @var \Xylemical\Expressions\Operator
     */
    protected $operator;

    /**
     * @var array
     */
    protected $values;

    /**
     * {@inheritdoc}
     * @param string $message
     * @param \Xylemical\Expressions\Operator $operator
     * @param array $values
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "", Operator $operator = null, array $values = [], int $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->operator = $operator;
        $this->values = $values;
    }

    /**
     * Get the operator.
     *
     * @return \Xylemical\Expressions\Operator|NULL
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Get the values.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }
}
