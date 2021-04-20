<?php

/**
 * @file
 */

namespace Xylemical\Expressions\Math;

use Xylemical\Expressions\MathInterface;

/**
 * Class BcMath
 *
 * @package Xylemical\Expressions\Math
 */
class BcMath implements MathInterface
{
    /**
     * {@inheritdoc}
     */
    public function add($a, $b, $decimals = 0) {
        return bcadd($a, $b, $decimals);
    }

    /**
     * {@inheritdoc}
     */
    public function subtract($a, $b, $decimals = 0) {
        return bcsub($a, $b, $decimals);
    }

    /**
     * {@inheritdoc}
     */
    public function multiply($a, $b, $decimals = 0) {
        return bcmul($a, $b, $decimals);
    }

    /**
     * {@inheritdoc}
     */
    public function divide($a, $b, $decimals = 0) {
        return bcdiv($a, $b, $decimals);
    }

    /**
     * {@inheritdoc}
     */
    public function modulus($a, $b) {
        return bcmod($a, $b);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($a, $b, $decimals = 0) {
        return bccomp($a, $b, $decimals);
    }

    /**
     * {@inheritdoc}
     */
    public function native($value) {
        if (strpos($value, '.') !== FALSE) {
            return floatval($value);
        }
        return intval($value);
    }
}
