<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use PHPUnit\Framework\TestCase;

class ShopperTest extends TestCase
{
    public function test_construct_withAllFields_createsInstance(): void
    {
        $data = [
            'href' => 'https://api.example.com/shoppers/test123',
            'id' => 'test123',
            'createdAt' => '2025-01-01T00:00:00Z',
            'customReference' => 'ref123',
            'customFields' => ['key1' => 'value1'],
        ];

        $shopper = Shopper::fromData($data);

        $this->assertSame('https://api.example.com/shoppers/test123', $shopper->href);
        $this->assertSame('test123', $shopper->id);
        $this->assertSame('2025-01-01T00:00:00Z', $shopper->createdAt);
        $this->assertSame('ref123', $shopper->customReference);
        $this->assertSame(['key1' => 'value1'], $shopper->customFields->all());
    }

    public function test_construct_withMinimalFields_createsInstance(): void
    {
        $shopper = new Shopper();

        $this->assertNull($shopper->href);
        $this->assertNull($shopper->id);
        $this->assertNull($shopper->customReference);
    }

    public function test_fromData_withValidData_createsInstance(): void
    {
        $data = [
            'id' => 'test456',
            'customReference' => 'ref456',
        ];

        $shopper = Shopper::fromData($data);

        $this->assertInstanceOf(Shopper::class, $shopper);
        $this->assertSame('test456', $shopper->id);
        $this->assertSame('ref456', $shopper->customReference);
    }

    public function test_toData_returnsCorrectArray(): void
    {
        $shopper = new Shopper(
            id: 'test789',
            customReference: 'ref789',
            customFields: new CustomFields(['field1' => 'val1']),
        );

        $data = $shopper->toData();

        $this->assertSame('test789', $data['id']);
        $this->assertSame('ref789', $data['customReference']);
        $this->assertSame(['field1' => 'val1'], $data['customFields']);
    }

    public function test_toData_omitsNullValues(): void
    {
        $shopper = new Shopper(
            id: 'test999',
        );

        $data = $shopper->toData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayNotHasKey('href', $data);
        $this->assertArrayNotHasKey('customReference', $data);
    }

    public function test_roundtrip_serialization(): void
    {
        $original = new Shopper(
            id: 'roundtrip123',
            customReference: 'ref_roundtrip',
            customFields: new CustomFields(['test' => 'data']),
        );

        $data = $original->toData();
        $restored = Shopper::fromData($data);

        $this->assertEquals($original->id, $restored->id);
        $this->assertEquals($original->customReference, $restored->customReference);
        $this->assertEquals($original->customFields->all(), $restored->customFields->all());
    }
}
