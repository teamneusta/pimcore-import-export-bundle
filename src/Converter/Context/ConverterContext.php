<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Converter\Context;

use Neusta\ConverterBundle\Converter\Context\GenericContext;

/**
 * @final
 */
class ConverterContext extends GenericContext
{
    /**
     * @param array<class-string, object> $context
     */
    private function __construct(
        private array $context = [],
    ) {
    }

    public static function create(object ...$objects): self
    {
        static $newInstance;
        $instance = ($newInstance ??= (new \ReflectionClass(self::class))->newInstanceWithoutConstructor(...))();

        foreach ($objects as $object) {
            $instance->context[$object::class] = $object;
        }

        return $instance;
    }

    public function with(object ...$value): self
    {
        $clone = clone $this;

        foreach ($value as $object) {
            $clone->context[$object::class] = $object;
        }

        return $clone;
    }

    /**
     * @param object|class-string $value
     */
    public function without(object|string $value): self
    {
        $class = \is_string($value) ? $value : $value::class;

        if (!isset($this->context[$class])) {
            return $this;
        }

        $clone = clone $this;
        unset($clone->context[$class]);

        return $clone;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    public function get(string $class): ?object
    {
        // @phpstan-ignore-next-line
        return $this->context[$class] ?? null;
    }
}
