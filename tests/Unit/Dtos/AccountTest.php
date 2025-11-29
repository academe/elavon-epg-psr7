<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Account;
use Academe\Elavon\Epg\Psr7\Dtos\AutoSettleAt;
use Academe\Elavon\Epg\Psr7\Dtos\ProcessorAccount;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Account DTO.
 */
class AccountTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        // Arrange & Act
        $account = new Account();

        // Assert
        $this->assertNull($account->href);
        $this->assertNull($account->id);
        $this->assertNull($account->createdAt);
        $this->assertNull($account->modifiedAt);
        $this->assertNull($account->merchant);
        $this->assertNull($account->processorAccounts);
        $this->assertNull($account->name);
        $this->assertNull($account->description);
        $this->assertNull($account->autoSettleAt);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $account = Account::fromData([
            'href' => 'https://api.eu.elavonpayments.com/accounts/f9g699w9v43r9gcp77y2bxq4rjcx',
            'id' => 'f9g699w9v43r9gcp77y2bxq4rjcx',
            'createdAt' => '2017-02-22T13:01:23.123Z',
            'modifiedAt' => '2017-02-22T13:01:33.567Z',
            'merchant' => 'https://api.eu.elavonpayments.com/merchants/6xxFwvM8BqmM6T6DcF3DyTB3',
            'processorAccounts' => [
                [
                    'id' => 'proc-123',
                    'legalName' => 'Test Processor',
                ],
            ],
            'name' => 'Sirius Corporation',
            'description' => 'A fintech company.',
            'tradeName' => 'Gringotts',
            'businessAddress' => '123 Main St, London',
            'businessPhone' => '+44 020 7946 0123',
            'businessEmail' => 'sales@gringotts.com',
            'businessWebsite' => 'www.gringotts.com',
            'planList' => 'https://api.eu.elavonpayments.com/plan-lists/f9g699w9v43r9gcp77y2bxq4rjcx',
            'logoUrl' => 'https://cf.media.eu.convergepay.com/logo.jpg',
            'autoSettleAt' => [
                'time' => '23:00',
                'timeZoneId' => 'Europe/Berlin',
            ]
        ]);

        // Assert
        $this->assertSame('https://api.eu.elavonpayments.com/accounts/f9g699w9v43r9gcp77y2bxq4rjcx', $account->href);
        $this->assertSame('f9g699w9v43r9gcp77y2bxq4rjcx', $account->id);
        $this->assertSame('2017-02-22T13:01:23.123Z', $account->createdAt);
        $this->assertSame('Sirius Corporation', $account->name);
        $this->assertSame('Gringotts', $account->tradeName);
        $this->assertInstanceOf(AutoSettleAt::class, $account->autoSettleAt);
        $this->assertSame('23:00', $account->autoSettleAt->time);
        $this->assertIsArray($account->processorAccounts);
        $this->assertCount(1, $account->processorAccounts);
        $this->assertContainsOnlyInstancesOf(ProcessorAccount::class, $account->processorAccounts);
    }

    public function test_construct_withAutoSettleAtObject_createsInstance(): void
    {
        // Arrange
        $autoSettleAt = new AutoSettleAt(
            time: '02:30',
            timeZoneId: 'America/New_York'
        );

        // Act
        $account = new Account(autoSettleAt: $autoSettleAt);

        // Assert
        $this->assertSame($autoSettleAt, $account->autoSettleAt);
    }

    public function test_construct_withProcessorAccountObjects_createsInstance(): void
    {
        // Arrange
        $processorAccount1 = new ProcessorAccount(
            id: 'proc-1',
            legalName: 'Processor One'
        );
        $processorAccount2 = new ProcessorAccount(
            id: 'proc-2',
            legalName: 'Processor Two'
        );

        // Act
        $account = new Account(
            processorAccounts: [$processorAccount1, $processorAccount2]
        );

        // Assert
        $this->assertCount(2, $account->processorAccounts);
        $this->assertSame($processorAccount1, $account->processorAccounts[0]);
        $this->assertSame($processorAccount2, $account->processorAccounts[1]);
    }

    public function test_fromData_withMinimalData_createsInstance(): void
    {
        // Arrange
        $data = [];

        // Act
        $account = Account::fromData($data);

        // Assert
        $this->assertNull($account->id);
        $this->assertNull($account->name);
    }

    public function test_fromData_withFullData_createsInstance(): void
    {
        // Arrange
        $data = [
            'href' => 'https://api.eu.elavonpayments.com/accounts/test123',
            'id' => 'test123',
            'createdAt' => '2023-01-15T10:00:00.000Z',
            'modifiedAt' => '2023-01-16T15:30:00.000Z',
            'merchant' => 'https://api.eu.elavonpayments.com/merchants/merchant456',
            'processorAccounts' => [
                [
                    'id' => 'proc-a',
                    'legalName' => 'Primary Processor',
                    'marketSegment' => 'retail',
                ],
                [
                    'id' => 'proc-b',
                    'legalName' => 'Secondary Processor',
                    'marketSegment' => 'restaurant',
                ],
            ],
            'name' => 'Test Account Name',
            'description' => 'This is a test account',
            'tradeName' => 'Test Trade Name',
            'businessAddress' => '456 Test Ave',
            'businessPhone' => '+1-555-1234',
            'businessEmail' => 'test@example.com',
            'businessWebsite' => 'www.example.com',
            'planList' => 'https://api.eu.elavonpayments.com/plan-lists/plan123',
            'salesTaxEntry' => 'tax-123',
            'signatureVerification' => 'sig-verify',
            'logoUrl' => 'https://example.com/logo.png',
            'autoSettleAt' => [
                'time' => '18:00',
                'timeZoneId' => 'Europe/London',
            ],
        ];

        // Act
        $account = Account::fromData($data);

        // Assert
        $this->assertSame('test123', $account->id);
        $this->assertSame('Test Account Name', $account->name);
        $this->assertSame('This is a test account', $account->description);
        $this->assertSame('Test Trade Name', $account->tradeName);
        $this->assertInstanceOf(AutoSettleAt::class, $account->autoSettleAt);
        $this->assertSame('18:00', $account->autoSettleAt->time);
        $this->assertSame('Europe/London', $account->autoSettleAt->timeZoneId);
        $this->assertIsArray($account->processorAccounts);
        $this->assertCount(2, $account->processorAccounts);
        $this->assertInstanceOf(ProcessorAccount::class, $account->processorAccounts[0]);
        $this->assertSame('proc-a', $account->processorAccounts[0]->id);
        $this->assertSame('Primary Processor', $account->processorAccounts[0]->legalName);
    }

    public function test_toData_withMinimalData_returnsArray(): void
    {
        // Arrange
        $account = new Account();

        // Act
        $array = $account->toData();

        // Assert
        $this->assertSame([], $array);
    }

    public function test_toData_withFullData_returnsArray(): void
    {
        // Arrange
        $account = Account::fromData([
            'id' => 'account-789',
            'name' => 'My Account',
            'description' => 'Test description',
            'autoSettleAt' => [
                'time' => '12:00',
                'timeZoneId' => 'UTC',
            ]
        ]);

        // Act
        $array = $account->toData();

        // Assert
        $this->assertArrayHasKey('id', $array);
        $this->assertSame('account-789', $array['id']);
        $this->assertSame('My Account', $array['name']);
        $this->assertSame('Test description', $array['description']);
        $this->assertArrayHasKey('autoSettleAt', $array);
        $this->assertSame('12:00', $array['autoSettleAt']['time']);
        $this->assertSame('UTC', $array['autoSettleAt']['timeZoneId']);
    }

    public function test_toData_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $account = new Account(
            id: 'acc-999',
            name: 'Simple Account'
        );

        // Act
        $array = $account->toData();

        // Assert
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('href', $array);
        $this->assertArrayNotHasKey('description', $array);
        $this->assertArrayNotHasKey('autoSettleAt', $array);
        $this->assertArrayNotHasKey('processorAccounts', $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'id' => 'round-trip-123',
            'name' => 'Round Trip Account',
            'description' => 'Testing round trip conversion',
            'tradeName' => 'RT Trade',
            'autoSettleAt' => [
                'time' => '15:30',
                'timeZoneId' => 'Asia/Tokyo',
            ],
        ];

        // Act
        $account = Account::fromData($originalData);
        $resultData = $account->toData();

        // Assert
        $this->assertSame($originalData['id'], $resultData['id']);
        $this->assertSame($originalData['name'], $resultData['name']);
        $this->assertSame($originalData['description'], $resultData['description']);
        $this->assertSame($originalData['tradeName'], $resultData['tradeName']);
        $this->assertSame($originalData['autoSettleAt'], $resultData['autoSettleAt']);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $account = new Account(id: 'test-account');

        // Act & Assert
        $reflection = new \ReflectionProperty($account, 'id');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($account, 'name');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($account, 'autoSettleAt');
        $this->assertTrue($reflection->isReadOnly());
    }
}
