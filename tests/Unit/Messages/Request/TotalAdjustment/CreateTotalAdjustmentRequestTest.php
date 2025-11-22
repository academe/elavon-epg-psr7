<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\TotalAdjustment;

use Academe\Elavon\Epg\Psr7\Dtos\TotalAdjustment;
use Academe\Elavon\Epg\Psr7\Enums\Currency;
use Academe\Elavon\Epg\Psr7\Messages\Request\TotalAdjustment\CreateTotalAdjustmentRequest;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

class CreateTotalAdjustmentRequestTest extends TestCase
{
    public function test_construct_withTotalAdjustmentObject_createsInstance(): void
    {
        $totalAdjustment = new TotalAdjustment(
            total: new Money('150.00', Currency::EUR),
            doCapture: true
        );

        $request = new CreateTotalAdjustmentRequest($totalAdjustment);

        $this->assertSame($totalAdjustment, $request->getTotalAdjustment());
    }

    public function test_construct_withArray_createsInstance(): void
    {
        $data = [
            'total' => ['amount' => '100.00', 'currencyCode' => 'EUR'],
            'doCapture' => true,
        ];

        $request = new CreateTotalAdjustmentRequest($data);

        $totalAdjustment = $request->getTotalAdjustment();
        $this->assertInstanceOf(TotalAdjustment::class, $totalAdjustment);
        $this->assertInstanceOf(Money::class, $totalAdjustment->total);
        $this->assertSame('100.00', $totalAdjustment->total->amount);
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $totalAdjustment = new TotalAdjustment(
            total: new Money('200.00', Currency::USD),
            doCapture: false
        );
        $request = new CreateTotalAdjustmentRequest($totalAdjustment);

        $psr7Request = $request->build();

        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertStringContainsString('/total-adjustments', (string) $psr7Request->getUri());
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Accept'));

        // Verify body contains serialized data
        $body = (string) $psr7Request->getBody();
        $decodedBody = json_decode($body, true);
        $this->assertIsArray($decodedBody);
        $this->assertArrayHasKey('total', $decodedBody);
        $this->assertSame('200.00', $decodedBody['total']['amount']);
        $this->assertSame('USD', $decodedBody['total']['currencyCode']);
        $this->assertFalse($decodedBody['doCapture']);
    }
}
