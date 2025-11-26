<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Batch;
use Academe\Elavon\Epg\Psr7\Dtos\CountAndTotal;
use Academe\Elavon\Epg\Psr7\Enums\BatchState;
use Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Batch DTO.
 */
class BatchTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        // Arrange & Act
        $batch = new Batch();

        // Assert
        $this->assertNull($batch->href);
        $this->assertNull($batch->id);
        $this->assertNull($batch->createdAt);
        $this->assertNull($batch->modifiedAt);
        $this->assertNull($batch->merchant);
        $this->assertNull($batch->processorAccount);
        $this->assertNull($batch->terminal);
        $this->assertNull($batch->account);
        $this->assertNull($batch->processorReference);
        $this->assertNull($batch->state);
        $this->assertNull($batch->credits);
        $this->assertNull($batch->debits);
        $this->assertNull($batch->net);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $batch = new Batch(
            href: 'https://api.converge.eu.elavon.net/batches/wrKK4HcHCXcK3KkXwFRMXVjQ',
            id: 'wrKK4HcHCXcK3KkXwFRMXVjQ',
            createdAt: '2018-07-31T00:00:01.508Z',
            modifiedAt: '2018-07-31T00:00:12.074Z',
            merchant: 'https://api.converge.eu.elavon.net/merchants/XrDXRBh9YHxwqQTj2Cmq7j49',
            processorAccount: 'https://api.converge.eu.elavon.net/processor-accounts/KmvmfQJpCBJpXHyP2kgrK2hD',
            terminal: 'https://api.converge.eu.elavon.net/terminals/terminal123',
            account: 'https://api.converge.eu.elavon.net/accounts/account456',
            processorReference: '21280002',
            state: 'settled',
            credits: [
                'count' => 1,
                'total' => ['amount' => '100.00', 'currencyCode' => 'EUR'],
            ],
            debits: [
                'count' => 3,
                'total' => ['amount' => '22.00', 'currencyCode' => 'EUR'],
            ],
            net: [
                'count' => 4,
                'total' => ['amount' => '78.00', 'currencyCode' => 'EUR'],
            ]
        );

        // Assert
        $this->assertSame('https://api.converge.eu.elavon.net/batches/wrKK4HcHCXcK3KkXwFRMXVjQ', $batch->href);
        $this->assertSame('wrKK4HcHCXcK3KkXwFRMXVjQ', $batch->id);
        $this->assertSame('2018-07-31T00:00:01.508Z', $batch->createdAt);
        $this->assertSame('2018-07-31T00:00:12.074Z', $batch->modifiedAt);
        $this->assertSame('https://api.converge.eu.elavon.net/merchants/XrDXRBh9YHxwqQTj2Cmq7j49', $batch->merchant);
        $this->assertSame('https://api.converge.eu.elavon.net/processor-accounts/KmvmfQJpCBJpXHyP2kgrK2hD', $batch->processorAccount);
        $this->assertSame('https://api.converge.eu.elavon.net/terminals/terminal123', $batch->terminal);
        $this->assertSame('https://api.converge.eu.elavon.net/accounts/account456', $batch->account);
        $this->assertSame('21280002', $batch->processorReference);
        $this->assertSame(BatchState::SETTLED, $batch->state);
        $this->assertInstanceOf(CountAndTotal::class, $batch->credits);
        $this->assertInstanceOf(CountAndTotal::class, $batch->debits);
        $this->assertInstanceOf(CountAndTotal::class, $batch->net);
        $this->assertSame(1, $batch->credits->count);
        $this->assertSame(3, $batch->debits->count);
        $this->assertSame(4, $batch->net->count);
    }

    public function test_construct_withBatchStateEnum_createsInstance(): void
    {
        // Arrange
        $state = BatchState::SUBMITTED;

        // Act
        $batch = new Batch(state: $state);

        // Assert
        $this->assertSame($state, $batch->state);
    }

    public function test_construct_withCountAndTotalObjects_createsInstance(): void
    {
        // Arrange
        $credits = new CountAndTotal(
            count: 2,
            total: Money::USD(5000)
        );
        $debits = new CountAndTotal(
            count: 1,
            total: Money::USD(2500)
        );
        $net = new CountAndTotal(
            count: 3,
            total: Money::USD(2500)
        );

        // Act
        $batch = new Batch(
            credits: $credits,
            debits: $debits,
            net: $net
        );

        // Assert
        $this->assertSame($credits, $batch->credits);
        $this->assertSame($debits, $batch->debits);
        $this->assertSame($net, $batch->net);
    }

    public function test_fromData_withMinimalData_createsInstance(): void
    {
        // Arrange
        $data = [];

        // Act
        $batch = Batch::fromData($data);

        // Assert
        $this->assertNull($batch->id);
        $this->assertNull($batch->state);
        $this->assertNull($batch->credits);
    }

    public function test_fromData_withFullData_createsInstance(): void
    {
        // Arrange
        $data = [
            'href' => 'https://api.converge.eu.elavon.net/batches/DTTpbrDvwQQprQfg9dXr3gdP',
            'id' => 'DTTpbrDvwQQprQfg9dXr3gdP',
            'createdAt' => '2018-07-30T00:00:05.088Z',
            'modifiedAt' => '2018-07-30T00:00:13.073Z',
            'merchant' => 'https://api.converge.eu.elavon.net/merchants/XrDXRBh9YHxwqQTj2Cmq7j49',
            'processorAccount' => 'https://api.converge.eu.elavon.net/processor-accounts/KmvmfQJpCBJpXHyP2kgrK2hD',
            'terminal' => 'https://api.converge.eu.elavon.net/terminals/terminal789',
            'account' => 'https://api.converge.eu.elavon.net/accounts/account789',
            'processorReference' => '21180001',
            'state' => 'settled',
            'credits' => [
                'count' => 1,
                'total' => [
                    'amount' => '100.00',
                    'currencyCode' => 'EUR',
                ],
            ],
            'debits' => [
                'count' => 5,
                'total' => [
                    'amount' => '318.00',
                    'currencyCode' => 'EUR',
                ],
            ],
            'net' => [
                'count' => 6,
                'total' => [
                    'amount' => '218.00',
                    'currencyCode' => 'EUR',
                ],
            ],
        ];

        // Act
        $batch = Batch::fromData($data);

        // Assert
        $this->assertSame('DTTpbrDvwQQprQfg9dXr3gdP', $batch->id);
        $this->assertSame('21180001', $batch->processorReference);
        $this->assertSame(BatchState::SETTLED, $batch->state);
        $this->assertInstanceOf(CountAndTotal::class, $batch->credits);
        $this->assertSame(1, $batch->credits->count);
        $this->assertSame('10000', $batch->credits->total->getAmount());
        $this->assertInstanceOf(CountAndTotal::class, $batch->debits);
        $this->assertSame(5, $batch->debits->count);
        $this->assertSame('31800', $batch->debits->total->getAmount());
        $this->assertInstanceOf(CountAndTotal::class, $batch->net);
        $this->assertSame(6, $batch->net->count);
        $this->assertSame('21800', $batch->net->total->getAmount());
    }

    public function test_fromData_withAllBatchStates_createsInstances(): void
    {
        // Arrange & Act & Assert
        $batch = Batch::fromData(['state' => 'submitted']);
        $this->assertSame(BatchState::SUBMITTED, $batch->state);

        $batch = Batch::fromData(['state' => 'settled']);
        $this->assertSame(BatchState::SETTLED, $batch->state);

        $batch = Batch::fromData(['state' => 'rejected']);
        $this->assertSame(BatchState::REJECTED, $batch->state);

        $batch = Batch::fromData(['state' => 'failed']);
        $this->assertSame(BatchState::FAILED, $batch->state);

        $batch = Batch::fromData(['state' => 'unknown']);
        $this->assertSame(BatchState::UNKNOWN, $batch->state);
    }

    public function test_toData_withMinimalData_returnsArray(): void
    {
        // Arrange
        $batch = new Batch();

        // Act
        $array = $batch->toData();

        // Assert
        $this->assertSame([], $array);
    }

    public function test_toData_withFullData_returnsArray(): void
    {
        // Arrange
        $batch = new Batch(
            id: 'batch123',
            state: 'settled',
            processorReference: 'proc-ref-456',
            credits: [
                'count' => 2,
                'total' => ['amount' => '200.00', 'currencyCode' => 'USD'],
            ],
            debits: [
                'count' => 1,
                'total' => ['amount' => '50.00', 'currencyCode' => 'USD'],
            ],
            net: [
                'count' => 3,
                'total' => ['amount' => '150.00', 'currencyCode' => 'USD'],
            ]
        );

        // Act
        $array = $batch->toData();

        // Assert
        $this->assertArrayHasKey('id', $array);
        $this->assertSame('batch123', $array['id']);
        $this->assertSame('settled', $array['state']);
        $this->assertSame('proc-ref-456', $array['processorReference']);
        $this->assertArrayHasKey('credits', $array);
        $this->assertSame(2, $array['credits']['count']);
        $this->assertArrayHasKey('debits', $array);
        $this->assertSame(1, $array['debits']['count']);
        $this->assertArrayHasKey('net', $array);
        $this->assertSame(3, $array['net']['count']);
    }

    public function test_toData_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $batch = new Batch(
            id: 'batch999',
            state: 'submitted'
        );

        // Act
        $array = $batch->toData();

        // Assert
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('state', $array);
        $this->assertArrayNotHasKey('href', $array);
        $this->assertArrayNotHasKey('credits', $array);
        $this->assertArrayNotHasKey('debits', $array);
        $this->assertArrayNotHasKey('net', $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'id' => 'batch-abc',
            'state' => 'settled',
            'processorReference' => 'ref123',
            'credits' => [
                'count' => 10,
                'total' => ['amount' => '1000.00', 'currencyCode' => 'GBP'],
            ],
            'debits' => [
                'count' => 5,
                'total' => ['amount' => '500.00', 'currencyCode' => 'GBP'],
            ],
            'net' => [
                'count' => 15,
                'total' => ['amount' => '500.00', 'currencyCode' => 'GBP'],
            ],
        ];

        // Act
        $batch = Batch::fromData($originalData);
        $resultData = $batch->toData();

        // Assert - Check field by field as order may differ
        $this->assertSame($originalData['id'], $resultData['id']);
        $this->assertSame($originalData['state'], $resultData['state']);
        $this->assertSame($originalData['processorReference'], $resultData['processorReference']);
        // For nested objects, check the values rather than exact array equality due to key ordering
        $this->assertSame(10, $resultData['credits']['count']);
        $this->assertSame($originalData['credits']['total'], $resultData['credits']['total']);
        $this->assertSame(5, $resultData['debits']['count']);
        $this->assertSame($originalData['debits']['total'], $resultData['debits']['total']);
        $this->assertSame(15, $resultData['net']['count']);
        $this->assertSame($originalData['net']['total'], $resultData['net']['total']);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $batch = new Batch(id: 'test-batch');

        // Act & Assert
        $reflection = new \ReflectionProperty($batch, 'id');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($batch, 'state');
        $this->assertTrue($reflection->isReadOnly());
    }
}
