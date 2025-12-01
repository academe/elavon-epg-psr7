<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\ApplePayPayment;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use PHPUnit\Framework\TestCase;

class ApplePayPaymentTest extends TestCase
{
    public function test_construct_withAllFields_createsInstance(): void
    {
        $data = [
            'href' => 'https://api.example.com/applepaypayments/test123',
            'id' => 'test123',
            'createdAt' => '2025-01-01T00:00:00Z',
            'customReference' => 'ref123',
            'customFields' => ['key1' => 'value1'],
        ];

        $applepaypayment = ApplePayPayment::fromData($data);

        $this->assertSame('https://api.example.com/applepaypayments/test123', $applepaypayment->href);
        $this->assertSame('test123', $applepaypayment->id);
        $this->assertSame('2025-01-01T00:00:00Z', $applepaypayment->createdAt);
        $this->assertSame('ref123', $applepaypayment->customReference);
        $this->assertSame(['key1' => 'value1'], $applepaypayment->customFields->all());
    }

    public function test_construct_withMinimalFields_createsInstance(): void
    {
        $applepaypayment = new ApplePayPayment();

        $this->assertNull($applepaypayment->href);
        $this->assertNull($applepaypayment->id);
        $this->assertNull($applepaypayment->customReference);
    }

    public function test_fromData_withValidData_createsInstance(): void
    {
        $data = [
            'id' => 'test456',
            'customReference' => 'ref456',
        ];

        $applepaypayment = ApplePayPayment::fromData($data);

        $this->assertInstanceOf(ApplePayPayment::class, $applepaypayment);
        $this->assertSame('test456', $applepaypayment->id);
        $this->assertSame('ref456', $applepaypayment->customReference);
    }

    public function test_toData_returnsCorrectArray(): void
    {
        $applepaypayment = new ApplePayPayment(
            id: 'test789',
            customReference: 'ref789',
            customFields: new CustomFields(['field1' => 'val1']),
        );

        $data = $applepaypayment->toData();

        $this->assertSame('test789', $data['id']);
        $this->assertSame('ref789', $data['customReference']);
        $this->assertSame(['field1' => 'val1'], $data['customFields']);
    }

    public function test_toData_omitsNullValues(): void
    {
        $applepaypayment = new ApplePayPayment(
            id: 'test999',
        );

        $data = $applepaypayment->toData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayNotHasKey('href', $data);
        $this->assertArrayNotHasKey('customReference', $data);
    }

    public function test_roundtrip_serialization(): void
    {
        $original = new ApplePayPayment(
            id: 'roundtrip123',
            customReference: 'ref_roundtrip',
            customFields: new CustomFields(['test' => 'data']),
        );

        $data = $original->toData();
        $restored = ApplePayPayment::fromData($data);

        $this->assertEquals($original->id, $restored->id);
        $this->assertEquals($original->customReference, $restored->customReference);
        $this->assertEquals($original->customFields->all(), $restored->customFields->all());
    }
}
