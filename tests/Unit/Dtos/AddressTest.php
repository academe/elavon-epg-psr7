<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Address;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Address DTO.
 */
class AddressTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        // Arrange & Act
        $address = new Address();

        // Assert
        $this->assertNull($address->street1);
        $this->assertNull($address->street2);
        $this->assertNull($address->city);
        $this->assertNull($address->stateOrProvince);
        $this->assertNull($address->postalCode);
        $this->assertNull($address->country);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $address = new Address(
            street1: '221 Baker St',
            street2: 'Apt 4B',
            city: 'London',
            stateOrProvince: 'Greater London',
            postalCode: 'NW1 6XE',
            country: 'GB'
        );

        // Assert
        $this->assertSame('221 Baker St', $address->street1);
        $this->assertSame('Apt 4B', $address->street2);
        $this->assertSame('London', $address->city);
        $this->assertSame('Greater London', $address->stateOrProvince);
        $this->assertSame('NW1 6XE', $address->postalCode);
        $this->assertSame('GB', $address->country);
    }

    public function test_fromData_withMinimalData_createsInstance(): void
    {
        // Arrange
        $data = [];

        // Act
        $address = Address::fromData($data);

        // Assert
        $this->assertNull($address->street1);
        $this->assertNull($address->city);
    }

    public function test_fromData_withFullData_createsInstance(): void
    {
        // Arrange
        $data = [
            'street1' => '10 Downing Street',
            'street2' => 'Westminster',
            'city' => 'London',
            'stateOrProvince' => 'Greater London',
            'postalCode' => 'SW1A 2AA',
            'country' => 'GB',
        ];

        // Act
        $address = Address::fromData($data);

        // Assert
        $this->assertSame('10 Downing Street', $address->street1);
        $this->assertSame('Westminster', $address->street2);
        $this->assertSame('London', $address->city);
        $this->assertSame('Greater London', $address->stateOrProvince);
        $this->assertSame('SW1A 2AA', $address->postalCode);
        $this->assertSame('GB', $address->country);
    }

    public function test_toData_withMinimalData_returnsArray(): void
    {
        // Arrange
        $address = new Address();

        // Act
        $array = $address->toData();

        // Assert
        $this->assertSame([], $array);
    }

    public function test_toData_withFullData_returnsArray(): void
    {
        // Arrange
        $address = new Address(
            street1: '742 Evergreen Terrace',
            city: 'Springfield',
            postalCode: '12345',
            country: 'US'
        );

        // Act
        $array = $address->toData();

        // Assert
        $this->assertArrayHasKey('street1', $array);
        $this->assertSame('742 Evergreen Terrace', $array['street1']);
        $this->assertArrayHasKey('city', $array);
        $this->assertSame('Springfield', $array['city']);
        $this->assertArrayHasKey('postalCode', $array);
        $this->assertSame('12345', $array['postalCode']);
        $this->assertArrayHasKey('country', $array);
        $this->assertSame('US', $array['country']);
        $this->assertArrayNotHasKey('street2', $array);
        $this->assertArrayNotHasKey('stateOrProvince', $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'street1' => '123 Main St',
            'street2' => 'Suite 100',
            'city' => 'New York',
            'stateOrProvince' => 'NY',
            'postalCode' => '10001',
            'country' => 'US',
        ];

        // Act
        $address = Address::fromData($originalData);
        $resultData = $address->toData();

        // Assert
        $this->assertSame($originalData, $resultData);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $address = new Address(street1: '123 Test St');

        // Act & Assert
        $reflection = new \ReflectionProperty($address, 'street1');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($address, 'city');
        $this->assertTrue($reflection->isReadOnly());
    }
}
