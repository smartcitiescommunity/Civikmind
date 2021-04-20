<?php

/**
 * @file
 */

namespace Xylemical\Expressions;

/**
 * Class Context
 *
 * @package Xylemical\Expressions
 */
class Context
{

    /**
     * The internal variables used by tasks.
     *
     * @var array
     */
    protected $variables = [];

    /**
     * Context constructor.
     *
     * @param array $variables
     */
    public function __construct(array $variables = [])
    {
        $this->setVariables($variables);
    }

    /**
     * Get a variable by name, with default support.
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getVariable($name, $default = null)
    {
        if (!array_key_exists($name, $this->variables)) {
            return $default;
        }
        return $this->variables[$name];
    }

    /**
     * Get the full list of variables for the context.
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Set a variable value by name.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return static
     */
    public function setVariable($name, $value)
    {
        if (is_null($value)) {
            unset($this->variables[$name]);
        }
        else {
            $this->variables[$name] = $value;
        }
        return $this;
    }

    /**
     * Indicate the $name variable has a value.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasVariable($name) {
        return array_key_exists($name, $this->variables);
    }

    /**
     * Set a bunch of variables.
     *
     * @param array $variables
     *
     * @return $this
     */
    public function setVariables(array $variables)
    {
        foreach ($variables as $key => $value) {
            $this->setVariable($key, $value);
        }
        return $this;
    }
}
