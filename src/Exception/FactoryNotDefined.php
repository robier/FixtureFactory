<?php

declare(strict_types=1);

namespace Robier\FixtureFactory\Exception;

use Exception;

final class FactoryNotDefined extends Exception
{
    public function __construct(string $class)
    {
        $message = sprintf('Factory not defined for class %s', $class);

        parent::__construct($message);
    }
}
