<?php

declare(strict_types=1);

namespace Robier\ForgeObject\Exception;

use Exception;

final class StateNotDefined extends Exception
{
    public function __construct(string $class, string ...$states)
    {
        $message = sprintf('States [%s] not defined for class %s', implode(' ', $states), $class);

        parent::__construct($message);
    }
}
