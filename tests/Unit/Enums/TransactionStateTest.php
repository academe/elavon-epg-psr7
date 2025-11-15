<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\TransactionState;
use PHPUnit\Framework\TestCase;

/**
 * Tests for TransactionState enum.
 */
class TransactionStateTest extends TestCase
{
    public function test_enumCases_containsAllExpectedStates(): void
    {
        // Arrange & Act
        $cases = TransactionState::cases();

        // Assert
        $this->assertCount(9, $cases);
        $this->assertContains(TransactionState::AUTHORIZATION_PENDING, $cases);
        $this->assertContains(TransactionState::AUTHORIZED, $cases);
        $this->assertContains(TransactionState::DECLINED, $cases);
        $this->assertContains(TransactionState::CAPTURED, $cases);
        $this->assertContains(TransactionState::SETTLED, $cases);
        $this->assertContains(TransactionState::REFUNDED, $cases);
        $this->assertContains(TransactionState::VOIDED, $cases);
        $this->assertContains(TransactionState::FAILED, $cases);
        $this->assertContains(TransactionState::UNKNOWN, $cases);
    }

    public function test_enumValues_matchApiSpecification(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('authorizationPending', TransactionState::AUTHORIZATION_PENDING->value);
        $this->assertSame('authorized', TransactionState::AUTHORIZED->value);
        $this->assertSame('declined', TransactionState::DECLINED->value);
        $this->assertSame('captured', TransactionState::CAPTURED->value);
        $this->assertSame('settled', TransactionState::SETTLED->value);
        $this->assertSame('refunded', TransactionState::REFUNDED->value);
        $this->assertSame('voided', TransactionState::VOIDED->value);
        $this->assertSame('failed', TransactionState::FAILED->value);
        $this->assertSame('unknown', TransactionState::UNKNOWN->value);
    }

    public function test_tryFrom_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'authorized';

        // Act
        $state = TransactionState::tryFrom($value);

        // Assert
        $this->assertSame(TransactionState::AUTHORIZED, $state);
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        // Arrange
        $value = 'invalid_state';

        // Act
        $state = TransactionState::tryFrom($value);

        // Assert
        $this->assertNull($state);
    }

    public function test_from_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'declined';

        // Act
        $state = TransactionState::from($value);

        // Assert
        $this->assertSame(TransactionState::DECLINED, $state);
    }

    public function test_from_withInvalidValue_throwsValueError(): void
    {
        // Arrange
        $value = 'nonexistent_state';

        // Assert
        $this->expectException(\ValueError::class);

        // Act
        TransactionState::from($value);
    }

    public function test_enumCases_areBackedByStrings(): void
    {
        // Arrange & Act
        $cases = TransactionState::cases();

        // Assert
        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }
}