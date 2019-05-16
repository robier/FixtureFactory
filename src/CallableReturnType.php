<?php

declare(strict_types=1);

namespace Robier\ForgeObject;

use ReflectionFunction;
use ReflectionMethod;
use Robier\ForgeObject\Exception\NoReturnTypeOnCallable;

/**
 * @internal
 */
final class CallableReturnType
{
    /**
     * @var string
     */
    private $returnType;
    /**
     * @var bool
     */
    private $allowsNull;

    public function __construct(string $returnType, bool $allowsNull)
    {
        $this->returnType = $returnType;
        $this->allowsNull = $allowsNull;
    }

    public function getReturnType(): string
    {
        return $this->returnType;
    }

    public function isReturnType(string $type): bool
    {
        return $this->returnType === $type;
    }

    public function isReturnTypeVoid(): bool
    {
        return $this->isReturnType('void');
    }

    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }

    /**
     * Creates CallableReturnType only if callable has an return type, if there is no return type on callable
     * exception will be thrown.
     *
     * @param callable $function
     *
     * @return CallableReturnType
     *
     * @throws NoReturnTypeOnCallable
     */
    public static function fromCallable(callable $function): self
    {
        // ReflectionException should never be thrown as string or array that is provided to function
        // will trigger PHP warning if provided value is not a callable

        if (is_array($function)) {
            $reflection = new ReflectionMethod($function[0], $function[1]);
        } else {
            $reflection = new ReflectionFunction($function);
        }

        $reflectionReturnType = $reflection->getReturnType();

        if (null === $reflectionReturnType) {
            throw new NoReturnTypeOnCallable();
        }

        $returnType = 'void';

        if ($reflectionReturnType instanceof \ReflectionNamedType) {
            $returnType = $reflectionReturnType->getName();
        } elseif ($reflectionReturnType instanceof \ReflectionType) {
            $returnType = (string)$reflectionReturnType;
        }

        return new static($returnType, $reflectionReturnType->allowsNull());
    }
}
