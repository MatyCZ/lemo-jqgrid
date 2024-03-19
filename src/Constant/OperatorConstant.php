<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Constant;

class OperatorConstant
{
    final public const OPERATOR_BEGINS_WITH      = '^';
    final public const OPERATOR_CONTAINS         = '~';
    final public const OPERATOR_EQUAL            = '=';
    final public const OPERATOR_ENDS_WITH        = '$';
    final public const OPERATOR_GREATER          = '>';
    final public const OPERATOR_GREATER_OR_EQUAL = '>=';
    final public const OPERATOR_IN               = '|';
    final public const OPERATOR_LESS             = '<';
    final public const OPERATOR_LESS_OR_EQUAL    = '<=';
    final public const OPERATOR_NOT_BEGINS_WITH  = '!^';
    final public const OPERATOR_NOT_CONTAINS     = '!~';
    final public const OPERATOR_NOT_EQUAL        = '!=';
    final public const OPERATOR_NOT_ENDS_WITH    = '!$';
    final public const OPERATOR_NOT_IN           = '!|';
}
