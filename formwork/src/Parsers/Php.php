<?php

namespace Formwork\Parsers;

use Formwork\Data\Contracts\ArraySerializable;
use Formwork\Utils\Arr;
use Formwork\Utils\FileSystem;
use Formwork\Utils\Str;
use LogicException;
use UnexpectedValueException;
use UnitEnum;

final class Php extends AbstractEncoder
{
    /**
     * Number of spaces used to indent arrays
     */
    private const int INDENT_SPACES = 4;

    /**
     * Class names of objects which cannot be encoded
     *
     * @var array<class-string>
     */
    private const array UNENCODABLE_CLASSES = [\Closure::class, \Reflector::class, \ReflectionGenerator::class, \ReflectionType::class, \IteratorIterator::class, \RecursiveIteratorIterator::class];

    /**
     * @param array<string, mixed> $options
     */
    public static function parse(string $data, array $options = []): never
    {
        throw new LogicException('Parsing a string of Php code is not allowed');
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function parseFile(string $file, array $options = []): mixed
    {
        return include $file;
    }

    /**
     * @param array<mixed>         $data
     * @param array<string, mixed> $options
     */
    public static function encode($data, array $options = []): string
    {
        return self::encodeData($data);
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function encodeToFile(mixed $data, string $file, array $options = []): bool
    {
        if (function_exists('opcache_invalidate') && ($options['invalidateOPcache'] ?? true)) {
            // Invalidate OPcache when a file is encoded again
            opcache_invalidate($file, true);
        }
        return FileSystem::write($file, sprintf("<?php\n\nreturn %s;\n", self::encodeData($data)));
    }

    /**
     * Encodes the given data like var_export() would do, but uses the short array syntax, avoids unneeded integer
     * array keys, outputs lowercase null and serializes objects which don't implement the __set_state() method
     */
    private static function encodeData(mixed $data, int $indent = 0): string
    {
        switch (($type = gettype($data))) {
            case 'array':
                if ($data === []) {
                    return '[]';
                }

                $parts = [];

                foreach ($data as $key => $value) {
                    $parts[] = str_repeat(' ', $indent + self::INDENT_SPACES)
                        . (Arr::isAssociative($data) ? self::encodeData($key) . ' => ' : '')
                        . self::encodeData($value, $indent + self::INDENT_SPACES);
                }

                return sprintf("[\n%s\n%s]", implode(",\n", $parts), str_repeat(' ', $indent));

            case 'boolean':
            case 'double':
            case 'integer':
            case 'string':
                return var_export($data, true);

            case 'NULL':
                return 'null';

            case 'object':
                $class = $data::class;

                // stdClass objects are encoded as object casts
                if ($class === \stdClass::class) {
                    return sprintf('(object) %s', self::encodeData((array) $data, $indent));
                }

                foreach (self::UNENCODABLE_CLASSES as $c) {
                    if ($data instanceof $c) {
                        throw new UnexpectedValueException(sprintf('Objects of class "%s" cannot be encoded', $class));
                    }
                }

                if ($data instanceof UnitEnum) {
                    return sprintf('\%s::%s', $class, $data->name);
                }

                if ($data instanceof ArraySerializable) {
                    return sprintf('\%s::fromArray(%s)', $class, self::encodeData($data->toArray(), $indent));
                }

                // Check if the class has a callable __set_state() magic method
                // @phpstan-ignore function.alreadyNarrowedType
                if (method_exists($data, '__set_state') && is_callable([$data, '__set_state'])) {
                    $properties = [];
                    foreach ((array) $data as $property => $value) {
                        // Private and protected properties begin with the class name or an asterisk enclosed
                        // between two NUL bytes, so we need to skip that sequence
                        $properties[Str::afterLast($property, "\0")] = $value;
                    }
                    return sprintf('\%s::__set_state(%s)', $class, self::encodeData($properties, $indent));
                }

                // In the end we try to serialize the object
                return sprintf('unserialize(%s)', var_export(serialize($data), true));

            default:
                // Resources and unknown types cannot be encoded
                throw new UnexpectedValueException(sprintf('Data of type "%s" cannot be encoded', $type));
        }
    }
}
