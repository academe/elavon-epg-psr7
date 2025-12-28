<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\PazePayment;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PazePaymentTest extends TestCase
{
    public function test_construct_withAllFields_createsInstance(): void
    {
        $data = [
            'href' => 'https://api.example.com/pazepayments/test123',
            'id' => 'test123',
            'createdAt' => '2025-01-01T00:00:00Z',
            'customReference' => 'ref123',
            'customFields' => ['key1' => 'value1'],
        ];

        $pazepayment = PazePayment::fromData($data);

        $this->assertSame('https://api.example.com/pazepayments/test123', $pazepayment->href);
        $this->assertSame('test123', $pazepayment->id);
        $this->assertInstanceOf(DateTimeImmutable::class, $pazepayment->createdAt);
        $this->assertSame('2025-01-01 00:00:00', $pazepayment->createdAt->format('Y-m-d H:i:s'));
        $this->assertSame('ref123', $pazepayment->customReference);
        $this->assertSame(['key1' => 'value1'], $pazepayment->customFields->all());
    }

    public function test_construct_withMinimalFields_createsInstance(): void
    {
        $pazepayment = new PazePayment();

        $this->assertNull($pazepayment->href);
        $this->assertNull($pazepayment->id);
        $this->assertNull($pazepayment->customReference);
    }

    public function test_fromData_withValidData_createsInstance(): void
    {
        $data = [
            'id' => 'test456',
            'customReference' => 'ref456',
        ];

        $pazepayment = PazePayment::fromData($data);

        $this->assertInstanceOf(PazePayment::class, $pazepayment);
        $this->assertSame('test456', $pazepayment->id);
        $this->assertSame('ref456', $pazepayment->customReference);
    }

    public function test_toData_returnsCorrectArray(): void
    {
        $pazepayment = new PazePayment(
            id: 'test789',
            customReference: 'ref789',
            customFields: new CustomFields(['field1' => 'val1']),
        );

        $data = $pazepayment->toData();

        $this->assertSame('test789', $data['id']);
        $this->assertSame('ref789', $data['customReference']);
        $this->assertSame(['field1' => 'val1'], $data['customFields']);
    }

    public function test_toData_omitsNullValues(): void
    {
        $pazepayment = new PazePayment(
            id: 'test999',
        );

        $data = $pazepayment->toData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayNotHasKey('href', $data);
        $this->assertArrayNotHasKey('customReference', $data);
    }

    public function test_roundtrip_serialization(): void
    {
        $original = new PazePayment(
            id: 'roundtrip123',
            customReference: 'ref_roundtrip',
            customFields: new CustomFields(['test' => 'data']),
        );

        $data = $original->toData();
        $restored = PazePayment::fromData($data);

        $this->assertEquals($original->id, $restored->id);
        $this->assertEquals($original->customReference, $restored->customReference);
        $this->assertEquals($original->customFields->all(), $restored->customFields->all());
    }
}
