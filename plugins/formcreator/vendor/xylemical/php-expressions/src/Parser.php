<?php

/**
 * @file
 */

namespace Xylemical\Expressions;

/**
 * Class Parser
 *
 * @package Xylemical\Expressions
 */
class Parser
{

    /**
     * @var \Xylemical\Expressions\Lexer
     */
    protected $lexer;

    /**
     * Parser constructor.
     *
     * @param \Xylemical\Expressions\Lexer $lexer
     */
    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * Get the top of the operators stack.
     *
     * @param \SplStack $operators
     *
     * @return bool|mixed
     */
    protected function getTop(\SplStack $operators)
    {
        if ($operators->isEmpty()) {
            return false;
        }
        return $operators->top();
    }

    /**
     * Parses a string into a series of tokens in Reverse Polish Notation order.
     *
     * @param string $string
     *
     * @return \Xylemical\Expressions\Token[]
     *
     * @throws \Xylemical\Expressions\ParserException
     *
     * @see https://en.wikipedia.org/wiki/Shunting-yard_algorithm
     */
    public function parse($string) {
        $output = new \SplQueue();
        $operator = new \SplStack();

        // Convert the string to tokens for the lexer.
        $tokens = $this->lexer->tokenize($string);

        // while there are tokens to be read:
        while (count($tokens)) {
            // read a token.
            $token = array_shift($tokens);

            // if the token is a number, then push it to the output queue.
            if ($token->isValue()) {
                $output->push($token);
                continue;
            }

            // if the token is a function, then push it onto the operator stack.
            if ($token->isFunction()) {
                $operator->push($token);
                continue;
            }

            // if the token is a function argument separator:
            if ($token->getValue() === ',') {
                while (($op = $this->getTop($operator)) && $op->getValue() !== '(') {
                    $output->push($operator->pop());
                }

                if ($operator->isEmpty()) {
                    throw new ParserException('Mismatched parentheses or misplaced comma.');
                }

                continue;
            }

            // if the token is an operator, then:
            if ($token->isOperator()) {
                // while (
                //  (there is an operator at the top of the operator stack with greater precedence) or
                //  (the operator at the top of the operator stack has equal precedence and the operator is left associative)) and
                //  (the operator at the top of the stack is not a left bracket):
                //     pop operators from the operator stack, onto the output queue.
                while (($op = $this->getTop($operator)) && $op->isOperator() && $token->hasHigherPriority($op)) {
                    $output->push($operator->pop());
                }

                // push the read operator onto the operator stack.
                $operator->push($token);
                continue;
            }

            // if the token is a left bracket (i.e. "("), then:
            if ($token->getValue() === '(') {
                // push it onto the operator stack.
                $operator->push($token);
                continue;
            }

            // if the token is a right bracket (i.e. ")"), then:
            if ($token->getValue() === ')') {
                // while the operator at the top of the operator stack is not a left bracket:
                while (($op = $this->getTop($operator)) && $op->getValue() !== '(') {
                    // pop operators from the operator stack onto the output queue.
                    $output->push($operator->pop());
                }

                // /* if the stack runs out without finding a left bracket, then there are mismatched parentheses. */
                if ($operator->isEmpty()) {
                    throw new ParserException('Mismatched parentheses');
                }

                // pop the left bracket from the stack.
                $operator->pop();

                // If the token at the top of the stack is a function token, pop it onto the output queue.
                if (($op = $this->getTop($operator)) && $op->isFunction()) {
                    $output->push($operator->pop());
                }
            }
        }

        // if there are no more tokens to read:
        // while there are still operator tokens on the stack:
        while (!$operator->isEmpty()) {
            // /* if the operator token on the top of the stack is a bracket, then there are mismatched parentheses. */
            if (is_null($operator->top()->getOperator())) {
                throw new ParserException('Mismatched parentheses or misplaced comma.');
            }

            // pop the operator onto the output queue.
            $output->push($operator->pop());
        }

        // exit.
        return iterator_to_array($output);
    }
}
