<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Concerns;

use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;

/**
 * Trait for data-driven serialization and deserialization of DTOs.
 *
 * Provides reusable implementations of fromData(), toData(), and toObjectArray()
 * based on property type definitions from getPropertyTypes().
 */
trait SerializesData
{
    /**
     * Creates an instance from JSON-compatible data.
     *
     * @param mixed $data Typically an array with DTO data
     * @return static
     */
    public static function fromData(mixed $data): static
    {
        /** @var DataTransferObject $class */
        $class = static::class;
        $propertyTypes = $class::getPropertyTypes();

        // Build constructor arguments dynamically using property type definitions
        $args = [];

        // Object properties - todo label the dtos as specificly dtos.
        foreach ($propertyTypes['object'] ?? [] as $prop) {
            if (isset($data[$prop]) && (is_array($data[$prop]) || is_scalar($data[$prop]))) {
                /** @var class-string<DataTransferObject> $dtoClass */
                $dtoClass = null;

                $type = static::getConstructorArg($prop)?->getType();

                if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                    $dtoClass = $type->getName();
                } elseif ($type instanceof \ReflectionUnionType) {
                    // Handle union types like MyDtoClass|string|null
                    foreach ($type->getTypes() as $unionType) {
                        if ($unionType instanceof \ReflectionNamedType && !$unionType->isBuiltin()) {
                            $typeName = $unionType->getName();
                            if (class_exists($typeName)) {
                                $dtoClass = $typeName;
                                break;
                            }
                        }
                    }
                }

                // Could be a DTO or VO - anything with a fromData() method.
                if ($dtoClass === null /*|| !is_subclass_of($dtoClass, DataTransferObject::class)*/) {
                    throw new InvalidArgumentException("Cannot determine DTO class for property {$prop}");
                }

                $args[$prop] = $dtoClass::fromData($data[$prop]);
            } elseif (isset($data[$prop]) && is_object($data[$prop])) {
                $args[$prop] = $data[$prop];
            } else {
                $args[$prop] = null;
            }
        }

        // Arrays as they are (will need comversion of items though)
        foreach ($propertyTypes['array'] ?? [] as $prop) {
            $args[$prop] = $data[$prop] ?? null;
        }

        // Money properties - parse decimal amount with currency into Money\Money object.
        // Expects data in format: ['amount' => '10.00', 'currencyCode' => 'USD']
        foreach ($propertyTypes['money'] ?? [] as $prop) {
            if (isset($data[$prop]) && is_array($data[$prop])) {
                $moneyData = $data[$prop];
                if (isset($moneyData['amount'], $moneyData['currencyCode'])) {
                    $currencies = new ISOCurrencies();
                    $parser = new DecimalMoneyParser($currencies);
                    $args[$prop] = $parser->parse(
                        (string) $moneyData['amount'],
                        $moneyData['currencyCode'] instanceof Currency
                            ? $moneyData['currencyCode']
                            : new Currency($moneyData['currencyCode'])
                    );
                } else {
                    $args[$prop] = null;
                }
            } elseif (isset($data[$prop]) && $data[$prop] instanceof Money) {
                $args[$prop] = $data[$prop];
            } else {
                $args[$prop] = null;
            }
        }

        // Enum properties - convert backing values to enums.
        foreach ($propertyTypes['enum'] ?? [] as $prop) {
            $args[$prop] = static::normalizeEnum($data[$prop] ?? null, $prop);
        }

        // String properties - cast to string if present
        foreach ($propertyTypes['string'] ?? [] as $prop) {
            $args[$prop] = isset($data[$prop]) ? (string) $data[$prop] : null;
        }

        // Boolean properties - cast to bool if present
        foreach ($propertyTypes['boolean'] ?? [] as $prop) {
            $args[$prop] = isset($data[$prop]) ? (bool) $data[$prop] : null;
        }

        // Integer properties - cast to int if present
        foreach ($propertyTypes['int'] ?? [] as $prop) {
            $args[$prop] = isset($data[$prop]) ? (int) $data[$prop] : null;
        }

