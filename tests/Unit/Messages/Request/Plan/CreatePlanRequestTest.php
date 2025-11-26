<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Plan;

use Academe\Elavon\Epg\Psr7\Dtos\Plan;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Plan\CreatePlanRequest;
use Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CreatePlanRequest.
 */
class CreatePlanRequestTest extends TestCase
{
    public function test_construct_withValidPlan_createsInstance(): void
    {
        // Arrange
        $plan = new Plan(
            name: 'Monthly License',
            billingInterval: ['timeUnit' => 'month', 'count' => 1],
            total: Money::USD(2999)
        );

        // Act
        $request = new CreatePlanRequest($plan);

        // Assert
        $this->assertInstanceOf(CreatePlanRequest::class, $request);
        $this->assertSame($plan, $request->getPlan());
    }

    public function test_construct_withArrayData_createsInstance(): void
    {
        // Arrange
        $planData = [
            'name' => 'Annual Plan',
            'billingInterval' => ['timeUnit' => 'year', 'count' => 1],
            'total' => ['amount' => '299.99', 'currencyCode' => 'USD'],
        ];

        // Act
        $request = new CreatePlanRequest($planData);

        // Assert
        $this->assertInstanceOf(Plan::class, $request->getPlan());
        $this->assertSame('Annual Plan', $request->getPlan()->name);
    }

    public function test_construct_withMissingName_throwsException(): void
    {
        // Arrange
        $plan = new Plan(
            billingInterval: ['timeUnit' => 'month', 'count' => 1],
            total: Money::USD(2999)
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Plan name is required for creating a plan');

        // Act
        new CreatePlanRequest($plan);
    }

    public function test_construct_withMissingBillingInterval_throwsException(): void
    {
        // Arrange
        $plan = new Plan(
            name: 'Test Plan',
            total: Money::USD(2999)
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Plan billingInterval is required for creating a plan');

        // Act
        new CreatePlanRequest($plan);
    }

    public function test_construct_withMissingTotal_throwsException(): void
    {
        // Arrange
        $plan = new Plan(
            name: 'Test Plan',
            billingInterval: ['timeUnit' => 'month', 'count' => 1]
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Plan total is required for creating a plan');

        // Act
        new CreatePlanRequest($plan);
    }

    public function test_build_createsValidPsr7Request(): void
    {
        // Arrange
        $plan = new Plan(
            name: 'Weekly Plan',
            description: 'Billed weekly',
            billingInterval: ['timeUnit' => 'week', 'count' => 1],
            total: Money::GBP(999)
        );
        $createRequest = new CreatePlanRequest($plan);

        // Act
        $psrRequest = $createRequest->build();

        // Assert
        $this->assertSame('POST', $psrRequest->getMethod());
        $this->assertStringContainsString('/plans', (string) $psrRequest->getUri());

        // Verify JSON body
        $body = (string) $psrRequest->getBody();
        $data = json_decode($body, true);
        $this->assertSame('Weekly Plan', $data['name']);
        $this->assertSame('Billed weekly', $data['description']);
        $this->assertSame('9.99', $data['total']['amount']);
    }
}
