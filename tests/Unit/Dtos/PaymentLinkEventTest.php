<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLinkEvent;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for PaymentLinkEvent DTO.
 */
class PaymentLinkEventTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        // Arrange & Act
        $event = new PaymentLinkEvent(
            type: 'payment',
        );

        // Assert
        $this->assertSame('payment', $event->type);
        $this->assertNull($event->id);
        $this->assertNull($event->paymentLink);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $event = new PaymentLinkEvent(
            href: 'https://api.example.com/payment-link-events/e123',
            id: 'e123',
            merchant: 'https://api.example.com/merchants/m123',
            createdAt: '2025-11-19T10:00:00Z',
            transaction: 'https://api.example.com/transactions/t123',
            paymentLink: 'https://api.example.com/payment-links/pl123',
            type: 'payment',
            createdBy: 'user@example.com',
            shopperEmailAddress: 'shopper@example.com',
        );

        // Assert
        $this->assertSame('https://api.example.com/payment-link-events/e123', $event->href);
        $this->assertSame('e123', $event->id);
        $this->assertSame('https://api.example.com/merchants/m123', $event->merchant);
        $this->assertSame('2025-11-19T10:00:00Z', $event->createdAt);
        $this->assertSame('https://api.example.com/transactions/t123', $event->transaction);
        $this->assertSame('https://api.example.com/payment-links/pl123', $event->paymentLink);
        $this->assertSame('payment', $event->type);
        $this->assertSame('user@example.com', $event->createdBy);
        $this->assertSame('shopper@example.com', $event->shopperEmailAddress);
    }

    public function test_construct_withInvalidType_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid event type: invalid_type');

        // Act
        new PaymentLinkEvent(
            type: 'invalid_type',
        );
    }

    public function test_construct_withValidTypes_createsInstance(): void
    {
        // Arrange & Act
        $paymentEvent = new PaymentLinkEvent(type: 'payment');
        $reminderEvent = new PaymentLinkEvent(type: 'reminderSent');
        $unknownEvent = new PaymentLinkEvent(type: 'unknown');

        // Assert
        $this->assertSame('payment', $paymentEvent->type);
        $this->assertSame('reminderSent', $reminderEvent->type);
        $this->assertSame('unknown', $unknownEvent->type);
    }

    public function test_construct_withTooLongCreatedBy_throwsException(): void
    {
        // Arrange
        $longCreatedBy = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Created by must not exceed 255 characters');

        // Act
        new PaymentLinkEvent(
            type: 'payment',
            createdBy: $longCreatedBy,
        );
    }

    public function test_fromData_withMinimalData_createsInstance(): void
    {
        // Arrange
        $data = [
            'type' => 'reminderSent',
        ];

        // Act
        $event = PaymentLinkEvent::fromData($data);

        // Assert
        $this->assertSame('reminderSent', $event->type);
        $this->assertNull($event->id);
    }

    public function test_fromData_withFullData_createsInstance(): void
    {
        // Arrange
        $data = [
            'href' => 'https://api.example.com/payment-link-events/e123',
            'id' => 'e123',
            'merchant' => 'https://api.example.com/merchants/m123',
            'paymentLink' => 'https://api.example.com/payment-links/pl123',
            'type' => 'payment',
            'createdAt' => '2025-11-19T10:00:00Z',
            'createdBy' => 'user@example.com',
            'transaction' => 'https://api.example.com/transactions/t123',
            'shopperEmailAddress' => 'shopper@example.com',
        ];

        // Act
        $event = PaymentLinkEvent::fromData($data);

        // Assert
        $this->assertSame('e123', $event->id);
        $this->assertSame('payment', $event->type);
        $this->assertSame('user@example.com', $event->createdBy);
        $this->assertSame('shopper@example.com', $event->shopperEmailAddress);
    }

    public function test_toData_withMinimalData_returnsArray(): void
    {
        // Arrange
        $event = new PaymentLinkEvent(
            type: 'payment',
        );

        // Act
        $array = $event->toData();

        // Assert
        $this->assertSame([
            'type' => 'payment',
        ], $array);
    }

    public function test_toData_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $event = new PaymentLinkEvent(
            type: 'reminderSent',
            shopperEmailAddress: 'test@example.com',
        );

        // Act
        $array = $event->toData();

        // Assert
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('shopperEmailAddress', $array);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('href', $array);
        $this->assertArrayNotHasKey('transaction', $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'type' => 'reminderSent',
            'paymentLink' => 'https://api.example.com/payment-links/pl123',
            'createdBy' => 'system@example.com',
            'shopperEmailAddress' => 'shopper@example.com',
        ];

        // Act
        $event = PaymentLinkEvent::fromData($originalData);
        $resultData = $event->toData();

        // Assert
        $this->assertSame($originalData['type'], $resultData['type']);
        $this->assertSame($originalData['paymentLink'], $resultData['paymentLink']);
        $this->assertSame($originalData['createdBy'], $resultData['createdBy']);
        $this->assertSame($originalData['shopperEmailAddress'], $resultData['shopperEmailAddress']);
    }
}
