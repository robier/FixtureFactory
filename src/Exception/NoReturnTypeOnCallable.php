<?php

declare(strict_types=1);

namespace Robier\ForgeObject\Exception;

use Exception;

final class NoReturnTypeOnCallable extends Exception
{
    public function __construct()
    {
        parent::__construct('Function is missing return type');
    }
}
