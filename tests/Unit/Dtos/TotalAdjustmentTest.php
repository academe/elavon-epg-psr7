<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Failure;
use Academe\Elavon\Epg\Psr7\Dtos\TotalAdjustment;
use Academe\Elavon\Epg\Psr7\Enums\Currency;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for TotalAdjustment DTO.
 */
class TotalAdjustmentTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        $totalAdjustment = new TotalAdjustment();

        $this->assertNull($totalAdjustment->href);
        $this->assertNull($totalAdjustment->id);
        $this->assertNull($totalAdjustment->transaction);
        $this->assertNull($totalAdjustment->total);
        $this->assertNull($totalAdjustment->isAuthorized);
    }

    public function test_construct_withMoneyObjects_createsInstance(): void
    {
        $total = new Money('150.00', Currency::EUR);
        $totalAdjustment = new Money('50.00', Currency::EUR);

        $adjustment = new TotalAdjustment(
            total: $total,
            totalAdjustment: $totalAdjustment
        );

        $this->assertSame($total, $adjustment->total);
        $this->assertSame($totalAdjustment, $adjustment->totalAdjustment);
    }

    public function test_construct_withMoneyArrays_createsMoneyObjects(): void
    {
        $adjustment = new TotalAdjustment(
            total: ['amount' => '100.00', 'currencyCode' => 'EUR'],
            tip: ['amount' => '10.00', 'currencyCode' => 'EUR']
        );

        $this->assertInstanceOf(Money::class, $adjustment->total);
        $this->assertSame('100.00', $adjustment->total->amount);
        $this->assertSame(Currency::EUR, $adjustment->total->currency);
        $this->assertInstanceOf(Money::class, $adjustment->tip);
        $this->assertSame('10.00', $adjustment->tip->amount);
    }

    public function test_construct_withFailures_createsFailureObjects(): void
    {
        $adjustment = new TotalAdjustment(
            failures: [
                ['code' => 'invalid_amount', 'description' => 'Amount too high'],
            ]
        );

        $this->assertIsArray($adjustment->failures);
        $this->assertCount(1, $adjustment->failures);
        $this->assertInstanceOf(Failure::class, $adjustment->failures[0]);
        $this->assertSame('invalid_amount', $adjustment->failures[0]->code);
    }

    public function test_fromData_withFullData_createsInstance(): void
    {
        $data = [
            'href' => 'https://api.eu.elavonpayments.com/total-adjustments/adj123',
            'id' => 'adj123',
            'transaction' => 'https://api.eu.elavonpayments.com/transactions/txn456',
            'createdAt' => '2025-02-22T13:01:23.123Z',
            'total' => ['amount' => '150.00', 'currencyCode' => 'EUR'],
            'totalAdjustment' => ['amount' => '50.00', 'currencyCode' => 'EUR'],
            'tip' => ['amount' => '15.00', 'currencyCode' => 'EUR'],
            'doCapture' => true,
            'isAuthorized' => true,
            'authorizationCode' => 'AUTH123',
            'customReference' => 'adj-ref-001',
        ];

        $adjustment = TotalAdjustment::fromData($data);

        $this->assertSame('adj123', $adjustment->id);
        $this->assertInstanceOf(Money::class, $adjustment->total);
        $this->assertSame('150.00', $adjustment->total->amount);
        $this->assertInstanceOf(Money::class, $adjustment->totalAdjustment);
        $this->assertSame('50.00', $adjustment->totalAdjustment->amount);
        $this->assertTrue($adjustment->doCapture);
        $this->assertTrue($adjustment->isAuthorized);
        $this->assertSame('AUTH123', $adjustment->authorizationCode);
    }

    public function test_toData_withMoneyObjects_serializesCorrectly(): void
    {
        $adjustment = new TotalAdjustment(
            id: 'adj-999',
            total: new Money('200.00', Currency::USD),
            tip: new Money('20.00', Currency::USD),
            isAuthorized: true
        );

        $array = $adjustment->toData();

        $this->assertArrayHasKey('id', $array);
        $this->assertSame('adj-999', $array['id']);
        $this->assertArrayHasKey('total', $array);
        $this->assertSame('200.00', $array['total']['amount']);
        $this->assertSame('USD', $array['total']['currencyCode']);
        $this->assertArrayHasKey('tip', $array);
        $this->assertSame('20.00', $array['tip']['amount']);
        $this->assertTrue($array['isAuthorized']);
    }
}
