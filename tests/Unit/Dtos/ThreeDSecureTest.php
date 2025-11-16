<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\ThreeDSecure;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ThreeDSecure data object.
 */
class ThreeDSecureTest extends TestCase
{
    public function test_construct_withAllProperties_createsInstance(): void
    {
        // Arrange & Act
        $threeDSecure = new ThreeDSecure(
            directoryServerTransactionId: '88093c16-4659-4b23-bc84-b5a790779107',
            transactionStatus: 'Y',
            protocolVersion: '2.1.0',
            electronicCommerceIndicator: '05',
            authenticationValue: 'DO+j0b3yB6NR9vJ+BO6O099GvzY='
        );

        // Assert
        $this->assertSame('88093c16-4659-4b23-bc84-b5a790779107', $threeDSecure->directoryServerTransactionId);
        $this->assertSame('Y', $threeDSecure->transactionStatus);
        $this->assertSame('2.1.0', $threeDSecure->protocolVersion);
        $this->assertSame('05', $threeDSecure->electronicCommerceIndicator);
        $this->assertSame('DO+j0b3yB6NR9vJ+BO6O099GvzY=', $threeDSecure->authenticationValue);
    }

    public function test_construct_withMinimalProperties_createsInstance(): void
    {
        // Arrange & Act
        $threeDSecure = new ThreeDSecure(
            directoryServerTransactionId: '12345678-1234-1234-8912-123456789012',
            transactionStatus: 'N',
            protocolVersion: '2.2.0'
        );

        // Assert
        $this->assertSame('12345678-1234-1234-8912-123456789012', $threeDSecure->directoryServerTransactionId);
        $this->assertSame('N', $threeDSecure->transactionStatus);
        $this->assertSame('2.2.0', $threeDSecure->protocolVersion);
        $this->assertNull($threeDSecure->electronicCommerceIndicator);
        $this->assertNull($threeDSecure->authenticationValue);
    }

    public function test_construct_withInvalidUuid_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory server transaction ID must be a valid UUID');

