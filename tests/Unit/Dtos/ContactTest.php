<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Contact;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Contact data object.
 */
class ContactTest extends TestCase
{
    public function test_construct_withAllProperties_createsInstance(): void
    {
        // Arrange & Act
        $contact = new Contact(
            fullName: 'John Doe',
            company: 'Acme Corp',
            street1: '221 Baker St',
            street2: 'Suite B',
            city: 'London',
            region: 'England',
            postalCode: 'NW1 6XE',
            countryCode: 'GBR',
            primaryPhone: '+44 020 7946 0123',
            alternatePhone: '+44 020 7946 0124',
            fax: '+44 020 7946 0125',
            email: 'john@email.com'
        );

        // Assert
        $this->assertSame('John Doe', $contact->fullName);
        $this->assertSame('Acme Corp', $contact->company);
        $this->assertSame('221 Baker St', $contact->street1);
        $this->assertSame('Suite B', $contact->street2);
        $this->assertSame('London', $contact->city);
        $this->assertSame('England', $contact->region);
        $this->assertSame('NW1 6XE', $contact->postalCode);
        $this->assertSame('GBR', $contact->countryCode);
        $this->assertSame('+44 020 7946 0123', $contact->primaryPhone);
        $this->assertSame('+44 020 7946 0124', $contact->alternatePhone);
        $this->assertSame('+44 020 7946 0125', $contact->fax);
        $this->assertSame('john@email.com', $contact->email);
    }

    public function test_construct_withNoProperties_createsInstance(): void
    {
        // Arrange & Act
        $contact = new Contact();

        // Assert
        $this->assertNull($contact->fullName);
        $this->assertNull($contact->company);
        $this->assertNull($contact->street1);
        $this->assertNull($contact->street2);
        $this->assertNull($contact->city);
        $this->assertNull($contact->region);
        $this->assertNull($contact->postalCode);
        $this->assertNull($contact->countryCode);
        $this->assertNull($contact->primaryPhone);
        $this->assertNull($contact->alternatePhone);
        $this->assertNull($contact->fax);
        $this->assertNull($contact->email);
    }

    public function test_construct_withInvalidCountryCode_throwsException(): void
    {
        // Arrange & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Country code must be exactly 3 characters");

        // Act
        new Contact(countryCode: 'US');
    }

    public function test_construct_withTooLongEmail_throwsException(): void
    {
        // Arrange
        $longEmail = str_repeat('a', 255) . '@example.com';

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email address must not exceed 254 characters');

        // Act
        new Contact(email: $longEmail);
    }

    public function test_construct_withTooLongFullName_throwsException(): void
    {
        // Arrange
        $longName = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('fullName must not exceed 255 characters');

        // Act
        new Contact(fullName: $longName);
    }

    public function test_fromArray_withAllProperties_createsInstance(): void
    {
        // Arrange
        $data = [
            'fullName' => 'Jane Smith',
            'company' => 'Tech Inc',
            'street1' => '123 Main St',
            'street2' => 'Apt 4',
            'city' => 'New York',
            'region' => 'NY',
            'postalCode' => '10001',
            'countryCode' => 'USA',
            'primaryPhone' => '+1 212 555 0100',
            'alternatePhone' => '+1 212 555 0101',
            'fax' => '+1 212 555 0102',
            'email' => 'jane@example.com',
        ];

        // Act
        $contact = Contact::fromArray($data);

        // Assert
        $this->assertSame('Jane Smith', $contact->fullName);
        $this->assertSame('Tech Inc', $contact->company);
        $this->assertSame('123 Main St', $contact->street1);
        $this->assertSame('Apt 4', $contact->street2);
        $this->assertSame('New York', $contact->city);
        $this->assertSame('NY', $contact->region);
        $this->assertSame('10001', $contact->postalCode);
        $this->assertSame('USA', $contact->countryCode);
        $this->assertSame('+1 212 555 0100', $contact->primaryPhone);
        $this->assertSame('+1 212 555 0101', $contact->alternatePhone);
        $this->assertSame('+1 212 555 0102', $contact->fax);
        $this->assertSame('jane@example.com', $contact->email);
    }

    public function test_fromArray_withMissingProperties_createsInstanceWithNulls(): void
    {
        // Arrange
        $data = [
            'fullName' => 'Bob Jones',
            'email' => 'bob@example.com',
        ];

        // Act
        $contact = Contact::fromArray($data);

        // Assert
        $this->assertSame('Bob Jones', $contact->fullName);
        $this->assertSame('bob@example.com', $contact->email);
        $this->assertNull($contact->company);
        $this->assertNull($contact->street1);
        $this->assertNull($contact->city);
    }

    public function test_toArray_withAllProperties_returnsCompleteArray(): void
    {
        // Arrange
        $contact = new Contact(
            fullName: 'Test User',
            company: 'Test Co',
            street1: '1 Test St',
            city: 'Test City',
            countryCode: 'TST',
            email: 'test@test.com'
        );

        // Act
        $result = $contact->toArray();

        // Assert
        $this->assertSame([
            'fullName' => 'Test User',
            'company' => 'Test Co',
            'street1' => '1 Test St',
            'city' => 'Test City',
            'countryCode' => 'TST',
            'email' => 'test@test.com',
        ], $result);
    }

    public function test_toArray_withNullProperties_excludesNullValues(): void
    {
        // Arrange
        $contact = new Contact(
            fullName: 'User',
            email: 'user@example.com'
        );

        // Act
        $result = $contact->toArray();

        // Assert
        $this->assertSame([
            'fullName' => 'User',
            'email' => 'user@example.com',
        ], $result);
        $this->assertArrayNotHasKey('company', $result);
        $this->assertArrayNotHasKey('street1', $result);
    }

    public function test_toArray_roundTrip_preservesData(): void
    {
        // Arrange
        $originalData = [
            'fullName' => 'Round Trip',
            'street1' => '100 Test Ave',
            'city' => 'TestVille',
            'countryCode' => 'GBR',
            'email' => 'roundtrip@test.com',
        ];
        $contact = Contact::fromArray($originalData);

        // Act
        $result = $contact->toArray();

        // Assert
        $this->assertSame($originalData, $result);
    }
}
