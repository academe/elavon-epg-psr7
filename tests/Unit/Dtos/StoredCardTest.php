<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Card;
use Academe\Elavon\Epg\Psr7\Dtos\StoredCard;
use Academe\Elavon\Epg\Psr7\Enums\CredentialOnFileType;
use Academe\Elavon\Epg\Psr7\Enums\ShopperInteraction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for StoredCard DTO.
 */
class StoredCardTest extends TestCase
{
    public function test_construct_withCardObject_createsInstance(): void
    {
        // Arrange
        $card = new Card(
            last4: '1111',
            bin: '411111',
        );

        // Act
        $storedCard = new StoredCard(card: $card);

        // Assert
        $this->assertSame($card, $storedCard->card);
    }

    public function test_construct_withCardArray_normalizesToCardObject(): void
    {
        // Arrange
        $cardData = [
            'last4' => '4444',
            'bin' => '555555',
        ];

        // Act
        $storedCard = StoredCard::fromData(['card' => $cardData]);

        // Assert
        $this->assertInstanceOf(Card::class, $storedCard->card);
        $this->assertSame('4444', $storedCard->card->last4);
        $this->assertSame('555555', $storedCard->card->bin);
    }

    public function test_construct_withNullCard_createsInstance(): void
    {
        // Act
        $storedCard = new StoredCard(card: null);

        // Assert
        $this->assertNull($storedCard->card);
    }

    public function test_construct_withRequestFields_createsInstance(): void
    {
        // Act
        $storedCard = new StoredCard(
            shopper: 'https://api.example.com/shoppers/s123',
            hostedCard: 'https://api.example.com/hosted-cards/hc456',
        );

        // Assert
        $this->assertSame('https://api.example.com/shoppers/s123', $storedCard->shopper);
        $this->assertSame('https://api.example.com/hosted-cards/hc456', $storedCard->hostedCard);
    }

    public function test_construct_withResponseFields_createsInstance(): void
    {
        // Act
        $storedCard = new StoredCard(
            href: 'https://api.example.com/stored-cards/sc123',
            id: 'sc123',
            createdAt: '2025-01-01T00:00:00Z',
            modifiedAt: '2025-01-02T00:00:00Z',
            deletedAt: '2025-01-31T23:59:59Z',
            merchant: 'https://api.example.com/merchants/m123',
            shopperInteraction: ShopperInteraction::ECOMMERCE,
            credentialOnFileType: CredentialOnFileType::RECURRING,
        );

        // Assert
        $this->assertSame('https://api.example.com/stored-cards/sc123', $storedCard->href);
        $this->assertSame('sc123', $storedCard->id);
        $this->assertSame('2025-01-01T00:00:00Z', $storedCard->createdAt);
        $this->assertSame('2025-01-02T00:00:00Z', $storedCard->modifiedAt);
        $this->assertSame('2025-01-31T23:59:59Z', $storedCard->deletedAt);
        $this->assertSame('https://api.example.com/merchants/m123', $storedCard->merchant);
        $this->assertSame(ShopperInteraction::ECOMMERCE, $storedCard->shopperInteraction);
        $this->assertSame(CredentialOnFileType::RECURRING, $storedCard->credentialOnFileType);
    }

    public function test_construct_withPaymentMethodLinks_createsInstance(): void
    {
        // Act
        $storedCard = new StoredCard(
            paymentMethodLink: 'https://api.example.com/payment-method-links/pml123',
            paymentMethodSession: 'https://api.example.com/payment-method-sessions/pms456',
        );

        // Assert
        $this->assertSame('https://api.example.com/payment-method-links/pml123', $storedCard->paymentMethodLink);
        $this->assertSame('https://api.example.com/payment-method-sessions/pms456', $storedCard->paymentMethodSession);
    }

    public function test_construct_withCustomReference_createsInstance(): void
    {
        // Act
        $storedCard = new StoredCard(
            customReference: 'customer-ref-12345',
        );

        // Assert
        $this->assertSame('customer-ref-12345', $storedCard->customReference);
    }

    public function test_construct_withCustomReferenceAt255Characters_isValid(): void
    {
        // Arrange
        $maxReference = str_repeat('a', 255);

        // Act
        $storedCard = new StoredCard(
            customReference: $maxReference,
        );

        // Assert
        $this->assertSame($maxReference, $storedCard->customReference);
    }

