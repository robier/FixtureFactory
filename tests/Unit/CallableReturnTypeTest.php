<?php

declare(strict_types=1);

namespace Robier\ForgeObject\Test\Unit;

use Generator;
use Robier\ForgeObject\CallableReturnType;
use PHPUnit\Framework\TestCase;
use Robier\ForgeObject\Exception\NoReturnTypeOnCallable;
use stdClass;

final class CallableReturnTypeTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     */
    public function testConstructingValidObject(string $type, bool $isNullable): void
    {
        $callableReturnType = new CallableReturnType($type, $isNullable);

        $this->assertTrue($callableReturnType->isReturnType($type));
        $this->assertSame($type, $callableReturnType->getReturnType());

        if ('void' === $type) {
            $this->assertTrue($callableReturnType->isReturnTypeVoid());
        } else {
            $this->assertFalse($callableReturnType->isReturnTypeVoid());
        }

        if ($isNullable) {
            $this->assertTrue($callableReturnType->allowsNull());
        } else {
            $this->assertFalse($callableReturnType->allowsNull());
        }
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testCreatingObjectFromACallable(string $type, bool $isNullable, callable $function): void
    {
        $callableReturnType = CallableReturnType::fromCallable($function);

        $this->assertInstanceOf(CallableReturnType::class, $callableReturnType);
        $this->assertSame($type, $callableReturnType->getReturnType());
        $this->assertSame($isNullable, $callableReturnType->allowsNull());
    }

    /**
     * @dataProvider badCallableDataProvider
     */
    public function testFailWhenInvalidCallableProvidedOnCreationObjectFromCallable(callable $function): void
    {
        $this->expectException(NoReturnTypeOnCallable::class);

        CallableReturnType::fromCallable($function);
    }

    public function validDataProvider(): Generator
    {
        yield 'void return type without nullable' =>
        [
            'void',
            false,
            static function (): void {
            },
        ];

        yield 'stdClass return type without nullable' =>
        [
            stdClass::class,
            false,
            static function (): stdClass {
                return new stdClass();
            },
        ];

        yield 'stdClass return type with nullable' =>
        [
            stdClass::class,
            true,
            static function (): ?stdClass {
                return null;
            },
        ];

        yield 'function with string return type' =>
        [
            'string',
            false,
            static function (): string {
                return '';
            },
        ];

        yield 'function with integer return type' =>
        [
            'int',
            false,
            static function (): int {
                return 0;
            },
        ];
    }

    public function badCallableDataProvider(): Generator
    {
        yield 'function without any return types' => [static function () {
        }];
    }
}
