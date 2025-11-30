<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Address;
use Academe\Elavon\Epg\Psr7\Dtos\ProcessorAccount;
use Academe\Elavon\Epg\Psr7\Enums\CardBrand;
use Academe\Elavon\Epg\Psr7\Enums\MarketSegment;
use Academe\Elavon\Epg\Psr7\Enums\PaymentMethod;
use Academe\Elavon\Epg\Psr7\Enums\PaymentMethodOrigin;
use Academe\Elavon\Epg\Psr7\Enums\Region;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ProcessorAccount DTO.
 */
class ProcessorAccountTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        // Arrange & Act
        $processorAccount = new ProcessorAccount();

        // Assert
        $this->assertNull($processorAccount->href);
        $this->assertNull($processorAccount->id);
        $this->assertNull($processorAccount->merchant);
        $this->assertNull($processorAccount->processorReference);
        $this->assertNull($processorAccount->legalName);
        $this->assertNull($processorAccount->friendlyName);
        $this->assertNull($processorAccount->businessAddress);
        $this->assertNull($processorAccount->marketSegment);
        $this->assertNull($processorAccount->region);
        $this->assertNull($processorAccount->supportedCardBrands);
        $this->assertNull($processorAccount->supportedPaymentMethods);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $processorAccount = ProcessorAccount::fromData([
            'href' => 'https://api.eu.elavonpayments.com/processor-accounts/123',
            'id' => '6xxFwvM8BqmM6T6DcF3DyTB3',
            'merchant' => 'https://api.eu.elavonpayments.com/merchants/456',
            'processorReference' => 'PROC-REF-789',
            'legalName' => 'Sirius Cybernetics Corporation',
            'friendlyName' => 'Sirius Corp',
            'tradeName' => 'Gringotts',
            'businessAddress' => [
                'street1' => '123 Main St',
                'city' => 'London',
                'country' => 'GB',
            ],
            'businessPhone' => '+44 020 7946 0123',
            'businessEmail' => 'sales@gringotts.com',
            'businessWebsite' => 'www.gringotts.com',
            'merchantCategoryCode' => '8734',
            'marketSegment' => 'retail',
            'region' => 'eu',
            'settlementCurrencyCode' => 'EUR',
            'languageTag' => 'en-GB',
            'supportedCardBrands' => ['Visa', 'MasterCard'],
            'supportedPaymentMethods' => ['Card'],
            'supportedPaymentMethodOrigins' => ['Card'],
            'isDccEnabled' => true,
            'isMccEnabled' => false,
            'isStandaloneRefundEnabled' => true
        ]);

        // Assert
        $this->assertSame('https://api.eu.elavonpayments.com/processor-accounts/123', $processorAccount->href);
        $this->assertSame('6xxFwvM8BqmM6T6DcF3DyTB3', $processorAccount->id);
        $this->assertSame('Sirius Cybernetics Corporation', $processorAccount->legalName);
        $this->assertInstanceOf(Address::class, $processorAccount->businessAddress);
        $this->assertSame('123 Main St', $processorAccount->businessAddress->street1);
        $this->assertSame(MarketSegment::RETAIL, $processorAccount->marketSegment);
        $this->assertSame(Region::EU, $processorAccount->region);
        $this->assertIsArray($processorAccount->supportedCardBrands);
        $this->assertCount(2, $processorAccount->supportedCardBrands);
        $this->assertContainsOnlyInstancesOf(CardBrand::class, $processorAccount->supportedCardBrands);
        $this->assertTrue($processorAccount->isDccEnabled);
        $this->assertFalse($processorAccount->isMccEnabled);
    }

    public function test_construct_withEnumObjects_createsInstance(): void
    {
        // Arrange
        $marketSegment = MarketSegment::RESTAURANT;
        $region = Region::NA;

        // Act
        $processorAccount = new ProcessorAccount(
            marketSegment: $marketSegment,
            region: $region,
            supportedCardBrands: [CardBrand::VISA, CardBrand::MASTERCARD],
            supportedPaymentMethods: [PaymentMethod::CARD],
            supportedPaymentMethodOrigins: [PaymentMethodOrigin::CARD]
        );

        // Assert
        $this->assertSame($marketSegment, $processorAccount->marketSegment);
        $this->assertSame($region, $processorAccount->region);
        $this->assertCount(2, $processorAccount->supportedCardBrands);
        $this->assertSame(CardBrand::VISA, $processorAccount->supportedCardBrands[0]);
        $this->assertSame(CardBrand::MASTERCARD, $processorAccount->supportedCardBrands[1]);
    }

    public function test_fromData_withMinimalData_createsInstance(): void
    {
        // Arrange
        $data = [];

        // Act
        $processorAccount = ProcessorAccount::fromData($data);

        // Assert
        $this->assertNull($processorAccount->id);
        $this->assertNull($processorAccount->legalName);
    }

    public function test_fromData_withFullData_createsInstance(): void
    {
        // Arrange
        $data = [
            'href' => 'https://api.eu.elavonpayments.com/processor-accounts/abc',
            'id' => 'KmvmfQJpCBJpXHyP2kgrK2hD',
            'merchant' => 'https://api.eu.elavonpayments.com/merchants/xyz',
            'processorReference' => 'PROC-123',
            'legalName' => 'Test Corporation Ltd',
            'friendlyName' => 'Test Corp',
            'tradeName' => 'TestCo',
            'businessAddress' => [
                'street1' => '10 Test Street',
                'city' => 'Berlin',
                'country' => 'DE',
            ],
            'businessPhone' => '+49 30 1234567',
            'businessEmail' => 'info@test.com',
            'businessWebsite' => 'www.test.com',
            'merchantCategoryCode' => '5999',
            'marketSegment' => 'retail',
            'region' => 'eu',
            'settlementCurrencyCode' => 'EUR',
            'supportedCardBrands' => ['Visa', 'MasterCard', 'American Express'],
            'supportedPaymentMethods' => ['Card', 'ACH'],
            'supportedPaymentMethodOrigins' => ['Card', 'Apple Pay'],
            'isDccEnabled' => false,
            'isMccEnabled' => true,
            'isStandaloneRefundEnabled' => false,
        ];

        // Act
        $processorAccount = ProcessorAccount::fromData($data);

        // Assert
        $this->assertSame('KmvmfQJpCBJpXHyP2kgrK2hD', $processorAccount->id);
        $this->assertSame('Test Corporation Ltd', $processorAccount->legalName);
        $this->assertSame(MarketSegment::RETAIL, $processorAccount->marketSegment);
        $this->assertSame(Region::EU, $processorAccount->region);
        $this->assertInstanceOf(Address::class, $processorAccount->businessAddress);
        $this->assertSame('10 Test Street', $processorAccount->businessAddress->street1);
        $this->assertCount(3, $processorAccount->supportedCardBrands);
        $this->assertSame(CardBrand::VISA, $processorAccount->supportedCardBrands[0]);
        $this->assertFalse($processorAccount->isDccEnabled);
        $this->assertTrue($processorAccount->isMccEnabled);
    }

    public function test_toData_withMinimalData_returnsArray(): void
    {
        // Arrange
        $processorAccount = new ProcessorAccount();

        // Act
        $array = $processorAccount->toData();

        // Assert
        $this->assertSame([], $array);
    }

    public function test_toData_withFullData_returnsArray(): void
    {
        // Arrange
        $processorAccount = ProcessorAccount::fromData([
            'id' => 'proc-123',
            'legalName' => 'My Company',
            'marketSegment' => 'retail',
            'region' => 'eu',
            'isDccEnabled' => true
        ]);

        // Act
        $array = $processorAccount->toData();

        // Assert
        $this->assertArrayHasKey('id', $array);
        $this->assertSame('proc-123', $array['id']);
        $this->assertSame('My Company', $array['legalName']);
        $this->assertSame('retail', $array['marketSegment']);
        $this->assertSame('eu', $array['region']);
        $this->assertTrue($array['isDccEnabled']);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'id' => 'proc-xyz',
            'legalName' => 'Round Trip Test',
            'marketSegment' => 'restaurant',
            'region' => 'na',
            'supportedCardBrands' => ['Visa'],
            'isDccEnabled' => false,
        ];

        // Act
        $processorAccount = ProcessorAccount::fromData($originalData);
        $resultData = $processorAccount->toData();

        // Assert
        $this->assertSame($originalData['id'], $resultData['id']);
        $this->assertSame($originalData['legalName'], $resultData['legalName']);
        $this->assertSame($originalData['marketSegment'], $resultData['marketSegment']);
        $this->assertSame($originalData['region'], $resultData['region']);
        $this->assertIsArray($resultData['supportedCardBrands']);
        $this->assertCount(1, $resultData['supportedCardBrands']);
        $this->assertSame('Visa', $resultData['supportedCardBrands'][0]);
        $this->assertFalse($resultData['isDccEnabled']);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $processorAccount = new ProcessorAccount(id: 'test-id');

        // Act & Assert
        $reflection = new \ReflectionProperty($processorAccount, 'id');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($processorAccount, 'legalName');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($processorAccount, 'marketSegment');
        $this->assertTrue($reflection->isReadOnly());
    }
}
