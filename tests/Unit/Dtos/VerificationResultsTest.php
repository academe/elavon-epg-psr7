<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\VerificationResults;
use Academe\Elavon\Epg\Psr7\Enums\Verification;
use PHPUnit\Framework\TestCase;

/**
 * Tests for VerificationResults DTO.
 */
class VerificationResultsTest extends TestCase
{
    public function test_construct_withNoFields_createsInstance(): void
    {
        // Arrange & Act
        $results = new VerificationResults();

        // Assert
        $this->assertNull($results->name);
        $this->assertNull($results->securityCode);
        $this->assertNull($results->addressStreet);
        $this->assertNull($results->addressPostalCode);
        $this->assertNull($results->threeDSecureV2);
        $this->assertNull($results->cryptogramSecurity);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $results = new VerificationResults(
            name: Verification::MATCHED,
            securityCode: Verification::MATCHED,
            addressStreet: Verification::UNMATCHED,
            addressPostalCode: Verification::MATCHED,
            threeDSecureV2: Verification::UNPROVIDED,
            cryptogramSecurity: Verification::UNSUPPORTED,
        );

        // Assert
        $this->assertSame(Verification::MATCHED, $results->name);
        $this->assertSame(Verification::MATCHED, $results->securityCode);
        $this->assertSame(Verification::UNMATCHED, $results->addressStreet);
        $this->assertSame(Verification::MATCHED, $results->addressPostalCode);
        $this->assertSame(Verification::UNPROVIDED, $results->threeDSecureV2);
        $this->assertSame(Verification::UNSUPPORTED, $results->cryptogramSecurity);
    }

    public function test_construct_withPartialFields_createsInstance(): void
    {
        // Arrange & Act
        $results = new VerificationResults(
            securityCode: Verification::MATCHED,
            addressPostalCode: Verification::UNMATCHED,
        );

        // Assert
        $this->assertNull($results->name);
        $this->assertSame(Verification::MATCHED, $results->securityCode);
        $this->assertNull($results->addressStreet);
        $this->assertSame(Verification::UNMATCHED, $results->addressPostalCode);
        $this->assertNull($results->threeDSecureV2);
        $this->assertNull($results->cryptogramSecurity);
    }

    public function test_fromData_withEmptyData_createsInstance(): void
    {
        // Arrange
        $data = [];

        // Act
        $results = VerificationResults::fromData($data);

        // Assert
        $this->assertNull($results->name);
        $this->assertNull($results->securityCode);
        $this->assertNull($results->addressStreet);
        $this->assertNull($results->addressPostalCode);
        $this->assertNull($results->threeDSecureV2);
        $this->assertNull($results->cryptogramSecurity);
    }

    public function test_fromData_withAllFields_createsInstance(): void
    {
        // Arrange
        $data = [
            'name' => 'matched',
            'securityCode' => 'matched',
            'addressStreet' => 'unmatched',
            'addressPostalCode' => 'matched',
            'threeDSecureV2' => 'unprovided',
            'cryptogramSecurity' => 'unsupported',
        ];

        // Act
        $results = VerificationResults::fromData($data);

        // Assert
        $this->assertSame(Verification::MATCHED, $results->name);
        $this->assertSame(Verification::MATCHED, $results->securityCode);
        $this->assertSame(Verification::UNMATCHED, $results->addressStreet);
        $this->assertSame(Verification::MATCHED, $results->addressPostalCode);
        $this->assertSame(Verification::UNPROVIDED, $results->threeDSecureV2);
        $this->assertSame(Verification::UNSUPPORTED, $results->cryptogramSecurity);
    }

    public function test_fromData_withPartialFields_createsInstance(): void
    {
        // Arrange
        $data = [
            'securityCode' => 'matched',
            'addressPostalCode' => 'unknown',
        ];

        // Act
        $results = VerificationResults::fromData($data);

        // Assert
        $this->assertNull($results->name);
        $this->assertSame(Verification::MATCHED, $results->securityCode);
        $this->assertNull($results->addressStreet);
        $this->assertSame(Verification::UNKNOWN, $results->addressPostalCode);
        $this->assertNull($results->threeDSecureV2);
        $this->assertNull($results->cryptogramSecurity);
    }

