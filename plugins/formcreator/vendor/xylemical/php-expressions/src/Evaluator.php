<?php

/**
 * @file
 */

namespace Xylemical\Expressions;

/**
 * Class Evaluator
 *
 * @package Xylemical\Expressions
 */
class Evaluator
{

    /**
     * Evaluates the series of tokens in Reverse Polish Notation Format.
     *
     * @param \Xylemical\Expressions\Token[] $tokens
     * @param \Xylemical\Expressions\Context $context
     *
     * @return string
     *
     * @throws \Xylemical\Expressions\ExpressionException
     *
     * @see https://en.wikipedia.org/wiki/Reverse_Polish_notation
     */
    public function evaluate(array $tokens, Context $context)
    {
        $results = new \SplStack();

        // for each token in the postfix expression:
        foreach ($tokens as $token) {
            // if token is an operator:
            if (is_null($token->getOperator())) {
                throw new ExpressionException('Unexpected token.');
            }

            // Get the operator, as this works relative
            $op = $token->getOperator();

            // Check there are enough operands.
            if ($op->getOperands() > $results->count()) {
                throw new ExpressionException('Improperly constructed expression.', $op);
            }

            // Get the operands from the stack in reverse order.
            $operands = [];
            for ($i = 0; $i < $op->getOperands(); $i++) {
                $operands[] = $results->pop();
            }
            $operands = array_reverse($operands);

            // result ← evaluate token with operand_1 and operand_2
            $result = $op->evaluate($operands, $context, $token);

            // push result back onto the stack
            $results->push($result);
        }

        // Check the expression has been properly constructed.
        if ($results->count() !== 1) {
            throw new ExpressionException('Improperly constructed expression.');
        }

        // Get the final result.
        return $results->pop();
    }
}