        // Unpack arguments array as named parameters
        return new static(...$args);
    }

    // Reflection helper to get constructor by name.
    protected static function getConstructorArg(string $property): mixed
    {
        $constructor = (new \ReflectionClass(static::class))->getConstructor();

        foreach ($constructor->getParameters() as $param) {
            if ($param->getName() === $property) {
                return $param;
            }
        }

        return null;
    }

    /**
     * Returns a shallow array of all non-null properties.
     *
     * Unlike toArray(), this does not recurse into nested objects - it returns
     * the actual object instances, arrays, enums, etc. as-is.
     *
     * @return array<string, mixed>
     */
    public function toObjectArray(): array
    {
        /** @var DataTransferObject $class */
        $class = static::class;
        $propertyTypes = $class::getPropertyTypes();

        $data = [];

        // Build complete property list from type definitions
        $allProperties = array_merge(
            $propertyTypes['money'] ?? [],
            $propertyTypes['object'] ?? [],
            $propertyTypes['array'] ?? [],
            $propertyTypes['enum'] ?? [],
            $propertyTypes['string'] ?? [],
            $propertyTypes['boolean'] ?? [],
            $propertyTypes['int'] ?? []
        );

        // Collect all non-null properties
        foreach ($allProperties as $property) {
            if ($this->$property !== null) {
                $data[$property] = $this->$property;
            }
        }

        return $data;
    }

    /**
     * Converts the DTO to JSON-compatible data.
     *
     * Recursively converts all nested objects to their data representations.
     * Only includes non-null values for cleaner JSON output.
     *
     * @return mixed Typically an array for DTOs
     */
    public function toData(): mixed
    {
        /** @var DataTransferObject $class */
        $class = static::class;
        $propertyTypes = $class::getPropertyTypes();

        $data = [];

        // Convert Money\Money objects to data (amount + currencyCode)
        foreach ($propertyTypes['money'] ?? [] as $prop) {
            if ($this->$prop !== null) {
                $currencies = new ISOCurrencies();
                $formatter = new DecimalMoneyFormatter($currencies);
                $data[$prop] = [
                    'amount' => $formatter->format($this->$prop),
                    'currencyCode' => $this->$prop->getCurrency()->getCode(),
                ];
            }
        }

        // Convert all objects (DTOs, value objects, etc.) to data
        foreach ($propertyTypes['object'] ?? [] as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = $this->$prop->toData();
            }
        }

        // Handle array properties - can contain objects with toData() or primitives
        foreach ($propertyTypes['array'] ?? [] as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = array_map(
                    fn($item) => is_object($item) && method_exists($item, 'toData')
                        ? $item->toData()
                        : $item,
                    $this->$prop
                );
            }
        }

        // Convert enum properties to their string values
        foreach ($propertyTypes['enum'] ?? [] as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = $this->$prop->value;
            }
        }

        // Add scalar properties (strings, booleans, and integers)
        $scalarProperties = array_merge(
            $propertyTypes['string'] ?? [],
            $propertyTypes['boolean'] ?? [],
            $propertyTypes['int'] ?? []
        );

        foreach ($scalarProperties as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = $this->$prop;
            }
        }

        return $data;
    }

    /**
     * Normalizes an enum value from either enum object or string.
     *
     * Uses reflection to determine the enum class from the constructor parameter type.
     *
     * @template T of \BackedEnum
     * @param T|string|null $value
     * @param string $fieldName Constructor parameter name to get enum type from
     * @return T|null
     * @throws InvalidArgumentException When string value is invalid
     */
    protected static function normalizeEnum(mixed $value, string $fieldName): mixed
    {
        if ($value === null) {
            return null;
        }

        // Use reflection to get the enum class from the constructor parameter
        $reflection = new \ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            throw new InvalidArgumentException("Class " . static::class . " has no constructor");
        }

        $enumClass = null;
        foreach ($constructor->getParameters() as $param) {
            if ($param->getName() === $fieldName) {
                $type = $param->getType();
                if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                    $enumClass = $type->getName();
                } elseif ($type instanceof \ReflectionUnionType) {
                    // Handle union types like EnumType|string|null
                    foreach ($type->getTypes() as $unionType) {
                        if ($unionType instanceof \ReflectionNamedType && !$unionType->isBuiltin()) {
                            $typeName = $unionType->getName();
                            if (enum_exists($typeName)) {
                                $enumClass = $typeName;
                                break;
                            }
                        }
                    }
                }
                break;
            }
        }

        if ($enumClass === null) {
            throw new InvalidArgumentException("Could not determine enum class for field {$fieldName}");
        }

        if ($value instanceof $enumClass) {
            return $value;
        }

        if (is_string($value)) {
            $enum = $enumClass::tryFrom($value);
            if ($enum === null) {
                throw new InvalidArgumentException("Invalid {$fieldName}: {$value}");
            }
            return $enum;
        }

        throw new InvalidArgumentException(
            "Field {$fieldName} must be a {$enumClass} enum or string, " . get_debug_type($value) . " given"
        );
    }

    /**
     * Normalizes an array of enum values from either enum objects or strings.
     *
     * @template T of \BackedEnum
     * @param array<T|string>|null $items
     * @param class-string<T> $enumClass
     * @return array<T>|null
     */
    protected function normalizeEnumArray(?array $items, string $enumClass): ?array
    {
        if ($items === null) {
            return null;
        }

        return array_map(
            fn ($item) => $item instanceof $enumClass ? $item : $enumClass::from($item),
            $items
        );
    }
}
