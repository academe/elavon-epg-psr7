<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Concerns;

use Academe\Elavon\Epg\Psr7\Attributes\ArrayOf;
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
     * Uses reflection to determine parameter types and convert data accordingly.
     * Supports: scalars (string, int, bool), enums, Money, DTOs/VOs with fromData(),
     * and arrays with #[ArrayOf] attributes.
     *
     * @param mixed $data Typically an array with DTO data
     * @return static
     */
    public static function fromData(mixed $data): static
    {
        $constructor = (new \ReflectionClass(static::class))->getConstructor();

        if ($constructor === null) {
            return new static();
        }

        $args = [];

        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();
            $rawValue = $data[$name] ?? null;

            // Get the parameter type first
            $type = $param->getType();
            $typeName = null;

            if ($type instanceof \ReflectionNamedType) {
                $typeName = $type->getName();
            } elseif ($type instanceof \ReflectionUnionType) {
                // Find the first non-null, non-builtin type
                foreach ($type->getTypes() as $unionType) {
                    if ($unionType instanceof \ReflectionNamedType && !$unionType->isBuiltin() && $unionType->getName() !== 'null') {
                        $typeName = $unionType->getName();
                        break;
                    }
                }
                // If no class type found, check for builtin types
                if ($typeName === null) {
                    foreach ($type->getTypes() as $unionType) {
                        if ($unionType instanceof \ReflectionNamedType && $unionType->isBuiltin() && $unionType->getName() !== 'null') {
                            $typeName = $unionType->getName();
                            break;
                        }
                    }
                }
            }

            // Handle null values
            if ($rawValue === null) {
                $args[$name] = null;
                continue;
            }

            // For array types, check for ArrayOf attribute to convert elements
            if ($typeName === 'array') {
                $arrayOfAttr = static::getArrayOfAttributeFromParam($param);
                $args[$name] = $arrayOfAttr !== null
                    ? $arrayOfAttr->convertArray($rawValue)
                    : $rawValue;
                continue;
            }

            // Already the correct type (object passed directly)
            if (is_object($rawValue) && $typeName !== null && $rawValue instanceof $typeName) {
                $args[$name] = $rawValue;
                continue;
            }

            // Convert based on type
            $args[$name] = match (true) {
                // Scalar types
                $typeName === 'string' => (string) $rawValue,
                $typeName === 'int' => (int) $rawValue,
                $typeName === 'bool' => (bool) $rawValue,
                $typeName === 'float' => (float) $rawValue,

                // Money type
                $typeName === Money::class => static::parseMoneyData($rawValue),

                // Enum type
                $typeName !== null && enum_exists($typeName) => static::parseEnumValue($rawValue, $typeName),

                // Class with fromData() method (DTO/VO)
                $typeName !== null && class_exists($typeName) && method_exists($typeName, 'fromData')
                    => $typeName::fromData($rawValue),

                // Default: pass through as-is
                default => $rawValue,
            };
        }

        return new static(...$args);
    }

    /**
     * Parse money data from array format.
     */
    private static function parseMoneyData(mixed $data): ?Money
    {
        if ($data instanceof Money) {
            return $data;
        }

        if (!is_array($data) || !isset($data['amount'], $data['currencyCode'])) {
            return null;
        }

        $currencies = new ISOCurrencies();
        $parser = new DecimalMoneyParser($currencies);

        return $parser->parse(
            (string) $data['amount'],
            $data['currencyCode'] instanceof Currency
                ? $data['currencyCode']
                : new Currency($data['currencyCode'])
        );
    }

    /**
     * Parse enum value from string.
     *
     * @template T of \BackedEnum
     * @param mixed $value
     * @param class-string<T> $enumClass
     * @return T|null
     */
    private static function parseEnumValue(mixed $value, string $enumClass): ?\BackedEnum
    {
        if ($value instanceof $enumClass) {
            return $value;
        }

        if (is_string($value) || is_int($value)) {
            $enum = $enumClass::tryFrom($value);
            if ($enum === null) {
                throw new InvalidArgumentException("Invalid enum value for {$enumClass}: {$value}");
            }
            return $enum;
        }

        return null;
    }

    // Reflection helper to get constructor parameter by name.
    protected static function getConstructorArg(string $property): ?\ReflectionParameter
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
     * Gets the ArrayOf attribute from a constructor parameter if present.
     */
    protected static function getArrayOfAttribute(string $property): ?ArrayOf
    {
        $param = static::getConstructorArg($property);

        if ($param === null) {
            return null;
        }

        return static::getArrayOfAttributeFromParam($param);
    }

    /**
     * Gets the ArrayOf attribute from a reflection parameter.
     */
    protected static function getArrayOfAttributeFromParam(\ReflectionParameter $param): ?ArrayOf
    {
        $attributes = $param->getAttributes(ArrayOf::class);

        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * Gets all ArrayOf attributes from constructor parameters.
     *
     * @return array<string, ArrayOf> Map of property name to ArrayOf attribute
     */
    protected static function getArrayOfAttributes(): array
    {
        $constructor = (new \ReflectionClass(static::class))->getConstructor();

        if ($constructor === null) {
            return [];
        }

        $result = [];
        foreach ($constructor->getParameters() as $param) {
            $attributes = $param->getAttributes(ArrayOf::class);
            if (!empty($attributes)) {
                $result[$param->getName()] = $attributes[0]->newInstance();
            }
        }

        return $result;
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
     * Uses reflection to inspect properties and convert them appropriately.
     * Recursively converts all nested objects to their data representations.
     * Only includes non-null values for cleaner JSON output.
     *
     * @return mixed Typically an array for DTOs
     */
    public function toData(): mixed
    {
        $reflection = new \ReflectionClass(static::class);
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();
            $value = $this->$name;

            // Skip null values
            if ($value === null) {
                continue;
            }

            // Convert value based on its type
            $data[$name] = $this->convertValueToData($value);
        }

        return $data;
    }

    /**
     * Converts a value to its JSON-compatible representation.
     */
    private function convertValueToData(mixed $value): mixed
    {
        // Money objects - convert to amount + currencyCode
        if ($value instanceof Money) {
            $currencies = new ISOCurrencies();
            $formatter = new DecimalMoneyFormatter($currencies);
            return [
                'amount' => $formatter->format($value),
                'currencyCode' => $value->getCurrency()->getCode(),
            ];
        }

        // Backed enums - convert to their value
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        // Objects with toData() method - recurse
        if (is_object($value) && method_exists($value, 'toData')) {
            return $value->toData();
        }

        // Arrays - convert each element
        if (is_array($value)) {
            return array_map(
                fn($item) => $this->convertValueToData($item),
                $value
            );
        }

        // Scalars and other values - pass through as-is
        return $value;
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
     * Normalizes an array using the ArrayOf attribute on the specified property.
     *
     * Call this from constructors to convert array elements based on the
     * ArrayOf attribute defined on the constructor parameter.
     *
     * @param string $property The constructor parameter name
     * @param array|null $values The array to normalize
     * @return array|null The normalized array
     */
    protected function normalizeArray(string $property, ?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $arrayOfAttr = static::getArrayOfAttribute($property);

        if ($arrayOfAttr === null) {
            return $values;
        }

        return $arrayOfAttr->convertArray($values);
    }
}
