<?php

/**
 * @file
 */

namespace Xylemical\Expressions;

/**
 * Class ExpressionFactory
 *
 * @package Xylemical\Expressions
 */
class ExpressionFactory
{

    /**
     * @var \Xylemical\Expressions\Operator[]
     */
    protected $operators = [];

    /**
     * @var \Xylemical\Expressions\MathInterface
     */
    protected $math;

    /**
     * ExpressionFactory constructor.
     *
     * @param \Xylemical\Expressions\MathInterface $math
     */
    public function __construct(MathInterface $math) {
        $this->math = $math;

        // Provide the base numerical value.
        $this->addOperator(new Value('(?<!\d)-?\d+(?:\.\d+)?', function($values, Context $context, Token $token) {
                // Remove extraneous zeros from the beginning of the value.
                $value = ltrim($token->getValue(), '0');
                return !$value ? '0' : $value;
            }));

        // Provide the default mathematical operators.
        $this->addOperator(new Operator('\+', 5, Operator::LEFT_ASSOCIATIVE, 2,
            function($values) {
                return $this->math->add($values[0], $values[1]);
            }))
          ->addOperator(new Operator('\-', 5, Operator::LEFT_ASSOCIATIVE, 2,
            function($values) {
                return $this->math->subtract($values[0], $values[1]);
            }))
          ->addOperator(new Operator('\*', 6, Operator::LEFT_ASSOCIATIVE, 2,
            function($values) {
                return $this->math->multiply($values[0], $values[1]);
            }))
          ->addOperator(new Operator('\/', 6, Operator::LEFT_ASSOCIATIVE, 2,
            function($values) {
                return $this->math->divide($values[0], $values[1]);
            }))
          ->addOperator(new Operator('\%', 6, Operator::LEFT_ASSOCIATIVE, 2,
            function($values) {
                return $this->math->modulus($values[0], $values[1]);
            }));

        // Provide the default comparison operators.
        $this
          ->addOperator(new Operator('==', 4, Operator::LEFT_ASSOCIATIVE, 2,
            function($values) {
                return (int)($this->math->compare($values[0], $values[1]) === 0);
            }))
          ->addOperator(new Operator('!=', 4, Operator::LEFT_ASSOCIATIVE, 2,
            function($values) {
                return (int)($this->math->compare($values[0], $values[1]) !== 0);
            }))
          ->addOperator(new Operator('<', 4, Operator::LEFT_ASSOCIATIVE, 2,
            function($values) {
                return (int)($this->math->compare($values[0], $values[1]) < 0);
            }))
          ->addOperator(new Operator('<=', 4, Operator::LEFT_ASSOCIATIVE, 2,
            function($values) {
                return (int)($this->math->compare($values[0], $values[1]) <= 0);
            }))
          ->addOperator(new Operator('>', 4, Operator::LEFT_ASSOCIATIVE, 2,
            function($values) {
                return (int)($this->math->compare($values[0], $values[1]) > 0);
            }))
          ->addOperator(new Operator('>=', 4, Operator::LEFT_ASSOCIATIVE, 2,
            function($values) {
                return (int)($this->math->compare($values[0], $values[1]) >= 0);
            }));

        // Provide basic logic operators.
        $this
          ->addOperator(new Operator('AND', 3, Operator::LEFT_ASSOCIATIVE, 2,
            function($values) {
                return (int)($this->math->native($values[0]) && $this->math->native($values[1]));
            }))
          ->addOperator(new Operator('OR', 2, Operator::LEFT_ASSOCIATIVE, 2,
            function($values) {
                return (int)($this->math->native($values[0]) || $this->math->native($values[1]));
            }))
          ->addOperator(new Procedure('NOT', 1, function($values) {
                return (int)(!$this->math->native($values[0]));
            }));

        // Provide basic min/max operators.
        $this
          ->addOperator(new Procedure('MIN', 2, function($values) {
                if ($this->math->compare($values[0], $values[1]) <= 0) {
                    return $values[0];
                }
                return $values[1];
            }))
          ->addOperator(new Procedure('MAX', 2, function($values) {
                if ($this->math->compare($values[0], $values[1]) >= 0) {
                    return $values[0];
                }
                return $values[1];
            }));
    }

    /**
     * Add an operator processor.
     *
     * @param \Xylemical\Expressions\Operator $op
     *
     * @return static
     */
    public function addOperator(Operator $op) {
        $this->operators[$op->getRegex()] = $op;
        return $this;
    }

    /**
     * Get the list of operators ordered by priority and association.
     *
     * @return array
     */
    public function getOperators()
    {
        // Order the by priority then precedent.
        $list = (array)$this->operators;
        usort($list, function(Operator $a, Operator $b) {
            // Sort by priority.
            if ($a->getPriority() === $b->getPriority()) {
                // Then by associativity.
                if ($a->getAssociativity() == $b->getAssociativity()) {
                    // Then by regex length (the longer the better).
                    if (strlen($a->getRegex()) === strlen($b->getRegex())) {
                        return 0;
                    }
                    return strlen($a->getRegex()) > strlen($b->getRegex()) ? -1 : 1;
                }
                return $a->getAssociativity() > $b->getAssociativity() ? -1 : 1;
            }
            return $a->getPriority() > $b->getPriority() ? -1 : 1;
        });

        return $list;
    }
}