<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\ValueObjects;

use Academe\Elavon\Epg\Psr7\Enums\Currency;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_constructor_withValidAmount_createsMoney(): void
    {
        // Arrange
        $amount = '99.99';
        $currency = Currency::USD;

        // Act
        $money = new Money($amount, $currency);

        // Assert
        $this->assertSame($amount, $money->amount);
        $this->assertSame($currency, $money->currency);
    }

    public function test_constructor_withIntegerAmount_createsMoney(): void
    {
        // Arrange
        $amount = '100';
        $currency = Currency::EUR;

        // Act
        $money = new Money($amount, $currency);

        // Assert
        $this->assertSame($amount, $money->amount);
        $this->assertSame($currency, $money->currency);
    }

    public function test_constructor_withMaxDigits_createsMoney(): void
    {
        // Arrange - 9 integer digits and 4 fractional digits
        $amount = '123456789.1234';
        $currency = Currency::GBP;

        // Act
        $money = new Money($amount, $currency);

        // Assert
        $this->assertSame($amount, $money->amount);
    }

    public function test_constructor_withNegativeAmount_createsMoney(): void
    {
        // Arrange
        $amount = '-50.00';
        $currency = Currency::USD;

        // Act
        $money = new Money($amount, $currency);

        // Assert
        $this->assertSame($amount, $money->amount);
    }

    public function test_constructor_withTooManyIntegerDigits_throwsException(): void
    {
        // Arrange
        $amount = '1234567890.00'; // 10 integer digits

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid amount format');

        // Act
        new Money($amount, Currency::USD);
    }

    public function test_constructor_withTooManyFractionalDigits_throwsException(): void
    {
        // Arrange
        $amount = '99.99999'; // 5 fractional digits

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid amount format');

        // Act
        new Money($amount, Currency::USD);
    }

    public function test_constructor_withInvalidFormat_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        new Money('invalid', Currency::USD);
    }

    public function test_fromArray_withValidData_createsMoney(): void
    {
        // Arrange
        $data = [
            'amount' => '50.00',
            'currencyCode' => 'EUR',
        ];

        // Act
        $money = Money::fromData($data);

        // Assert
        $this->assertSame('50.00', $money->amount);
        $this->assertSame(Currency::EUR, $money->currency);
    }

    public function test_fromArray_withMissingAmount_throwsException(): void
    {
        // Arrange
        $data = ['currencyCode' => 'USD'];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field: amount');

        // Act
        Money::fromData($data);
    }

    public function test_fromArray_withMissingCurrency_throwsException(): void
    {
        // Arrange
        $data = ['amount' => '100.00'];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field: currencyCode');

        // Act
        Money::fromData($data);
    }

    public function test_fromArray_withInvalidCurrency_throwsException(): void
    {
        // Arrange
        $data = [
            'amount' => '100.00',
            'currencyCode' => 'INVALID',
        ];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency code: INVALID');

        // Act
        Money::fromData($data);
    }

    public function test_toArray_returnsCorrectFormat(): void
    {
        // Arrange
        $money = new Money('99.99', Currency::USD);

        // Act
        $result = $money->toData();

        // Assert
        $this->assertSame([
            'amount' => '99.99',
            'currencyCode' => 'USD',
        ], $result);
    }

    public function test_equals_withSameValues_returnsTrue(): void
    {
        // Arrange
        $money1 = new Money('100.00', Currency::EUR);
        $money2 = new Money('100.00', Currency::EUR);

        // Act
        $result = $money1->equals($money2);

        // Assert
        $this->assertTrue($result);
    }

    public function test_equals_withDifferentAmount_returnsFalse(): void
    {
        // Arrange
        $money1 = new Money('100.00', Currency::EUR);
        $money2 = new Money('99.99', Currency::EUR);

        // Act
        $result = $money1->equals($money2);

        // Assert
        $this->assertFalse($result);
    }

    public function test_equals_withDifferentCurrency_returnsFalse(): void
    {
        // Arrange
        $money1 = new Money('100.00', Currency::EUR);
        $money2 = new Money('100.00', Currency::USD);

        // Act
        $result = $money1->equals($money2);

        // Assert
        $this->assertFalse($result);
    }

    public function test_hasSameCurrency_withSameCurrency_returnsTrue(): void
    {
        // Arrange
        $money1 = new Money('100.00', Currency::GBP);
        $money2 = new Money('50.00', Currency::GBP);

        // Act
        $result = $money1->hasSameCurrency($money2);

        // Assert
        $this->assertTrue($result);
    }

    public function test_hasSameCurrency_withDifferentCurrency_returnsFalse(): void
    {
        // Arrange
        $money1 = new Money('100.00', Currency::GBP);
        $money2 = new Money('100.00', Currency::USD);

        // Act
        $result = $money1->hasSameCurrency($money2);

        // Assert
        $this->assertFalse($result);
    }

    public function test_negate_withPositiveAmount_returnsNegative(): void
    {
        // Arrange
        $money = new Money('50.00', Currency::USD);

        // Act
        $result = $money->negate();

        // Assert
        $this->assertSame('-50.00', $result->amount);
        $this->assertSame(Currency::USD, $result->currency);
    }

    public function test_negate_withNegativeAmount_returnsPositive(): void
    {
        // Arrange
        $money = new Money('-50.00', Currency::USD);

        // Act
        $result = $money->negate();

        // Assert
        $this->assertSame('50.00', $result->amount);
    }

    public function test_isPositive_withPositiveAmount_returnsTrue(): void
    {
        // Arrange
        $money = new Money('0.01', Currency::USD);

        // Act & Assert
        $this->assertTrue($money->isPositive());
    }

    public function test_isPositive_withZeroAmount_returnsFalse(): void
    {
        // Arrange
        $money = new Money('0', Currency::USD);

        // Act & Assert
        $this->assertFalse($money->isPositive());
    }

    public function test_isNegative_withNegativeAmount_returnsTrue(): void
    {
        // Arrange
        $money = new Money('-0.01', Currency::USD);

        // Act & Assert
        $this->assertTrue($money->isNegative());
    }

    public function test_isNegative_withPositiveAmount_returnsFalse(): void
    {
        // Arrange
        $money = new Money('1.00', Currency::USD);

        // Act & Assert
        $this->assertFalse($money->isNegative());
    }

    public function test_isZero_withZeroAmount_returnsTrue(): void
    {
        // Arrange
        $money = new Money('0', Currency::USD);

        // Act & Assert
        $this->assertTrue($money->isZero());
    }

    public function test_isZero_withZeroDecimalAmount_returnsTrue(): void
    {
        // Arrange
        $money = new Money('0.00', Currency::USD);

        // Act & Assert
        $this->assertTrue($money->isZero());
    }

    public function test_isZero_withNonZeroAmount_returnsFalse(): void
    {
        // Arrange
        $money = new Money('0.01', Currency::USD);

        // Act & Assert
        $this->assertFalse($money->isZero());
    }
}