    public function test_fromData_withAllVerificationValues_createsInstance(): void
    {
        // Test each verification value can be parsed
        $verificationValues = ['matched', 'unmatched', 'unprovided', 'unsupported', 'unavailable', 'unknown'];

        foreach ($verificationValues as $value) {
            $results = VerificationResults::fromData(['securityCode' => $value]);
            $this->assertInstanceOf(Verification::class, $results->securityCode);
            $this->assertSame($value, $results->securityCode->value);
        }
    }

    public function test_toData_withNoFields_returnsEmptyArray(): void
    {
        // Arrange
        $results = new VerificationResults();

        // Act
        $array = $results->toData();

        // Assert
        $this->assertSame([], $array);
    }

    public function test_toData_withAllFields_returnsArray(): void
    {
        // Arrange
        $results = new VerificationResults(
            name: Verification::MATCHED,
            securityCode: Verification::UNMATCHED,
            addressStreet: Verification::UNPROVIDED,
            addressPostalCode: Verification::UNSUPPORTED,
            threeDSecureV2: Verification::UNAVAILABLE,
            cryptogramSecurity: Verification::UNKNOWN,
        );

        // Act
        $array = $results->toData();

        // Assert
        $this->assertSame([
            'name' => 'matched',
            'securityCode' => 'unmatched',
            'addressStreet' => 'unprovided',
            'addressPostalCode' => 'unsupported',
            'threeDSecureV2' => 'unavailable',
            'cryptogramSecurity' => 'unknown',
        ], $array);
    }

    public function test_toData_withPartialFields_returnsOnlyNonNullValues(): void
    {
        // Arrange
        $results = new VerificationResults(
            securityCode: Verification::MATCHED,
            addressPostalCode: Verification::UNMATCHED,
        );

        // Act
        $array = $results->toData();

        // Assert
        $this->assertSame([
            'securityCode' => 'matched',
            'addressPostalCode' => 'unmatched',
        ], $array);
        $this->assertArrayNotHasKey('name', $array);
        $this->assertArrayNotHasKey('addressStreet', $array);
        $this->assertArrayNotHasKey('threeDSecureV2', $array);
        $this->assertArrayNotHasKey('cryptogramSecurity', $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'name' => 'matched',
            'securityCode' => 'unmatched',
            'addressStreet' => 'unprovided',
            'addressPostalCode' => 'unsupported',
            'threeDSecureV2' => 'unavailable',
            'cryptogramSecurity' => 'unknown',
        ];

        // Act
        $results = VerificationResults::fromData($originalData);
        $resultData = $results->toData();

        // Assert
        $this->assertSame($originalData, $resultData);
    }

    public function test_roundTrip_partialData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'securityCode' => 'matched',
            'threeDSecureV2' => 'unprovided',
        ];

        // Act
        $results = VerificationResults::fromData($originalData);
        $resultData = $results->toData();

        // Assert
        $this->assertSame($originalData, $resultData);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $results = new VerificationResults(securityCode: Verification::MATCHED);

        // Act & Assert
        $reflection = new \ReflectionProperty($results, 'securityCode');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($results, 'name');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($results, 'addressStreet');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($results, 'addressPostalCode');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($results, 'threeDSecureV2');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($results, 'cryptogramSecurity');
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_toObjectArray_returnsShallowArray(): void
    {
        // Arrange
        $results = new VerificationResults(
            securityCode: Verification::MATCHED,
            addressPostalCode: Verification::UNMATCHED,
        );

        // Act
        $array = $results->toObjectArray();

        // Assert
        $this->assertCount(2, $array);
        $this->assertSame(Verification::MATCHED, $array['securityCode']);
        $this->assertSame(Verification::UNMATCHED, $array['addressPostalCode']);
    }
}