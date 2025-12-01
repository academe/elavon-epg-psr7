<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\ValueObjects;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CustomFields value object.
 */
class CustomFieldsTest extends TestCase
{
    public function test_construct_withValidFields_createsInstance(): void
    {
        // Arrange & Act
        $customFields = new CustomFields([
            'project' => 'Alpha',
            'priority' => 'high',
        ]);

        // Assert
        $this->assertSame('Alpha', $customFields['project']);
        $this->assertSame('high', $customFields['priority']);
        $this->assertCount(2, $customFields);
    }

    public function test_construct_withEmptyArray_createsInstance(): void
    {
        // Arrange & Act
        $customFields = new CustomFields([]);

        // Assert
        $this->assertCount(0, $customFields);
        $this->assertTrue($customFields->isEmpty());
    }

    public function test_construct_withKeyTooLong_throwsException(): void
    {
        // Arrange
        $longKey = str_repeat('a', 65);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must not exceed 64 characters');

        // Act
        new CustomFields([$longKey => 'value']);
    }

    public function test_construct_withKeyAtMaxLength_succeeds(): void
    {
        // Arrange
        $maxKey = str_repeat('a', 64);

        // Act
        $customFields = new CustomFields([$maxKey => 'value']);

        // Assert
        $this->assertSame('value', $customFields[$maxKey]);
    }

    public function test_construct_withValueTooLong_throwsException(): void
    {
        // Arrange
        $longValue = str_repeat('a', 1025);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must not exceed 1024 characters');

        // Act
        new CustomFields(['key' => $longValue]);
    }

    public function test_construct_withValueAtMaxLength_succeeds(): void
    {
        // Arrange
        $maxValue = str_repeat('a', 1024);

        // Act
        $customFields = new CustomFields(['key' => $maxValue]);

        // Assert
        $this->assertSame($maxValue, $customFields['key']);
    }

    public function test_fromData_withArray_createsInstance(): void
    {
        // Arrange
        $data = ['key1' => 'value1', 'key2' => 'value2'];

        // Act
        $customFields = CustomFields::fromData($data);

        // Assert
        $this->assertSame('value1', $customFields['key1']);
        $this->assertSame('value2', $customFields['key2']);
    }

    public function test_fromData_withCustomFieldsInstance_returnsSameType(): void
    {
        // Arrange
        $original = new CustomFields(['key' => 'value']);

        // Act
        $result = CustomFields::fromData($original);

        // Assert
        $this->assertSame($original, $result);
    }

    public function test_fromData_withInvalidType_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom fields must be an array');

        // Act
        CustomFields::fromData('not an array');
    }

    public function test_toData_returnsArray(): void
    {
        // Arrange
        $data = ['project' => 'Beta', 'status' => 'active'];
        $customFields = new CustomFields($data);

        // Act
        $result = $customFields->toData();

        // Assert
        $this->assertSame($data, $result);
    }

    public function test_all_returnsArray(): void
    {
        // Arrange
        $data = ['a' => '1', 'b' => '2'];
        $customFields = new CustomFields($data);

        // Act
        $result = $customFields->all();

        // Assert
        $this->assertSame($data, $result);
    }

    public function test_get_withExistingKey_returnsValue(): void
    {
        // Arrange
        $customFields = new CustomFields(['key' => 'value']);

        // Act & Assert
        $this->assertSame('value', $customFields->get('key'));
    }

    public function test_get_withMissingKey_returnsNull(): void
    {
        // Arrange
        $customFields = new CustomFields(['key' => 'value']);

        // Act & Assert
        $this->assertNull($customFields->get('missing'));
    }

    public function test_get_withMissingKeyAndDefault_returnsDefault(): void
    {
        // Arrange
        $customFields = new CustomFields(['key' => 'value']);

        // Act & Assert
        $this->assertSame('default', $customFields->get('missing', 'default'));
    }

    public function test_has_withExistingKey_returnsTrue(): void
    {
        // Arrange
        $customFields = new CustomFields(['key' => 'value']);

        // Act & Assert
        $this->assertTrue($customFields->has('key'));
    }

    public function test_has_withMissingKey_returnsFalse(): void
    {
        // Arrange
        $customFields = new CustomFields(['key' => 'value']);

        // Act & Assert
        $this->assertFalse($customFields->has('missing'));
    }

    public function test_with_addsFieldAndReturnsNewInstance(): void
    {
        // Arrange
        $original = new CustomFields(['key1' => 'value1']);

        // Act
        $new = $original->with('key2', 'value2');

        // Assert
        $this->assertNotSame($original, $new);
        $this->assertFalse($original->has('key2'));
        $this->assertTrue($new->has('key2'));
        $this->assertSame('value2', $new['key2']);
    }

    public function test_without_removesFieldAndReturnsNewInstance(): void
    {
        // Arrange
        $original = new CustomFields(['key1' => 'value1', 'key2' => 'value2']);

        // Act
        $new = $original->without('key1');

        // Assert
        $this->assertNotSame($original, $new);
        $this->assertTrue($original->has('key1'));
        $this->assertFalse($new->has('key1'));
        $this->assertTrue($new->has('key2'));
    }

    public function test_arrayAccess_offsetExists_works(): void
    {
        // Arrange
        $customFields = new CustomFields(['key' => 'value']);

        // Act & Assert
        $this->assertTrue(isset($customFields['key']));
        $this->assertFalse(isset($customFields['missing']));
    }

    public function test_arrayAccess_offsetGet_works(): void
    {
        // Arrange
        $customFields = new CustomFields(['key' => 'value']);

        // Act & Assert
        $this->assertSame('value', $customFields['key']);
        $this->assertNull($customFields['missing']);
    }

    public function test_arrayAccess_offsetSet_throwsException(): void
    {
        // Arrange
        $customFields = new CustomFields(['key' => 'value']);

        // Assert
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('immutable');

        // Act
        $customFields['new'] = 'value';
    }

    public function test_arrayAccess_offsetUnset_throwsException(): void
    {
        // Arrange
        $customFields = new CustomFields(['key' => 'value']);

        // Assert
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('immutable');

        // Act
        unset($customFields['key']);
    }

    public function test_countable_count_works(): void
    {
        // Arrange
        $customFields = new CustomFields(['a' => '1', 'b' => '2', 'c' => '3']);

        // Act & Assert
        $this->assertCount(3, $customFields);
        $this->assertSame(3, count($customFields));
    }

    public function test_iteratorAggregate_foreach_works(): void
    {
        // Arrange
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $customFields = new CustomFields($data);
        $result = [];

        // Act
        foreach ($customFields as $key => $value) {
            $result[$key] = $value;
        }

        // Assert
        $this->assertSame($data, $result);
    }

    public function test_isEmpty_withEmptyFields_returnsTrue(): void
    {
        // Arrange
        $customFields = new CustomFields([]);

        // Act & Assert
        $this->assertTrue($customFields->isEmpty());
    }

    public function test_isEmpty_withFields_returnsFalse(): void
    {
        // Arrange
        $customFields = new CustomFields(['key' => 'value']);

        // Act & Assert
        $this->assertFalse($customFields->isEmpty());
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'project' => 'Gamma',
            'priority' => 'low',
            'category' => 'test',
        ];

        // Act
        $customFields = CustomFields::fromData($originalData);
        $resultData = $customFields->toData();

        // Assert
        $this->assertSame($originalData, $resultData);
    }
}
