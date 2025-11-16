<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\DataObjects;

use Academe\Elavon\Epg\Psr7\DataObjects\ShopperStatement;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ShopperStatement data object.
 */
class ShopperStatementTest extends TestCase
{
    public function test_construct_withAllProperties_createsInstance(): void
    {
        // Arrange & Act
        $statement = new ShopperStatement(
            name: 'GLOBE THEATRE*OTHELLO',
            phone: '02079021409',
            url: 'GLOBE'
        );

        // Assert
        $this->assertSame('GLOBE THEATRE*OTHELLO', $statement->name);
        $this->assertSame('02079021409', $statement->phone);
        $this->assertSame('GLOBE', $statement->url);
    }

    public function test_construct_withNoProperties_createsInstance(): void
    {
        // Arrange & Act
        $statement = new ShopperStatement();

        // Assert
        $this->assertNull($statement->name);
        $this->assertNull($statement->phone);
        $this->assertNull($statement->url);
    }

    public function test_construct_withTooLongName_throwsException(): void
    {
        // Arrange
        $longName = str_repeat('a', 26);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Statement name must not exceed 25 characters');

        // Act
        new ShopperStatement(name: $longName);
    }

    public function test_construct_withTooLongPhone_throwsException(): void
    {
        // Arrange
        $longPhone = str_repeat('1', 21);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Statement phone must not exceed 20 characters');

        // Act
        new ShopperStatement(phone: $longPhone);
    }

    public function test_construct_withTooLongUrl_throwsException(): void
    {
        // Arrange
        $longUrl = str_repeat('a', 14);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Statement URL must not exceed 13 characters');

        // Act
        new ShopperStatement(url: $longUrl);
    }

    public function test_fromArray_withAllProperties_createsInstance(): void
    {
        // Arrange
        $data = [
            'name' => 'ACME CORP*PRODUCT',
            'phone' => '18005551234',
            'url' => 'ACMECORP',
        ];

        // Act
        $statement = ShopperStatement::fromArray($data);

        // Assert
        $this->assertSame('ACME CORP*PRODUCT', $statement->name);
        $this->assertSame('18005551234', $statement->phone);
        $this->assertSame('ACMECORP', $statement->url);
    }

    public function test_fromArray_withMissingProperties_createsInstanceWithNulls(): void
    {
        // Arrange
        $data = [
            'name' => 'STORE',
        ];

        // Act
        $statement = ShopperStatement::fromArray($data);

        // Assert
        $this->assertSame('STORE', $statement->name);
        $this->assertNull($statement->phone);
        $this->assertNull($statement->url);
    }

    public function test_toArray_withAllProperties_returnsCompleteArray(): void
    {
        // Arrange
        $statement = new ShopperStatement(
            name: 'TEST MERCHANT',
            phone: '5551234567',
            url: 'TESTMERCH'
        );

        // Act
        $result = $statement->toArray();

        // Assert
        $this->assertSame([
            'name' => 'TEST MERCHANT',
            'phone' => '5551234567',
            'url' => 'TESTMERCH',
        ], $result);
    }

    public function test_toArray_withNullProperties_excludesNullValues(): void
    {
        // Arrange
        $statement = new ShopperStatement(name: 'ONLY NAME');

        // Act
        $result = $statement->toArray();

        // Assert
        $this->assertSame(['name' => 'ONLY NAME'], $result);
        $this->assertArrayNotHasKey('phone', $result);
        $this->assertArrayNotHasKey('url', $result);
    }

    public function test_toArray_roundTrip_preservesData(): void
    {
        // Arrange
        $originalData = [
            'name' => 'ROUND TRIP',
            'phone' => '1234567890',
            'url' => 'ROUNDTRIP',
        ];
        $statement = ShopperStatement::fromArray($originalData);

        // Act
        $result = $statement->toArray();

        // Assert
        $this->assertSame($originalData, $result);
    }
}
