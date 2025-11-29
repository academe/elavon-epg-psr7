<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Merchant;
use Academe\Elavon\Epg\Psr7\Enums\Region;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Merchant DTO.
 */
class MerchantTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        $merchant = new Merchant();

        $this->assertNull($merchant->href);
        $this->assertNull($merchant->id);
        $this->assertNull($merchant->legalName);
        $this->assertNull($merchant->friendlyName);
        $this->assertNull($merchant->regions);
        $this->assertNull($merchant->isDemo);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        $merchant = new Merchant(
            href: 'https://api.eu.elavonpayments.com/merchants/6xxFwvM8BqmM6T6DcF3DyTB3',
            id: '6xxFwvM8BqmM6T6DcF3DyTB3',
            legalName: 'Sirius Cybernetics Corporation',
            friendlyName: 'Sirius Corp',
            regions: ['eu', 'na'],
            isDemo: true
        );

        $this->assertSame('https://api.eu.elavonpayments.com/merchants/6xxFwvM8BqmM6T6DcF3DyTB3', $merchant->href);
        $this->assertSame('6xxFwvM8BqmM6T6DcF3DyTB3', $merchant->id);
        $this->assertSame('Sirius Cybernetics Corporation', $merchant->legalName);
        $this->assertSame('Sirius Corp', $merchant->friendlyName);
        $this->assertTrue($merchant->isDemo);
        $this->assertIsArray($merchant->regions);
        $this->assertCount(2, $merchant->regions);
        $this->assertContainsOnlyInstancesOf(Region::class, $merchant->regions);
        $this->assertSame(Region::EU, $merchant->regions[0]);
        $this->assertSame(Region::NA, $merchant->regions[1]);
    }

    public function test_construct_withRegionEnums_createsInstance(): void
    {
        $merchant = Merchant::fromData([
            'regions' => [Region::EU, Region::NA]
        ]);

        $this->assertCount(2, $merchant->regions);
        $this->assertSame(Region::EU, $merchant->regions[0]);
        $this->assertSame(Region::NA, $merchant->regions[1]);
    }

    public function test_fromData_withFullData_createsInstance(): void
    {
        $data = [
            'href' => 'https://api.eu.elavonpayments.com/merchants/test123',
            'id' => 'test123',
            'legalName' => 'Test Legal Name',
            'friendlyName' => 'Test Friendly Name',
            'regions' => ['eu'],
            'isDemo' => false,
        ];

        $merchant = Merchant::fromData($data);

        $this->assertSame('test123', $merchant->id);
        $this->assertSame('Test Legal Name', $merchant->legalName);
        $this->assertSame('Test Friendly Name', $merchant->friendlyName);
        $this->assertFalse($merchant->isDemo);
        $this->assertCount(1, $merchant->regions);
        $this->assertSame(Region::EU, $merchant->regions[0]);
    }

    public function test_toData_withFullData_returnsArray(): void
    {
        $merchant = Merchant::fromData([
            'id' => 'merchant-789',
            'legalName' => 'My Company',
            'friendlyName' => 'Company',
            'regions' => [Region::NA],
            'isDemo' => true
        ]);

        $array = $merchant->toData();

        $this->assertArrayHasKey('id', $array);
        $this->assertSame('merchant-789', $array['id']);
        $this->assertSame('My Company', $array['legalName']);
        $this->assertSame('Company', $array['friendlyName']);
        $this->assertTrue($array['isDemo']);
        $this->assertArrayHasKey('regions', $array);
        // For arrays of enums, toData() returns the enum objects, not their string values
        $this->assertIsArray($array['regions']);
        $this->assertCount(1, $array['regions']);
        $this->assertInstanceOf(Region::class, $array['regions'][0]);
        $this->assertSame('na', $array['regions'][0]->value);
    }

    public function test_toData_onlyIncludesNonNullValues(): void
    {
        $merchant = new Merchant(
            id: 'merchant-999',
            legalName: 'Simple Merchant'
        );

        $array = $merchant->toData();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('legalName', $array);
        $this->assertArrayNotHasKey('href', $array);
        $this->assertArrayNotHasKey('friendlyName', $array);
        $this->assertArrayNotHasKey('regions', $array);
        $this->assertArrayNotHasKey('isDemo', $array);
    }
}
