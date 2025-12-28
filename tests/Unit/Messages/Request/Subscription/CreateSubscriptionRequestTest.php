<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Subscription;

use Academe\Elavon\Epg\Psr7\Dtos\Subscription;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Subscription\CreateSubscriptionRequest;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CreateSubscriptionRequest.
 */
class CreateSubscriptionRequestTest extends TestCase
{
    public function test_construct_withValidSubscription_createsInstance(): void
    {
        // Arrange
        $subscription = new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            storedCard: 'https://api.example.com/stored-cards/card123',
            firstBillAt: '2025-01-01',
            timeZoneId: 'Europe/London'
        );

        // Act
        $request = new CreateSubscriptionRequest($subscription);

        // Assert
        $this->assertInstanceOf(CreateSubscriptionRequest::class, $request);
        $this->assertSame($subscription, $request->subscription);
    }

    public function test_fromData_withArrayData_createsInstance(): void
    {
        // Arrange
        $subscriptionData = [
            'plan' => 'https://api.example.com/plans/plan123',
            'storedCard' => 'https://api.example.com/stored-cards/card123',
            'firstBillAt' => '2025-02-01',
            'timeZoneId' => 'America/New_York',
        ];

        // Act
        $request = CreateSubscriptionRequest::fromData(['subscription' => $subscriptionData]);

        // Assert
        $this->assertInstanceOf(Subscription::class, $request->subscription);
        $this->assertSame('2025-02-01', $request->subscription->firstBillAt);
    }

    public function test_construct_withMissingPlan_throwsException(): void
    {
        // Arrange
        $subscription = new Subscription(
            storedCard: 'https://api.example.com/stored-cards/card123',
            firstBillAt: '2025-01-01',
            timeZoneId: 'Europe/London'
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription plan is required for creating a subscription');

        // Act
        new CreateSubscriptionRequest($subscription);
    }

    public function test_construct_withMissingStoredCard_throwsException(): void
    {
        // Arrange
        $subscription = new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            firstBillAt: '2025-01-01',
            timeZoneId: 'Europe/London'
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription storedCard or storedAchPayment is required');

        // Act
        new CreateSubscriptionRequest($subscription);
    }

    public function test_construct_withStoredAchPayment_createsInstance(): void
    {
        // Arrange
        $subscription = new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            storedAchPayment: 'https://api.example.com/stored-ach-payments/ach123',
            firstBillAt: '2025-01-01',
            timeZoneId: 'Europe/London'
        );

        // Act
        $request = new CreateSubscriptionRequest($subscription);

        // Assert
        $this->assertInstanceOf(CreateSubscriptionRequest::class, $request);
    }

    public function test_construct_withMissingFirstBillAt_throwsException(): void
    {
        // Arrange
        $subscription = new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            storedCard: 'https://api.example.com/stored-cards/card123',
            timeZoneId: 'Europe/London'
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription firstBillAt is required for creating a subscription');

        // Act
        new CreateSubscriptionRequest($subscription);
    }

    public function test_construct_withMissingTimeZoneId_throwsException(): void
    {
        // Arrange
        $subscription = new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            storedCard: 'https://api.example.com/stored-cards/card123',
            firstBillAt: '2025-01-01'
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription timeZoneId is required for creating a subscription');

        // Act
        new CreateSubscriptionRequest($subscription);
    }

    public function test_build_createsValidPsr7Request(): void
    {
        // Arrange
        $subscription = new Subscription(
            plan: 'https://api.example.com/plans/plan123',
            storedCard: 'https://api.example.com/stored-cards/card123',
            firstBillAt: '2025-03-01',
            timeZoneId: 'UTC',
            customReference: 'SUB-001'
        );
        $createRequest = new CreateSubscriptionRequest($subscription);

        // Act
        $psrRequest = $createRequest->build();

        // Assert
        $this->assertSame('POST', $psrRequest->getMethod());
        $this->assertStringContainsString('/subscriptions', (string) $psrRequest->getUri());

        // Verify JSON body
        $body = (string) $psrRequest->getBody();
        $data = json_decode($body, true);
        $this->assertSame('https://api.example.com/plans/plan123', $data['plan']);
        $this->assertSame('2025-03-01', $data['firstBillAt']);
        $this->assertSame('UTC', $data['timeZoneId']);
        $this->assertSame('SUB-001', $data['customReference']);
    }
}