    public function test_construct_withCustomReferenceTooLong_throwsException(): void
    {
        // Arrange
        $tooLongReference = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom reference must not exceed 255 characters');

        // Act
        new StoredCard(customReference: $tooLongReference);
    }

    public function test_construct_withCustomFields_createsInstance(): void
    {
        // Arrange
        $customFields = [
            'subscriptionId' => 'sub-789',
            'tier' => 'gold',
        ];

        // Act
        $storedCard = new StoredCard(
            customFields: $customFields,
        );

        // Assert
        $this->assertSame($customFields, $storedCard->customFields);
    }

    public function test_construct_withCustomFieldKeyAt64Characters_isValid(): void
    {
        // Arrange
        $maxKey = str_repeat('a', 64);
        $customFields = [$maxKey => 'value'];

        // Act
        $storedCard = new StoredCard(
            customFields: $customFields,
        );

        // Assert
        $this->assertSame($customFields, $storedCard->customFields);
    }

    public function test_construct_withCustomFieldKeyTooLong_throwsException(): void
    {
        // Arrange
        $tooLongKey = str_repeat('a', 65);
        $customFields = [$tooLongKey => 'value'];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom field name must not exceed 64 characters');

        // Act
        new StoredCard(customFields: $customFields);
    }

    public function test_construct_withCustomFieldValueAt1024Characters_isValid(): void
    {
        // Arrange
        $maxValue = str_repeat('a', 1024);
        $customFields = ['key' => $maxValue];

        // Act
        $storedCard = new StoredCard(
            customFields: $customFields,
        );

        // Assert
        $this->assertSame($customFields, $storedCard->customFields);
    }

    public function test_construct_withCustomFieldValueTooLong_throwsException(): void
    {
        // Arrange
        $tooLongValue = str_repeat('a', 1025);
        $customFields = ['key' => $tooLongValue];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom field value must not exceed 1024 characters');

        // Act
        new StoredCard(customFields: $customFields);
    }

    public function test_construct_withAllCredentialOnFileTypes_createsInstance(): void
    {
        // Test each enum value
        $types = [
            CredentialOnFileType::NONE,
            CredentialOnFileType::RECURRING,
            CredentialOnFileType::SUBSCRIPTION,
            CredentialOnFileType::UNSCHEDULED,
        ];

        foreach ($types as $type) {
            $storedCard = new StoredCard(credentialOnFileType: $type);
            $this->assertSame($type, $storedCard->credentialOnFileType);
        }
    }

    public function test_construct_withAllShopperInteractionTypes_createsInstance(): void
    {
        // Test each enum value
        $interactions = [
            ShopperInteraction::ECOMMERCE,
            ShopperInteraction::MAIL_ORDER,
            ShopperInteraction::TELEPHONE_ORDER,
            ShopperInteraction::MERCHANT_INITIATED,
            ShopperInteraction::IN_PERSON,
        ];

        foreach ($interactions as $interaction) {
            $storedCard = new StoredCard(shopperInteraction: $interaction);
            $this->assertSame($interaction, $storedCard->shopperInteraction);
        }
    }

    public function test_fromData_withRequestData_createsInstance(): void
    {
        // Arrange
        $data = [
            'shopper' => 'https://api.example.com/shoppers/s789',
            'hostedCard' => 'https://api.example.com/hosted-cards/hc789',
            'customReference' => 'ref-abc',
        ];

        // Act
        $storedCard = StoredCard::fromData($data);

        // Assert
        $this->assertSame('https://api.example.com/shoppers/s789', $storedCard->shopper);
        $this->assertSame('https://api.example.com/hosted-cards/hc789', $storedCard->hostedCard);
        $this->assertSame('ref-abc', $storedCard->customReference);
    }

