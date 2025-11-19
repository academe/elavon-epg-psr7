<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Subscription;
use Academe\Elavon\Epg\Psr7\Enums\SubscriptionState;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Subscription DTO.
 */
class SubscriptionTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        // Arrange & Act
        $subscription = new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            storedCard: 'https://api.example.com/stored-cards/card123',
            firstBillAt: '2025-01-01',
            timeZoneId: 'Europe/London'
        );

        // Assert
        $this->assertSame('https://api.example.com/plans/plan123', $subscription->plan);
        $this->assertSame('https://api.example.com/stored-cards/card123', $subscription->storedCard);
        $this->assertSame('2025-01-01', $subscription->firstBillAt);
        $this->assertSame('Europe/London', $subscription->timeZoneId);
        $this->assertNull($subscription->id);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $subscription = new Subscription(
            href: 'https://api.example.com/subscriptions/sub123',
            id: 'sub123',
            createdAt: '2025-11-19T10:00:00Z',
            modifiedAt: '2025-11-19T11:00:00Z',
            merchant: 'https://api.example.com/merchants/m123',
            shopper: 'https://api.example.com/shoppers/sh123',
            plan: 'https://api.example.com/plans/plan123',
            storedCard: 'https://api.example.com/stored-cards/card123',
            firstBillAt: '2025-01-01',
            timeZoneId: 'America/New_York',
            billCount: 12,
            doSendReceipt: 'DEFAULT',
            subscriptionState: SubscriptionState::ACTIVE,
            customReference: 'SUB-001'
        );

        // Assert
        $this->assertSame('sub123', $subscription->id);
        $this->assertSame(12, $subscription->billCount);
        $this->assertSame('DEFAULT', $subscription->doSendReceipt);
        $this->assertSame(SubscriptionState::ACTIVE, $subscription->subscriptionState);
        $this->assertSame('SUB-001', $subscription->customReference);
    }

    public function test_construct_withDoSendReceiptTrue_createsInstance(): void
    {
        // Arrange & Act
        $subscription = new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            storedCard: 'https://api.example.com/stored-cards/card123',
            firstBillAt: '2025-01-01',
            timeZoneId: 'Europe/London',
            doSendReceipt: true
        );

        // Assert
        $this->assertTrue($subscription->doSendReceipt);
    }

    public function test_construct_withInvalidDoSendReceipt_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('doSendReceipt must be true, false, or "DEFAULT"');

        // Act
        new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            storedCard: 'https://api.example.com/stored-cards/card123',
            firstBillAt: '2025-01-01',
            timeZoneId: 'Europe/London',
            doSendReceipt: 'invalid'
        );
    }

    public function test_construct_withZeroBillCount_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bill count must be at least 1');

        // Act
        new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            storedCard: 'https://api.example.com/stored-cards/card123',
            firstBillAt: '2025-01-01',
            timeZoneId: 'Europe/London',
            billCount: 0
        );
    }

    public function test_construct_withInvalidFirstBillAtFormat_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('First bill date must be in YYYY-MM-DD format');

        // Act
        new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            storedCard: 'https://api.example.com/stored-cards/card123',
            firstBillAt: '01/01/2025',
            timeZoneId: 'Europe/London'
        );
    }

    public function test_construct_withInvalidSubscriptionState_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription state must be one of');

        // Act
        new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            storedCard: 'https://api.example.com/stored-cards/card123',
            firstBillAt: '2025-01-01',
            timeZoneId: 'Europe/London',
            subscriptionState: 'invalid'
        );
    }

    public function test_fromData_createsInstance(): void
    {
        // Arrange
        $data = [
            'plan' => 'https://api.example.com/plans/plan123',
            'storedCard' => 'https://api.example.com/stored-cards/card123',
            'firstBillAt' => '2025-02-01',
            'timeZoneId' => 'UTC',
        ];

        // Act
        $subscription = Subscription::fromData($data);

        // Assert
        $this->assertSame('https://api.example.com/plans/plan123', $subscription->plan);
        $this->assertSame('2025-02-01', $subscription->firstBillAt);
        $this->assertSame('UTC', $subscription->timeZoneId);
    }

    public function test_toData_returnsArrayWithoutNullValues(): void
    {
        // Arrange
        $subscription = new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            storedCard: 'https://api.example.com/stored-cards/card123',
            firstBillAt: '2025-01-01',
            timeZoneId: 'Europe/London',
            customReference: 'REF-123'
        );

        // Act
        $data = $subscription->toData();

        // Assert
        $this->assertArrayHasKey('plan', $data);
        $this->assertArrayHasKey('storedCard', $data);
        $this->assertArrayHasKey('firstBillAt', $data);
        $this->assertArrayHasKey('timeZoneId', $data);
        $this->assertArrayHasKey('customReference', $data);
        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayNotHasKey('href', $data);
    }

    public function test_toData_withSubscriptionStateEnum_convertsToString(): void
    {
        // Arrange
        $subscription = new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            storedCard: 'https://api.example.com/stored-cards/card123',
            firstBillAt: '2025-01-01',
            timeZoneId: 'Europe/London',
            subscriptionState: SubscriptionState::CANCELLED
        );

        // Act
        $data = $subscription->toData();

        // Assert
        $this->assertSame('cancelled', $data['subscriptionState']);
    }
}