        // Act
        new ThreeDSecure(
            directoryServerTransactionId: 'not-a-uuid',
            transactionStatus: 'Y',
            protocolVersion: '2.1.0'
        );
    }

    public function test_construct_withInvalidTransactionStatus_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction status must be Y, N, U, or A');

        // Act
        new ThreeDSecure(
            directoryServerTransactionId: '88093c16-4659-4b23-bc84-b5a790779107',
            transactionStatus: 'X',
            protocolVersion: '2.1.0'
        );
    }

    public function test_construct_withInvalidProtocolVersion_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Protocol version must be in format X.Y.Z');

        // Act
        new ThreeDSecure(
            directoryServerTransactionId: '88093c16-4659-4b23-bc84-b5a790779107',
            transactionStatus: 'Y',
            protocolVersion: '2.1'
        );
    }

    public function test_construct_withInvalidEci_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Electronic commerce indicator must be 0, 1, 2, 5, 6, or 7');

        // Act
        new ThreeDSecure(
            directoryServerTransactionId: '88093c16-4659-4b23-bc84-b5a790779107',
            transactionStatus: 'Y',
            protocolVersion: '2.1.0',
            electronicCommerceIndicator: '9'
        );
    }

    public function test_construct_withInvalidAuthenticationValue_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Authentication value must be exactly 28 characters');

        // Act
        new ThreeDSecure(
            directoryServerTransactionId: '88093c16-4659-4b23-bc84-b5a790779107',
            transactionStatus: 'Y',
            protocolVersion: '2.1.0',
            authenticationValue: 'too-short'
        );
    }

    /**
     * @dataProvider validTransactionStatusProvider
     */
    public function test_construct_withValidTransactionStatus_createsInstance(string $status): void
    {
        // Arrange & Act
        $threeDSecure = new ThreeDSecure(
            directoryServerTransactionId: '88093c16-4659-4b23-bc84-b5a790779107',
            transactionStatus: $status,
            protocolVersion: '2.1.0'
        );

        // Assert
        $this->assertSame($status, $threeDSecure->transactionStatus);
    }

    public static function validTransactionStatusProvider(): array
    {
        return [
            ['Y'],
            ['N'],
            ['U'],
            ['A'],
        ];
    }

    /**
     * @dataProvider validEciProvider
     */
    public function test_construct_withValidEci_createsInstance(string $eci): void
    {
        // Arrange & Act
        $threeDSecure = new ThreeDSecure(
            directoryServerTransactionId: '88093c16-4659-4b23-bc84-b5a790779107',
            transactionStatus: 'Y',
            protocolVersion: '2.1.0',
            electronicCommerceIndicator: $eci
        );

        // Assert
        $this->assertSame($eci, $threeDSecure->electronicCommerceIndicator);
    }

    public static function validEciProvider(): array
    {
        return [
            ['0'],
            ['1'],
            ['2'],
            ['5'],
            ['6'],
            ['7'],
            ['01'],
            ['02'],
            ['05'],
            ['06'],
            ['07'],
        ];
    }

    public function test_fromArray_withAllProperties_createsInstance(): void
    {
        // Arrange
        $data = [
            'directoryServerTransactionId' => '88093c16-4659-4b23-bc84-b5a790779107',
            'transactionStatus' => 'Y',
            'protocolVersion' => '2.1.0',
            'electronicCommerceIndicator' => '05',
            'authenticationValue' => 'DO+j0b3yB6NR9vJ+BO6O099GvzY=',
        ];

        // Act
        $threeDSecure = ThreeDSecure::fromArray($data);

        // Assert
        $this->assertSame('88093c16-4659-4b23-bc84-b5a790779107', $threeDSecure->directoryServerTransactionId);
        $this->assertSame('Y', $threeDSecure->transactionStatus);
        $this->assertSame('2.1.0', $threeDSecure->protocolVersion);
        $this->assertSame('05', $threeDSecure->electronicCommerceIndicator);
        $this->assertSame('DO+j0b3yB6NR9vJ+BO6O099GvzY=', $threeDSecure->authenticationValue);
    }

    public function test_fromArray_withMissingOptionalProperties_createsInstance(): void
    {
        // Arrange
        $data = [
            'directoryServerTransactionId' => '88093c16-4659-4b23-bc84-b5a790779107',
            'transactionStatus' => 'U',
            'protocolVersion' => '2.1.0',
        ];

        // Act
        $threeDSecure = ThreeDSecure::fromArray($data);

        // Assert
        $this->assertSame('88093c16-4659-4b23-bc84-b5a790779107', $threeDSecure->directoryServerTransactionId);
        $this->assertSame('U', $threeDSecure->transactionStatus);
        $this->assertSame('2.1.0', $threeDSecure->protocolVersion);
        $this->assertNull($threeDSecure->electronicCommerceIndicator);
        $this->assertNull($threeDSecure->authenticationValue);
    }

    public function test_fromArray_withMissingRequiredProperty_throwsException(): void
    {
        // Arrange
        $data = [
            'directoryServerTransactionId' => '88093c16-4659-4b23-bc84-b5a790779107',
            'transactionStatus' => 'Y',
            // Missing protocolVersion
        ];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('protocolVersion is required');

        // Act
        ThreeDSecure::fromArray($data);
    }

    public function test_toArray_withAllProperties_returnsCompleteArray(): void
    {
        // Arrange
        $threeDSecure = new ThreeDSecure(
            directoryServerTransactionId: '88093c16-4659-4b23-bc84-b5a790779107',
            transactionStatus: 'Y',
            protocolVersion: '2.1.0',
            electronicCommerceIndicator: '05',
            authenticationValue: 'DO+j0b3yB6NR9vJ+BO6O099GvzY='
        );

        // Act
        $result = $threeDSecure->toArray();

        // Assert
        $this->assertSame([
            'directoryServerTransactionId' => '88093c16-4659-4b23-bc84-b5a790779107',
            'transactionStatus' => 'Y',
            'protocolVersion' => '2.1.0',
            'electronicCommerceIndicator' => '05',
            'authenticationValue' => 'DO+j0b3yB6NR9vJ+BO6O099GvzY=',
        ], $result);
    }

    public function test_toArray_withNullProperties_excludesNullValues(): void
    {
        // Arrange
        $threeDSecure = new ThreeDSecure(
            directoryServerTransactionId: '88093c16-4659-4b23-bc84-b5a790779107',
            transactionStatus: 'A',
            protocolVersion: '2.2.0'
        );

        // Act
        $result = $threeDSecure->toArray();

        // Assert
        $this->assertSame([
            'directoryServerTransactionId' => '88093c16-4659-4b23-bc84-b5a790779107',
            'transactionStatus' => 'A',
            'protocolVersion' => '2.2.0',
        ], $result);
        $this->assertArrayNotHasKey('electronicCommerceIndicator', $result);
        $this->assertArrayNotHasKey('authenticationValue', $result);
    }

    public function test_toArray_roundTrip_preservesData(): void
    {
        // Arrange
        $originalData = [
            'directoryServerTransactionId' => '88093c16-4659-4b23-bc84-b5a790779107',
            'transactionStatus' => 'Y',
            'protocolVersion' => '2.1.0',
            'electronicCommerceIndicator' => '05',
            'authenticationValue' => 'DO+j0b3yB6NR9vJ+BO6O099GvzY=',
        ];
        $threeDSecure = ThreeDSecure::fromArray($originalData);

        // Act
        $result = $threeDSecure->toArray();

        // Assert
        $this->assertSame($originalData, $result);
    }
}