    public function test_fromData_withResponseData_createsInstance(): void
    {
        // Arrange
        $data = [
            'href' => 'https://api.example.com/stored-cards/sc456',
            'id' => 'sc456',
            'createdAt' => '2025-01-10T12:00:00Z',
            'merchant' => 'https://api.example.com/merchants/m789',
            'shopperInteraction' => 'ecommerce',
            'credentialOnFileType' => 'subscription',
            'card' => [
                'last4' => '9999',
                'bin' => '424242',
                'scheme' => 'Visa',
            ],
        ];

        // Act
        $storedCard = StoredCard::fromData($data);

        // Assert
        $this->assertSame('sc456', $storedCard->id);
        $this->assertSame(ShopperInteraction::ECOMMERCE, $storedCard->shopperInteraction);
        $this->assertSame(CredentialOnFileType::SUBSCRIPTION, $storedCard->credentialOnFileType);
        $this->assertInstanceOf(Card::class, $storedCard->card);
        $this->assertSame('9999', $storedCard->card->last4);
    }

    public function test_toData_withRequestData_returnsArray(): void
    {
        // Arrange
        $storedCard = new StoredCard(
            shopper: 'https://api.example.com/shoppers/s111',
            hostedCard: 'https://api.example.com/hosted-cards/hc111',
            customReference: 'ref-111',
        );

        // Act
        $array = $storedCard->toData();

        // Assert
        $this->assertSame([
            'shopper' => 'https://api.example.com/shoppers/s111',
            'hostedCard' => 'https://api.example.com/hosted-cards/hc111',
            'customReference' => 'ref-111',
        ], $array);
    }

    public function test_toData_withResponseData_returnsArray(): void
    {
        // Arrange
        $card = new Card(
            last4: '7777',
            bin: '515151',
        );
        $storedCard = new StoredCard(
            card: $card,
            href: 'https://api.example.com/stored-cards/sc789',
            id: 'sc789',
            createdAt: '2025-01-15T08:30:00Z',
            shopperInteraction: ShopperInteraction::MERCHANT_INITIATED,
            credentialOnFileType: CredentialOnFileType::UNSCHEDULED,
        );

        // Act
        $array = $storedCard->toData();

        // Assert
        $this->assertSame([
            'card' => [
                'last4' => '7777',
                'bin' => '515151',
            ],
            'shopperInteraction' => 'merchantInitiated',
            'credentialOnFileType' => 'unscheduled',
            'href' => 'https://api.example.com/stored-cards/sc789',
            'id' => 'sc789',
            'createdAt' => '2025-01-15T08:30:00Z',
        ], $array);
    }

    public function test_toData_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $storedCard = new StoredCard(
            id: 'sc999',
            customReference: 'ref-999',
        );

        // Act
        $array = $storedCard->toData();

        // Assert
        $this->assertSame([
            'id' => 'sc999',
            'customReference' => 'ref-999',
        ], $array);
        $this->assertArrayNotHasKey('href', $array);
        $this->assertArrayNotHasKey('createdAt', $array);
        $this->assertArrayNotHasKey('card', $array);
        $this->assertArrayNotHasKey('shopper', $array);
        $this->assertArrayNotHasKey('shopperInteraction', $array);
    }

    public function test_toData_withCustomFields_returnsArray(): void
    {
        // Arrange
        $customFields = [
            'membershipLevel' => 'platinum',
            'accountType' => 'business',
        ];
        $storedCard = new StoredCard(
            id: 'sc222',
            customFields: $customFields,
        );

        // Act
        $array = $storedCard->toData();

        // Assert
        $this->assertSame([
            'customFields' => $customFields,
            'id' => 'sc222',
        ], $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'card' => [
                'last4' => '3333',
                'bin' => '378282',
            ],
            'customFields' => [
                'subscriptionPlan' => 'premium',
                'autoRenew' => 'true',
            ],
            'shopperInteraction' => 'ecommerce',
            'credentialOnFileType' => 'recurring',
            'shopper' => 'https://api.example.com/shoppers/s999',
            'customReference' => 'order-final',
        ];

        // Act
        $storedCard = StoredCard::fromData($originalData);
        $resultData = $storedCard->toData();

        // Assert
        $this->assertSame($originalData, $resultData);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $storedCard = new StoredCard(id: 'sc123');

        // Act & Assert
        $reflection = new \ReflectionProperty($storedCard, 'id');
        $this->assertTrue($reflection->isReadOnly());

        $cardReflection = new \ReflectionProperty($storedCard, 'card');
        $this->assertTrue($cardReflection->isReadOnly());
    }
}
