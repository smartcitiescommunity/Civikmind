<?php

/**
 * @file
 */

namespace Xylemical\Expressions;

/**
 * Class Lexer
 *
 * @package Xylemical\Expressions
 */
class Lexer
{

    /**
     * @var \Xylemical\Expressions\ExpressionFactory
     */
    protected $factory;

    /**
     * Lexer constructor.
     *
     * @param \Xylemical\Expressions\ExpressionFactory $factory
     */
    public function __construct(ExpressionFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Converts a string into tokens.
     *
     * @param $string
     *
     * @return \Xylemical\Expressions\Token[]
     *
     * @throws \Xylemical\Expressions\LexerException
     */
    public function tokenize($string)
    {
        // Get the list of sorted operators.
        $operators = $this->factory->getOperators();

        // Get the operator regular expression.
        $regex = $this->getRegex($operators);

        // Check that we have matched all the tokens in the string.
        if (!preg_match_all($regex, $string, $matches, PREG_SET_ORDER)) {
            throw new LexerException('Unable to tokenize string.');
        }

        // Cycle through all available tokens.
        $tokens = [];
        foreach ($matches as $match) {
            $item = $match[0];

            // Process the parentheses as special cases.
            if (in_array($item, ['(', ')', ','])) {
                $tokens[] = new Token($item);
                continue;
            }

            // Locate the first operator that matches the token.
            /** @var \Xylemical\Expressions\Operator $operator */
            foreach ($operators as $operator) {
                if (preg_match('#^' . $operator->getRegex() . '$#i', $item)) {
                    $tokens[] = new Token($item, $operator);
                    break;
                }
            }
        }

        return $tokens;
    }

    /**
     * Get the regex used to locate tokens.
     *
     * @return string
     */
    protected function getRegex($operators)
    {
        $regexes = [];

        /** @var \Xylemical\Expressions\Operator $operator */
        foreach ($operators as $operator)
        {
            $regexes[] = $operator->getRegex();
        }

        // Add parentheses regexes.
        $regexes[] = '\(';
        $regexes[] = '\)';
        $regexes[] = ',';

        // Generate the full regex.
        return '#(?:' . implode('|', $regexes) . ')#i';
    }
}
