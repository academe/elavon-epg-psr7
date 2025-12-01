<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\GooglePayPayment;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use PHPUnit\Framework\TestCase;

class GooglePayPaymentTest extends TestCase
{
    public function test_construct_withAllFields_createsInstance(): void
    {
        $data = [
            'href' => 'https://api.example.com/googlepaypayments/test123',
            'id' => 'test123',
            'createdAt' => '2025-01-01T00:00:00Z',
            'customReference' => 'ref123',
            'customFields' => ['key1' => 'value1'],
        ];

        $googlepaypayment = GooglePayPayment::fromData($data);

        $this->assertSame('https://api.example.com/googlepaypayments/test123', $googlepaypayment->href);
        $this->assertSame('test123', $googlepaypayment->id);
        $this->assertSame('2025-01-01T00:00:00Z', $googlepaypayment->createdAt);
        $this->assertSame('ref123', $googlepaypayment->customReference);
        $this->assertSame(['key1' => 'value1'], $googlepaypayment->customFields->all());
    }

    public function test_construct_withMinimalFields_createsInstance(): void
    {
        $googlepaypayment = new GooglePayPayment();

        $this->assertNull($googlepaypayment->href);
        $this->assertNull($googlepaypayment->id);
        $this->assertNull($googlepaypayment->customReference);
    }

    public function test_fromData_withValidData_createsInstance(): void
    {
        $data = [
            'id' => 'test456',
            'customReference' => 'ref456',
        ];

        $googlepaypayment = GooglePayPayment::fromData($data);

        $this->assertInstanceOf(GooglePayPayment::class, $googlepaypayment);
        $this->assertSame('test456', $googlepaypayment->id);
        $this->assertSame('ref456', $googlepaypayment->customReference);
    }

    public function test_toData_returnsCorrectArray(): void
    {
        $googlepaypayment = new GooglePayPayment(
            id: 'test789',
            customReference: 'ref789',
            customFields: new CustomFields(['field1' => 'val1']),
        );

        $data = $googlepaypayment->toData();

        $this->assertSame('test789', $data['id']);
        $this->assertSame('ref789', $data['customReference']);
        $this->assertSame(['field1' => 'val1'], $data['customFields']);
    }

    public function test_toData_omitsNullValues(): void
    {
        $googlepaypayment = new GooglePayPayment(
            id: 'test999',
        );

        $data = $googlepaypayment->toData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayNotHasKey('href', $data);
        $this->assertArrayNotHasKey('customReference', $data);
    }

    public function test_roundtrip_serialization(): void
    {
        $original = new GooglePayPayment(
            id: 'roundtrip123',
            customReference: 'ref_roundtrip',
            customFields: new CustomFields(['test' => 'data']),
        );

        $data = $original->toData();
        $restored = GooglePayPayment::fromData($data);

        $this->assertEquals($original->id, $restored->id);
        $this->assertEquals($original->customReference, $restored->customReference);
        $this->assertEquals($original->customFields->all(), $restored->customFields->all());
    }
}
