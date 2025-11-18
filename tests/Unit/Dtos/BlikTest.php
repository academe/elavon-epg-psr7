<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Blik;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Blik DTO.
 */
class BlikTest extends TestCase
{
    public function test_construct_withValidCode_createsInstance(): void
    {
        // Arrange & Act
        $blik = new Blik(code: '123456');

        // Assert
        $this->assertSame('123456', $blik->code);
    }

    public function test_construct_withValidCodeAllZeros_createsInstance(): void
    {
        // Arrange & Act
        $blik = new Blik(code: '000000');

        // Assert
        $this->assertSame('000000', $blik->code);
    }

    public function test_construct_withValidCodeAllNines_createsInstance(): void
    {
        // Arrange & Act
        $blik = new Blik(code: '999999');

        // Assert
        $this->assertSame('999999', $blik->code);
    }

    public function test_construct_withCodeTooShort_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BLIK code must be exactly 6 digits');

        // Act
        new Blik(code: '12345');
    }

    public function test_construct_withCodeTooLong_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BLIK code must be exactly 6 digits');

        // Act
        new Blik(code: '1234567');
    }

    public function test_construct_withCodeContainingLetters_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BLIK code must be exactly 6 digits');

        // Act
        new Blik(code: '12345a');
    }

    public function test_construct_withCodeContainingSpaces_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BLIK code must be exactly 6 digits');

        // Act
        new Blik(code: '123 456');
    }

    public function test_construct_withCodeContainingSpecialCharacters_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BLIK code must be exactly 6 digits');

        // Act
        new Blik(code: '123-456');
    }

    public function test_construct_withEmptyCode_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BLIK code must be exactly 6 digits');

        // Act
        new Blik(code: '');
    }

    public function test_fromData_withValidCode_createsInstance(): void
    {
        // Arrange
        $data = ['code' => '654321'];

        // Act
        $blik = Blik::fromData($data);

        // Assert
        $this->assertSame('654321', $blik->code);
    }

    public function test_fromData_withInvalidCode_throwsException(): void
    {
        // Arrange
        $data = ['code' => 'invalid'];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BLIK code must be exactly 6 digits');

        // Act
        Blik::fromData($data);
    }

    public function test_toData_returnsArray(): void
    {
        // Arrange
        $blik = new Blik(code: '789012');

        // Act
        $array = $blik->toData();

        // Assert
        $this->assertSame(['code' => '789012'], $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = ['code' => '456789'];

        // Act
        $blik = Blik::fromData($originalData);
        $resultData = $blik->toData();

        // Assert
        $this->assertSame($originalData, $resultData);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $blik = new Blik(code: '123456');

        // Act & Assert
        $reflection = new \ReflectionProperty($blik, 'code');
        $this->assertTrue($reflection->isReadOnly());
    }
}
