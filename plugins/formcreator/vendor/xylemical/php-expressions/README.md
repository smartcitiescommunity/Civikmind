# PHP Expressions.

Simple mathematical expression parser and calculator.

## Install
The recommended way to install this library is [through composer](http://getcomposer.org).

```sh
composer require xylemical/php-expressions
```

## Usage

Most basic use of the parsing and evaluation classes:
```php
<?php

use Xylemical\Expressions\Math\BcMath;
use Xylemical\Expressions\Context;
use Xylemical\Expressions\ExpressionFactory;
use Xylemical\Expressions\Evaluator;
use Xylemical\Expressions\Lexer;
use Xylemical\Expressions\Parser;

$math = new BcMath();
$factory = new ExpressionFactory($math);
$lexer = new Lexer($factory);
$parser = new Parser($lexer);
$evaluator = new Evaluator();
$context = new Context();

$tokens = $parser->parse('1 + 1');
$result = $evaluator->evaluate($tokens, $context);
```

### Variables.

Extending the expression factory to incorporate variable substitution involves adding a Value operator that will parse the variable, and use the values from the Context

```php
use Xylemical\Expressions\Token;
use Xylemical\Expressions\Value;

$factory->addOperator(new Value('\$[a-zA-Z_][a-zA-Z0-9_]*', function(array $operands, Context $context, Token $token) {
    return $context->getVariable(substr($token->getValue(), 1));
}));

$context->setVariable('example', 10);

$tokens = $parser->parse('2 * $example');
$result = $evaluator->evaluate($tokens, $context);
```

### Functions

Extending the expression factory to incorporate more functions involves adding a Procedure operator that will parse the function name, and perform the expression substitution.

```php
use Xylemical\Expressions\Token;
use Xylemical\Expressions\Procedure;

$factory->addOperator(new Procedure('ABS', 1, function(array $operands, Context $context, Token $token) {
    $value = $token->getValue();
    if (substr($value, 0, 1) === '-') {
        return substr($value, 1);
    }
    return $value;
}));

$tokens = $parser->parse('abs(-1.2)');
$result = $evaluator->evaluate($tokens, $context);
```

## License

MIT, see LICENSE.
